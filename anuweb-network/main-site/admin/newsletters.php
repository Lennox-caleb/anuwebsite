<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['marketing','super_admin']);
require_once __DIR__ . '/../includes/mailer.php';
require_once __DIR__ . '/../includes/newsletter_template.php';
$admin = current_admin();

$tableOk = (bool) db()->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'newsletters'")->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tableOk) {
    if (!csrf_check()) {
        flash_set('danger', 'Session expired. Please try again.');
    } else {
        $act = $_POST['action'] ?? '';
        if ($act === 'test_mail') {
            $to = trim($_POST['to'] ?? '');
            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                flash_set('danger', 'Enter a valid email address for the test.');
            } else {
                $html = '<div style="font-family:Arial,sans-serif;font-size:16px;line-height:1.6;color:#222;"><h2 style="margin:0 0 10px;">Email test</h2><p>This is a test message from the ANU website. If you can read it, sending works.</p></div>';
                $r = anu_mail_send($to, '', 'ANU website email test', $html, "Email test\n\nThis is a test message from the ANU website. If you can read it, sending works.\n");
                flash_set($r['ok'] ? 'success' : 'danger', $r['ok'] ? "Test email sent to $to. Check the inbox (and spam folder)." : 'Could not send: ' . $r['error']);
            }
        } elseif ($act === 'duplicate') {
            $src = db()->prepare('SELECT * FROM newsletters WHERE id = ?'); $src->execute([(int)($_POST['id'] ?? 0)]);
            if ($n = $src->fetch()) {
                db()->prepare('INSERT INTO newsletters (subject, preheader, headline, body, button_text, button_url, include_news, include_events, status, created_by) VALUES (?,?,?,?,?,?,?,?,?,?)')
                    ->execute([$n['subject'] . ' (copy)', $n['preheader'], $n['headline'], $n['body'], $n['button_text'], $n['button_url'], $n['include_news'], $n['include_events'], 'draft', $admin['id']]);
                flash_set('success', 'Copied as a new draft.');
            }
        } elseif ($act === 'delete') {
            $st = db()->prepare("SELECT status FROM newsletters WHERE id = ?"); $st->execute([(int)($_POST['id'] ?? 0)]);
            if (($st->fetchColumn() ?: '') === 'draft') {
                db()->prepare('DELETE FROM newsletters WHERE id = ? AND status = "draft"')->execute([(int)$_POST['id']]);
                flash_set('success', 'Draft deleted.');
            } else {
                flash_set('danger', 'Only drafts can be deleted (sent newsletters are kept as a record).');
            }
        }
    }
    redirect('/admin/newsletters.php');
}

$pageTitle = 'Newsletters';
require __DIR__ . '/includes/admin_header.php';

$driver = anu_mail_driver();
$cfg = anu_mail_config();
[$fromEmail, $fromName] = anu_mail_from();
$hasActive = (bool) db()->query("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'subscribers' AND column_name = 'is_active'")->fetchColumn();
$activeSubs = (int) db()->query('SELECT COUNT(*) FROM subscribers' . ($hasActive ? ' WHERE is_active = 1' : ''))->fetchColumn();
$rows = $tableOk ? db()->query("SELECT n.*, (SELECT COUNT(*) FROM newsletter_sends s WHERE s.newsletter_id = n.id AND s.status='sent') AS ok_count, (SELECT COUNT(*) FROM newsletter_sends s WHERE s.newsletter_id = n.id AND s.status='failed') AS fail_count FROM newsletters n ORDER BY n.id DESC")->fetchAll() : [];
?>
<?php if (!$tableOk): ?>
  <div class="alert alert-warning">The newsletter update has not been applied yet. Run the update's deploy script, then reload this page.</div>
<?php endif; ?>

