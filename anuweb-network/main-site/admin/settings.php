<?php
require_once __DIR__ . '/includes/auth.php';
require_super_admin();

$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $stmt = db()->prepare('UPDATE settings SET setting_value = ? WHERE setting_key = ?');
    foreach ($_POST as $key => $value) {
        if ($key === 'csrf_token') continue;
        $stmt->execute([trim($value), $key]);
    }
    $success = true;
}

$pageTitle = 'Site Settings';
require __DIR__ . '/includes/admin_header.php';
$rows = db()->query('SELECT * FROM settings ORDER BY setting_key')->fetchAll();
?>
<?php if ($success): ?><div class="alert alert-success">Settings saved.</div><?php endif; ?>
<form method="post" class="card-anu">
  <div class="body">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <div class="row g-3">
      <?php foreach ($rows as $r): ?>
        <div class="col-md-6">
          <label class="form-label small text-muted"><?= h($r['setting_key']) ?></label>
          <?php if (strlen($r['setting_value']) > 80): ?>
            <textarea name="<?= h($r['setting_key']) ?>" rows="3" class="form-control"><?= h($r['setting_value']) ?></textarea>
          <?php else: ?>
            <input type="text" name="<?= h($r['setting_key']) ?>" class="form-control" value="<?= h($r['setting_value']) ?>">
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
    <button class="btn btn-apply mt-4" type="submit">Save Settings</button>
  </div>
</form>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
