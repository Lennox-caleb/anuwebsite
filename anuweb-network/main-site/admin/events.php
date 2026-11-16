<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['editor','super_admin']);
$pageTitle = 'Events';
require __DIR__ . '/includes/admin_header.php';

$unitLabels = [
    'law' => 'School of Law', 'business' => 'School of Business',
    'science-technology' => 'School of Science & Technology',
    'humanities' => 'School of Humanities & Social Sciences',
    'religion' => 'School of Religion & Christian Ministry',
];
$unit = $_GET['unit'] ?? null;
$sql = 'SELECT * FROM events';
$params = [];
// Phase 5c fix: don't fatal if unit_slug isn't migrated onto this
// database yet when an admin clicks one of the unit filter buttons.
if ($unit !== null && schema_has_column('events', 'unit_slug')) {
    if ($unit === '') {
        $sql .= ' WHERE unit_slug IS NULL';
    } else {
        $sql .= ' WHERE unit_slug = ?';
        $params[] = $unit;
    }
}
$sql .= ' ORDER BY start_datetime DESC';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <a href="?" class="btn btn-sm <?= $unit===null?'btn-apply':'btn-outline-anu' ?>">All</a>
    <a href="?unit=" class="btn btn-sm <?= $unit===''?'btn-apply':'btn-outline-anu' ?>">Main Site</a>
    <?php foreach ($unitLabels as $val => $label): ?>
      <a href="?unit=<?= h($val) ?>" class="btn btn-sm <?= $unit===$val?'btn-apply':'btn-outline-anu' ?>"><?= h($label) ?></a>
    <?php endforeach; ?>
  </div>
  <a href="<?= BASE_URL ?>/admin/events_form.php<?= $unit ? '?unit=' . urlencode($unit) : '' ?>" class="btn btn-apply">+ New Event</a>
</div>
<div class="card-anu">
  <div class="body p-0">
    <table class="table table-anu mb-0">
      <thead><tr><th>Title</th><th>Category</th><th>Posted to</th><th>Date</th><th>Location</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= h($r['title']) ?></td>
          <td><?= !empty($r['category']) ? h($r['category']) : '<span class="text-muted">-</span>' ?></td>
          <td><?= h($r['unit_slug'] ? ($unitLabels[$r['unit_slug']] ?? $r['unit_slug']) : 'Main Site') ?></td>
          <td><?= date('d M Y, g:i A', strtotime($r['start_datetime'])) ?></td>
          <td><?= h($r['location']) ?></td>
          <td><?= $r['is_published'] ? '<span class="badge bg-success">Published</span>' : '<span class="badge bg-secondary">Draft</span>' ?></td>
          <td class="text-end">
            <a href="<?= BASE_URL ?>/admin/events_form.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-anu">Edit</a>
            <a href="<?= BASE_URL ?>/admin/events_delete.php?id=<?= (int)$r['id'] ?>&amp;t=<?= urlencode(delete_token()) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this event?');">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="7" class="text-muted">No events yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
