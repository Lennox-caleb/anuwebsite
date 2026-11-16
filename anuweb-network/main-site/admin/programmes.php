<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['editor','super_admin']);
$pageTitle = 'Programmes';
require __DIR__ . '/includes/admin_header.php';

$schoolId = isset($_GET['school_id']) && $_GET['school_id'] !== '' ? (int)$_GET['school_id'] : null;
$unit     = $_GET['unit'] ?? null;
$schools  = db()->query('SELECT id, name FROM schools ORDER BY sort_order')->fetchAll();

$sql = 'SELECT p.*, s.name AS school_name FROM programmes p LEFT JOIN schools s ON s.id = p.school_id';
$where = []; $params = [];
if ($schoolId !== null) { $where[] = 'p.school_id = ?'; $params[] = $schoolId; }
if ($unit !== null && $unit !== '') { $where[] = 'p.unit_slug = ?'; $params[] = $unit; }
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY p.level, p.title';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$newLink = BASE_URL . '/admin/programmes_form.php';
if ($schoolId !== null) $newLink .= '?school_id=' . $schoolId;
elseif ($unit) $newLink .= '?unit=' . urlencode($unit);
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <a href="?" class="btn btn-sm <?= ($schoolId===null && $unit===null)?'btn-apply':'btn-outline-anu' ?>">All</a>
    <?php foreach ($schools as $s): ?>
      <a href="?school_id=<?= (int)$s['id'] ?>" class="btn btn-sm <?= $schoolId===(int)$s['id']?'btn-apply':'btn-outline-anu' ?>"><?= h($s['name']) ?></a>
    <?php endforeach; ?>
    <a href="?unit=sepc" class="btn btn-sm <?= $unit==='sepc'?'btn-apply':'btn-outline-anu' ?>">SEPC</a>
  </div>
  <a href="<?= h($newLink) ?>" class="btn btn-apply">+ New Programme</a>
</div>
<div class="card-anu">
  <div class="body p-0">
    <table class="table table-anu mb-0">
      <thead><tr><th>Title</th><th>Level</th><th>School</th><th>Intake</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= h($r['title']) ?><?php if (!empty($r['is_featured'])): ?> <span class="badge bg-warning text-dark">Featured</span><?php endif; ?></td>
          <td><?= h($r['level']) ?></td>
          <td><?= h($r['school_name'] ?: ($r['unit_slug'] ? ucfirst(str_replace('-', ' ', $r['unit_slug'])) : '-')) ?></td>
          <td><?= !empty($r['intake']) ? h($r['intake']) : '<span class="text-muted">to confirm</span>' ?></td>
          <td><?= $r['is_published'] ? '<span class="badge bg-success">Published</span>' : '<span class="badge bg-secondary">Draft</span>' ?></td>
          <td class="text-end">
            <a href="<?= BASE_URL ?>/admin/programmes_form.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-anu">Edit</a>
            <a href="<?= BASE_URL ?>/admin/programmes_delete.php?id=<?= (int)$r['id'] ?>&amp;t=<?= urlencode(delete_token()) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this programme?');">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
