<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['marketing','super_admin']);
current_admin();

$status = $_GET['status'] ?? 'all';
if (!in_array($status, ['all', 'active', 'unsubscribed'], true)) $status = 'all';
$q = trim($_GET['q'] ?? '');

// Is the newsletter upgrade (is_active column) present? Pages keep working before the update is applied.
$hasActive = (bool) db()->query("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'subscribers' AND column_name = 'is_active'")->fetchColumn();

// Row actions (POST + CSRF): switch subscription on/off, or remove a person entirely.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        flash_set('danger', 'Session expired. Please try again.');
    } else {
        $sid = (int)($_POST['id'] ?? 0);
        $act = $_POST['action'] ?? '';
        if ($sid && $hasActive && $act === 'toggle') {
            db()->prepare('UPDATE subscribers SET is_active = 1 - is_active, unsubscribed_at = IF(is_active = 0, NOW(), NULL) WHERE id = ?')->execute([$sid]);
            flash_set('success', 'Subscriber updated.');
        } elseif ($sid && $act === 'delete') {
            db()->prepare('DELETE FROM subscribers WHERE id = ?')->execute([$sid]);
            flash_set('success', 'Subscriber removed.');
        }
    }
    redirect('/admin/subscribers.php?status=' . urlencode($status) . ($q !== '' ? '&q=' . urlencode($q) : ''));
}

$where = []; $args = [];
if ($hasActive && $status === 'active') $where[] = 'is_active = 1';
if ($hasActive && $status === 'unsubscribed') $where[] = 'is_active = 0';
if ($q !== '') { $where[] = '(email LIKE ? OR full_name LIKE ?)'; $args[] = "%$q%"; $args[] = "%$q%"; }
$sql = 'SELECT * FROM subscribers' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY subscribed_at DESC';

if (isset($_GET['export'])) {
    $st = db()->prepare($sql); $st->execute($args);
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="anu-subscribers.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Name', 'Email', 'Subscribed At', 'Status']);
    foreach ($st->fetchAll() as $r) fputcsv($out, [$r['full_name'], $r['email'], $r['subscribed_at'], $hasActive ? ($r['is_active'] ? 'active' : 'unsubscribed') : 'active']);
    fclose($out);
    exit;
}

$pageTitle = 'Subscribers';
require __DIR__ . '/includes/admin_header.php';
$st = db()->prepare($sql); $st->execute($args);
$rows = $st->fetchAll();
$total  = (int) db()->query('SELECT COUNT(*) FROM subscribers')->fetchColumn();
$active = $hasActive ? (int) db()->query('SELECT COUNT(*) FROM subscribers WHERE is_active = 1')->fetchColumn() : $total;
?>
<?php if (!$hasActive): ?><div class="alert alert-warning">The newsletter update has not been applied yet (run the update's deploy script). Subscribers are listed, but unsubscribing is not available until then.</div><?php endif; ?>
<div class="row g-3 mb-3">
  <div class="col-sm-4"><div class="card-anu"><div class="body"><div class="small text-muted">Total</div><div class="fs-3 fw-bold"><?= $total ?></div></div></div></div>
  <div class="col-sm-4"><div class="card-anu"><div class="body"><div class="small text-muted">Active (will receive newsletters)</div><div class="fs-3 fw-bold"><?= $active ?></div></div></div></div>
  <div class="col-sm-4"><div class="card-anu"><div class="body"><div class="small text-muted">Unsubscribed</div><div class="fs-3 fw-bold"><?= $total - $active ?></div></div></div></div>
</div>
<form method="get" class="d-flex flex-wrap gap-2 justify-content-between mb-3">
  <div class="d-flex flex-wrap gap-2">
    <input type="search" name="q" value="<?= h($q) ?>" class="form-control" style="width:240px" placeholder="Search name or email">
    <select name="status" class="form-select" style="width:170px" onchange="this.form.submit()">
      <option value="all" <?= $status==='all'?'selected':'' ?>>All</option>
      <option value="active" <?= $status==='active'?'selected':'' ?>>Active</option>
      <option value="unsubscribed" <?= $status==='unsubscribed'?'selected':'' ?>>Unsubscribed</option>
    </select>
    <button class="btn btn-outline-anu" type="submit">Filter</button>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= BASE_URL ?>/admin/newsletters.php" class="btn btn-apply"><i class="bi bi-send"></i> Newsletters</a>
    <a href="?export=1&amp;status=<?= h($status) ?>&amp;q=<?= h(urlencode($q)) ?>" class="btn btn-outline-anu"><i class="bi bi-download"></i> Export CSV</a>
  </div>
</form>
<div class="card-anu">
  <div class="body p-0">
    <table class="table table-anu mb-0">
      <thead><tr><th>Name</th><th>Email</th><th>Subscribed</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): $on = !$hasActive || (int)$r['is_active'] === 1; ?>
        <tr>
          <td><?= h($r['full_name']) ?></td>
          <td><?= h($r['email']) ?></td>
          <td><?= date('d M Y', strtotime($r['subscribed_at'])) ?></td>
          <td><?= $on ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Unsubscribed</span>' ?></td>
          <td class="text-end">
            <form method="post" class="d-inline">
              <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <?php if ($hasActive): ?><button name="action" value="toggle" class="btn btn-sm btn-outline-anu"><?= $on ? 'Unsubscribe' : 'Resubscribe' ?></button><?php endif; ?>
              <button name="action" value="delete" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this person from the list permanently?');">Remove</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="5" class="text-muted">No subscribers found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
