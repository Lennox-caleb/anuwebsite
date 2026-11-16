<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['marketing','super_admin']);
current_admin();

if (isset($_GET['read'])) {
    db()->prepare('UPDATE contact_messages SET is_read = 1 WHERE id = ?')->execute([(int)$_GET['read']]);
    redirect('/admin/messages.php');
}
if (isset($_GET['delete'])) {
    db()->prepare('DELETE FROM contact_messages WHERE id = ?')->execute([(int)$_GET['delete']]);
    redirect('/admin/messages.php');
}

$pageTitle = 'Messages';
require __DIR__ . '/includes/admin_header.php';
$rows = db()->query('SELECT * FROM contact_messages ORDER BY submitted_at DESC')->fetchAll();
?>
<div class="card-anu">
  <div class="body p-0">
    <table class="table table-anu mb-0">
      <thead><tr><th>From</th><th>Unit</th><th>Subject</th><th>Message</th><th>Received</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr class="<?= $r['is_read'] ? '' : 'fw-semibold' ?>">
          <td><?= h($r['full_name']) ?><br><span class="small text-muted"><?= h($r['email']) ?></span></td>
          <td><span class="badge bg-secondary"><?= h($r['unit']) ?></span></td>
          <td><?= h($r['subject']) ?></td>
          <td class="td-message"><?= h(excerpt($r['message'], 120)) ?></td>
          <td><?= date('d M Y, g:i A', strtotime($r['submitted_at'])) ?></td>
          <td class="text-end">
            <?php if (!$r['is_read']): ?><a href="?read=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-anu">Mark read</a><?php endif; ?>
            <a href="?delete=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this message?');">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="6" class="text-muted">No messages yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
