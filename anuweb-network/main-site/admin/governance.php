<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['editor','super_admin']);
$pageTitle = 'Governance';
require __DIR__ . '/includes/admin_header.php';
$rows = db()->query("SELECT * FROM governance_members ORDER BY FIELD(category,'Board of Trustees','University Council','Management'), sort_order")->fetchAll();
?>
<p class="text-muted small">These entries populate the public Governance page (Board of Trustees, University Council).</p>
<div class="alert alert-info small">
  <strong>Looking for the Vice Chancellor or a Deputy Vice Chancellor?</strong> They now have their own
  section, go to <a href="<?= BASE_URL ?>/admin/leadership.php">Leadership</a> in the menu on the left.
  Please don't add them here anymore; the Management category below is only for other Management-team
  members who aren't the VC or a DVC.
</div>
<div class="d-flex justify-content-end mb-3">
  <a href="<?= BASE_URL ?>/admin/governance_form.php" class="btn btn-apply">+ New Member</a>
</div>
<div class="card-anu">
  <div class="body p-0">
    <table class="table table-anu mb-0">
      <thead><tr><th>Name</th><th>Title</th><th>Category</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= h($r['full_name']) ?></td>
          <td><?= h($r['title']) ?></td>
          <td><span class="badge bg-secondary"><?= h($r['category']) ?></span></td>
          <td><?= $r['is_published'] ? '<span class="badge bg-success">Published</span>' : '<span class="badge bg-secondary">Hidden</span>' ?></td>
          <td class="text-end">
            <a href="<?= BASE_URL ?>/admin/governance_form.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-anu">Edit</a>
            <a href="<?= BASE_URL ?>/admin/governance_delete.php?id=<?= (int)$r['id'] ?>&amp;t=<?= urlencode(delete_token()) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this member?');">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="5" class="text-muted">No entries yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
