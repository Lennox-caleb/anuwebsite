<?php
$pageTitle = 'Cultural Week';
require_once __DIR__ . '/config/db.php';
$__hero = site_hero();
require __DIR__ . '/includes/header.php';
?>
<section class="sl-hero compact<?= $__hero && $__hero['image'] ? ' has-image' : '' ?>"<?= $__hero && $__hero['image'] ? ' style="background-image: linear-gradient(180deg, rgba(10,10,10,.55) 0%, rgba(10,10,10,.72) 100%), url(\'' . h(MAIN_SITE_URL . '/assets/uploads/' . $__hero['image']) . '\')"' : '' ?>>
  <div class="container">
    <div class="eyebrow mb-2">Explore</div>
    <h1>Cultural Week</h1>
  </div>
</section>
<section class="sl-section">
  <div class="container">
    <p class="text-muted max-ch-60"><?= nl2br(h(unit_content('cultural_week_intro', 'An annual celebration of the cultural diversity of ANU\'s student body, drawn from across Africa and beyond.'))) ?></p>
    <div class="row g-4 mt-2">
      <div class="col-md-6 col-lg-4">
        <div class="sl-card"><div class="icon-badge"><i class="bi bi-globe2"></i></div><div class="body"><h3>Performances</h3><p class="small text-muted mb-0">Music, dance and drama showcasing different African and international cultures.</p></div></div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="sl-card"><div class="icon-badge"><i class="bi bi-globe2"></i></div><div class="body"><h3>Food and Fashion</h3><p class="small text-muted mb-0">Traditional dress and food from student home countries and communities.</p></div></div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="sl-card"><div class="icon-badge"><i class="bi bi-globe2"></i></div><div class="body"><h3>Community</h3><p class="small text-muted mb-0">A chance for the whole University community to celebrate together.</p></div></div>
      </div>
    </div>
    <p class="text-muted small mt-4"><?= nl2br(h(unit_content('cultural_week_closing', 'Dates and this year\'s programme should be confirmed and published here by the Student Life office.'))) ?></p>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
