<?php
$pageTitle = 'Counseling Services';
require_once __DIR__ . '/config/db.php';
$__hero = site_hero();
require __DIR__ . '/includes/header.php';
?>
<section class="sl-hero compact<?= $__hero && $__hero['image'] ? ' has-image' : '' ?>"<?= $__hero && $__hero['image'] ? ' style="background-image: linear-gradient(180deg, rgba(10,10,10,.55) 0%, rgba(10,10,10,.72) 100%), url(\'' . h(MAIN_SITE_URL . '/assets/uploads/' . $__hero['image']) . '\')"' : '' ?>>
  <div class="container">
    <div class="eyebrow mb-2">Explore</div>
    <h1>Counseling</h1>
  </div>
</section>
<section class="sl-section">
  <div class="container">
    <p class="text-muted max-ch-60"><?= nl2br(h(unit_content('counseling_intro', 'Confidential counseling and wellness support for students.'))) ?></p>
    <div class="row g-4 mt-2">
      <div class="col-md-6 col-lg-4">
        <div class="sl-card"><div class="icon-badge"><i class="bi bi-heart-pulse"></i></div><div class="body"><h3>Individual Counseling</h3><p class="small text-muted mb-0">One-on-one confidential sessions with trained counselors.</p></div></div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="sl-card"><div class="icon-badge"><i class="bi bi-heart-pulse"></i></div><div class="body"><h3>Group Sessions</h3><p class="small text-muted mb-0">Peer support and group wellness sessions.</p></div></div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="sl-card"><div class="icon-badge"><i class="bi bi-heart-pulse"></i></div><div class="body"><h3>Crisis Support</h3><p class="small text-muted mb-0">How to reach support urgently when needed.</p></div></div>
      </div>
    </div>
    <p class="text-muted small mt-4"><?= nl2br(h(unit_content('counseling_closing', 'This page\'s content sections are placeholders showing the intended structure, the Dean of Students office should populate each with current names, photos and contacts once ready.'))) ?></p>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
