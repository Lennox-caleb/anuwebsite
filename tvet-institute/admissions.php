<?php
$pageTitle = 'Admissions';
require_once __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/header.php';
?>
<section class="tvet-hero compact<?= page_banner_url('admissions') ? ' has-image' : '' ?>">
  <?php if ($b = page_banner_url('admissions')): ?><img class="hero-bg-image" src="<?= h($b) ?>" alt=""><?php endif; ?>
  <div class="container">
    <div class="eyebrow mb-2">Join Us</div>
    <h1>Admissions</h1>
    <p class="lead">Everything you need to know to apply, from entry requirements to how the process works.</p>
  </div>
</section>

<section class="tvet-section">
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-7">
        <h2 class="h4 mb-4">How to Apply</h2>
        <div class="step-timeline">
          <div class="step"><h4>Choose your programme</h4><p class="small text-muted mb-0">Browse the <a href="<?= BASE_URL ?>/programmes.php">Programmes</a> page and confirm you meet the entry requirements for your chosen level.</p></div>
          <div class="step"><h4>Complete the online application</h4><p class="small text-muted mb-0">Fill in the <a href="<?= h(apply_url()) ?>" target="_blank" rel="noopener">online application form</a> with your personal, academic and programme details.</p></div>
          <div class="step"><h4>Submit supporting documents</h4><p class="small text-muted mb-0">Bring or email certified copies of your KCPE/KCSE certificate (or equivalent), national ID/birth certificate, and passport photos to the Admissions Office.</p></div>
          <div class="step"><h4>Receive your admission letter</h4><p class="small text-muted mb-0">Successful applicants receive an official admission letter with fee structure and reporting date.</p></div>
          <div class="step"><h4>Pay fees &amp; report</h4><p class="small text-muted mb-0">Pay the requisite fees (instalment plans available) and report for orientation on the stated date.</p></div>
        </div>
        <a href="<?= h(apply_url()) ?>" target="_blank" rel="noopener" class="btn btn-tvet-cta btn-lg mt-2">Start Your Application</a>
      </div>
      <div class="col-lg-5">
        <div class="tvet-card" style="padding-top:1.7rem">
          <h3 class="h5 mb-3">Minimum Entry Requirements</h3>
          <table class="table table-sm">
            <thead><tr><th>Level</th><th>Requirement</th></tr></thead>
            <tbody>
              <tr><td><span class="level-badge lvl-craft">Certificate</span></td><td class="small">KCSE certificate or equivalent</td></tr>
              <tr><td><span class="level-badge lvl-diploma">Diploma</span></td><td class="small">KCSE mean grade C- or a relevant Certificate</td></tr>
              <tr><td><span class="level-badge lvl-short">Short Course</span></td><td class="small">Open entry -- see individual programme page</td></tr>
            </tbody>
          </table>
          <hr>
          <h3 class="h6">Intakes</h3>
          <p class="small text-muted mb-0">January, May and August each year, plus rolling intakes for select short courses.</p>
        </div>
        <div class="tvet-card mt-4" style="padding-top:1.7rem">
          <h3 class="h5 mb-2"><i class="bi bi-globe"></i> International Applicants</h3>
          <p class="small text-muted mb-0">International and East African Community applicants are welcome. You will need equivalent academic documentation (evaluated where required), a valid passport, and where applicable a student pass/visa. Contact the Admissions Office for guidance on credential recognition and accommodation.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="tvet-section alt">
  <div class="container text-center">
    <h2>Questions before you apply?</h2>
    <p class="text-muted">Our Admissions Office is happy to help.</p>
    <div class="d-flex gap-3 justify-content-center flex-wrap">
      <a href="<?= BASE_URL ?>/contact.php" class="btn btn-tvet-outline">Contact Admissions</a>
      <a href="<?= BASE_URL ?>/faqs.php" class="btn btn-tvet-outline">Read FAQs</a>
      <a href="<?= h(setting('whatsapp_url')) ?>" target="_blank" rel="noopener" class="btn btn-tvet-cta"><i class="bi bi-whatsapp"></i> Chat on WhatsApp</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
