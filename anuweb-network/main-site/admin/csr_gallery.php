<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['marketing','super_admin']);
$pageTitle = 'CSR Photo Gallery';
require __DIR__ . '/includes/admin_header.php';
$rows = db()->query('SELECT * FROM csr_gallery ORDER BY sort_order, created_at DESC')->fetchAll();
?>
<p class="text-muted small">Shown in the gallery on the public CSR page.</p>
<div class="d-flex justify-content-end mb-3">
  <a href="<?= BASE_URL ?>/admin/csr_gallery_form.php" class="btn btn-apply">+ New Photo</a>
</div>
<div class="card-anu">
  <div class="body p-0">
    <table class="table table-anu mb-0">
      <thead><tr><th>Photo</th><th>Title</th><th>Category</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?php if ($r['photo']): ?><img src="<?= BASE_URL ?>/assets/uploads/<?= h($r['photo']) ?>" class="img-preview-sm"><?php else: ?><span class="text-muted small">No photo</span><?php endif; ?></td>
          <td><?= h($r['title']) ?></td>
          <td><?= h($r['category']) ?></td>
          <td><?= $r['is_published'] ? '<span class="badge bg-success">Published</span>' : '<span class="badge bg-secondary">Hidden</span>' ?></td>
          <td class="text-end">
            <a href="<?= BASE_URL ?>/admin/csr_gallery_form.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-anu">Edit</a>
            <a href="<?= BASE_URL ?>/admin/csr_gallery_delete.php?id=<?= (int)$r['id'] ?>&amp;t=<?= urlencode(delete_token()) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this photo?');">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="5" class="text-muted">No photos added yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
