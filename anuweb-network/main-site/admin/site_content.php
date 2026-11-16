<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['editor','marketing','super_admin']);

$sites = include __DIR__ . '/includes/sites_registry.php';

$unit  = $_GET['unit'] ?? '';
$group = $_GET['group'] ?? '';

if (!isset($sites[$unit]) || !isset($sites[$unit]['content'][$group])) {
    redirect('/admin/unit_sites.php');
}

$site   = $sites[$unit];
$page   = $site['content'][$group];
$fields = $page['fields'];

$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (csrf_check()) {
        $stmt = db()->prepare('INSERT INTO site_content (unit_slug, content_key, content_value) VALUES (?,?,?)
                                ON DUPLICATE KEY UPDATE content_value = VALUES(content_value)');
        foreach ($fields as $key => $f) {
            $stmt->execute([$unit, $key, trim($_POST[$key] ?? '')]);
        }
        $success = true;
    }
}

$pageTitle = $site['label'] . '-' . $page['label'];
require __DIR__ . '/includes/admin_header.php';

$stmt = db()->prepare('SELECT content_key, content_value FROM site_content WHERE unit_slug = ?');
$stmt->execute([$unit]);
$values = [];
foreach ($stmt->fetchAll() as $r) { $values[$r['content_key']] = $r['content_value']; }
?>
<nav class="small text-muted mb-3">
  <a href="<?= BASE_URL ?>/admin/unit_sites.php">Sites</a> &rsaquo;
  <a href="<?= BASE_URL ?>/admin/site.php?site=<?= h($unit) ?>"><?= h($site['label']) ?></a> &rsaquo;
  <?= h($page['label']) ?>
</nav>
<?php if ($success): ?><div class="alert alert-success">Saved. Changes are live on the site immediately.</div><?php endif; ?>
<form method="post" class="card-anu">
  <div class="body">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <?php foreach ($fields as $key => $f): ?>
      <div class="mb-3">
        <label class="form-label small text-muted"><?= h($f['label']) ?></label>
        <?php if (($f['type'] ?? 'text') === 'textarea'): ?>
          <textarea name="<?= h($key) ?>" rows="4" class="form-control" placeholder="<?= h($f['default']) ?>"><?= h($values[$key] ?? '') ?></textarea>
        <?php else: ?>
          <input type="text" name="<?= h($key) ?>" class="form-control" value="<?= h($values[$key] ?? '') ?>" placeholder="<?= h($f['default']) ?>">
        <?php endif; ?>
        <?php if (empty($values[$key])): ?>
          <div class="form-text">Currently showing the default text above (shown as a placeholder). Type here to replace it.</div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
    <button class="btn btn-apply mt-2" type="submit">Save</button>
    <a href="<?= BASE_URL ?>/admin/site.php?site=<?= h($unit) ?>" class="btn btn-outline-anu mt-2">&larr; Back to <?= h($site['label']) ?></a>
  </div>
</form>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
