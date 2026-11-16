<?php
$pageTitle = 'Admission Requirements';
require_once __DIR__ . '/config/db.php';
$__hero = site_hero();
require __DIR__ . '/includes/header.php';
?>
<section class="adm-hero compact<?= $__hero && $__hero['image'] ? ' has-image' : '' ?>"<?= $__hero && $__hero['image'] ? ' style="background-image: linear-gradient(180deg, rgba(10,10,10,.55) 0%, rgba(10,10,10,.72) 100%), url(\'' . h(MAIN_SITE_URL . '/assets/uploads/' . $__hero['image']) . '\')"' : '' ?>>
  <div class="container">
    <div class="eyebrow mb-2">Before you apply</div>
    <h1>Admission Requirements</h1>
  </div>
</section>

<section class="adm-section">
  <div class="container container-narrow">
    <p class="text-muted"><?= nl2br(h(unit_content('requirements_intro', "Specific entry requirements vary by programme and level (certificate, diploma, undergraduate, masters, PhD). The list below covers what's generally needed to start an application, the Admissions office will confirm exact requirements for your chosen programme."))) ?></p>

    <div class="adm-req-row">
      <i class="bi bi-file-earmark-text"></i>
      <div><strong>Completed application form</strong><p class="small text-muted mb-0">Submitted through the online application portal.</p></div>
    </div>
    <div class="adm-req-row">
      <i class="bi bi-mortarboard"></i>
      <div><strong>Academic transcripts &amp; certificates</strong><p class="small text-muted mb-0">Certified copies of your highest qualification relevant to the programme level.</p></div>
    </div>
    <div class="adm-req-row">
      <i class="bi bi-person-vcard"></i>
      <div><strong>Identification document</strong><p class="small text-muted mb-0">National ID or passport copy.</p></div>
    </div>
    <div class="adm-req-row">
      <i class="bi bi-cash-coin"></i>
      <div><strong>Application fee</strong><p class="small text-muted mb-0">KSh 1,500, paid as part of the application process.</p></div>
    </div>
    <div class="adm-req-row">
      <i class="bi bi-camera"></i>
      <div><strong>Passport photo</strong><p class="small text-muted mb-0">A recent passport-size photograph.</p></div>
    </div>

    <p class="text-muted mt-4">International applicants and postgraduate applicants (Masters/PhD) should confirm additional requirements, such as equivalence certification or research proposals, directly with the Admissions office before applying.</p>

    <div class="adm-req-row no-top">
      <i class="bi bi-info-circle"></i>
      <div><strong>No interview required</strong><p class="small text-muted mb-0">Unless your chosen programme has special entry requirements.</p></div>
    </div>
    <div class="adm-req-row">
      <i class="bi bi-arrow-left-right"></i>
      <div><strong>Credit transfer accepted</strong><p class="small text-muted mb-0">Credits from other accredited universities are accepted for undergraduate programmes.</p></div>
    </div>
    <div class="adm-req-row">
      <i class="bi bi-passport"></i>
      <div><strong>International applicants</strong><p class="small text-muted mb-0">Valid immigration documents and a police clearance certificate are required.</p></div>
    </div>

    <div class="d-flex gap-3 flex-wrap mt-4">
      <a href="<?= BASE_URL ?>/contact.php" class="btn btn-adm-cta btn-lg">Ask Admissions a Question</a>
      <a href="<?= h(unit_content('application_form_url', 'https://www.anu.ac.ke/download/application-form/')) ?>" target="_blank" rel="noopener" class="btn btn-adm-outline btn-lg">Download Application Form</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
