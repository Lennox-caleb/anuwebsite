<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['marketing','super_admin']);
require_once __DIR__ . '/../includes/mailer.php';
require_once __DIR__ . '/../includes/newsletter_template.php';
$admin = current_admin();

const NL_BATCH = 20;   // emails per request; the page continues automatically until everyone is done

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$st = db()->prepare('SELECT * FROM newsletters WHERE id = ?'); $st->execute([$id]);
$n = $st->fetch();
if (!$n) { flash_set('danger', 'Newsletter not found.'); redirect('/admin/newsletters.php'); }

$hasActive = (bool) db()->query("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'subscribers' AND column_name = 'is_active'")->fetchColumn();
$activeWhere = $hasActive ? 'sub.is_active = 1' : '1=1';

$news   = $n['include_news']   ? anu_nl_load_news(3)   : [];
$events = $n['include_events'] ? anu_nl_load_events(3) : [];

/** Recipients still to send: active subscribers with no send record, plus ones re-queued by "Retry failed". */
function nl_pending(int $nid, string $activeWhere, int $limit): array
{
    $cond = "(s.id IS NULL OR s.status = 'queued')";
    $sql = "SELECT sub.* FROM subscribers sub LEFT JOIN newsletter_sends s ON s.newsletter_id = ? AND s.subscriber_id = sub.id WHERE $activeWhere AND $cond ORDER BY sub.id LIMIT " . (int)$limit;
    $q = db()->prepare($sql); $q->execute([$nid]);
    return $q->fetchAll();
}
function nl_pending_count(int $nid, string $activeWhere): int
{
    $cond = "(s.id IS NULL OR s.status = 'queued')";
    $q = db()->prepare("SELECT COUNT(*) FROM subscribers sub LEFT JOIN newsletter_sends s ON s.newsletter_id = ? AND s.subscriber_id = sub.id WHERE $activeWhere AND $cond");
    $q->execute([$nid]);
    return (int)$q->fetchColumn();
}

/** Send one batch. Returns [sentOk, failed, lastError]. */
function nl_send_batch(array $n, array $news, array $events, string $activeWhere): array
{
    $subs = nl_pending((int)$n['id'], $activeWhere, NL_BATCH);
    if (!$subs) return [0, 0, ''];
    $msgs = [];
    foreach ($subs as $s) {
        $unsub = anu_nl_unsub_url($s);
        [$html, $text, $imgs] = anu_nl_render($n, $s, $unsub, $news, $events);
        $msgs[] = ['to' => $s['email'], 'name' => $s['full_name'] ?? '', 'subject' => $n['subject'], 'html' => $html, 'text' => $text, 'inline' => $imgs,
                   'headers' => ['List-Unsubscribe' => '<' . $unsub . '>', 'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click', 'Precedence' => 'bulk']];
    }
    $res = anu_mail_send_many($msgs);
    $ok = 0; $bad = 0; $last = '';
    $up = db()->prepare('INSERT INTO newsletter_sends (newsletter_id, subscriber_id, email, status, error) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE status = VALUES(status), error = VALUES(error), sent_at = NOW()');
    foreach ($subs as $i => $s) {
        $r = $res[$i] ?? ['ok' => false, 'error' => 'No result'];
        $up->execute([$n['id'], $s['id'], $s['email'], $r['ok'] ? 'sent' : 'failed', $r['ok'] ? null : mb_substr($r['error'], 0, 250)]);
        if ($r['ok']) $ok++; else { $bad++; $last = $r['error']; }
    }
    return [$ok, $bad, $last];
}

$notice = ''; $noticeType = 'info'; $running = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        flash_set('danger', csrf_error_message());
        redirect('/admin/newsletter_send.php?id=' . $id);
    }
    $act = $_POST['action'] ?? '';
    if ($act === 'test') {
        $to = trim($_POST['to'] ?? '');
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            flash_set('danger', 'Enter a valid email address for the test.');
        } else {
            $fake = ['full_name' => $admin['full_name'] ?? '', 'unsubscribe_token' => str_repeat('0', 32)];
            [$html, $text, $imgs] = anu_nl_render($n, $fake, anu_nl_unsub_url($fake), $news, $events);
            $r = anu_mail_send($to, '', '[TEST] ' . $n['subject'], $html, $text, [], $imgs);
            flash_set($r['ok'] ? 'success' : 'danger', $r['ok'] ? "Test sent to $to. Check the inbox (and spam folder)." : 'Test failed: ' . $r['error']);
        }
        redirect('/admin/newsletter_send.php?id=' . $id);
    }
    if ($act === 'send' || $act === 'retry') {
        if ($act === 'send' && $n['status'] === 'sent') { redirect('/admin/newsletter_send.php?id=' . $id); }
        if ($act === 'send' && empty($_POST['confirm'])) {
            flash_set('danger', 'Tick the box to confirm you have previewed the newsletter.');
            redirect('/admin/newsletter_send.php?id=' . $id);
        }
        if ($act === 'retry') {
            db()->prepare("UPDATE newsletter_sends SET status = 'queued' WHERE newsletter_id = ? AND status = 'failed'")->execute([$id]);
        }
        $_SESSION['nl_run'][$id] = 'go';
        db()->prepare("UPDATE newsletters SET status = 'sending' WHERE id = ?")->execute([$id]);
        redirect('/admin/newsletter_send.php?id=' . $id . '&run=1');
    }
}

