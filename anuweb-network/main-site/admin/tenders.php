<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['marketing','super_admin']);
$pageTitle = 'Tenders & Procurement';
require __DIR__ . '/includes/admin_header.php';
$rows = db()->query("SELECT * FROM tenders ORDER BY FIELD(status,'open','awarded','closed'), closing_date DESC, sort_order")->fetchAll();
$statusBadge = ['open' => 'bg-success', 'closed' => 'bg-secondary', 'awarded' => 'bg-primary'];
?>
<p class="text-muted small">Shown on the public Tenders &amp; Procurement page.</p>
<div class="d-flex justify-content-end mb-3">
  <a href="<?= BASE_URL ?>/admin/tenders_form.php" class="btn btn-apply">+ New Tender</a>
</div>
<div class="card-anu">
  <div class="body p-0">
    <table class="table table-anu mb-0">
      <thead><tr><th>Title</th><th>Reference</th><th>Category</th><th>Closing Date</th><th>Status</th><th>Published</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= h($r['title']) ?></td>
          <td><?= h($r['reference_no']) ?></td>
          <td><?= h($r['category']) ?></td>
          <td><?= $r['closing_date'] ? h(date('d M Y', strtotime($r['closing_date']))) : '-' ?></td>
          <td><span class="badge <?= $statusBadge[$r['status']] ?? 'bg-secondary' ?>"><?= h(ucfirst($r['status'])) ?></span></td>
          <td><?= $r['is_published'] ? '<span class="badge bg-success">Published</span>' : '<span class="badge bg-secondary">Hidden</span>' ?></td>
          <td class="text-end">
            <a href="<?= BASE_URL ?>/admin/tenders_form.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-anu">Edit</a>
            <a href="<?= BASE_URL ?>/admin/tenders_delete.php?id=<?= (int)$r['id'] ?>&amp;t=<?= urlencode(delete_token()) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this tender?');">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="7" class="text-muted">No tenders added yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
