<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['editor','super_admin']);
$pageTitle = 'Departments';
require __DIR__ . '/includes/admin_header.php';

// Phase 5: Departments belong to the 5 academic school sites only. A
// school with zero rows here shows NO "Departments" nav item at all on
// its public site -- checked live from this same table, not a flag --
// so an empty list here is a normal, honest starting state, not a bug.
$schoolUnits = [
    'law' => 'School of Law', 'business' => 'School of Business',
    'science-technology' => 'School of Science & Technology',
    'humanities' => 'School of Humanities & Social Sciences',
    'religion' => 'School of Religion & Christian Ministry',
];

$unit = $_GET['unit'] ?? '';
$sql = 'SELECT * FROM departments';
$params = [];
if ($unit !== '' && isset($schoolUnits[$unit])) { $sql .= ' WHERE unit_slug = ?'; $params[] = $unit; }
$sql .= ' ORDER BY unit_slug, sort_order';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>
<p class="text-muted small">Each school's own Departments page won't show a "Departments" menu item on the public site until at least one is added here for that school.</p>
<div class="d-flex justify-content-between mb-3">
  <div>
    <a href="?unit=" class="btn btn-sm <?= $unit===''?'btn-apply':'btn-outline-anu' ?>">All</a>
    <?php foreach ($schoolUnits as $slug => $label): ?>
      <a href="?unit=<?= h($slug) ?>" class="btn btn-sm <?= $unit===$slug?'btn-apply':'btn-outline-anu' ?>"><?= h($label) ?></a>
    <?php endforeach; ?>
  </div>
  <a href="<?= BASE_URL ?>/admin/departments_form.php<?= $unit !== '' ? '?unit=' . h($unit) : '' ?>" class="btn btn-apply">+ New Department</a>
</div>
<div class="card-anu">
  <div class="body p-0">
    <table class="table table-anu mb-0">
      <thead><tr><th>Name</th><th>School</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= h($r['name']) ?></td>
          <td><span class="badge bg-secondary"><?= h($schoolUnits[$r['unit_slug']] ?? $r['unit_slug']) ?></span></td>
          <td><?= $r['is_published'] ? '<span class="badge bg-success">Published</span>' : '<span class="badge bg-secondary">Hidden</span>' ?></td>
          <td class="text-end">
            <a href="<?= BASE_URL ?>/admin/departments_form.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-anu">Edit</a>
            <a href="<?= BASE_URL ?>/admin/departments_delete.php?id=<?= (int)$r['id'] ?>&amp;t=<?= urlencode(delete_token()) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this department?');">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="4" class="text-muted">No departments yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
