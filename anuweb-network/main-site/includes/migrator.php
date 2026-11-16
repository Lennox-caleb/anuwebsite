<?php
/**
 * Consolidated, re-runnable schema migration (Audit Part 1).
 *
 * WHY: the project shipped 53 loose .sql files with no record of which had
 * run, so any module whose table/column came from a skipped file failed to
 * save. Every step below checks the real schema first and only changes what
 * is missing, so it is safe to run any number of times, on any database
 * (fresh install, half-migrated, or fully migrated). Applied steps are
 * recorded in `schema_migrations`.
 *
 * It never drops tables or columns and never deletes rows.
 */
require_once __DIR__ . '/../config/db.php';

function mig_table_exists(string $t): bool
{
    $s = db()->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?');
    $s->execute([$t]);
    return (bool) $s->fetchColumn();
}

function mig_column(string $t, string $c): ?array
{
    $s = db()->prepare('SELECT column_type, collation_name FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?');
    $s->execute([$t, $c]);
    return $s->fetch() ?: null;
}

function mig_add_column(string $t, string $c, string $ddl): string
{
    if (!mig_table_exists($t)) return "skipped: table $t missing";
    if (mig_column($t, $c)) return 'already present';
    db()->exec("ALTER TABLE `$t` ADD COLUMN `$c` $ddl");
    return "added $t.$c";
}

function mig_create(string $t, string $ddl): string
{
    if (mig_table_exists($t)) return 'already present';
    db()->exec($ddl);
    return "created $t";
}

