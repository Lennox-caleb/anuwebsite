<?php
/**
 * Stand-alone admin panel for ONE mini-site.
 *
 * Every mini-site has a tiny front door (<site>/admin/index.php) that loads its own config and then
 * includes this engine with $sa_unit set. The panel lives at the mini-site's own address, has its own
 * sign-in and its own accounts (table site_admins), and can only ever read or change rows that belong
 * to that mini-site (every query is scoped to the unit, ownership is re-checked on edit and delete).
 *
 * Needs from the calling site's config/db.php: db(), h(), BASE_URL, MAIN_SITE_URL, UPLOADS_URL, UNIT_SLUG.
 */
if (!isset($sa_unit) || !function_exists('db')) { http_response_code(500); exit('Admin is not configured for this site.'); }

define('SA_UNIT', $sa_unit);
define('SA_MAIN', dirname(__DIR__));                       // .../main-site
$__cu = SA_MAIN . '/includes/clean_urls.php';          // addresses without .php in the panel too
if (is_file($__cu)) { require_once $__cu; anu_clean_urls_boot(defined('SITE_ROOT') ? SITE_ROOT : dirname($_SERVER['SCRIPT_FILENAME'], 2)); }
if (!defined('UPLOADS_PATH')) define('UPLOADS_PATH', SA_MAIN . '/assets/uploads');
require_once SA_MAIN . '/admin/includes/upload.php';
require_once SA_MAIN . '/admin/includes/upload_doc.php';

if (session_status() === PHP_SESSION_NONE) session_start();
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');

$SA_REG  = include SA_MAIN . '/admin/includes/sites_registry.php';
$SA_SITE = $SA_REG[SA_UNIT] ?? ['label' => ucfirst(SA_UNIT), 'group' => '', 'url' => BASE_URL . '/index.php', 'content' => []];
define('SA_URL', BASE_URL . '/admin/index.php');

/* ---------- tiny helpers ---------- */
function sa_url(array $q = []): string { return SA_URL . ($q ? '?' . http_build_query($q) : ''); }
function sa_go(array $q = []): void { header('Location: ' . sa_url($q)); exit; }
function sa_flash(string $t, string $m): void { $_SESSION['sa_flash'] = [$t, $m]; }
function sa_csrf(): string { if (empty($_SESSION['sa_csrf'])) $_SESSION['sa_csrf'] = bin2hex(random_bytes(32)); return $_SESSION['sa_csrf']; }
function sa_csrf_ok(): bool { return isset($_POST['csrf'], $_SESSION['sa_csrf']) && hash_equals($_SESSION['sa_csrf'], (string)$_POST['csrf']); }
function sa_slug(string $t): string { $t = strtolower(trim(preg_replace('~[^\pL\d]+~u', '-', $t), '-')); $t = iconv('utf-8', 'us-ascii//TRANSLIT', $t) ?: $t; $t = trim(preg_replace('~[^-\w]+~', '', $t), '-'); return $t !== '' ? $t : 'item'; }
function sa_unique_slug(string $table, string $base, int $excludeId = 0): string {
    $slug = $base; $i = 2;
    while (true) {
        $s = db()->prepare("SELECT COUNT(*) FROM `$table` WHERE slug = ? AND id <> ?"); $s->execute([$slug, $excludeId]);
        if (!(int)$s->fetchColumn()) return $slug;
        $slug = $base . '-' . $i++;
    }
}

