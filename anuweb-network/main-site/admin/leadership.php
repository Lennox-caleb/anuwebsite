<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['editor','super_admin']);
$pageTitle = 'Leadership';
require __DIR__ . '/includes/admin_header.php';
$rows = db()->query("SELECT * FROM leadership ORDER BY FIELD(role_type,'chancellor','vc','dvc'), sort_order")->fetchAll();
$vcCount = 0; $chancellorCount = 0;
foreach ($rows as $r) {
    if ($r['role_type'] === 'vc') $vcCount++;
    if ($r['role_type'] === 'chancellor') $chancellorCount++;
}
$roleLabels = ['vc' => 'Vice Chancellor', 'dvc' => 'Deputy VC', 'chancellor' => 'Chancellor'];
?>
<p class="text-muted small">This is the one place to manage the Chancellor, Vice Chancellor and Deputy Vice Chancellors. Each person you add here automatically gets their own public welcome-message page (the Chancellor's page also links from About ANU).</p>
<div class="alert alert-info small mb-3">
  <strong>How this works:</strong> add the Chancellor once (Role: "Chancellor"), the Vice Chancellor once (Role: "Vice Chancellor"), and as many Deputy Vice Chancellors as you need (Role: "Deputy Vice Chancellor"), e.g. Academic Affairs, Finance, Strategy. You do not need to touch Faculty &amp; Staff or Governance for any of these roles anymore.
  <?php if ($vcCount > 1): ?>
    <br><strong class="text-danger">Note:</strong> more than one entry is currently marked "Vice Chancellor", only the first published one is shown as the VC on the public site. Consider unpublishing or removing the extra entry.
  <?php endif; ?>
  <?php if ($chancellorCount > 1): ?>
    <br><strong class="text-danger">Note:</strong> more than one entry is currently marked "Chancellor", only the first published one is shown on the public site. Consider unpublishing or removing the extra entry.
  <?php endif; ?>
</div>
<div class="d-flex justify-content-end mb-3">
  <a href="<?= BASE_URL ?>/admin/leadership_form.php" class="btn btn-apply">+ New Leader</a>
</div>
<div class="card-anu">
  <div class="body p-0">
    <table class="table table-anu mb-0">
      <thead><tr><th>Photo</th><th>Name</th><th>Title</th><th>Role</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td>
            <?php if ($r['photo']): ?>
              <img src="<?= BASE_URL ?>/assets/uploads/<?= h($r['photo']) ?>" class="img-preview-circle-sm rounded-circle border" alt="">
            <?php else: ?>
              <span class="text-muted small">No photo</span>
            <?php endif; ?>
          </td>
          <td><?= h($r['full_name']) ?></td>
          <td><?= h($r['title']) ?></td>
          <td><span class="badge bg-secondary"><?= h($roleLabels[$r['role_type']] ?? $r['role_type']) ?></span></td>
          <td><?= $r['is_published'] ? '<span class="badge bg-success">Published</span>' : '<span class="badge bg-secondary">Hidden</span>' ?></td>
          <td class="text-end">
            <a href="<?= BASE_URL ?>/admin/leadership_form.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-anu">Edit</a>
            <a href="<?= BASE_URL ?>/admin/leadership_delete.php?id=<?= (int)$r['id'] ?>&amp;t=<?= urlencode(delete_token()) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this leader? Their public page will stop working.');">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="6" class="text-muted">No one added yet, click "+ New Leader" above to add the Vice Chancellor or a Deputy Vice Chancellor.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
