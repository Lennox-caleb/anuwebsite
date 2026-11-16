<?php
$pageTitle = 'Sports & Recreation';
require_once __DIR__ . '/config/db.php';
$__hero = site_hero();
require __DIR__ . '/includes/header.php';
?>
<section class="sl-hero compact<?= $__hero && $__hero['image'] ? ' has-image' : '' ?>"<?= $__hero && $__hero['image'] ? ' style="background-image: linear-gradient(180deg, rgba(10,10,10,.55) 0%, rgba(10,10,10,.72) 100%), url(\'' . h(MAIN_SITE_URL . '/assets/uploads/' . $__hero['image']) . '\')"' : '' ?>>
  <div class="container">
    <div class="eyebrow mb-2">Explore</div>
    <h1>Sports & Recreation</h1>
  </div>
</section>
<section class="sl-section">
  <div class="container">
    <p class="text-muted max-ch-60"><?= nl2br(h(unit_content('sports_intro', 'Campus sports teams, facilities and intramural activities.'))) ?></p>
    <div class="row g-4 mt-2">
      <div class="col-md-6 col-lg-4">
        <div class="sl-card"><div class="icon-badge"><i class="bi bi-trophy"></i></div><div class="body"><h3>Teams</h3><p class="small text-muted mb-0">Varsity and intramural teams across several sports.</p></div></div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="sl-card"><div class="icon-badge"><i class="bi bi-trophy"></i></div><div class="body"><h3>Facilities</h3><p class="small text-muted mb-0">Campus sports facilities available to students.</p></div></div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="sl-card"><div class="icon-badge"><i class="bi bi-trophy"></i></div><div class="body"><h3>Fixtures</h3><p class="small text-muted mb-0">Upcoming matches and tournaments.</p></div></div>
      </div>
    </div>
    <p class="text-muted small mt-4"><?= nl2br(h(unit_content('sports_closing', 'This page\'s content sections are placeholders showing the intended structure, the Dean of Students office should populate each with current names, photos and contacts once ready.'))) ?></p>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
