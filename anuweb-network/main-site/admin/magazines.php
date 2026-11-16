<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['marketing','super_admin']);
$pageTitle = 'ASPIRE & ANU Voice';
require __DIR__ . '/includes/admin_header.php';
$rows = db()->query('SELECT * FROM publications_gallery ORDER BY publication_type, published_date DESC')->fetchAll();
?>
<p class="text-muted small">Thumbnail-cover gallery for ASPIRE (graduation magazine, released once per graduation) and ANU Voice (semesterly newsletter), shown on the public Resources page.</p>
<div class="d-flex justify-content-end mb-3">
  <a href="<?= BASE_URL ?>/admin/magazines_form.php" class="btn btn-apply">+ New Issue</a>
</div>
<div class="card-anu">
  <div class="body p-0">
    <table class="table table-anu mb-0">
      <thead><tr><th>Cover</th><th>Title</th><th>Type</th><th>Issue</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?php if ($r['thumbnail']): ?><img src="<?= BASE_URL ?>/assets/uploads/<?= h($r['thumbnail']) ?>" class="img-preview-sm"><?php else: ?><span class="text-muted small">No cover</span><?php endif; ?></td>
          <td><?= h($r['title']) ?></td>
          <td><span class="badge bg-secondary"><?= h($r['publication_type']) ?></span></td>
          <td><?= h($r['issue_label']) ?></td>
          <td><?= $r['is_published'] ? '<span class="badge bg-success">Published</span>' : '<span class="badge bg-secondary">Hidden</span>' ?></td>
          <td class="text-end">
            <a href="<?= BASE_URL ?>/admin/magazines_form.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-anu">Edit</a>
            <a href="<?= BASE_URL ?>/admin/magazines_delete.php?id=<?= (int)$r['id'] ?>&amp;t=<?= urlencode(delete_token()) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this issue?');">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="6" class="text-muted">No issues added yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
