<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['marketing','super_admin']);
$pageTitle = 'Resources Library';
require __DIR__ . '/includes/admin_header.php';
$rows = db()->query('SELECT * FROM resources ORDER BY category, title')->fetchAll();
?>
<div class="d-flex justify-content-end mb-3">
  <a href="<?= BASE_URL ?>/admin/resources_form.php" class="btn btn-apply">+ Upload Resource</a>
</div>
<div class="card-anu">
  <div class="body p-0">
    <table class="table table-anu mb-0">
      <thead><tr><th>Title</th><th>Category</th><th>Size</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= h($r['title']) ?></td>
          <td><?= h($r['category']) ?></td>
          <td><?= format_bytes((int)$r['file_size']) ?></td>
          <td><?= $r['is_published'] ? '<span class="badge bg-success">Published</span>' : '<span class="badge bg-secondary">Hidden</span>' ?></td>
          <td class="text-end">
            <a href="<?= BASE_URL ?>/admin/resources_form.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-anu">Edit</a>
            <a href="<?= BASE_URL ?>/admin/resources_delete.php?id=<?= (int)$r['id'] ?>&amp;t=<?= urlencode(delete_token()) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this resource?');">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="5" class="text-muted">No resources uploaded yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
