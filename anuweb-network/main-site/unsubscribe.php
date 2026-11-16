<?php
$pageTitle = 'Unsubscribe';
$extraHead = '<meta name="robots" content="noindex,nofollow">';
require_once __DIR__ . '/includes/functions.php';

$token = trim($_GET['t'] ?? $_POST['t'] ?? '');
$sub = null;
if (preg_match('/^[a-f0-9]{32}$/', $token)) {
    $q = db()->prepare('SELECT id, email, is_active FROM subscribers WHERE unsubscribe_token = ?');
    $q->execute([$token]);
    $sub = $q->fetch() ?: null;
}

$done = false;
// POST = the visitor pressed the button, or a mail app's one-click "Unsubscribe" button.
// (A plain link click only shows the confirmation page, so mail scanners can't unsubscribe anyone by accident.)
if ($sub && $_SERVER['REQUEST_METHOD'] === 'POST') {
    db()->prepare('UPDATE subscribers SET is_active = 0, unsubscribed_at = NOW() WHERE id = ?')->execute([$sub['id']]);
    $done = true;
}
require __DIR__ . '/includes/header.php';
?>
<section class="py-5">
  <div class="container container-narrow text-center" style="max-width:640px;">
    <h1 class="mb-3">Unsubscribe</h1>
    <?php if (!$sub): ?>
      <p class="text-muted">This unsubscribe link is not valid. If you keep receiving emails you do not want, please
      <a href="<?= BASE_URL ?>/contact.php">contact us</a> and we will remove you.</p>
    <?php elseif ($done || !(int)$sub['is_active']): ?>
      <div class="alert alert-success"><?= $done ? 'You have been unsubscribed.' : 'You are already unsubscribed.' ?> You will no longer receive newsletters from Africa Nazarene University.</div>
      <a class="btn btn-apply" href="<?= BASE_URL ?>/index.php">Back to the website</a>
    <?php else: ?>
      <p class="mb-4">Stop sending newsletters to <strong><?= h($sub['email']) ?></strong>?</p>
      <form method="post">
        <input type="hidden" name="t" value="<?= h($token) ?>">
        <button class="btn btn-apply" type="submit">Yes, unsubscribe me</button>
        <a class="btn btn-outline-dark ms-2" href="<?= BASE_URL ?>/index.php">No, keep me subscribed</a>
      </form>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
