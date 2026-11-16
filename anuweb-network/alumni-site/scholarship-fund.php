<?php
$pageTitle = 'Scholarship Fund';
require_once __DIR__ . '/config/db.php';
$__hero = site_hero();
require __DIR__ . '/includes/header.php';
?>
<section class="alum-hero compact<?= $__hero && $__hero['image'] ? ' has-image' : '' ?>"<?= $__hero && $__hero['image'] ? ' style="background-image: linear-gradient(180deg, rgba(10,10,10,.55) 0%, rgba(10,10,10,.72) 100%), url(\'' . h(MAIN_SITE_URL . '/assets/uploads/' . $__hero['image']) . '\')"' : '' ?>><div class="container"><div class="eyebrow mb-2">Give back</div><h1>ANU Alumni Scholarship Fund</h1></div></section>
<section class="alum-section">
  <div class="container container-narrow">
    <p class="text-muted"><?= nl2br(h(unit_content('scholarship_intro', 'The Alumni Scholarship Fund helps empower, encourage and motivate current students. Alumni and friends of the University can give directly to support the next generation of ANU graduates.'))) ?></p>
    <div class="row g-4 mt-2">
      <div class="col-md-6">
        <div class="alum-card"><span class="icon"><i class="bi bi-phone"></i></span><h3>Give via M-PESA</h3><p class="small text-muted mb-0">Donations can be made via the ANU Alumni Scholarship Fund M-PESA Paybill. Contact the Alumni Affairs Office for the current paybill and account number before sending a gift.</p></div>
      </div>
      <div class="col-md-6">
        <div class="alum-card"><span class="icon"><i class="bi bi-bank"></i></span><h3>Give via Bank Transfer</h3><p class="small text-muted mb-0">Bank account details for the ANU Alumni Scholarship Fund are available from the Alumni Affairs Office. Please email your deposit slip along with your address so the University can acknowledge your gift.</p></div>
      </div>
    </div>
    <p class="text-muted small mt-4"><?= nl2br(h(unit_content('scholarship_closing', 'Exact Paybill and bank account numbers should be confirmed with the Alumni Affairs Office before publishing, so donors always see current, correct details.'))) ?></p>
    <a href="<?= BASE_URL ?>/contact.php" class="btn btn-alum-cta btn-lg mt-2">Contact the Alumni Office</a>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