<div class="row g-3 mb-3">
  <div class="col-lg-7">
    <div class="card-anu h-100"><div class="body">
      <h2 class="h6 mb-2">Email delivery</h2>
      <?php if ($driver === 'smtp'): ?>
        <p class="mb-1"><span class="badge bg-success">Ready</span> Sending through <strong><?= h($cfg['host']) ?></strong> as <strong><?= h($fromEmail) ?></strong>.</p>
      <?php else: ?>
        <p class="mb-1"><span class="badge bg-warning text-dark">Not set up</span> Using the server's basic mail program (<code>mail()</code>), which often does not work.</p>
        <p class="small text-muted mb-1">To send reliably, create <code>main-site/config/mail.php</code> from <code>includes/mail.sample.php</code> and enter your mail account details.</p>
      <?php endif; ?>
      <p class="small text-muted mb-2">Links and images inside emails start with <code><?= h(anu_nl_origin() ?: '(not set)') ?></code>, this must be your public website address (set <code>SITE_URL</code> in config).</p>
      <form method="post" class="d-flex flex-wrap gap-2">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="action" value="test_mail">
        <input type="email" name="to" class="form-control" style="max-width:280px" value="<?= h($admin['email'] ?? '') ?>" placeholder="Send a test email to…" required>
        <button class="btn btn-outline-anu" type="submit"<?= $tableOk ? '' : ' disabled' ?>>Send test email</button>
      </form>
    </div></div>
  </div>
  <div class="col-lg-5">
    <div class="card-anu h-100"><div class="body">
      <h2 class="h6 mb-2">Audience</h2>
      <div class="fs-2 fw-bold"><?= $activeSubs ?></div>
      <div class="small text-muted mb-2">active subscribers</div>
      <a href="<?= BASE_URL ?>/admin/subscribers.php" class="btn btn-sm btn-outline-anu">Manage subscribers</a>
    </div></div>
  </div>
</div>

<div class="d-flex justify-content-end mb-3">
  <a href="<?= BASE_URL ?>/admin/newsletter_form.php" class="btn btn-apply<?= $tableOk ? '' : ' disabled' ?>">+ New Newsletter</a>
</div>
<div class="card-anu"><div class="body p-0">
  <table class="table table-anu mb-0">
    <thead><tr><th>Subject</th><th>Status</th><th>Sent</th><th class="text-end">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><strong><?= h($r['subject']) ?></strong><div class="small text-muted"><?= date('d M Y', strtotime($r['created_at'])) ?></div></td>
        <td><?php
          if ($r['status'] === 'sent') echo '<span class="badge bg-success">Sent</span>';
          elseif ($r['status'] === 'sending') echo '<span class="badge bg-warning text-dark">Sending…</span>';
          else echo '<span class="badge bg-secondary">Draft</span>'; ?></td>
        <td class="small"><?= (int)$r['ok_count'] ?> delivered to server<?= (int)$r['fail_count'] ? ', <span class="text-danger">' . (int)$r['fail_count'] . ' failed</span>' : '' ?><?= $r['sent_at'] ? '<div class="text-muted">' . date('d M Y H:i', strtotime($r['sent_at'])) . '</div>' : '' ?></td>
        <td class="text-end">
          <form method="post" class="d-inline">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <?php if ($r['status'] === 'draft'): ?><a href="<?= BASE_URL ?>/admin/newsletter_form.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-anu">Edit</a><?php endif; ?>
            <a href="<?= BASE_URL ?>/admin/newsletter_preview.php?id=<?= (int)$r['id'] ?>" target="_blank" class="btn btn-sm btn-outline-anu">Preview</a>
            <a href="<?= BASE_URL ?>/admin/newsletter_send.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-apply"><?= $r['status'] === 'draft' ? 'Send…' : 'Details' ?></a>
            <button name="action" value="duplicate" class="btn btn-sm btn-outline-anu">Copy</button>
            <?php if ($r['status'] === 'draft'): ?><button name="action" value="delete" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this draft?');">Delete</button><?php endif; ?>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="4" class="text-muted">No newsletters yet. Click &ldquo;New Newsletter&rdquo; to write the first one.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div></div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