// Continue sending (GET with run=1 only works after a POST started it in this session).
$mode = $_SESSION['nl_run'][$id] ?? '';
if (isset($_GET['run']) && $mode !== '') {
    @set_time_limit(120);
    [$ok, $bad, $last] = nl_send_batch($n, $news, $events, $activeWhere);
    $remaining = nl_pending_count($id, $activeWhere);
    $stall = ($ok === 0 && $bad > 0);               // whole batch failed (e.g. wrong mail login): stop instead of looping
    if ($remaining > 0 && !$stall) {
        $running = true;
    } else {
        unset($_SESSION['nl_run'][$id]);
        $anySent = (int) db()->query('SELECT COUNT(*) FROM newsletter_sends WHERE newsletter_id = ' . (int)$id . " AND status = 'sent'")->fetchColumn();
        db()->prepare("UPDATE newsletters SET status = ?, sent_at = IF(? = 'sent', COALESCE(sent_at, NOW()), sent_at) WHERE id = ?")->execute([$anySent ? 'sent' : 'draft', $anySent ? 'sent' : 'draft', $id]);
        $notice = $stall ? 'Sending stopped: the mail server rejected the messages. Last error: ' . $last . ', fix the mail settings, then use "Retry failed".' : 'Finished sending.';
        $noticeType = $stall ? 'danger' : 'success';
    }
    $st->execute([$id]); $n = $st->fetch();
}

$pageTitle = 'Send Newsletter';
require __DIR__ . '/includes/admin_header.php';

$counts = db()->prepare("SELECT SUM(status='sent') ok, SUM(status='failed') bad FROM newsletter_sends WHERE newsletter_id = ?");
$counts->execute([$id]); $c = $counts->fetch();
$okN = (int)($c['ok'] ?? 0); $badN = (int)($c['bad'] ?? 0);
$pending = nl_pending_count($id, $activeWhere);
$totalActive = (int) db()->query("SELECT COUNT(*) FROM subscribers sub WHERE $activeWhere")->fetchColumn();
$driver = anu_mail_driver();
?>
<?php if ($running): ?><meta http-equiv="refresh" content="1;url=<?= BASE_URL ?>/admin/newsletter_send.php?id=<?= $id ?>&amp;run=1"><?php endif; ?>
<?php if ($notice): ?><div class="alert alert-<?= $noticeType ?>"><?= h($notice) ?></div><?php endif; ?>

<?php if ($running): ?>
  <div class="alert alert-warning"><strong>Sending…</strong> <?= $okN ?> sent so far, <?= $pending ?> to go. Keep this page open, it continues by itself.</div>
<?php endif; ?>

