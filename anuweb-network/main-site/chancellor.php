<?php
// The Chancellor's photo and welcome message are now managed from admin
// panel -> Leadership (Role: "Chancellor"), the same place as the Vice
// Chancellor and Deputy Vice Chancellors. If one is published, send
// visitors straight to that full profile page; otherwise fall back to
// the explanatory note below rather than a broken or empty page.
$pageTitle = 'Chancellor';
$pageDescription = "The Chancellor of Africa Nazarene University.";
require_once __DIR__ . '/includes/functions.php';

$stmt = db()->query("SELECT id FROM leadership WHERE role_type = 'chancellor' AND is_published = 1 ORDER BY sort_order LIMIT 1");
$chancellor = $stmt->fetch();

if ($chancellor) {
    redirect('/leadership-message.php?id=' . (int)$chancellor['id']);
}

require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container container-narrow text-center">
    <span class="hero-eyebrow d-inline-block mb-2" style="color: var(--anu-red);">Office of the Chancellor</span>
    <h1>Chancellor</h1>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="message-card reveal">
      <div class="message-card-photo">
        <span class="placeholder-icon"><i class="bi bi-person"></i></span>
      </div>
      <div class="message-card-body">
        <i class="bi bi-quote"></i>
        <span class="message-card-eyebrow">Church of the Nazarene Leadership</span>
        <p class="message">As a Church of the Nazarene sponsored university, ANU's Chancellor is provided
        through the Church's denominational leadership structure. The current office holder's name, photo
        and a welcome message will be added here once confirmed by the University's communications office.</p>
      </div>
    </div>
    <a href="<?= BASE_URL ?>/about.php" class="btn btn-outline-anu mt-3">&larr; Back to About ANU</a>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
