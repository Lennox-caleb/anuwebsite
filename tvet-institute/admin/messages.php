<?php
$pageTitle = 'Contact Messages';
require_once __DIR__ . '/includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'mark') {
    if (csrf_verify()) {
        db()->prepare('UPDATE contact_messages SET status = ? WHERE id = ?')->execute([$_POST['status'], (int)$_POST['id']]);
    }
    redirect(ADMIN_URL . '/messages.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'delete') {
    if (csrf_verify()) {
        db()->prepare('DELETE FROM contact_messages WHERE id = ?')->execute([(int)$_POST['id']]);
        flash_set('success', 'Message deleted.');
    }
    redirect(ADMIN_URL . '/messages.php');
}

$viewId = (int)($_GET['view'] ?? 0);
if ($viewId) {
    db()->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ? AND status = 'new'")->execute([$viewId]);
}
$messages = db()->query('SELECT * FROM contact_messages ORDER BY created_at DESC')->fetchAll();
$viewRecord = null;
foreach ($messages as $m) { if ((int)$m['id'] === $viewId) { $viewRecord = $m; break; } }
require __DIR__ . '/includes/admin-header.php';
?>

<?php if ($viewRecord): ?>
<div class="admin-card mb-4">
  <div class="admin-card-header">
    <h2>Message from <?= h($viewRecord['full_name']) ?></h2>
    <a href="messages.php" class="btn btn-sm btn-outline-secondary">&larr; Back to list</a>
  </div>
  <p class="small text-muted mb-1"><i class="bi bi-envelope"></i> <?= h($viewRecord['email']) ?> &nbsp; <?php if($viewRecord['phone']): ?><i class="bi bi-telephone"></i> <?= h($viewRecord['phone']) ?><?php endif; ?></p>
  <p class="small text-muted mb-3"><i class="bi bi-calendar3"></i> <?= fdate($viewRecord['created_at'], 'd M Y, H:i') ?></p>
  <p><strong><?= h($viewRecord['subject']) ?></strong></p>
  <p><?= nl2br(h($viewRecord['message'])) ?></p>
  <a class="btn btn-admin-primary btn-sm" href="mailto:<?= h($viewRecord['email']) ?>?subject=RE: <?= h($viewRecord['subject']) ?>"><i class="bi bi-reply"></i> Reply by Email</a>
  <form method="post" class="d-inline">
    <?= csrf_field() ?><input type="hidden" name="form" value="mark"><input type="hidden" name="id" value="<?= (int)$viewRecord['id'] ?>"><input type="hidden" name="status" value="replied">
    <button class="btn btn-outline-success btn-sm" type="submit"><i class="bi bi-check2"></i> Mark as Replied</button>
  </form>
</div>
<?php endif; ?>

<div class="admin-card">
  <div class="admin-card-header"><h2>Messages <span class="text-muted small">(<?= count($messages) ?>)</span></h2></div>
  <div class="table-responsive">
    <table class="table table-admin table-hover align-middle">
      <thead><tr><th>From</th><th>Subject</th><th>Status</th><th>Date</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
      <?php if (!$messages): ?><tr><td colspan="5" class="text-center text-muted py-4">No messages yet.</td></tr><?php endif; ?>
      <?php foreach ($messages as $m): ?>
        <tr class="<?= $m['status'] === 'new' ? 'fw-bold' : '' ?>">
          <td><?= h($m['full_name']) ?><div class="small text-muted fw-normal"><?= h($m['email']) ?></div></td>
          <td class="small fw-normal"><a class="text-dark" href="messages.php?view=<?= (int)$m['id'] ?>"><?= h(excerpt($m['subject'] ?: $m['message'], 50)) ?></a></td>
          <td><span class="badge badge-status-<?= h($m['status']) ?>"><?= h(ucfirst($m['status'])) ?></span></td>
          <td class="small text-muted fw-normal"><?= fdate($m['created_at']) ?></td>
          <td class="text-end">
            <a href="messages.php?view=<?= (int)$m['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
            <form method="post" class="d-inline" onsubmit="return confirm('Delete this message?');">
              <?= csrf_field() ?><input type="hidden" name="form" value="delete"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
              <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/includes/admin-footer.php'; ?>