/* ---------- self-healing tables (no separate migration needed) ---------- */
try {
    db()->exec("CREATE TABLE IF NOT EXISTS site_admins (
        id INT AUTO_INCREMENT PRIMARY KEY, unit_slug VARCHAR(60) NOT NULL, full_name VARCHAR(120) NOT NULL,
        email VARCHAR(150) DEFAULT NULL, username VARCHAR(60) NOT NULL, password_hash VARCHAR(255) NOT NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1, last_login DATETIME DEFAULT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_unit_user (unit_slug, username)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    db()->exec("CREATE TABLE IF NOT EXISTS login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(100) NOT NULL, ip VARCHAR(45) NOT NULL,
        attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, KEY k_u (username, ip)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    if (db()->query("SHOW INDEX FROM site_admins WHERE Key_name = 'uq_username'")->fetch()) {   // older layout: username was unique across all sites
        db()->exec("ALTER TABLE site_admins DROP INDEX uq_username, ADD UNIQUE KEY uq_unit_user (unit_slug, username)");
    }
} catch (Throwable $e) { error_log('site-admin tables: ' . $e->getMessage()); }

/* ---------- authentication ---------- */
function sa_user(): ?array {
    static $u = false;
    if ($u !== false) return $u;
    $u = null;
    if (!empty($_SESSION['sa_id']) && ($_SESSION['sa_unit'] ?? '') === SA_UNIT) {
        $s = db()->prepare('SELECT id, full_name, username, unit_slug FROM site_admins WHERE id = ? AND unit_slug = ? AND is_active = 1');
        $s->execute([$_SESSION['sa_id'], SA_UNIT]);
        if ($r = $s->fetch()) $u = ['id' => (int)$r['id'], 'name' => $r['full_name'], 'username' => $r['username'], 'main' => false];
    }
    if (!$u && !empty($_SESSION['admin_id'])) {   // university web team signed in to the main admin
        $s = db()->prepare("SELECT id, full_name, username FROM admin_users WHERE id = ? AND is_active = 1 AND role IN ('super_admin','editor')");
        $s->execute([$_SESSION['admin_id']]);
        if ($r = $s->fetch()) $u = ['id' => (int)$r['id'], 'name' => $r['full_name'], 'username' => $r['username'], 'main' => true];
    }
    return $u;
}

$p = preg_replace('/[^a-z_]/', '', $_GET['p'] ?? 'dashboard');

if ($p === 'logout') { unset($_SESSION['sa_id'], $_SESSION['sa_unit']); sa_go(['p' => 'login']); }

if ($p === 'login') {
    $err = '';
    if (sa_user()) sa_go();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!sa_csrf_ok()) { $err = 'Session expired, please try again.'; }
        else {
            $un = trim($_POST['username'] ?? ''); $pw = (string)($_POST['password'] ?? '');
            $ip = substr($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', 0, 45); $key = SA_UNIT . ':' . mb_substr($un, 0, 80);
            $c = db()->prepare('SELECT COUNT(*) FROM login_attempts WHERE username = ? AND ip = ? AND attempted_at > (NOW() - INTERVAL 15 MINUTE)');
            $c->execute([$key, $ip]);
            if ((int)$c->fetchColumn() >= 5) { $err = 'Too many failed attempts. Please wait 15 minutes and try again.'; }
            else {
                $s = db()->prepare('SELECT * FROM site_admins WHERE username = ? AND unit_slug = ? AND is_active = 1 LIMIT 1');
                $s->execute([$un, SA_UNIT]); $r = $s->fetch();
                if ($r && password_verify($pw, $r['password_hash'])) {
                    session_regenerate_id(true);
                    $_SESSION['sa_id'] = (int)$r['id']; $_SESSION['sa_unit'] = SA_UNIT;
                    db()->prepare('UPDATE site_admins SET last_login = NOW() WHERE id = ?')->execute([$r['id']]);
                    db()->prepare('DELETE FROM login_attempts WHERE username = ? AND ip = ?')->execute([$key, $ip]);
                    sa_go();
                }
                $err = 'Invalid username or password.';
                db()->prepare('INSERT INTO login_attempts (username, ip) VALUES (?, ?)')->execute([$key, $ip]);
            }
        }
    }
    sa_login_page($err); exit;
}

if (!sa_user()) sa_go(['p' => 'login']);
$SA_ME = sa_user();

/* ---------- module definitions (everything is scoped to this unit) ---------- */
function sa_modules(): array {
    global $SA_SITE; $u = SA_UNIT;
    $m = [];
    $m['hero'] = ['label' => 'Hero Banner', 'icon' => 'bi-image', 'table' => 'sliders', 'scope' => ['unit_slug' => $u], 'order' => 'sort_order, id',
        'cols' => ['headline' => 'Headline', 'image' => 'Image', 'is_published' => 'Status'], 'add' => 'Add Hero',
        'help' => 'The big banner at the top of your homepage. Use an image, or a video link (YouTube or an uploaded MP4).',
        'fields' => [
            'headline' => ['Headline', 'text'], 'subtext' => ['Sub-text', 'text'],
            'image' => ['Hero image', 'image'], 'video_url' => ['Video link (YouTube or MP4 address, optional)', 'text'],
            'link_text' => ['Button 1 text', 'text'], 'link_url' => ['Button 1 link', 'text'],
            'link2_text' => ['Button 2 text', 'text'], 'link2_url' => ['Button 2 link', 'text'],
            'sort_order' => ['Order (1 = first)', 'number', 1], 'is_published' => ['Show on the site', 'check', 1]]];
    $m['staff'] = ['label' => 'Staff & Team', 'icon' => 'bi-people', 'table' => 'staff', 'scope' => ['unit_slug' => $u], 'order' => 'sort_order, full_name',
        'cols' => ['full_name' => 'Name', 'title' => 'Title', 'is_published' => 'Status'], 'add' => 'Add Person',
        'fields' => [
            'full_name' => ['Full name', 'text', '', true], 'title' => ['Title / role', 'text'], 'email' => ['Email', 'text'],
            'bio' => ['Short bio', 'textarea'], 'photo' => ['Photo', 'image'],
            'sort_order' => ['Order', 'number', 0], 'is_published' => ['Show on the site', 'check', 1]]];
    $m['news'] = ['label' => 'News & Updates', 'icon' => 'bi-newspaper', 'table' => 'news', 'scope' => ['unit_slug' => $u], 'order' => 'published_at DESC, id DESC',
        'cols' => ['title' => 'Title', 'published_at' => 'Date', 'is_published' => 'Status'], 'add' => 'New Article', 'slug' => 'title',
        'fields' => [
            'title' => ['Title', 'text', '', true], 'category' => ['Category', 'text'], 'excerpt' => ['Short summary', 'textarea'],
            'body' => ['Article text (blank line = new paragraph)', 'textarea', '', false, 12], 'cover_image' => ['Cover image', 'image'],
            'author' => ['Author', 'text', 'ANU Communications'], 'published_at' => ['Date', 'date', date('Y-m-d'), true],
            'is_featured' => ['Feature this article', 'check', 0], 'is_published' => ['Show on the site', 'check', 1]]];
    $m['events'] = ['label' => 'Events', 'icon' => 'bi-calendar-event', 'table' => 'events', 'scope' => ['unit_slug' => $u], 'order' => 'start_datetime DESC',
        'cols' => ['title' => 'Event', 'start_datetime' => 'Starts', 'is_published' => 'Status'], 'add' => 'New Event', 'slug' => 'title',
        'fields' => [
            'title' => ['Event title', 'text', '', true], 'category' => ['Category', 'text'], 'description' => ['Description', 'textarea', '', false, 7],
            'location' => ['Location', 'text'], 'start_datetime' => ['Starts', 'datetime', '', true], 'end_datetime' => ['Ends (optional)', 'datetime'],
            'cover_image' => ['Cover image', 'image'], 'is_published' => ['Show on the site', 'check', 1]]];
    $m['announcements'] = ['label' => 'Pop-up Announcement', 'icon' => 'bi-megaphone', 'table' => 'announcements', 'scope' => ['target' => $u], 'order' => 'id DESC',
        'cols' => ['title' => 'Title', 'is_active' => 'Status'], 'add' => 'New Announcement',
        'help' => 'A pop-up that appears when visitors open your site. Only the most recent active one is shown. It replaces any university-wide announcement on your site while it is active. Use a full address (https://...) for the button link, or a page of your site such as /contact.',
        'fields' => [
            'title' => ['Title', 'text', '', true], 'message' => ['Message', 'textarea', '', true, 5], 'image' => ['Image (optional)', 'image'],
            'cta_text' => ['Button text (optional)', 'text', 'Learn more'], 'cta_url' => ['Button link (optional)', 'text'],
            'starts_at' => ['Show from (optional)', 'datetime'], 'ends_at' => ['Show until (optional)', 'datetime'],
            'is_active' => ['Active (visible on the site now)', 'check', 1]]];
    if (!empty($SA_SITE['has_departments'])) {
        $m['departments'] = ['label' => 'Departments', 'icon' => 'bi-diagram-3', 'table' => 'departments', 'scope' => ['unit_slug' => $u], 'order' => 'sort_order, name',
            'cols' => ['name' => 'Department', 'is_published' => 'Status'], 'add' => 'Add Department',
            'fields' => ['name' => ['Department name', 'text', '', true], 'summary' => ['Summary', 'textarea'],
                'sort_order' => ['Order', 'number', 0], 'is_published' => ['Show on the site', 'check', 1]]];
    }
    if (!empty($SA_SITE['has_programmes'])) {
        $pf = $SA_SITE['programme_filter'] ?? null; $scope = ['unit_slug' => $u]; $extra = [];
        if ($pf && ($pf['type'] ?? '') === 'school_slug') {
            $s = db()->prepare('SELECT id FROM schools WHERE slug = ?'); $s->execute([$pf['value']]); $sid = (int)$s->fetchColumn();
            if ($sid) { $scope = ['school_id' => $sid]; }
        } elseif ($pf) { $scope = ['unit_slug' => $pf['value']]; }
        $m['programmes'] = ['label' => 'Programmes', 'icon' => 'bi-mortarboard', 'table' => 'programmes', 'scope' => $scope, 'order' => 'level, title',
            'cols' => ['title' => 'Programme', 'level' => 'Level', 'is_published' => 'Status'], 'add' => 'New Programme', 'slug' => 'title',
            'fields' => [
                'title' => ['Programme title', 'text', '', true],
                'level' => ['Level', 'select', 'Undergraduate', true, ['PhD', 'Masters', 'Undergraduate', 'Diploma', 'Certificate', 'Other']],
                'duration' => ['Duration', 'text'], 'mode' => ['Mode of study', 'text', 'Full-time / Part-time / Online'], 'intake' => ['Intake', 'text'],
                'overview' => ['Overview', 'textarea', '', false, 6], 'requirements' => ['Entry requirements', 'textarea', '', false, 5],
                'fee_structure' => ['Fee structure (text)', 'textarea', '', false, 4], 'course_units' => ['Course units', 'textarea', '', false, 6],
                'image' => ['Programme image', 'image'], 'fee_structure_file' => ['Fee structure file (PDF/Word/Excel)', 'file'], 'planner_file' => ['Course planner file', 'file'],
                'is_featured' => ['Feature on the homepage', 'check', 0], 'is_published' => ['Show on the site', 'check', 1]]];
    }
    if (SA_UNIT === 'chaplaincy') {
        $m['ministries'] = ['label' => 'Ministries', 'icon' => 'bi-heart', 'table' => 'chaplaincy_programs', 'scope' => [], 'order' => 'sort_order, id',
            'cols' => ['title' => 'Ministry', 'is_published' => 'Status'], 'add' => 'Add Ministry',
            'help' => 'Tip: start the description with the day and time on its own line, for example "Monday | 4:00 pm - 6:00 pm", then the description on the next line.',
            'fields' => ['title' => ['Ministry name', 'text', '', true], 'description' => ['Description', 'textarea', '', false, 5],
                'icon' => ['Icon name (optional, e.g. bi-heart)', 'text', 'bi-heart'], 'sort_order' => ['Order', 'number', 0], 'is_published' => ['Show on the site', 'check', 1]]];
    }
    if (SA_UNIT === 'research') {
        $m['publications'] = ['label' => 'Publications', 'icon' => 'bi-journal-text', 'table' => 'research_publications', 'scope' => [], 'order' => 'publication_year DESC, title',
            'cols' => ['title' => 'Title', 'publication_year' => 'Year', 'is_published' => 'Status'], 'add' => 'New Publication',
            'fields' => ['title' => ['Title', 'text', '', true], 'authors' => ['Authors', 'text'], 'publication_year' => ['Year', 'number', date('Y')],
                'summary' => ['Summary', 'textarea', '', false, 5], 'external_link' => ['External link (optional)', 'text'],
                'file_name' => ['Publication file (PDF/Word)', 'file'], 'is_published' => ['Show on the site', 'check', 1]]];
    }
    if (SA_UNIT === 'library') {
        $m['resources'] = ['label' => 'Resources', 'icon' => 'bi-collection', 'table' => 'resources', 'scope' => [], 'order' => 'category, title',
            'cols' => ['title' => 'Title', 'category' => 'Category', 'is_published' => 'Status'], 'add' => 'Add Resource',
            'fields' => ['title' => ['Title', 'text', '', true], 'description' => ['Description', 'text'],
                'category' => ['Category', 'select', 'Other', true, ['Policies & Guidelines', 'Handbooks & Forms', 'Past Papers', 'Financial Aid Forms', 'Publications', 'Other']],
                'file_name' => ['File (PDF/Word/Excel)', 'file', '', true, 'file_size'], 'is_published' => ['Show on the site', 'check', 1]]];
    }
    return $m;
}
require_once __DIR__ . '/../includes/announcements_lib.php'; anu_ann_ensure();
$MODS = sa_modules();

/* ---------- generic CRUD ---------- */
function sa_scope_sql(array $mod, string $prefix = ' AND '): array {
    $sql = ''; $par = [];
    foreach ($mod['scope'] as $c => $v) { $sql .= $prefix . "`$c` = ?"; $par[] = $v; }
    return [$sql, $par];
}
function sa_row(array $mod, int $id): ?array {
    [$sc, $sp] = sa_scope_sql($mod);
    $s = db()->prepare("SELECT * FROM `{$mod['table']}` WHERE id = ?$sc"); $s->execute(array_merge([$id], $sp));
    return $s->fetch() ?: null;
}

function sa_save(array $mod, int $id, ?array &$data, array &$errors): bool {
    $row = $id ? sa_row($mod, $id) : null;
    if ($id && !$row) { $errors[] = 'That item was not found.'; return false; }
    $vals = [];
    foreach ($mod['fields'] as $k => $f) {
        $type = $f[1]; $req = !empty($f[3]);
        if ($type === 'check') { $vals[$k] = isset($_POST[$k]) ? 1 : 0; continue; }
        if ($type === 'image' || $type === 'file') {
            $cur = $row[$k] ?? ''; $new = null;
            try {
                if ($type === 'image') { $new = handle_image_upload($k); }
                else { $up = handle_document_upload($k); if ($up) { $new = $up['filename']; if (!empty($f[4])) $vals[$f[4]] = $up['size']; } }
            } catch (RuntimeException $e) { $errors[] = $f[0] . ': ' . $e->getMessage(); }
            if (!empty($_POST['remove_' . $k]) && !$new && !$req) $new = '';
            $vals[$k] = ($new === null) ? $cur : $new;
            if ($req && $vals[$k] === '') $errors[] = $f[0] . ' is required.';
            continue;
        }
        $v = trim((string)($_POST[$k] ?? ''));
        if ($type === 'number') $v = ($v === '') ? (($f[2] ?? '') === '' ? null : (int)$f[2]) : (int)$v;
        if ($type === 'date') { if ($v !== '' && !strtotime($v)) $errors[] = $f[0] . ' is not a valid date.'; $v = $v === '' ? null : date('Y-m-d', strtotime($v)); }
        if ($type === 'datetime') { if ($v !== '' && !strtotime($v)) $errors[] = $f[0] . ' is not a valid date and time.'; $v = $v === '' ? null : date('Y-m-d H:i:s', strtotime($v)); }
        if ($type === 'select' && !in_array($v, $f[4], true)) $v = $f[2];
        if ($req && ($v === '' || $v === null)) $errors[] = $f[0] . ' is required.';
        $vals[$k] = $v;
    }
    $data = array_merge($row ?? [], $vals);
    if ($errors) return false;
    if (!empty($mod['slug'])) {
        $base = sa_slug((string)$vals[$mod['slug']]);
        if (!$row || ($row['slug'] ?? '') === '' || sa_slug((string)$row[$mod['slug']]) !== $base) $vals['slug'] = sa_unique_slug($mod['table'], $base, $id);
    }
    try {
        if ($row) {
            $set = implode(', ', array_map(fn($c) => "`$c` = ?", array_keys($vals)));
            [$sc, $sp] = sa_scope_sql($mod);
            db()->prepare("UPDATE `{$mod['table']}` SET $set WHERE id = ?$sc")->execute(array_merge(array_values($vals), [$id], $sp));
        } else {
            $all = $vals + $mod['scope'];
            $cols = implode(', ', array_map(fn($c) => "`$c`", array_keys($all)));
            db()->prepare("INSERT INTO `{$mod['table']}` ($cols) VALUES (" . implode(',', array_fill(0, count($all), '?')) . ")")->execute(array_values($all));
        }
    } catch (Throwable $e) {
        error_log('site-admin save: ' . $e->getMessage());
        $errors[] = stripos($e->getMessage(), 'Data too long') !== false ? 'One of the fields is too long. Please shorten it.' : 'Could not save. Please check the details and try again.';
        return false;
    }
    return true;
}

/* ---------- special pages: leader message, page content, users, account ---------- */
function sa_leader_row(): ?array {
    global $SA_SITE; if (empty($SA_SITE['leader_slug'])) return null;
    $s = db()->prepare('SELECT * FROM staff WHERE unit_slug = ? ORDER BY id LIMIT 1'); $s->execute([$SA_SITE['leader_slug']]);
    return $s->fetch() ?: null;
}

/* ---------- route ---------- */
$errors = []; $data = null; $out = '';
$mod = $MODS[$p] ?? null;
$a = $_GET['a'] ?? 'list'; $id = (int)($_GET['id'] ?? 0);
$pageTitle = 'Dashboard';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !sa_csrf_ok()) { $errors[] = 'Your session expired. Please try again.'; $_POST = []; }
$isPost = $_SERVER['REQUEST_METHOD'] === 'POST' && !$errors;

