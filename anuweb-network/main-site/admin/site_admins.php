<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['super_admin']);
$sites = include __DIR__ . '/includes/sites_registry.php';
// Mini-sites that have their own admin panel (<site>/admin/index.php)
$own = [];
foreach ($sites as $slug => $s) {
    if (empty($s['url']) || strpos($s['url'], '/anuweb-network/') !== 0 || $slug === 'orientation-pack') continue;
    $dir = $slug === 'admissions' ? '/anuweb-network/admissions-site' : dirname($s['url']);
    if (basename($dir) !== 'main-site' && is_file(dirname(__DIR__, 2) . '/' . basename($dir) . '/admin/index.php')) $own[$slug] = $dir . '/admin/index.php';
}
try {
    db()->exec("CREATE TABLE IF NOT EXISTS site_admins (id INT AUTO_INCREMENT PRIMARY KEY, unit_slug VARCHAR(60) NOT NULL, full_name VARCHAR(120) NOT NULL, email VARCHAR(150) DEFAULT NULL, username VARCHAR(60) NOT NULL, password_hash VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL DEFAULT 1, last_login DATETIME DEFAULT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY uq_unit_user (unit_slug, username)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (Throwable $e) {}
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) { $errors[] = 'Session expired, please try again.'; }
    else {
        $act = $_POST['act'] ?? '';
        if ($act === 'add') {
            $unit = $_POST['unit_slug'] ?? ''; $un = strtolower(trim($_POST['username'] ?? '')); $pw = (string)($_POST['password'] ?? '');
            if (!isset($own[$unit])) $errors[] = 'Choose a site.';
            if (!preg_match('/^[a-z0-9._-]{3,40}$/', $un)) $errors[] = 'Username: 3 to 40 letters, numbers, dot, dash or underscore.';
            if (strlen($pw) < 10) $errors[] = 'Password must be at least 10 characters.';
            if (trim($_POST['full_name'] ?? '') === '') $errors[] = 'Full name is required.';
            if (!$errors) {
                try { db()->prepare('INSERT INTO site_admins (unit_slug, full_name, email, username, password_hash) VALUES (?,?,?,?,?)')->execute([$unit, trim($_POST['full_name']), trim($_POST['email'] ?? ''), $un, password_hash($pw, PASSWORD_DEFAULT)]); flash_set('success', 'Account created.'); redirect('/admin/site_admins.php'); }
                catch (Throwable $e) { $errors[] = 'That username is already taken.'; }
            }
        } elseif ($act === 'toggle') { db()->prepare('UPDATE site_admins SET is_active = 1 - is_active WHERE id = ?')->execute([(int)($_POST['uid'] ?? 0)]); flash_set('success', 'Updated.'); redirect('/admin/site_admins.php'); }
        elseif ($act === 'reset') {
            $pw = (string)($_POST['password'] ?? '');
            if (strlen($pw) < 10) $errors[] = 'Password must be at least 10 characters.';
            else { db()->prepare('UPDATE site_admins SET password_hash = ? WHERE id = ?')->execute([password_hash($pw, PASSWORD_DEFAULT), (int)($_POST['uid'] ?? 0)]); flash_set('success', 'Password changed.'); redirect('/admin/site_admins.php'); }
        }
    }
}
$pageTitle = 'Site Admin Accounts';
require __DIR__ . '/includes/admin_header.php';
$rows = db()->query('SELECT * FROM site_admins ORDER BY unit_slug, full_name')->fetchAll();
$byUnit = []; foreach ($rows as $r) $byUnit[$r['unit_slug']][] = $r;
?>
<p class="text-muted">Each site below has its own admin panel at its own address, with its own sign-in. Create accounts here for the people who run each site, or open a panel directly (you are signed in as university admin, so no extra sign-in is needed).</p>
<?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<div class="card-anu mb-4"><div class="body p-0"><table class="table table-anu mb-0">
  <thead><tr><th>Site</th><th>Accounts</th><th class="text-end">Panel</th></tr></thead><tbody>
  <?php foreach ($own as $slug => $url): ?>
    <tr><td><strong><?= h($sites[$slug]['label']) ?></strong></td>
      <td><?php foreach ($byUnit[$slug] ?? [] as $r): ?>
        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap"><span><?= h($r['full_name']) ?> <small class="text-muted">(<?= h($r['username']) ?>)</small></span>
          <?= $r['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Disabled</span>' ?>
          <form method="post" class="d-inline"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><input type="hidden" name="act" value="toggle"><input type="hidden" name="uid" value="<?= (int)$r['id'] ?>"><button class="btn btn-sm btn-outline-anu"><?= $r['is_active'] ? 'Disable' : 'Enable' ?></button></form>
          <form method="post" class="d-inline-flex gap-1"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><input type="hidden" name="act" value="reset"><input type="hidden" name="uid" value="<?= (int)$r['id'] ?>"><input type="password" name="password" class="form-control form-control-sm" style="width:140px" placeholder="New password" minlength="10" required autocomplete="new-password"><button class="btn btn-sm btn-outline-anu">Set</button></form></div>
        <?php endforeach; if (empty($byUnit[$slug])): ?><span class="text-muted small">No accounts yet</span><?php endif; ?></td>
      <td class="text-end"><a class="btn btn-sm btn-apply" href="<?= h($url) ?>" target="_blank" rel="noopener">Open panel</a></td></tr>
  <?php endforeach; ?>
  </tbody></table></div></div>
<div class="card-anu"><div class="body"><h2 class="h6 mb-3">Create a site administrator</h2>
<form method="post"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><input type="hidden" name="act" value="add"><div class="row g-3">
  <div class="col-md-4"><label class="form-label">Site</label><select name="unit_slug" class="form-select" required><option value="">Choose a site</option><?php foreach ($own as $slug => $u): ?><option value="<?= h($slug) ?>"><?= h($sites[$slug]['label']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-4"><label class="form-label">Full name</label><input class="form-control" name="full_name" required></div>
  <div class="col-md-4"><label class="form-label">Email (optional)</label><input class="form-control" type="email" name="email"></div>
  <div class="col-md-6"><label class="form-label">Username</label><input class="form-control" name="username" required autocomplete="off"></div>
  <div class="col-md-6"><label class="form-label">Password (10+ characters)</label><input class="form-control" type="password" name="password" minlength="10" required autocomplete="new-password"></div>
</div><button class="btn btn-apply mt-3">Create account</button></form></div></div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
