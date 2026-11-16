<?php
$pageTitle = 'Alumni Card';
require_once __DIR__ . '/config/db.php';
$__hero = site_hero();
require __DIR__ . '/includes/header.php';
?>
<section class="alum-hero compact<?= $__hero && $__hero['image'] ? ' has-image' : '' ?>"<?= $__hero && $__hero['image'] ? ' style="background-image: linear-gradient(180deg, rgba(10,10,10,.55) 0%, rgba(10,10,10,.72) 100%), url(\'' . h(MAIN_SITE_URL . '/assets/uploads/' . $__hero['image']) . '\')"' : '' ?>><div class="container"><div class="eyebrow mb-2">Register</div><h1>Alumni Card</h1></div></section>
<section class="alum-section">
  <div class="container container-narrow">
    <p class="text-muted"><?= nl2br(h(unit_content('alumni_card_intro', 'Register or update your alumni information and apply for your official ANU Alumni Card, your ongoing connection to the University community.'))) ?></p>
    <div class="row g-4 mt-2">
      <div class="col-md-6">
        <div class="alum-card"><span class="icon"><i class="bi bi-person-vcard"></i></span><h3>Register / Update Your Information</h3><p class="small text-muted mb-0">Keep your contact details current so the Alumni Office can reach you with updates and events.</p></div>
      </div>
      <div class="col-md-6">
        <div class="alum-card"><span class="icon"><i class="bi bi-credit-card"></i></span><h3>Card Application &amp; Payment</h3><p class="small text-muted mb-0"><?= nl2br(h(unit_content('alumni_card_payment_note', 'Application steps and payment details for the Alumni Card should be confirmed with the Alumni Affairs Office.'))) ?></p></div>
      </div>
    </div>
    <a href="<?= BASE_URL ?>/contact.php" class="btn btn-alum-cta btn-lg mt-4">Contact the Alumni Office</a>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