if ($mod) {
    $pageTitle = $mod['label'];
    if ($a === 'delete' && $isPost && $id) {
        if (sa_row($mod, $id)) { [$sc, $sp] = sa_scope_sql($mod); db()->prepare("DELETE FROM `{$mod['table']}` WHERE id = ?$sc")->execute(array_merge([$id], $sp)); sa_flash('success', 'Deleted.'); }
        sa_go(['p' => $p]);
    }
    if (($a === 'edit' || $a === 'new') && $isPost) {
        if (sa_save($mod, $a === 'edit' ? $id : 0, $data, $errors)) { sa_flash('success', 'Saved. Changes are live on the site.'); sa_go(['p' => $p]); }
    }
}

if ($p === 'leader') {
    $pageTitle = $SA_SITE['leader_label'] ?? 'Leader message';
    $row = sa_leader_row();
    if ($isPost) {
        $v = ['full_name' => trim($_POST['full_name'] ?? ''), 'title' => trim($_POST['title'] ?? ''), 'bio' => trim($_POST['bio'] ?? ''), 'email' => trim($_POST['email'] ?? ''), 'is_published' => isset($_POST['is_published']) ? 1 : 0];
        try { $ph = handle_image_upload('photo'); } catch (RuntimeException $e) { $errors[] = $e->getMessage(); $ph = null; }
        if (!empty($_POST['remove_photo']) && !$ph) $ph = '';
        $v['photo'] = $ph === null ? ($row['photo'] ?? null) : $ph;
        if (!$errors) {
            if ($row) { db()->prepare('UPDATE staff SET full_name=?, title=?, bio=?, email=?, is_published=?, photo=? WHERE id=?')->execute([$v['full_name'], $v['title'], $v['bio'], $v['email'], $v['is_published'], $v['photo'], $row['id']]); }
            else { db()->prepare('INSERT INTO staff (unit_slug, full_name, title, bio, email, is_published, photo) VALUES (?,?,?,?,?,?,?)')->execute([$SA_SITE['leader_slug'], $v['full_name'], $v['title'], $v['bio'], $v['email'], $v['is_published'], $v['photo']]); }
            sa_flash('success', 'Saved. Changes are live on the site.'); sa_go(['p' => 'leader']);
        }
        $row = array_merge($row ?? [], $v);
    }
}

