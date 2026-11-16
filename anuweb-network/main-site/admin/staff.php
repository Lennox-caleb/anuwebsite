<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['editor','super_admin']);
$pageTitle = 'Faculty & Staff';
require __DIR__ . '/includes/admin_header.php';

$sites = include __DIR__ . '/includes/sites_registry.php';
$regularUnits = array_keys($sites); // this list's own valid unit slugs
$leaderSlugs  = array_filter(array_map(fn($s) => $s['leader_slug'] ?? null, $sites));

$unit = $_GET['unit'] ?? '';

$sql = 'SELECT * FROM staff WHERE unit_slug NOT IN (' . implode(',', array_fill(0, count($leaderSlugs), '?')) . ')';
$params = array_values($leaderSlugs);
if ($unit !== '') { $sql .= ' AND unit_slug = ?'; $params[] = $unit; }
$sql .= ' ORDER BY unit_slug, sort_order';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>
<p class="text-muted small">Regular staff/faculty listings only, one site at a time. (Dean's Message, Registrar and other featured welcome photos live on each site's own page under Admin -> Sites, not here. ANU Online and Non-Degree have their own separate admin.)</p>
<div class="d-flex justify-content-between mb-3">
  <div>
    <a href="?unit=" class="btn btn-sm <?= $unit===''?'btn-apply':'btn-outline-anu' ?>">All</a>
    <?php foreach ($sites as $slug => $site): ?>
      <a href="?unit=<?= h($slug) ?>" class="btn btn-sm <?= $unit===$slug?'btn-apply':'btn-outline-anu' ?>"><?= h($site['label']) ?></a>
    <?php endforeach; ?>
  </div>
  <a href="<?= BASE_URL ?>/admin/staff_form.php<?= $unit !== '' ? '?unit=' . h($unit) : '' ?>" class="btn btn-apply">+ New Person</a>
</div>
<div class="card-anu">
  <div class="body p-0">
    <table class="table table-anu mb-0">
      <thead><tr><th>Name</th><th>Title</th><th>Site</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= h($r['full_name']) ?></td>
          <td><?= h($r['title']) ?></td>
          <td><span class="badge bg-secondary"><?= h($sites[$r['unit_slug']]['label'] ?? $r['unit_slug']) ?></span></td>
          <td><?= $r['is_published'] ? '<span class="badge bg-success">Published</span>' : '<span class="badge bg-secondary">Hidden</span>' ?></td>
          <td class="text-end">
            <a href="<?= BASE_URL ?>/admin/staff_form.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-anu">Edit</a>
            <a href="<?= BASE_URL ?>/admin/staff_delete.php?id=<?= (int)$r['id'] ?>&amp;t=<?= urlencode(delete_token()) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this person?');">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="5" class="text-muted">No entries yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