function mig_unicode(string $t): string
{
    if (!mig_table_exists($t)) return "skipped: table $t missing";
    $s = db()->prepare('SELECT table_collation FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?');
    $s->execute([$t]);
    if ($s->fetchColumn() === 'utf8mb4_unicode_ci') return 'already utf8mb4_unicode_ci';
    db()->exec("ALTER TABLE `$t` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    return "converted $t to utf8mb4_unicode_ci";
}

/** @return array<int,array{id:string,desc:string,run:callable}> */
function migration_steps(): array
{
    $tbl = fn(string $name, string $body) =>
        "CREATE TABLE `$name` ($body) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    return [
        ['id' => '001_photo_gallery', 'desc' => 'Photo Gallery table',
         'run' => fn() => mig_create('photo_gallery', $tbl('photo_gallery', "
            `id` int(11) NOT NULL AUTO_INCREMENT, `title` varchar(255) NOT NULL, `external_url` varchar(500) NOT NULL,
            `cover_image` varchar(255) DEFAULT NULL, `sort_order` int(11) DEFAULT 0, `is_published` tinyint(1) DEFAULT 1,
            `created_at` datetime DEFAULT current_timestamp(), PRIMARY KEY (`id`)"))],
        ['id' => '002_departments', 'desc' => 'Departments table',
         'run' => fn() => mig_create('departments', $tbl('departments', "
            `id` int(11) NOT NULL AUTO_INCREMENT, `unit_slug` varchar(60) NOT NULL, `name` varchar(150) NOT NULL,
            `summary` text DEFAULT NULL, `sort_order` int(11) DEFAULT 0, `is_published` tinyint(1) DEFAULT 1,
            `created_at` datetime DEFAULT current_timestamp(), PRIMARY KEY (`id`)"))],
        ['id' => '003_chaplaincy_programs', 'desc' => 'Chaplaincy programmes table',
         'run' => fn() => mig_create('chaplaincy_programs', $tbl('chaplaincy_programs', "
            `id` int(11) NOT NULL AUTO_INCREMENT, `title` varchar(150) NOT NULL, `description` text DEFAULT NULL,
            `icon` varchar(60) DEFAULT 'bi-heart', `sort_order` int(11) NOT NULL DEFAULT 0,
            `is_published` tinyint(1) NOT NULL DEFAULT 1, `created_at` timestamp NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`)"))],
        ['id' => '004_csr_gallery', 'desc' => 'CSR gallery table',
         'run' => fn() => mig_create('csr_gallery', $tbl('csr_gallery', "
            `id` int(11) NOT NULL AUTO_INCREMENT, `title` varchar(255) NOT NULL, `category` varchar(100) DEFAULT NULL,
            `photo` varchar(255) DEFAULT NULL, `caption` text DEFAULT NULL, `sort_order` int(11) NOT NULL DEFAULT 0,
            `is_published` tinyint(1) NOT NULL DEFAULT 1, `created_at` timestamp NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`)"))],
        ['id' => '005_tenders', 'desc' => 'Tenders table',
         'run' => fn() => mig_create('tenders', $tbl('tenders', "
            `id` int(11) NOT NULL AUTO_INCREMENT, `title` varchar(255) NOT NULL, `reference_no` varchar(100) DEFAULT NULL,
            `category` varchar(100) DEFAULT NULL, `summary` text DEFAULT NULL, `document` varchar(255) DEFAULT NULL,
            `closing_date` date DEFAULT NULL, `status` enum('open','closed','awarded') NOT NULL DEFAULT 'open',
            `sort_order` int(11) NOT NULL DEFAULT 0, `is_published` tinyint(1) NOT NULL DEFAULT 1,
            `created_at` timestamp NULL DEFAULT current_timestamp(), PRIMARY KEY (`id`)"))],
        ['id' => '006_leadership', 'desc' => 'Leadership (VC & Deputy VCs) table',
         'run' => fn() => mig_create('leadership', $tbl('leadership', "
            `id` int(11) NOT NULL AUTO_INCREMENT, `role_type` enum('vc','dvc','chancellor') NOT NULL DEFAULT 'dvc',
            `full_name` varchar(150) NOT NULL, `title` varchar(150) DEFAULT NULL, `message` text DEFAULT NULL,
            `photo` varchar(255) DEFAULT NULL, `sort_order` int(11) DEFAULT 0, `is_published` tinyint(1) DEFAULT 1,
            `created_at` datetime DEFAULT current_timestamp(), PRIMARY KEY (`id`)"))],
        ['id' => '007_site_content', 'desc' => 'Editable page content table (Orientation Pack, unit sites)',
         'run' => fn() => mig_create('site_content', $tbl('site_content', "
            `id` int(11) NOT NULL AUTO_INCREMENT, `unit_slug` varchar(60) NOT NULL, `content_key` varchar(80) NOT NULL,
            `content_value` text DEFAULT NULL, `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`), UNIQUE KEY `unit_key` (`unit_slug`,`content_key`)"))],
        ['id' => '010_sliders_video_url', 'desc' => 'sliders.video_url',
         'run' => fn() => mig_add_column('sliders', 'video_url', 'varchar(500) DEFAULT NULL')],
        ['id' => '011_events_unit_slug', 'desc' => 'events.unit_slug',
         'run' => fn() => mig_add_column('events', 'unit_slug', 'varchar(60) DEFAULT NULL')],
        ['id' => '012_events_category', 'desc' => 'events.category',
         'run' => fn() => mig_add_column('events', 'category', 'varchar(80) DEFAULT NULL')],
        ['id' => '013_news_unit_slug', 'desc' => 'news.unit_slug',
         'run' => fn() => mig_add_column('news', 'unit_slug', 'varchar(60) DEFAULT NULL')],
        ['id' => '014_news_category', 'desc' => 'news.category',
         'run' => fn() => mig_add_column('news', 'category', 'varchar(80) DEFAULT NULL')],
        ['id' => '015_news_is_featured', 'desc' => 'news.is_featured',
         'run' => fn() => mig_add_column('news', 'is_featured', 'tinyint(1) DEFAULT 0')],
        ['id' => '016_programmes_intake', 'desc' => 'programmes.intake',
         'run' => fn() => mig_add_column('programmes', 'intake', 'varchar(100) DEFAULT NULL')],
        ['id' => '017_programmes_fee_file', 'desc' => 'programmes.fee_structure_file',
         'run' => fn() => mig_add_column('programmes', 'fee_structure_file', 'varchar(255) DEFAULT NULL')],
        ['id' => '018_programmes_is_featured', 'desc' => 'programmes.is_featured',
         'run' => fn() => mig_add_column('programmes', 'is_featured', 'tinyint(1) DEFAULT 0')],
        ['id' => '020_publication_type_enum', 'desc' => "publications_gallery.publication_type allows 'Graduation List'",
         'run' => function () {
            $c = mig_column('publications_gallery', 'publication_type');
            if (!$c) return 'skipped: column missing';
            if (str_contains($c['column_type'], 'Graduation List')) return 'already present';
            db()->exec("ALTER TABLE publications_gallery MODIFY publication_type ENUM('ASPIRE','ANU Voice','Graduation List','Other') NOT NULL DEFAULT 'Other'");
            return 'enum extended (existing values kept)';
         }],
        ['id' => '021_resources_description_text', 'desc' => 'resources.description widened to TEXT (was varchar(400): "Data too long")',
         'run' => function () {
            $c = mig_column('resources', 'description');
            if (!$c) return 'skipped: column missing';
            if (stripos($c['column_type'], 'text') !== false) return 'already TEXT';
            db()->exec('ALTER TABLE resources MODIFY description TEXT NULL');
            return 'widened to TEXT';
         }],
        ['id' => '040_login_attempts', 'desc' => 'Admin login attempt log (lockout after repeated failures)',
         'run' => fn() => mig_create('login_attempts', $tbl('login_attempts', "
            `id` int(11) NOT NULL AUTO_INCREMENT, `username` varchar(100) NOT NULL, `ip` varchar(45) NOT NULL,
            `attempted_at` datetime NOT NULL DEFAULT current_timestamp(), PRIMARY KEY (`id`),
            KEY `idx_user_ip_time` (`username`,`ip`,`attempted_at`)"))],
        ['id' => '050_testimonials', 'desc' => 'Testimonials table (homepage slider; shown only when published rows exist)',
         'run' => fn() => mig_create('testimonials', $tbl('testimonials', "
            `id` int(11) NOT NULL AUTO_INCREMENT, `quote` text NOT NULL, `person_name` varchar(150) NOT NULL,
            `person_role` varchar(200) DEFAULT NULL, `photo` varchar(255) DEFAULT NULL,
            `sort_order` int(11) NOT NULL DEFAULT 0, `is_published` tinyint(1) NOT NULL DEFAULT 1,
            `created_at` datetime DEFAULT current_timestamp(), PRIMARY KEY (`id`)"))],
        ['id' => '060_orientation_hero_youtube', 'desc' => 'Orientation Pack hero: YouTube video lBitPZ1iQfc (only if no Orientation hero exists yet)',
         'run' => function () {
            if (!mig_table_exists('sliders') || !mig_column('sliders', 'unit_slug') || !mig_column('sliders', 'video_url')) return 'skipped: sliders columns missing';
            $c = db()->prepare("SELECT COUNT(*) FROM sliders WHERE unit_slug = 'orientation-pack'");
            $c->execute();
            if ((int) $c->fetchColumn() > 0) return 'already present (existing hero left untouched)';
            db()->prepare("INSERT INTO sliders (unit_slug, headline, subtext, video_url, sort_order, is_published) VALUES ('orientation-pack', 'Orientation', 'Everything you need to prepare for Orientation Week', ?, 0, 1)")
                ->execute(['https://www.youtube.com/watch?v=lBitPZ1iQfc']);
            return 'inserted Orientation Pack YouTube hero';
         }],
        ['id' => '061_orientation_hero_video_fill', 'desc' => 'Orientation Pack hero: add the YouTube video lBitPZ1iQfc to the existing hero row IF it has no video yet (never overwrites a video that is already set)',
         'run' => function () {
            if (!mig_table_exists('sliders') || !mig_column('sliders', 'unit_slug') || !mig_column('sliders', 'video_url')) return 'skipped: sliders columns missing';
            $q = db()->prepare("SELECT id, video_url FROM sliders WHERE unit_slug = 'orientation-pack' AND is_published = 1 ORDER BY sort_order DESC, id DESC LIMIT 1");
            $q->execute();
            $row = $q->fetch();
            if (!$row) return 'no published Orientation hero row (nothing to fill)';
            if (trim((string)($row['video_url'] ?? '')) !== '') return 'hero already has a video (left untouched)';
            db()->prepare("UPDATE sliders SET video_url = ? WHERE id = ?")->execute(['https://www.youtube.com/watch?v=lBitPZ1iQfc', $row['id']]);
            return 'added YouTube video to existing Orientation hero (id ' . $row['id'] . '); its image, if any, stays as the fallback';
         }],
        ['id' => '070_subscribers_status', 'desc' => 'Newsletter subscribers: active flag, unsubscribe token (backfilled), unsubscribed_at. Existing rows kept.',
         'run' => function () {
            if (!mig_table_exists('subscribers')) return 'skipped: subscribers table missing';
            $out = [];
            $out[] = mig_add_column('subscribers', 'is_active', 'TINYINT(1) NOT NULL DEFAULT 1');
            $out[] = mig_add_column('subscribers', 'unsubscribe_token', 'CHAR(32) NULL');
            $out[] = mig_add_column('subscribers', 'unsubscribed_at', 'DATETIME NULL');
            $n = db()->exec("UPDATE subscribers SET unsubscribe_token = MD5(CONCAT(id, '-', UUID(), '-', RAND())) WHERE unsubscribe_token IS NULL OR unsubscribe_token = ''");
            if ($n) $out[] = "backfilled $n unsubscribe tokens";
            $has = db()->query("SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'subscribers' AND index_name = 'uq_subscribers_token'")->fetchColumn();
            if (!$has) { db()->exec('ALTER TABLE subscribers ADD UNIQUE KEY uq_subscribers_token (unsubscribe_token)'); $out[] = 'added unique token index'; }
            return implode('; ', $out);
         }],
        ['id' => '071_newsletters', 'desc' => 'Newsletters + per-recipient send log tables',
         'run' => function () {
            $out = [];
            $out[] = mig_create('newsletters', "CREATE TABLE `newsletters` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `subject` varchar(200) NOT NULL,
                `preheader` varchar(200) DEFAULT NULL,
                `headline` varchar(200) NOT NULL,
                `body` text NOT NULL,
                `button_text` varchar(80) DEFAULT NULL,
                `button_url` varchar(255) DEFAULT NULL,
                `include_news` tinyint(1) NOT NULL DEFAULT 0,
                `include_events` tinyint(1) NOT NULL DEFAULT 0,
                `status` varchar(20) NOT NULL DEFAULT 'draft',
                `created_by` int(11) DEFAULT NULL,
                `created_at` datetime DEFAULT current_timestamp(),
                `sent_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $out[] = mig_create('newsletter_sends', "CREATE TABLE `newsletter_sends` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `newsletter_id` int(11) NOT NULL,
                `subscriber_id` int(11) NOT NULL,
                `email` varchar(150) NOT NULL,
                `status` varchar(10) NOT NULL,
                `error` varchar(255) DEFAULT NULL,
                `sent_at` datetime DEFAULT current_timestamp(),
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_newsletter_subscriber` (`newsletter_id`,`subscriber_id`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            return implode('; ', $out);
         }],
        ['id' => '072_programme_content_from_anu', 'desc' => 'Programme descriptions, requirements, duration and intake from anu.ac.ke (fills empty fields only)',
         'run' => function () {
            if (!mig_table_exists('programmes')) return 'skipped: no programmes table';
            $data = require __DIR__ . '/programme_content_data.php';
            $sel = db()->prepare('SELECT id, overview, requirements, duration, intake FROM programmes WHERE slug = ?');
            $upd = db()->prepare('UPDATE programmes SET overview = ?, requirements = ?, duration = ?, intake = ? WHERE id = ?');
            $rows = 0;
            foreach ($data as $e) {
                foreach ($e['slugs'] as $slug) {
                    $sel->execute([$slug]);
                    foreach ($sel->fetchAll() as $r) {
                        $ov = trim((string) $r['overview']);
                        $newOv = $r['overview'];
                        if (strpos($ov, $e['overview']) === false && ($ov === '' || mb_strlen($ov) < 200)) {
                            $keep = trim(str_replace('Full overview to be added by the School.', '', $ov));
                            $newOv = $e['overview'] . ($keep !== '' ? "\n\n" . $keep : '');
                        }
                        $newReq = trim((string) $r['requirements']) === '' ? $e['requirements'] : $r['requirements'];
                        $newDur = trim((string) $r['duration']) === '' ? $e['duration'] : $r['duration'];
                        $newInt = trim((string) $r['intake']) === '' ? $e['intake'] : $r['intake'];
                        if ($newOv !== $r['overview'] || $newReq !== $r['requirements'] || $newDur !== $r['duration'] || $newInt !== $r['intake']) {
                            $upd->execute([$newOv, $newReq, $newDur, $newInt, $r['id']]);
                            $rows++;
                        }
                    }
                }
            }
            return $rows ? "updated $rows programme row(s)" : 'already present';
         }],
        ['id' => '073_programme_images', 'desc' => 'Cover image for each programme (original artwork shipped in assets/uploads/programmes); fills empty image only',
         'run' => function () {
            if (!mig_table_exists('programmes')) return 'skipped: no programmes table';
            $map = require __DIR__ . '/programme_images_data.php';
            $upd = db()->prepare("UPDATE programmes SET image = ? WHERE slug = ? AND (image IS NULL OR image = '')");
            $n = 0;
            foreach ($map as $slug => $file) { $upd->execute([$file, $slug]); $n += $upd->rowCount(); }
            return $n ? "set image on $n programme(s)" : 'already present';
         }],
        ['id' => '074_unit_hero_images', 'desc' => 'Hero image for each school / unit mini-site that has none (original artwork in assets/uploads/heroes)',
         'run' => function () {
            if (!mig_table_exists('sliders')) return 'skipped: no sliders table';
            $map = require __DIR__ . '/hero_images_data.php';
            $chk = db()->prepare('SELECT COUNT(*) FROM sliders WHERE unit_slug = ?');
            $ins = db()->prepare("INSERT INTO sliders (unit_slug, headline, image, sort_order, is_published) VALUES (?, '', ?, 1, 1)");
            $n = 0;
            foreach ($map as $unit => $file) {
                $chk->execute([$unit]);
                if ((int) $chk->fetchColumn() === 0) { $ins->execute([$unit, $file]); $n++; }
            }
            return $n ? "added hero image for $n site(s)" : 'already present';
         }],
        ['id' => '075_school_short_slugs', 'desc' => "Schools use the short URL slugs the school sites and programme filters expect (sol, sob, sst, shss, scrm)",
         'run' => function () {
            if (!mig_table_exists('schools')) return 'skipped: no schools table';
            $map = ['school-of-law' => 'sol', 'school-of-business' => 'sob', 'school-of-science-and-technology' => 'sst',
                    'school-of-humanities-and-social-sciences' => 'shss', 'school-of-religion-and-christian-ministry' => 'scrm'];
            $n = 0;
            $chk = db()->prepare('SELECT COUNT(*) FROM schools WHERE slug = ?');
            $upd = db()->prepare('UPDATE schools SET slug = ? WHERE slug = ?');
            foreach ($map as $old => $new) {
                $chk->execute([$new]);
                if ((int) $chk->fetchColumn() > 0) continue; // short slug already in place
                $upd->execute([$new, $old]); $n += $upd->rowCount();
            }
            return $n ? "renamed $n school slug(s) to the short form" : 'already present';
         }],
        ['id' => '076_programme_images_all', 'desc' => 'Cover image for every programme that still has none (picked by subject + school colours)',
         'run' => function () {
            if (!mig_table_exists('programmes')) return 'skipped: no programmes table';
            $kw = ['law'=>'scales','criminal'=>'shield','security'=>'shield','theolog'=>'book','ministry'=>'cross','religion'=>'cross',
                   'environment'=>'leaf','natural resource'=>'leaf','impact assessment'=>'leaf','computer'=>'chip','information technology'=>'chip',
                   'business and information'=>'chip','mass comm'=>'mic','counsel'=>'people','community'=>'people','peace'=>'dove',
                   'mediation'=>'dove','governance'=>'dove','procurement'=>'boxes','commerce'=>'chart','business'=>'chart',
                   'monitoring'=>'globe','international'=>'globe','education'=>'cap'];
            $rows = db()->query("SELECT id, title, school_id FROM programmes WHERE image IS NULL OR image = ''")->fetchAll();
            $upd = db()->prepare('UPDATE programmes SET image = ? WHERE id = ?');
            $n = 0;
            foreach ($rows as $r) {
                $title = mb_strtolower((string) $r['title']);
                $motif = 'cap';
                foreach ($kw as $k => $m) { if (strpos($title, $k) !== false) { $motif = $m; break; } }
                $sid = (int) $r['school_id']; if ($sid < 1 || $sid > 5) $sid = 2;
                $upd->execute(["programmes/auto-$motif-$sid.jpg", $r['id']]); $n++;
            }
            return $n ? "set image on $n programme(s)" : 'already present';
         }],
        ['id' => '077_apply_url', 'desc' => 'Every Apply button (all sites) points to the ANU application portal',
         'run' => function () {
            if (!mig_table_exists('settings')) return 'skipped: no settings table';
            $url = 'https://records.anu.ac.ke/wStartUpCreateUserNamePassword.jsp';
            $cur = db()->query("SELECT setting_value FROM settings WHERE setting_key = 'apply_url'")->fetchColumn();
            if ($cur === $url) return 'already present';
            db()->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('apply_url', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)")->execute([$url]);
            return 'apply_url set to the ANU application portal';
         }],
        ['id' => '079_orientation_rename', 'desc' => 'Rename the Orientation Pack category and hero headline to Orientation (rows backed up first)',
         'run' => function () {
            $out = [];
            if (mig_table_exists('resources')) {
                $col = db()->query("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'resources' AND COLUMN_NAME = 'category'")->fetchColumn();
                if ($col && stripos($col, 'enum(') === 0) {
                    // Keep every existing value, only add the ones that are missing.
                    $have = [];
                    preg_match_all("/'((?:[^']|'')*)'/", $col, $m);
                    foreach ($m[1] as $v) $have[] = str_replace("''", "'", $v);
                    $want = $have;
                    foreach (['Orientation', 'Orientation Pack'] as $v) if (!in_array($v, $want, true)) $want[] = $v;
                    if ($want !== $have) {
                        $list = implode(',', array_map(fn($v) => db()->quote($v), $want));
                        db()->exec("ALTER TABLE resources MODIFY category ENUM($list) NOT NULL DEFAULT 'Other'");
                        $out[] = 'category list extended';
                    }
                }
                $n = (int)db()->query("SELECT COUNT(*) FROM resources WHERE category = 'Orientation Pack'")->fetchColumn();
                if ($n) {
                    db()->exec("CREATE TABLE IF NOT EXISTS resources_bak_079 AS SELECT * FROM resources WHERE category = 'Orientation Pack'");
                    db()->exec("UPDATE resources SET category = 'Orientation' WHERE category = 'Orientation Pack'");
                    $out[] = "renamed category on $n document(s)";
                }
            }
            if (mig_table_exists('sliders')) {
                $n = (int)db()->query("SELECT COUNT(*) FROM sliders WHERE unit_slug = 'orientation-pack' AND headline = 'Orientation Pack'")->fetchColumn();
                if ($n) {
                    db()->exec("UPDATE sliders SET headline = 'Orientation' WHERE unit_slug = 'orientation-pack' AND headline = 'Orientation Pack'");
                    $out[] = 'hero headline renamed';
                }
            }
            return $out ? implode('; ', $out) : 'already present';
         }],
        ['id' => '080_plain_text_cleanup', 'desc' => 'Replace em dashes and remove emoji in stored page content (each changed table is copied to <table>_bak_080 first)',
         'run' => function () {
            $skip = '/(user|admin|login|subscriber|session|migration|_bak_|setting|password|token)/i';
            $dash = '/\s*(?:\x{2014}|&mdash;|&#8212;|&#x2014;)\s*/u';
            $emoji = '/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{2B50}\x{FE0F}\x{200D}]/u';
            $clean = function (string $v) use ($dash, $emoji): string {
                if (preg_match('/^(a|s|O|i):\d/', $v) || (strlen($v) && ($v[0] === '{' || $v[0] === '['))) return $v; // serialized / JSON, leave alone
                if (preg_match('/^\s*(?:\x{2014}|&mdash;)\s*$/u', $v)) return '-';
                $v = preg_replace($dash, ', ', $v);
                $v = preg_replace($emoji, '', $v);
                return str_replace([',, ', ', ,', '  '], [', ', ',', ' '], $v);
            };
            $db = db();
            $tables = $db->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE'")->fetchAll(PDO::FETCH_COLUMN);
            $touched = []; $rowsFixed = 0;
            foreach ($tables as $t) {
                if (preg_match($skip, $t)) continue;
                $cols = $db->prepare("SELECT COLUMN_NAME, DATA_TYPE, COLUMN_KEY FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
                $cols->execute([$t]);
                $text = []; $pk = [];
                foreach ($cols->fetchAll() as $c) {
                    if (in_array($c['DATA_TYPE'], ['char', 'varchar', 'text', 'mediumtext', 'longtext', 'tinytext'], true)) $text[] = $c['COLUMN_NAME'];
                    if ($c['COLUMN_KEY'] === 'PRI') $pk[] = $c['COLUMN_NAME'];
                }
                if (!$text || !$pk) continue;
                $sel = implode(',', array_map(fn($c) => "`$c`", array_merge($pk, $text)));
                $rows = $db->query("SELECT $sel FROM `$t`")->fetchAll();
                foreach ($rows as $r) {
                    $set = []; $args = [];
                    foreach ($text as $c) {
                        if (!is_string($r[$c]) || $r[$c] === '') continue;
                        $n = $clean($r[$c]);
                        if ($n !== $r[$c]) { $set[] = "`$c` = ?"; $args[] = $n; }
                    }
                    if (!$set) continue;
                    if (!isset($touched[$t])) {
                        $db->exec("CREATE TABLE IF NOT EXISTS `{$t}_bak_080` AS SELECT * FROM `$t`");
                        $touched[$t] = 0;
                    }
                    $where = implode(' AND ', array_map(fn($c) => "`$c` = ?", $pk));
                    foreach ($pk as $c) $args[] = $r[$c];
                    $db->prepare("UPDATE `$t` SET " . implode(', ', $set) . " WHERE $where")->execute($args);
                    $touched[$t]++; $rowsFixed++;
                }
            }
            if (!$touched) return 'already present';
            $d = []; foreach ($touched as $t => $n) $d[] = "$t:$n";
            return "cleaned $rowsFixed row(s) (" . implode(', ', $d) . ')';
         }],
        ['id' => '081_staff_dept_head', 'desc' => 'Staff: mark Heads of Department (shown with their message on the school home page)',
         'run' => function () {
            if (!mig_table_exists('staff')) return 'skipped: no staff table';
            return mig_add_column('staff', 'is_dept_head', 'tinyint(1) NOT NULL DEFAULT 0') . '; '
                 . mig_add_column('staff', 'department', 'varchar(150) DEFAULT NULL');
         }],
        ['id' => '082_remove_generic_programme_images', 'desc' => 'Programmes: remove generic stock/auto pictures (kept in programmes_image_backup); real photos named <slug>-photo.* are kept',
         'run' => function () {
            if (!mig_table_exists('programmes')) return 'skipped: no programmes table';
            $pdo = db();
            $pdo->exec("CREATE TABLE IF NOT EXISTS programmes_image_backup (id INT NOT NULL PRIMARY KEY, image VARCHAR(255) DEFAULT NULL, saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) DEFAULT CHARSET=utf8mb4");
            $pdo->exec("INSERT IGNORE INTO programmes_image_backup (id, image) SELECT id, image FROM programmes WHERE image IS NOT NULL AND image <> '' AND image NOT LIKE '%-photo.%'");
            $n = $pdo->exec("UPDATE programmes SET image = NULL WHERE image IS NOT NULL AND image <> '' AND image NOT LIKE '%-photo.%'");
            return 'generic pictures removed from ' . (int)$n . ' programme(s)';
         }],
        ['id' => '030_collation_unicode', 'desc' => 'Unify table collation (prevents "Illegal mix of collations")',
         'run' => function () {
            $out = [];
            foreach (['chaplaincy_programs', 'csr_gallery', 'tenders', 'leadership', 'site_content', 'photo_gallery', 'departments'] as $t) {
                $out[] = mig_unicode($t);
            }
            return implode('; ', $out);
         }],
    ];
}

function migration_ledger_init(): void
{
    db()->exec("CREATE TABLE IF NOT EXISTS schema_migrations (
        id varchar(80) NOT NULL PRIMARY KEY,
        description varchar(255) NOT NULL,
        result varchar(500) DEFAULT NULL,
        applied_at datetime NOT NULL DEFAULT current_timestamp()
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

/**
 * Run every step (each is individually idempotent; the ledger records the
 * latest result). Stops at the first failure.
 * @return array<int,array{id:string,desc:string,ok:bool,result:string,changed:bool}>
 */
function run_migrations(): array
{
    migration_ledger_init();
    $report = [];
    foreach (migration_steps() as $step) {
        try {
            $res = (string) ($step['run'])();
            $changed = !preg_match('/^(already|skipped)/', $res);
            $report[] = ['id' => $step['id'], 'desc' => $step['desc'], 'ok' => true, 'result' => $res, 'changed' => $changed];
            $st = db()->prepare('INSERT INTO schema_migrations (id, description, result) VALUES (?,?,?)
                ON DUPLICATE KEY UPDATE result = IF(? = 1, VALUES(result), result), applied_at = IF(? = 1, NOW(), applied_at)');
            $st->execute([$step['id'], $step['desc'], mb_substr($res, 0, 500), (int) $changed, (int) $changed]);
        } catch (Throwable $e) {
            error_log('Migration ' . $step['id'] . ' failed: ' . $e->getMessage());
            $report[] = ['id' => $step['id'], 'desc' => $step['desc'], 'ok' => false, 'result' => $e->getMessage(), 'changed' => false];
            break;
        }
    }
    return $report;
}