<?php $mailOk = ($driver === 'smtp'); ?>
<style>
  .nl-steps .step-no{display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:50%;background:#000;color:#FAC823;font-weight:700;font-size:.8rem;margin-right:.5rem}
  .nl-sum{display:flex;flex-wrap:wrap;gap:.5rem 1.5rem;align-items:center}
  .nl-sum .k{color:#6b6b6b;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;display:block}
  .nl-sum .v{font-weight:600}
  .nl-prev iframe{width:100%;height:640px;border:0;background:#f1f1ee}
</style>

<div class="card-anu mb-3"><div class="body nl-sum">
  <div><span class="k">Subject</span><span class="v"><?= h($n['subject']) ?></span></div>
  <div><span class="k">Status</span><span class="v"><?= h(ucfirst($n['status'])) ?></span></div>
  <div><span class="k">Active subscribers</span><span class="v"><?= $totalActive ?></span></div>
  <div><span class="k">Delivered to server</span><span class="v"><?= $okN ?><?= $badN ? ' <span class="text-danger">/ ' . $badN . ' failed</span>' : '' ?></span></div>
  <div><span class="k">Mail</span><span class="v"><?= $mailOk ? 'SMTP set up' : '<span class="text-danger">Not set up</span>' ?></span></div>
  <div class="ms-auto d-flex gap-2">
    <a href="#nl-preview" class="btn btn-sm btn-outline-anu">View email preview</a>
    <?php if ($n['status'] === 'draft'): ?><a href="<?= BASE_URL ?>/admin/newsletter_form.php?id=<?= $id ?>" class="btn btn-sm btn-outline-anu">Edit</a><?php endif; ?>
  </div>
</div></div>

<?php if (!$mailOk): ?>
  <div class="alert alert-warning">Email sending is <strong>not set up</strong> on the server yet, so sending will probably fail. Create <code>main-site/config/mail.php</code> from <code>includes/mail.sample.php</code> (see the Newsletters page).</div>
<?php endif; ?>
<?php if (!$totalActive): ?>
  <div class="alert alert-info">There are <strong>no active subscribers</strong> yet, so the send button is switched off. You can still send yourself a test.</div>
<?php endif; ?>

<div class="row g-3 nl-steps">
  <div class="col-md-6">
    <div class="card-anu h-100"><div class="body">
      <h2 class="h6"><span class="step-no">1</span>Send yourself a test</h2>
      <form method="post" class="d-flex flex-wrap gap-2">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="id" value="<?= $id ?>">
        <input type="hidden" name="action" value="test">
        <input type="email" name="to" class="form-control" style="max-width:280px" value="<?= h($admin['email'] ?? '') ?>" required>
        <button class="btn btn-outline-anu" type="submit">Send test</button>
      </form>
      <p class="small text-muted mt-2 mb-0">Check it in your inbox (and spam) before sending to everyone.</p>
    </div></div>
  </div>
  <div class="col-md-6">
    <div class="card-anu h-100"><div class="body">
      <h2 class="h6"><span class="step-no">2</span>Send to subscribers</h2>
      <?php if ($n['status'] === 'sent' && !$pending && !$badN): ?>
        <p class="text-success mb-0">This newsletter has been sent to everyone who was subscribed at the time. To send another, use <em>Copy</em> on the Newsletters page.</p>
      <?php else: ?>
        <?php if ($n['status'] !== 'sent'): ?>
        <form method="post">
          <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
          <input type="hidden" name="id" value="<?= $id ?>">
          <input type="hidden" name="action" value="send">
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="confirm" id="conf" value="1">
            <label class="form-check-label" for="conf">I have previewed this and sent myself a test.</label>
          </div>
          <button class="btn btn-apply" type="submit" <?= $totalActive ? '' : 'disabled' ?> onclick="return confirm('Send this newsletter to <?= $pending ?> subscriber(s) now? This cannot be undone.');">Send to <?= $pending ?> subscriber<?= $pending === 1 ? '' : 's' ?></button>
        </form>
        <?php endif; ?>
        <?php if ($badN): ?>
        <form method="post" class="mt-3">
          <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
          <input type="hidden" name="id" value="<?= $id ?>">
          <input type="hidden" name="action" value="retry">
          <button class="btn btn-outline-danger" type="submit">Retry the <?= $badN ?> that failed</button>
        </form>
        <?php endif; ?>
      <?php endif; ?>
      <?php if ($badN): ?>
        <details class="mt-3 small"><summary>Why did some fail?</summary>
          <?php $fe = db()->prepare("SELECT email, error FROM newsletter_sends WHERE newsletter_id = ? AND status='failed' ORDER BY id DESC LIMIT 8"); $fe->execute([$id]);
          foreach ($fe->fetchAll() as $f): ?><div><?= h($f['email']) ?>, <?= h($f['error']) ?></div><?php endforeach; ?>
        </details>
      <?php endif; ?>
    </div></div>
  </div>
</div>

<div class="card-anu mt-3 nl-prev" id="nl-preview"><div class="body p-2">
  <div class="px-2 pt-1 pb-2 small text-muted">Preview, this is how the email looks (sample name shown).</div>
  <iframe src="<?= BASE_URL ?>/admin/newsletter_preview.php?id=<?= $id ?>" title="Newsletter preview"></iframe>
</div></div>
<p class="mt-3"><a href="<?= BASE_URL ?>/admin/newsletters.php">&larr; Back to newsletters</a></p>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
