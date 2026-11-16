<?php
$pageTitle = 'Programmes';
require_once __DIR__ . '/includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'delete') {
    if (csrf_verify()) {
        db()->prepare('DELETE FROM programmes WHERE id = ?')->execute([(int)$_POST['id']]);
        flash_set('success', 'Programme deleted.');
    }
    redirect(ADMIN_URL . '/programmes.php');
}

$rows = db()->query("SELECT p.*, d.name AS dept_name FROM programmes p LEFT JOIN departments d ON d.id = p.department_id ORDER BY p.sort_order, p.title")->fetchAll();
require __DIR__ . '/includes/admin-header.php';
?>
<div class="admin-card">
  <div class="admin-card-header">
    <h2>Programmes <span class="text-muted small">(<?= count($rows) ?>)</span></h2>
    <a href="programme-edit.php" class="btn btn-admin-primary btn-sm"><i class="bi bi-plus-circle"></i> Add Programme</a>
  </div>
  <div class="table-responsive">
    <table class="table table-admin table-hover align-middle">
      <thead><tr><th>Image</th><th>Title</th><th>Level</th><th>Department</th><th>Duration</th><th>Featured</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
      <?php if (!$rows): ?><tr><td colspan="8" class="text-center text-muted py-4">No programmes yet.</td></tr><?php endif; ?>
      <?php foreach ($rows as $p): ?>
        <tr>
          <td><?php if ($p['image']): ?><img class="thumb-sm" src="<?= h(upload_url('programmes', $p['image'])) ?>" alt=""><?php else: ?>, <?php endif; ?></td>
          <td><strong><?= h($p['title']) ?></strong><div class="small text-muted">/<?= h($p['slug']) ?></div></td>
          <td><span class="badge bg-dark"><?= h($p['level']) ?></span></td>
          <td class="small"><?= h($p['dept_name'] ?? '-') ?></td>
          <td class="small"><?= h($p['duration']) ?></td>
          <td><?= $p['is_featured'] ? '<span class="badge bg-warning text-dark">Featured</span>' : '' ?></td>
          <td><?= $p['is_published'] ? '<span class="badge bg-success">Published</span>' : '<span class="badge bg-secondary">Draft</span>' ?></td>
          <td class="text-end">
            <a href="programme-edit.php?id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
            <form method="post" class="d-inline" onsubmit="return confirm('Delete this programme? This cannot be undone.');">
              <?= csrf_field() ?>
              <input type="hidden" name="form" value="delete">
              <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
              <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/includes/admin-footer.php'; ?>
