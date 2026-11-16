<?php
$pageTitle = 'Nellie Montague Clinic';
require_once __DIR__ . '/config/db.php';
$__hero = site_hero();
require __DIR__ . '/includes/header.php';
?>
<section class="sl-hero compact<?= $__hero && $__hero['image'] ? ' has-image' : '' ?>"<?= $__hero && $__hero['image'] ? ' style="background-image: linear-gradient(180deg, rgba(10,10,10,.55) 0%, rgba(10,10,10,.72) 100%), url(\'' . h(MAIN_SITE_URL . '/assets/uploads/' . $__hero['image']) . '\')"' : '' ?>>
  <div class="container">
    <div class="eyebrow mb-2">Explore</div>
    <h1>Nellie Montague Clinic</h1>
  </div>
</section>
<section class="sl-section">
  <div class="container">
    <p class="text-muted max-ch-60"><?= nl2br(h(unit_content('health_clinic_intro', 'The University\'s on-campus health clinic, supporting students\' physical wellbeing alongside academics.'))) ?></p>
    <div class="row g-4 mt-2">
      <div class="col-md-6 col-lg-4">
        <div class="sl-card"><div class="icon-badge"><i class="bi bi-hospital"></i></div><div class="body"><h3>Outpatient Care</h3><p class="small text-muted mb-0">General consultations and basic outpatient treatment for students.</p></div></div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="sl-card"><div class="icon-badge"><i class="bi bi-hospital"></i></div><div class="body"><h3>Referrals</h3><p class="small text-muted mb-0">Coordinated referrals to partner hospitals for specialist or emergency care.</p></div></div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="sl-card"><div class="icon-badge"><i class="bi bi-hospital"></i></div><div class="body"><h3>Health Education</h3><p class="small text-muted mb-0">Wellness talks and preventive health programmes run through the year.</p></div></div>
      </div>
    </div>
    <p class="text-muted small mt-4"><?= nl2br(h(unit_content('health_clinic_closing', 'Clinic hours, contact details and specific services should be confirmed and published here by the Clinic administration.'))) ?></p>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
