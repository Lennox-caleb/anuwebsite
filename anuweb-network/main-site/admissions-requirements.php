<?php
/**
 * Phase 7b -- content ported from admissions-site/requirements.php plus
 * real application-fee figures confirmed against anu.ac.ke/application-fee/.
 */
$pageTitle = 'Admission Requirements & Application Fees';
$pageDescription = 'What you need to apply to Africa Nazarene University, and current application fees.';
require_once __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/header.php';
?>
<section class="about-sub-hero">
  <div class="container container-narrow text-center">
    <span class="hero-eyebrow d-inline-block mb-2">Before You Apply</span>
    <h1>Requirements &amp; Application Fees</h1>
  </div>
</section>

<section class="py-5">
  <div class="container container-narrow">
    <p class="text-muted"><?= nl2br(h(page_content('admissions', 'requirements_intro', "Specific entry requirements vary by programme and level (certificate, diploma, undergraduate, masters, PhD). The list below covers what's generally needed to start an application, the Admissions office will confirm exact requirements for your chosen programme."))) ?></p>

    <div class="program-section">
      <h2 class="program-section-heading"><i class="bi bi-list-check"></i> What You'll Need</h2>
      <div class="program-section-body">
        <div class="program-fact"><span class="program-fact-icon"><i class="bi bi-file-earmark-text"></i></span><span><strong>Completed application form</strong><span>Submitted through the online application portal.</span></span></div>
        <div class="program-fact"><span class="program-fact-icon"><i class="bi bi-mortarboard"></i></span><span><strong>Academic transcripts &amp; certificates</strong><span>Certified copies of your highest qualification relevant to the programme level.</span></span></div>
        <div class="program-fact"><span class="program-fact-icon"><i class="bi bi-person-vcard"></i></span><span><strong>Identification document</strong><span>National ID or passport copy.</span></span></div>
        <div class="program-fact"><span class="program-fact-icon"><i class="bi bi-camera"></i></span><span><strong>Passport photo</strong><span>A recent passport-size photograph.</span></span></div>
        <div class="program-fact"><span class="program-fact-icon"><i class="bi bi-arrow-left-right"></i></span><span><strong>Credit transfer (if applicable)</strong><span>Credits from other accredited universities are accepted for undergraduate programmes.</span></span></div>
        <div class="program-fact"><span class="program-fact-icon"><i class="bi bi-passport"></i></span><span><strong>International applicants</strong><span>Valid immigration documents and a police clearance certificate are required, see <a href="<?= BASE_URL ?>/admissions-international.php">International Students</a>.</span></span></div>
      </div>
    </div>

    <div class="program-section">
      <h2 class="program-section-heading"><i class="bi bi-cash-coin"></i> Application Fees</h2>
      <div class="program-section-body">
        <div class="program-fact"><span class="program-fact-icon"><i class="bi bi-flag"></i></span><span><strong>Kenyan / East African applicants</strong><span><?= h(page_content('admissions', 'fee_local', 'KES 1,500')) ?>, charged once per application.</span></span></div>
        <div class="program-fact"><span class="program-fact-icon"><i class="bi bi-globe"></i></span><span><strong>International applicants</strong><span><?= h(page_content('admissions', 'fee_international', 'USD 35')) ?>.</span></span></div>
        <p class="small text-muted mb-0">See <a href="<?= BASE_URL ?>/admissions-tuition-payment.php">Tuition Payment Options</a> for how to pay by M-Pesa, bank transfer or PayPal. Upon payment, an official receipt is issued by the Finance office, attach it to your online application.</p>
      </div>
    </div>

    <p class="text-muted mt-4">International applicants and postgraduate applicants (Masters/PhD) should confirm additional requirements, such as equivalence certification or research proposals, directly with the Admissions office before applying. No interview is required unless your chosen programme has special entry requirements.</p>

    <div class="d-flex gap-3 flex-wrap mt-4">
      <a href="<?= BASE_URL ?>/admissions-enquiries.php" class="btn btn-apply btn-lg">Ask Admissions a Question</a>
      <a href="<?= h(page_content('admissions', 'application_form_url', 'https://www.anu.ac.ke/download/application-form/')) ?>" target="_blank" rel="noopener" class="btn btn-outline-anu btn-lg">Download Application Form</a>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
