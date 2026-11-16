<?php
/**
 * Phase 7b -- content ported from the old admissions-site/how-to-apply.php
 * (already sourced from anu.ac.ke in an earlier phase), rebuilt as a
 * main-site page. Admin-editable fields via Admin -> Sites -> Admissions.
 */
$pageTitle = 'How to Apply';
$pageDescription = 'Step-by-step guide to applying to Africa Nazarene University.';
require_once __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/header.php';
?>
<section class="about-sub-hero">
  <div class="container container-narrow text-center">
    <span class="hero-eyebrow d-inline-block mb-2">The Process</span>
    <h1>How to Apply</h1>
  </div>
</section>

<section class="py-5">
  <div class="container container-narrow">
    <div class="program-section">
      <h2 class="program-section-heading"><span class="program-fact-icon me-1">1</span> Pick your programme</h2>
      <div class="program-section-body">
        <p class="mb-0">Browse programmes across ANU's five schools, Non-Degree, ANU Online and Short Courses, and confirm the entry requirements for your chosen level.</p>
      </div>
    </div>

    <div class="program-section">
      <h2 class="program-section-heading"><span class="program-fact-icon me-1">2</span> Create your application</h2>
      <div class="program-section-body">
        <p class="mb-0">Start your application through the <a href="<?= h(page_content('admissions', 'application_portal_url', 'https://records.anu.ac.ke/')) ?>" target="_blank" rel="noopener">online records portal</a> and pay the application fee, <?= h(page_content('admissions', 'fee_local', 'KES 1,500')) ?> for Kenyan/East African applicants, or <?= h(page_content('admissions', 'fee_international', 'USD 35')) ?> for international applicants. See <a href="<?= BASE_URL ?>/admissions-tuition-payment.php">Tuition Payment Options</a> for how to pay.</p>
      </div>
    </div>

    <div class="program-section">
      <h2 class="program-section-heading"><span class="program-fact-icon me-1">3</span> Upload your documents</h2>
      <div class="program-section-body">
        <p class="mb-0">Submit certified academic transcripts, ID, and a passport photo, see the <a href="<?= BASE_URL ?>/admissions-requirements.php">Requirements &amp; Application Fees</a> page for the full list.</p>
      </div>
    </div>

    <div class="program-section">
      <h2 class="program-section-heading"><span class="program-fact-icon me-1">4</span> Await your admission letter</h2>
      <div class="program-section-body">
        <p class="mb-0">Feedback is usually given within <?= h(page_content('admissions', 'feedback_time', '5 working days')) ?>. Admission is on a first-come, first-served basis, so early applications are encouraged.</p>
      </div>
    </div>

    <div class="program-section">
      <h2 class="program-section-heading"><span class="program-fact-icon me-1">5</span> Confirm your place</h2>
      <div class="program-section-body">
        <p class="mb-0">Follow the fee payment guide sent with your admission letter to secure your place and register. Admission letters remain valid for one year, so you may defer without a financial penalty.</p>
      </div>
    </div>

    <a href="<?= h(setting('apply_url')) ?>" target="_blank" rel="noopener" class="btn btn-apply btn-lg mt-2">Start Your Application</a>
    <a href="<?= BASE_URL ?>/admissions.php" class="btn btn-outline-anu btn-lg mt-2">&larr; Back to Admissions</a>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
