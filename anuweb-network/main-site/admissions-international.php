<?php
/**
 * Phase 7b -- International Students page, branded "ANUISA" per the
 * spec (replacing Daystar's "ISADU" naming in the structural pattern
 * this hub is modeled on). Per the spec's own instruction, the full
 * expansion of "ANUISA" is NOT invented here -- anu.ac.ke does not
 * publish one, so this page uses the initialism as a section label only.
 *
 * Content sourced from anu.ac.ke (the ANU-to-MVNU exchange program page
 * and the application-fee page); that source page itself has very
 * little substantive detail on eligibility/process, which is reported
 * honestly below rather than invented.
 */
$pageTitle = 'International Students (ANUISA)';
$pageDescription = 'Information for international applicants to Africa Nazarene University, and the ANU-MVNU exchange program.';
require_once __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/header.php';
?>
<section class="about-sub-hero">
  <div class="container container-narrow text-center">
    <span class="hero-eyebrow d-inline-block mb-2">ANUISA</span>
    <h1>International Students</h1>
  </div>
</section>

<section class="py-5">
  <div class="container container-narrow">
    <p class="text-muted"><?= nl2br(h(page_content('admissions', 'international_intro', 'Africa Nazarene University welcomes students from outside Kenya. This page covers what international applicants need to know, and the exchange partnership between ANU and Mount Vernon Nazarene University (MVNU).'))) ?></p>

    <div class="program-section">
      <h2 class="program-section-heading"><i class="bi bi-passport"></i> Applying as an International Student</h2>
      <div class="program-section-body">
        <div class="program-fact"><span class="program-fact-icon"><i class="bi bi-cash-coin"></i></span><span><strong>Application fee</strong><span><?= h(page_content('admissions', 'fee_international', 'USD 35')) ?>, no fee waivers are offered on application or tuition fees for international students.</span></span></div>
        <div class="program-fact"><span class="program-fact-icon"><i class="bi bi-file-earmark-text"></i></span><span><strong>Immigration documents</strong><span>A valid passport, visa and police clearance certificate from your home country are required. Joining instructions are sent to admitted applicants.</span></span></div>
        <div class="program-fact"><span class="program-fact-icon"><i class="bi bi-translate"></i></span><span><strong>English proficiency</strong><span>Applicants from non-English-speaking countries must submit English language test results.</span></span></div>
        <div class="program-fact"><span class="program-fact-icon"><i class="bi bi-credit-card"></i></span><span><strong>Paying from abroad</strong><span>International wire transfer or PayPal, see <a href="<?= BASE_URL ?>/admissions-tuition-payment.php">Tuition Payment Options</a>.</span></span></div>
      </div>
      <p class="small text-muted mt-3 mb-0">Full requirements are the same as for local applicants, see <a href="<?= BASE_URL ?>/admissions-requirements.php">Requirements &amp; Application Fees</a>, plus the immigration documents above.</p>
    </div>

    <div class="program-section">
      <h2 class="program-section-heading"><i class="bi bi-arrow-left-right"></i> ANU &ndash; MVNU Exchange Program</h2>
      <div class="program-section-body">
        <p>ANU and Mount Vernon Nazarene University (MVNU) in Ohio, USA, another Church of the Nazarene institution, run a student exchange under a memorandum of understanding: students from each university switch places for one semester, with ANU students paying their usual ANU fees. The first exchange took place in January 2024.</p>
        <p class="mb-2">To express interest, email <a href="mailto:studyabroad@anu.ac.ke">studyabroad@anu.ac.ke</a>. Applications typically open nine months before the exchange semester.</p>
        <?php $__sl = (string) setting('url_student_life'); $__slx = strpos($__sl, '?') !== false ? $__sl : rtrim(preg_replace('#/index\.php$#', '', $__sl), '/') . '/exchange-program.php'; ?><a href="<?= h($__slx) ?>" class="btn btn-outline-anu">Full details, eligibility &amp; FAQs &rarr;</a>
      </div>
    </div>

    <div class="d-flex gap-3 flex-wrap mt-4">
      <a href="<?= BASE_URL ?>/admissions-enquiries.php" class="btn btn-apply btn-lg">Ask Admissions a Question</a>
      <a href="<?= BASE_URL ?>/admissions.php" class="btn btn-outline-anu btn-lg">&larr; Back to Admissions</a>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
