<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['marketing','super_admin']);
$pageTitle = 'Chaplaincy Ministries';
require __DIR__ . '/includes/admin_header.php';
$rows = db()->query('SELECT * FROM chaplaincy_programs ORDER BY sort_order, id')->fetchAll();
?>
<p class="text-muted small">Shown on the Chaplaincy minisite's Ministries page.</p>
<div class="d-flex justify-content-end mb-3">
  <a href="<?= BASE_URL ?>/admin/chaplaincy_form.php" class="btn btn-apply">+ New Ministry</a>
</div>
<div class="card-anu">
  <div class="body p-0">
    <table class="table table-anu mb-0">
      <thead><tr><th>Title</th><th>Description</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><i class="bi <?= h($r['icon'] ?: 'bi-heart') ?> me-2"></i><?= h($r['title']) ?></td>
          <td class="small text-muted"><?= h(excerpt($r['description'] ?? '', 90)) ?></td>
          <td><?= $r['is_published'] ? '<span class="badge bg-success">Published</span>' : '<span class="badge bg-secondary">Hidden</span>' ?></td>
          <td class="text-end">
            <a href="<?= BASE_URL ?>/admin/chaplaincy_form.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-anu">Edit</a>
            <a href="<?= BASE_URL ?>/admin/chaplaincy_delete.php?id=<?= (int)$r['id'] ?>&amp;t=<?= urlencode(delete_token()) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this ministry?');">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="4" class="text-muted">No ministries added yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
