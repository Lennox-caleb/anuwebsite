<?php
$pageTitle = 'How to Apply';
require_once __DIR__ . '/config/db.php';
$__hero = site_hero();
require __DIR__ . '/includes/header.php';
?>
<section class="adm-hero compact<?= $__hero && $__hero['image'] ? ' has-image' : '' ?>"<?= $__hero && $__hero['image'] ? ' style="background-image: linear-gradient(180deg, rgba(10,10,10,.55) 0%, rgba(10,10,10,.72) 100%), url(\'' . h(MAIN_SITE_URL . '/assets/uploads/' . $__hero['image']) . '\')"' : '' ?>>
  <div class="container">
    <div class="eyebrow mb-2">The process</div>
    <h1>How to Apply</h1>
  </div>
</section>

<section class="adm-section">
  <div class="container container-narrow">
    <div class="adm-step text-start px-0">
      <div class="d-flex align-items-center gap-3 mb-2"><div class="num">1</div><h3 class="mb-0">Pick your programme</h3></div>
      <p class="text-muted">Browse programmes across ANU's five schools, Non-Degree, ANU Online and Short Courses, and confirm the entry requirements for your chosen level.</p>
    </div>
    <div class="adm-step text-start px-0">
      <div class="d-flex align-items-center gap-3 mb-2"><div class="num">2</div><h3 class="mb-0">Create your application</h3></div>
      <p class="text-muted">Start your application through the <a href="<?= h(unit_content('application_portal_url', 'https://records.anu.ac.ke/')) ?>" target="_blank" rel="noopener">online records portal</a> and pay the application fee, <?= h(unit_content('fee_local', 'KSh 1,500')) ?> for Kenyan/East African applicants, or <?= h(unit_content('fee_international', 'USD 35')) ?> for international applicants, via M-Pesa or bank transfer.</p>
    </div>
    <div class="adm-step text-start px-0">
      <div class="d-flex align-items-center gap-3 mb-2"><div class="num">3</div><h3 class="mb-0">Upload your documents</h3></div>
      <p class="text-muted">Submit certified academic transcripts, ID, and a passport photo, see the <a href="<?= BASE_URL ?>/requirements.php">Requirements</a> page for the full list.</p>
    </div>
    <div class="adm-step text-start px-0">
      <div class="d-flex align-items-center gap-3 mb-2"><div class="num">4</div><h3 class="mb-0">Await your admission letter</h3></div>
      <p class="text-muted">Feedback is usually given within <?= h(unit_content('feedback_time', '5 working days')) ?>. Admission is on a first-come, first-served basis, so early applications are encouraged.</p>
    </div>
    <div class="adm-step text-start px-0">
      <div class="d-flex align-items-center gap-3 mb-2"><div class="num">5</div><h3 class="mb-0">Confirm your place</h3></div>
      <p class="text-muted">Follow the fee payment guide sent with your admission letter to secure your place and register. Admission letters remain valid for one year, so you may defer without a financial penalty.</p>
    </div>

    <a href="<?= h(main_setting('apply_url')) ?>" class="btn btn-adm-cta btn-lg mt-4">Start Your Application</a>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