if ($p === 'content') {
    $g = $_GET['g'] ?? ''; $grp = $SA_SITE['content'][$g] ?? null;
    if ($grp) {
        $pageTitle = $grp['label'];
        if ($isPost) {
            $st = db()->prepare('INSERT INTO site_content (unit_slug, content_key, content_value) VALUES (?,?,?) ON DUPLICATE KEY UPDATE content_value = VALUES(content_value)');
            foreach ($grp['fields'] as $k => $f) $st->execute([SA_UNIT, $k, trim((string)($_POST[$k] ?? ''))]);
            sa_flash('success', 'Saved. Changes are live on the site.'); sa_go(['p' => 'content', 'g' => $g]);
        }
    } else { $pageTitle = 'Page Content'; }
}

if ($p === 'users') {
    $pageTitle = 'Admin Accounts';
    if ($isPost) {
        $act = $_POST['act'] ?? '';
        if ($act === 'add') {
            $un = strtolower(trim($_POST['username'] ?? '')); $pw = (string)($_POST['password'] ?? '');
            if (!preg_match('/^[a-z0-9._-]{3,40}$/', $un)) $errors[] = 'Username: 3 to 40 letters, numbers, dot, dash or underscore.';
            if (strlen($pw) < 10) $errors[] = 'Password must be at least 10 characters.';
            if (trim($_POST['full_name'] ?? '') === '') $errors[] = 'Full name is required.';
            if (!$errors) {
                try { db()->prepare('INSERT INTO site_admins (unit_slug, full_name, email, username, password_hash) VALUES (?,?,?,?,?)')->execute([SA_UNIT, trim($_POST['full_name']), trim($_POST['email'] ?? ''), $un, password_hash($pw, PASSWORD_DEFAULT)]); sa_flash('success', 'Account created.'); sa_go(['p' => 'users']); }
                catch (Throwable $e) { $errors[] = 'That username is already taken.'; }
            }
        } elseif (in_array($act, ['toggle', 'reset'], true)) {
            $uid = (int)($_POST['uid'] ?? 0);
            $own = db()->prepare('SELECT * FROM site_admins WHERE id = ? AND unit_slug = ?'); $own->execute([$uid, SA_UNIT]); $tr = $own->fetch();
            if ($tr) {
                if ($act === 'toggle') {
                    if (!$SA_ME['main'] && $uid === $SA_ME['id']) { $errors[] = 'You cannot deactivate your own account.'; }
                    else { db()->prepare('UPDATE site_admins SET is_active = 1 - is_active WHERE id = ?')->execute([$uid]); sa_flash('success', 'Updated.'); sa_go(['p' => 'users']); }
                } else {
                    $pw = (string)($_POST['password'] ?? '');
                    if (strlen($pw) < 10) $errors[] = 'Password must be at least 10 characters.';
                    else { db()->prepare('UPDATE site_admins SET password_hash = ? WHERE id = ?')->execute([password_hash($pw, PASSWORD_DEFAULT), $uid]); sa_flash('success', 'Password changed.'); sa_go(['p' => 'users']); }
                }
            }
        }
    }
}

