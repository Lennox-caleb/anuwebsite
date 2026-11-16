<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['marketing','editor','super_admin']);
$pageTitle = 'Testimonials';
require __DIR__ . '/includes/admin_header.php';
$rows = db()->query('SELECT * FROM testimonials ORDER BY sort_order, id DESC')->fetchAll();
?>
<p class="text-muted small">Real quotes from students and alumni, shown in the homepage slider. Only published entries appear; with none published the section is hidden. Always get the person's permission before publishing.</p>
<div class="d-flex justify-content-end mb-3">
  <a href="<?= BASE_URL ?>/admin/testimonials_form.php" class="btn btn-apply">+ New Testimonial</a>
</div>
<div class="card-anu">
  <div class="body p-0">
    <table class="table table-anu mb-0">
      <thead><tr><th>Person</th><th>Quote</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><strong><?= h($r['person_name']) ?></strong><br><span class="small text-muted"><?= h($r['person_role']) ?></span></td>
          <td class="small"><?= h(excerpt($r['quote'], 90)) ?></td>
          <td><?= $r['is_published'] ? '<span class="badge bg-success">Published</span>' : '<span class="badge bg-secondary">Hidden</span>' ?></td>
          <td class="text-end">
            <a href="<?= BASE_URL ?>/admin/testimonials_form.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-anu">Edit</a>
            <a href="<?= BASE_URL ?>/admin/testimonials_delete.php?id=<?= (int)$r['id'] ?>&amp;t=<?= urlencode(delete_token()) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this testimonial?');">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="4" class="text-muted">No testimonials added yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
