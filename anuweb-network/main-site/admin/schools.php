<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['editor','super_admin']);
$pageTitle = 'Schools';
require __DIR__ . '/includes/admin_header.php';
$rows = db()->query('SELECT * FROM schools ORDER BY sort_order')->fetchAll();
?>
<div class="d-flex justify-content-end mb-3">
  <a href="<?= BASE_URL ?>/admin/schools_form.php" class="btn btn-apply">+ New School</a>
</div>
<div class="card-anu">
  <div class="body p-0">
    <table class="table table-anu mb-0">
      <thead><tr><th>#</th><th>Name</th><th>URL slug</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= (int)$r['sort_order'] ?></td>
          <td><?= h($r['name']) ?></td>
          <td><code class="small text-muted">/schools/<?= h($r['slug']) ?></code></td>
          <td><?= $r['is_published'] ? '<span class="badge bg-success">Published</span>' : '<span class="badge bg-secondary">Draft</span>' ?></td>
          <td class="text-end">
            <a href="<?= BASE_URL ?>/admin/schools_form.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-anu">Edit</a>
            <a href="<?= BASE_URL ?>/admin/schools_delete.php?id=<?= (int)$r['id'] ?>&amp;t=<?= urlencode(delete_token()) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this school? Linked programmes will be unassigned.');">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