if ($p === 'account') {
    $pageTitle = 'My Account';
    if ($isPost && !$SA_ME['main']) {
        $s = db()->prepare('SELECT password_hash FROM site_admins WHERE id = ?'); $s->execute([$SA_ME['id']]); $hash = $s->fetchColumn();
        $new = (string)($_POST['new_password'] ?? '');
        if (!password_verify((string)($_POST['current_password'] ?? ''), (string)$hash)) $errors[] = 'Your current password is not correct.';
        elseif (strlen($new) < 10) $errors[] = 'The new password must be at least 10 characters.';
        elseif ($new !== (string)($_POST['new_password2'] ?? '')) $errors[] = 'The two new passwords do not match.';
        else { db()->prepare('UPDATE site_admins SET password_hash = ? WHERE id = ?')->execute([password_hash($new, PASSWORD_DEFAULT), $SA_ME['id']]); sa_flash('success', 'Password changed.'); sa_go(['p' => 'account']); }
    }
}

/* ---------- render ---------- */
function sa_field(string $k, array $f, array $d): void {
    [$label, $type] = $f; $val = $d[$k] ?? ($f[2] ?? ''); $req = !empty($f[3]);
    echo '<div class="mb-3">';
    if ($type === 'check') {
        echo '<div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="f_' . h($k) . '" name="' . h($k) . '" value="1"' . ($val ? ' checked' : '') . '><label class="form-check-label" for="f_' . h($k) . '">' . h($label) . '</label></div></div>'; return;
    }
    echo '<label class="form-label" for="f_' . h($k) . '">' . h($label) . ($req ? ' <span class="text-danger">*</span>' : '') . '</label>';
    if ($type === 'textarea') echo '<textarea class="form-control" id="f_' . h($k) . '" name="' . h($k) . '" rows="' . (int)($f[4] ?? 4) . '">' . h((string)$val) . '</textarea>';
    elseif ($type === 'select') { echo '<select class="form-select" id="f_' . h($k) . '" name="' . h($k) . '">'; foreach ($f[4] as $o) echo '<option' . ($o === $val ? ' selected' : '') . '>' . h($o) . '</option>'; echo '</select>'; }
    elseif ($type === 'image') {
        if ($val) echo '<div class="mb-2"><img src="' . h(UPLOADS_URL . '/' . $val) . '" alt="" style="max-height:110px;border-radius:8px;border:1px solid #ddd"></div>';
        echo '<input class="form-control" type="file" id="f_' . h($k) . '" name="' . h($k) . '" accept="image/jpeg,image/png,image/webp"><div class="form-text">JPG, PNG or WEBP, up to 4 MB.</div>';
        if ($val && !$req) echo '<div class="form-check mt-1"><input class="form-check-input" type="checkbox" name="remove_' . h($k) . '" value="1" id="rm_' . h($k) . '"><label class="form-check-label small" for="rm_' . h($k) . '">Remove current image</label></div>';
    } elseif ($type === 'file') {
        if ($val) echo '<div class="small text-muted mb-1"><i class="bi bi-file-earmark"></i> Current file: ' . h($val) . '</div>';
        echo '<input class="form-control" type="file" id="f_' . h($k) . '" name="' . h($k) . '" accept=".pdf,.doc,.docx,.xls,.xlsx"><div class="form-text">PDF, Word or Excel.' . ($val ? ' Choose a new file only to replace it.' : '') . '</div>';
        if ($val && !$req) echo '<div class="form-check mt-1"><input class="form-check-input" type="checkbox" name="remove_' . h($k) . '" value="1" id="rm_' . h($k) . '"><label class="form-check-label small" for="rm_' . h($k) . '">Remove current file</label></div>';
    } else {
        $t = ['number' => 'number', 'date' => 'date', 'datetime' => 'datetime-local'][$type] ?? 'text';
        if ($type === 'datetime' && $val) $val = date('Y-m-d\TH:i', strtotime((string)$val));
        echo '<input class="form-control" type="' . $t . '" id="f_' . h($k) . '" name="' . h($k) . '" value="' . h((string)$val) . '"' . ($req ? ' required' : '') . '>';
    }
    echo '</div>';
}

