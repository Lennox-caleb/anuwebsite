<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['editor','super_admin']);
$pageTitle = 'Photo Gallery';
require __DIR__ . '/includes/admin_header.php';

// Phase (news+events merge): ANU's real Photo Gallery on anu.ac.ke is a
// grid of album links out to Facebook photo sets -- not a self-hosted
// gallery. We mirror that honestly instead of inventing a self-hosted
// gallery ANU doesn't actually have: each row here is one album (a
// title + a link to its real Facebook album), with an optional cover
// image you can upload if you have one.
$stmt = db()->query('SELECT * FROM photo_gallery ORDER BY sort_order, id');
$rows = $stmt->fetchAll();
?>
<p class="text-muted small">Each entry links out to a real photo album (Facebook, or any other album host). This mirrors how anu.ac.ke's own Photo Gallery works today -- not a self-hosted gallery.</p>
<div class="d-flex justify-content-end mb-3">
  <a href="<?= BASE_URL ?>/admin/photo_gallery_form.php" class="btn btn-apply">+ New Album</a>
</div>
<div class="card-anu">
  <div class="body p-0">
    <table class="table table-anu mb-0">
      <thead><tr><th>Title</th><th>Link</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= h($r['title']) ?></td>
          <td><a href="<?= h($r['external_url']) ?>" target="_blank" rel="noopener" class="small"><?= h(mb_strimwidth($r['external_url'], 0, 50, '…')) ?></a></td>
          <td><?= $r['is_published'] ? '<span class="badge bg-success">Published</span>' : '<span class="badge bg-secondary">Hidden</span>' ?></td>
          <td class="text-end">
            <a href="<?= BASE_URL ?>/admin/photo_gallery_form.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-anu">Edit</a>
            <a href="<?= BASE_URL ?>/admin/photo_gallery_delete.php?id=<?= (int)$r['id'] ?>&amp;t=<?= urlencode(delete_token()) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this album?');">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="4" class="text-muted">No albums yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
