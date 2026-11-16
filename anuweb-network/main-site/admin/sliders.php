<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['editor','marketing','super_admin']);
$pageTitle = 'Hero Images';
require __DIR__ . '/includes/admin_header.php';

$unit = $_GET['unit'] ?? null; // null = show all, '' = Main site only, else a unit slug
$units = db()->query("SELECT DISTINCT unit_slug FROM sliders WHERE unit_slug IS NOT NULL ORDER BY unit_slug")->fetchAll(PDO::FETCH_COLUMN);

$sql = 'SELECT * FROM sliders';
$params = [];
if ($unit !== null) {
    if ($unit === '') { $sql .= ' WHERE unit_slug IS NULL'; }
    else { $sql .= ' WHERE unit_slug = ?'; $params[] = $unit; }
}
$sql .= ' ORDER BY unit_slug IS NULL DESC, unit_slug, sort_order';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>
<p class="text-muted small">One hero image per site's homepage (the main site can have several, shown as a slideshow).</p>
<div class="d-flex justify-content-between mb-3">
  <div>
    <a href="?" class="btn btn-sm <?= $unit===null?'btn-apply':'btn-outline-anu' ?>">All</a>
    <a href="?unit=" class="btn btn-sm <?= $unit===''?'btn-apply':'btn-outline-anu' ?>">Main site</a>
    <?php foreach ($units as $u): ?>
      <a href="?unit=<?= h($u) ?>" class="btn btn-sm <?= $unit===$u?'btn-apply':'btn-outline-anu' ?>"><?= h($u) ?></a>
    <?php endforeach; ?>
  </div>
  <a href="<?= BASE_URL ?>/admin/sliders_form.php<?= $unit ? '?unit=' . h($unit) : '' ?>" class="btn btn-apply">+ New Hero Image</a>
</div>
<div class="card-anu">
  <div class="body p-0">
    <table class="table table-anu mb-0">
      <thead><tr><th>Image</th><th>Site</th><th>Headline</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td>
            <?php if ($r['image']): ?><img src="<?= BASE_URL ?>/assets/uploads/<?= h($r['image']) ?>" class="img-preview-sm"><?php else: ?><span class="text-muted small">No image</span><?php endif; ?>
            <?php if (!empty($r['video_url'])): ?>
              <?php if (hero_youtube_id($r['video_url'])): ?>
                <span class="badge bg-danger ms-1"><i class="bi bi-youtube"></i> YouTube</span>
              <?php else: ?>
                <span class="badge bg-dark ms-1"><i class="bi bi-camera-video"></i> Video</span>
              <?php endif; ?>
            <?php endif; ?>
          </td>
          <td><span class="badge bg-secondary"><?= h($r['unit_slug'] ?: 'Main site') ?></span></td>
          <td><?= h($r['headline']) ?></td>
          <td><?= $r['is_published'] ? '<span class="badge bg-success">Published</span>' : '<span class="badge bg-secondary">Hidden</span>' ?></td>
          <td class="text-end">
            <a href="<?= BASE_URL ?>/admin/sliders_form.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-anu">Edit</a>
            <a href="<?= BASE_URL ?>/admin/sliders_delete.php?id=<?= (int)$r['id'] ?>&amp;t=<?= urlencode(delete_token()) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this hero image?');">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="5" class="text-muted">No hero images added yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
