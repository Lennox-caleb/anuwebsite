<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['marketing','super_admin']);
$pageTitle = 'Vacancies';
require __DIR__ . '/includes/admin_header.php';
$rows = db()->query('SELECT * FROM vacancies ORDER BY closing_date DESC, created_at DESC')->fetchAll();
?>
<div class="d-flex justify-content-end mb-3">
  <a href="<?= BASE_URL ?>/admin/vacancies_form.php" class="btn btn-apply">+ New Vacancy</a>
</div>
<div class="card-anu">
  <div class="body p-0">
    <table class="table table-anu mb-0">
      <thead><tr><th>Title</th><th>Department</th><th>Type</th><th>Closing</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= h($r['title']) ?></td>
          <td><?= h($r['department']) ?></td>
          <td><?= h($r['employment_type']) ?></td>
          <td><?= $r['closing_date'] ? format_date($r['closing_date']) : '-' ?></td>
          <td><?= $r['is_published'] ? '<span class="badge bg-success">Published</span>' : '<span class="badge bg-secondary">Hidden</span>' ?></td>
          <td class="text-end">
            <a href="<?= BASE_URL ?>/admin/vacancies_form.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-anu">Edit</a>
            <a href="<?= BASE_URL ?>/admin/vacancies_delete.php?id=<?= (int)$r['id'] ?>&amp;t=<?= urlencode(delete_token()) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this vacancy?');">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="6" class="text-muted">No vacancies posted yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