function sa_head(string $title): void {
    global $SA_SITE, $SA_ME, $MODS, $p, $errors;
    $flash = $_SESSION['sa_flash'] ?? null; unset($_SESSION['sa_flash']);
    $nav = [['dashboard', 'Dashboard', 'bi-speedometer2']];
    foreach (['hero', 'staff', 'news', 'events', 'departments', 'programmes', 'ministries', 'publications', 'resources', 'announcements'] as $k) if (isset($MODS[$k])) $nav[] = [$k, $MODS[$k]['label'], $MODS[$k]['icon']];
    if (!empty($SA_SITE['leader_slug'])) $nav[] = ['leader', $SA_SITE['leader_label'], 'bi-person-badge'];
    if (!empty($SA_SITE['content'])) $nav[] = ['content', 'Page Content', 'bi-pencil-square'];
    $nav[] = ['users', 'Admin Accounts', 'bi-person-gear']; $nav[] = ['account', 'My Account', 'bi-key'];
    ?><!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow">
<title><?= h($title) ?> | <?= h($SA_SITE['label']) ?> Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">
<style><?= sa_css() ?></style></head><body>
<div class="sa-shell">
<aside class="sa-side"><div class="sa-brand"><span class="sa-sub">Site admin</span><span class="sa-name"><?= h($SA_SITE['label']) ?></span></div>
<nav><?php foreach ($nav as [$k, $l, $ic]): ?><a href="<?= h($k === 'dashboard' ? sa_url() : sa_url(['p' => $k])) ?>" class="<?= $p === $k || ($k === 'dashboard' && $p === '') ? 'active' : '' ?>"><i class="bi <?= h($ic) ?>"></i><?= h($l) ?></a><?php endforeach; ?></nav>
<div class="sa-foot"><a href="<?= h(BASE_URL . '/index.php') ?>" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i>View site</a>
<?php if (empty($SA_ME['main'])): ?><a href="<?= h(sa_url(['p' => 'logout'])) ?>"><i class="bi bi-box-arrow-left"></i>Sign out</a><?php else: ?><a href="<?= h(MAIN_SITE_URL . '/admin/dashboard.php') ?>"><i class="bi bi-arrow-left"></i>Main admin</a><?php endif; ?></div></aside>
<main class="sa-main"><div class="sa-top"><h1><?= h($title) ?></h1><span class="sa-pill"><i class="bi bi-person-circle"></i> <?= h($SA_ME['name']) ?><?= !empty($SA_ME['main']) ? ' (university admin)' : '' ?></span></div>
<?php if ($flash): ?><div class="alert alert-<?= h($flash[0]) ?>"><?= h($flash[1]) ?></div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-danger mb-3"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<?php }
function sa_foot(): void { echo '</main></div></body></html>'; }

function sa_css(): string { return <<<'CSS'
:root{--gold:#FAC823;--red:#FF0000;--ink:#111}*{box-sizing:border-box}body{margin:0;font-family:Roboto,Arial,sans-serif;background:#F4F2ED;color:#222}
.sa-shell{display:flex;min-height:100vh}.sa-side{width:250px;background:#000;color:#fff;flex-shrink:0;position:sticky;top:0;height:100vh;overflow-y:auto;display:flex;flex-direction:column}
.sa-brand{padding:1.4rem 1.2rem;border-bottom:1px solid rgba(255,255,255,.15)}.sa-sub{display:block;font-size:.68rem;letter-spacing:.08em;text-transform:uppercase;color:var(--gold);font-weight:700}.sa-name{display:block;font-size:1.05rem;font-weight:700;line-height:1.25;margin-top:.2rem}
.sa-side nav{padding:.6rem 0;flex:1}.sa-side a{color:rgba(255,255,255,.8);text-decoration:none;display:flex;align-items:center;gap:.6rem;padding:.6rem 1.2rem;font-size:.92rem;margin:1px 8px;border-radius:6px}
.sa-side a:hover{background:rgba(255,255,255,.1);color:#fff}.sa-side a.active{background:var(--gold);color:#111;font-weight:700}.sa-foot{border-top:1px solid rgba(255,255,255,.15);padding:.5rem 0}
.sa-main{flex:1;padding:1.8rem 2.2rem;min-width:0}.sa-top{display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;background:#fff;border-radius:10px;padding:1rem 1.4rem;margin-bottom:1.4rem;box-shadow:0 2px 8px rgba(0,0,0,.05)}
.sa-top h1{font-size:1.3rem;font-weight:700;margin:0}.sa-pill{background:#f1efe9;border-radius:20px;padding:.35rem .9rem;font-size:.82rem}
.sa-card{background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,.05);padding:1.4rem}.btn-sa{background:var(--red);color:#fff;border:0;font-weight:700}.btn-sa:hover{background:#000;color:#fff}
.btn-sa-o{border:1px solid #000;color:#000;background:#fff;font-weight:500}.btn-sa-o:hover{background:#000;color:#fff}
.sa-tile{display:block;background:#fff;border-radius:10px;padding:1.2rem;text-decoration:none;color:#111;border-top:3px solid var(--gold);box-shadow:0 2px 8px rgba(0,0,0,.05);height:100%;transition:transform .15s,box-shadow .15s}
.sa-tile:hover{transform:translateY(-3px);box-shadow:0 10px 22px rgba(0,0,0,.1);color:#111}.sa-tile i{font-size:1.5rem;color:var(--red)}.sa-tile b{display:block;margin-top:.4rem}.sa-tile span{font-size:.85rem;color:#666}
.table>:not(caption)>*>*{padding:.75rem 1rem;vertical-align:middle}.sa-thumb{height:40px;border-radius:6px}
@media(max-width:767px){.sa-shell{flex-direction:column}.sa-side{width:100%;height:auto;position:static}.sa-side nav{display:flex;flex-wrap:wrap;padding:.4rem}.sa-main{padding:1rem}}
CSS; }

function sa_login_page(string $err): void {
    global $SA_SITE; ?><!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow">
<title>Sign in | <?= h($SA_SITE['label']) ?> Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">
<style>*{box-sizing:border-box}body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;font-family:Roboto,Arial,sans-serif;background:linear-gradient(135deg,#000 0%,#2a1a00 100%);padding:1rem}
.box{background:#fff;border-radius:16px;padding:2.2rem;width:100%;max-width:400px;border-top:5px solid #FAC823;box-shadow:0 20px 60px rgba(0,0,0,.4)}.eyebrow{font-size:.72rem;letter-spacing:.1em;text-transform:uppercase;color:#FF0000;font-weight:700}
h1{margin:.3rem 0 1.4rem;font-size:1.5rem}label{display:block;font-size:.85rem;font-weight:500;margin:0 0 .3rem}input{width:100%;padding:.75rem .9rem;border:1px solid #d5d5db;border-radius:8px;font:inherit;margin-bottom:1rem}
input:focus{outline:2px solid #FAC823;border-color:#FAC823}button{width:100%;padding:.85rem;border:0;border-radius:8px;background:#FF0000;color:#fff;font:inherit;font-weight:700;cursor:pointer}button:hover{background:#000}
.err{background:#fde8e8;color:#9b1c1c;padding:.7rem .9rem;border-radius:8px;margin-bottom:1rem;font-size:.9rem}.back{display:block;text-align:center;margin-top:1rem;font-size:.85rem;color:#555;text-decoration:none}</style></head><body>
<form class="box" method="post" action="<?= h(sa_url(['p' => 'login'])) ?>"><div class="eyebrow">Site admin</div><h1><?= h($SA_SITE['label']) ?></h1>
<?php if ($err): ?><div class="err"><?= h($err) ?></div><?php endif; ?>
<input type="hidden" name="csrf" value="<?= h(sa_csrf()) ?>"><label for="u">Username</label><input id="u" name="username" autocomplete="username" required autofocus>
<label for="pw">Password</label><input id="pw" name="password" type="password" autocomplete="current-password" required><button type="submit">Sign in</button>
<a class="back" href="<?= h(BASE_URL . '/index.php') ?>">&larr; Back to the site</a></form></body></html><?php
}

/* ===== pages ===== */
if ($p === 'dashboard' || (!$mod && !in_array($p, ['leader', 'content', 'users', 'account'], true))) {
    sa_head('Dashboard');
    $tiles = [];
    foreach ($MODS as $k => $m) {
        [$sc, $sp] = sa_scope_sql($m, ' WHERE ');
        $c = db()->prepare("SELECT COUNT(*) FROM `{$m['table']}`$sc"); $c->execute($sp);
        $tiles[] = [$k, $m['label'], $m['icon'], (int)$c->fetchColumn() . ' item(s)'];
    }
    if (!empty($SA_SITE['leader_slug'])) { $l = sa_leader_row(); $tiles[] = ['leader', $SA_SITE['leader_label'], 'bi-person-badge', ($l && $l['full_name']) ? 'Added' : 'Not added yet']; }
    if (!empty($SA_SITE['content'])) $tiles[] = ['content', 'Page Content', 'bi-pencil-square', count($SA_SITE['content']) . ' page group(s)'];
    echo '<p class="text-muted">Welcome. Everything you change here appears on the ' . h($SA_SITE['label']) . ' website straight away, and only on this site.</p><div class="row g-3">';
    foreach ($tiles as [$k, $l, $ic, $sub]) echo '<div class="col-sm-6 col-lg-4"><a class="sa-tile" href="' . h($k === 'content' ? sa_url(['p' => 'content']) : sa_url(['p' => $k])) . '"><i class="bi ' . h($ic) . '"></i><b>' . h($l) . '</b><span>' . h($sub) . '</span></a></div>';
    echo '</div>'; sa_foot(); exit;
}

if ($mod) {
    $n = $mod; $label = $n['label'];
    if ($a === 'edit' || $a === 'new') {
        if ($a === 'edit' && !$data) { $data = sa_row($n, $id); if (!$data) { sa_flash('danger', 'That item was not found.'); sa_go(['p' => $p]); } }
        $data = $data ?? [];
        sa_head(($a === 'new' ? 'New: ' : 'Edit: ') . $label);
        if (!empty($n['help'])) echo '<div class="alert alert-info">' . h($n['help']) . '</div>';
        echo '<form method="post" enctype="multipart/form-data" class="sa-card"><input type="hidden" name="csrf" value="' . h(sa_csrf()) . '">';
        foreach ($n['fields'] as $k => $f) sa_field($k, $f, $data);
        echo '<button class="btn btn-sa" type="submit">Save</button> <a class="btn btn-sa-o" href="' . h(sa_url(['p' => $p])) . '">Cancel</a>';
        if ($a === 'edit') echo '</form><form method="post" action="' . h(sa_url(['p' => $p, 'a' => 'delete', 'id' => $id])) . '" class="mt-3" onsubmit="return confirm(\'Delete this item permanently?\')"><input type="hidden" name="csrf" value="' . h(sa_csrf()) . '"><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Delete</button></form>';
        else echo '</form>';
        sa_foot(); exit;
    }
    sa_head($label);
    [$sc, $sp] = sa_scope_sql($n, ' WHERE ');
    $q = db()->prepare("SELECT * FROM `{$n['table']}`$sc ORDER BY {$n['order']}"); $q->execute($sp); $rows = $q->fetchAll();
    if (!empty($n['help'])) echo '<p class="text-muted">' . h($n['help']) . '</p>';
    echo '<div class="d-flex justify-content-end mb-3"><a class="btn btn-sa" href="' . h(sa_url(['p' => $p, 'a' => 'new'])) . '"><i class="bi bi-plus-lg"></i> ' . h($n['add']) . '</a></div>';
    echo '<div class="sa-card p-0 table-responsive"><table class="table mb-0"><thead><tr>';
    foreach ($n['cols'] as $c => $cl) echo '<th>' . h($cl) . '</th>'; echo '<th class="text-end">Actions</th></tr></thead><tbody>';
    foreach ($rows as $r) {
        echo '<tr>';
        foreach ($n['cols'] as $c => $cl) {
            $v = $r[$c] ?? '';
            if ($c === 'is_published') echo '<td>' . ($v ? '<span class="badge bg-success">Published</span>' : '<span class="badge bg-secondary">Hidden</span>') . '</td>';
            elseif ($c === 'is_active') echo '<td>' . ($v ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Off</span>') . '</td>';
            elseif ($c === 'image' || $c === 'cover_image' || $c === 'photo') echo '<td>' . ($v ? '<img class="sa-thumb" src="' . h(UPLOADS_URL . '/' . $v) . '" alt="">' : '<span class="text-muted small">none</span>') . '</td>';
            else echo '<td>' . h(mb_strimwidth((string)$v, 0, 80, '…')) . '</td>';
        }
        echo '<td class="text-end"><a class="btn btn-sm btn-sa-o" href="' . h(sa_url(['p' => $p, 'a' => 'edit', 'id' => $r['id']])) . '">Edit</a></td></tr>';
    }
    if (!$rows) echo '<tr><td colspan="' . (count($n['cols']) + 1) . '" class="text-muted p-4">Nothing here yet.</td></tr>';
    echo '</tbody></table></div>'; sa_foot(); exit;
}

if ($p === 'leader') {
    sa_head($pageTitle);
    if (empty($SA_SITE['leader_slug'])) { echo '<p class="text-muted">This site has no featured message.</p>'; sa_foot(); exit; }
    $row = $row ?? sa_leader_row() ?? [];
    $row += ['title' => $SA_SITE['leader_label'], 'is_published' => 1];
    echo '<p class="text-muted">This is the message card shown on the homepage, with a "Read the full message" page.</p><form method="post" enctype="multipart/form-data" class="sa-card"><input type="hidden" name="csrf" value="' . h(sa_csrf()) . '">';
    sa_field('full_name', ['Full name', 'text'], $row); sa_field('title', ['Title', 'text'], $row); sa_field('email', ['Email (optional)', 'text'], $row);
    sa_field('bio', ['Message', 'textarea', '', false, 12], $row); sa_field('photo', ['Photo', 'image'], $row); sa_field('is_published', ['Show on the site', 'check'], $row);
    echo '<button class="btn btn-sa" type="submit">Save</button></form>'; sa_foot(); exit;
}

if ($p === 'content') {
    $g = $_GET['g'] ?? ''; $grp = $SA_SITE['content'][$g] ?? null;
    sa_head($pageTitle);
    if (!$grp) {
        echo '<div class="list-group sa-card p-0">';
        foreach ($SA_SITE['content'] as $gk => $gr) echo '<a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 px-4" href="' . h(sa_url(['p' => 'content', 'g' => $gk])) . '">' . h($gr['label']) . '<i class="bi bi-chevron-right"></i></a>';
        echo '</div>'; sa_foot(); exit;
    }
    $s = db()->prepare('SELECT content_key, content_value FROM site_content WHERE unit_slug = ?'); $s->execute([SA_UNIT]); $vals = [];
    foreach ($s->fetchAll() as $r) $vals[$r['content_key']] = $r['content_value'];
    echo '<form method="post" class="sa-card"><input type="hidden" name="csrf" value="' . h(sa_csrf()) . '">';
    foreach ($grp['fields'] as $k => $f) {
        echo '<div class="mb-3"><label class="form-label small text-muted">' . h($f['label']) . '</label>';
        if (($f['type'] ?? 'text') === 'textarea') echo '<textarea name="' . h($k) . '" rows="4" class="form-control" placeholder="' . h($f['default']) . '">' . h($vals[$k] ?? '') . '</textarea>';
        else echo '<input name="' . h($k) . '" class="form-control" value="' . h($vals[$k] ?? '') . '" placeholder="' . h($f['default']) . '">';
        if (empty($vals[$k])) echo '<div class="form-text">Showing the default text (grey). Type here to replace it.</div>';
        echo '</div>';
    }
    echo '<button class="btn btn-sa" type="submit">Save</button> <a class="btn btn-sa-o" href="' . h(sa_url(['p' => 'content'])) . '">Back</a></form>'; sa_foot(); exit;
}

if ($p === 'users') {
    sa_head($pageTitle);
    $us = db()->prepare('SELECT * FROM site_admins WHERE unit_slug = ? ORDER BY full_name'); $us->execute([SA_UNIT]); $rows = $us->fetchAll();
    echo '<div class="sa-card p-0 table-responsive mb-4"><table class="table mb-0"><thead><tr><th>Name</th><th>Username</th><th>Last sign-in</th><th>Status</th><th class="text-end">Actions</th></tr></thead><tbody>';
    foreach ($rows as $r) {
        echo '<tr><td>' . h($r['full_name']) . '<div class="small text-muted">' . h((string)$r['email']) . '</div></td><td>' . h($r['username']) . '</td><td>' . h($r['last_login'] ?: 'never') . '</td><td>' . ($r['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Disabled</span>') . '</td><td class="text-end">';
        echo '<form method="post" class="d-inline"><input type="hidden" name="csrf" value="' . h(sa_csrf()) . '"><input type="hidden" name="act" value="toggle"><input type="hidden" name="uid" value="' . (int)$r['id'] . '"><button class="btn btn-sm btn-sa-o">' . ($r['is_active'] ? 'Disable' : 'Enable') . '</button></form> ';
        echo '<form method="post" class="d-inline-flex gap-1"><input type="hidden" name="csrf" value="' . h(sa_csrf()) . '"><input type="hidden" name="act" value="reset"><input type="hidden" name="uid" value="' . (int)$r['id'] . '"><input type="password" name="password" class="form-control form-control-sm" style="width:150px" placeholder="New password" minlength="10" required autocomplete="new-password"><button class="btn btn-sm btn-sa-o">Set</button></form></td></tr>';
    }
    if (!$rows) echo '<tr><td colspan="5" class="text-muted p-4">No accounts yet.</td></tr>';
    echo '</tbody></table></div><div class="sa-card"><h2 class="h6 mb-3">Add an administrator for this site</h2><form method="post"><input type="hidden" name="csrf" value="' . h(sa_csrf()) . '"><input type="hidden" name="act" value="add"><div class="row g-3">';
    echo '<div class="col-md-6"><label class="form-label">Full name</label><input class="form-control" name="full_name" required></div><div class="col-md-6"><label class="form-label">Email (optional)</label><input class="form-control" name="email" type="email"></div>';
    echo '<div class="col-md-6"><label class="form-label">Username</label><input class="form-control" name="username" required autocomplete="off"></div><div class="col-md-6"><label class="form-label">Password (10+ characters)</label><input class="form-control" type="password" name="password" minlength="10" required autocomplete="new-password"></div></div><button class="btn btn-sa mt-3">Create account</button></form></div>';
    sa_foot(); exit;
}

if ($p === 'account') {
    sa_head($pageTitle);
    if (!empty($SA_ME['main'])) { echo '<div class="alert alert-info">You are signed in through the main university admin. Change that password from the main admin.</div>'; sa_foot(); exit; }
    echo '<form method="post" class="sa-card" style="max-width:480px"><input type="hidden" name="csrf" value="' . h(sa_csrf()) . '">';
    echo '<div class="mb-3"><label class="form-label">Current password</label><input class="form-control" type="password" name="current_password" required autocomplete="current-password"></div>';
    echo '<div class="mb-3"><label class="form-label">New password (10+ characters)</label><input class="form-control" type="password" name="new_password" minlength="10" required autocomplete="new-password"></div>';
    echo '<div class="mb-3"><label class="form-label">Repeat new password</label><input class="form-control" type="password" name="new_password2" minlength="10" required autocomplete="new-password"></div><button class="btn btn-sa">Change password</button></form>';
    sa_foot(); exit;
}
