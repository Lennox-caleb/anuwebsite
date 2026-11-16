<?php
$pageTitle = 'Financial Aid';
require_once __DIR__ . '/config/db.php';
$__hero = site_hero();
require __DIR__ . '/includes/header.php';
?>
<section class="sl-hero compact<?= $__hero && $__hero['image'] ? ' has-image' : '' ?>"<?= $__hero && $__hero['image'] ? ' style="background-image: linear-gradient(180deg, rgba(10,10,10,.55) 0%, rgba(10,10,10,.72) 100%), url(\'' . h(MAIN_SITE_URL . '/assets/uploads/' . $__hero['image']) . '\')"' : '' ?>>
  <div class="container">
    <div class="eyebrow mb-2">Explore</div>
    <h1>Financial Aid</h1>
  </div>
</section>
<section class="sl-section">
  <div class="container">
    <p class="text-muted max-ch-60"><?= nl2br(h(unit_content('financial_aid_intro', 'Africa Nazarene University offers several financial aid packages to needy and deserving students, alumni and their families, as part of its commitment to supporting students\' educational journeys.'))) ?></p>
    <div class="row g-4 mt-2">
      <div class="col-md-6 col-lg-4">
        <div class="sl-card"><div class="icon-badge"><i class="bi bi-cash-coin"></i></div><div class="body"><h3>Applying</h3><p class="small text-muted mb-0">Eligible students can apply for financial aid through the Financial Aid office; successful applicants are contacted by phone or email.</p></div></div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="sl-card"><div class="icon-badge"><i class="bi bi-cash-coin"></i></div><div class="body"><h3>Retaining Aid</h3><p class="small text-muted mb-0">Students must maintain satisfactory academic progress -- meeting attendance requirements, passing all attempted units, and achieving the minimum grade set in their award letter -- to keep their aid.</p></div></div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="sl-card"><div class="icon-badge"><i class="bi bi-cash-coin"></i></div><div class="body"><h3>Other Support</h3><p class="small text-muted mb-0">Work-study opportunities and alumni mentoring are also available alongside direct financial aid.</p></div></div>
      </div>
    </div>
    <p class="text-muted small mt-4"><?= nl2br(h(unit_content('financial_aid_closing', 'Specific packages, amounts and application deadlines should be confirmed and published here by the Financial Aid office.'))) ?></p>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
