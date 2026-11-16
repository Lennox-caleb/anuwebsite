<?php
$pageTitle = 'Alumni';
require_once __DIR__ . '/config/db.php';

$staffStmt = db()->prepare("SELECT * FROM staff WHERE unit_slug = ? AND is_published = 1 ORDER BY sort_order LIMIT 3");
$staffStmt->execute([UNIT_SLUG]);
$staff = $staffStmt->fetchAll();

$heroStmt = db()->prepare("SELECT * FROM sliders WHERE unit_slug = ? AND is_published = 1 ORDER BY sort_order DESC, id DESC LIMIT 1");
$heroStmt->execute([UNIT_SLUG]);
$hero = $heroStmt->fetch();

require __DIR__ . '/includes/header.php';
?>
<section class="alum-hero<?= $hero && $hero['image'] ? ' has-image' : '' ?>"<?= $hero && $hero['image'] ? ' style="background-image: linear-gradient(90deg, rgba(0,0,0,.74) 0%, rgba(0,0,0,.46) 42%, rgba(0,0,0,.2) 78%), url(\'' . h(MAIN_SITE_URL . '/assets/uploads/' . $hero['image']) . '\')"' : '' ?>>
  <div class="container" style="position:relative;">
    <div class="icon-badge"><i class="bi bi-mortarboard"></i></div>
    <p class="lead"><?= $hero && $hero['subtext'] ? h($hero['subtext']) : 'stay connected, give back, and grow the ANU network worldwide.' ?></p>
    <h1 class="mb-3"><?= $hero && $hero['headline'] ? h($hero['headline']) : 'Alumni' ?></h1>
    <p class="fw-bold mb-0" style="font-size:1.15rem;">14,000+ alumni across Kenya and beyond</p>
    <div class="d-flex gap-3 mt-4 flex-wrap">
      <a href="<?= h(main_setting('foundation_url')) ?>" class="btn btn-alum-cta btn-lg" target="_blank" rel="noopener">Join the Alumni Network</a>
      <a href="<?= BASE_URL ?>/about.php" class="btn btn-alum-outline btn-lg">Learn More</a>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../main-site/includes/school_messages.php';
sm_render_home(['leader_slug' => 'head-alumni', 'unit' => UNIT_SLUG, 'base' => BASE_URL, 'uploads' => UPLOADS_URL, 'css' => MAIN_SITE_URL . '/assets/css/school-messages.css', 'heading' => 'Message from Alumni Relations', 'eyebrow' => 'Alumni Relations', 'heads' => false, 'soon' => 'The Alumni Relations message will be published here soon.']); ?>


<section class="alum-section">
  <div class="container">
    <div class="alum-section-head"><h2>Stay Connected</h2></div>
    <div class="row g-4">
      <div class="col-md-4">
        <a class="alum-card d-block text-decoration-none" href="<?= BASE_URL ?>/chapters.php"><span class="index">01</span><h3>Global alumni network</h3><p class="small text-muted mb-0">Graduates working and serving across Kenya and around the world.</p><span class="tile-go">Find a Chapter &rarr;</span></a>
      </div>
      <div class="col-md-4">
        <a class="alum-card d-block text-decoration-none" href="<?= BASE_URL ?>/alumni-card.php"><span class="index">02</span><h3>Mentorship & events</h3><p class="small text-muted mb-0">Reunions, networking evenings and mentorship for current students.</p><span class="tile-go">Get Your Alumni Card &rarr;</span></a>
      </div>
      <div class="col-md-4">
        <a class="alum-card d-block text-decoration-none" href="<?= BASE_URL ?>/scholarship-fund.php"><span class="index">03</span><h3>Give back</h3><p class="small text-muted mb-0">Support future students through the ANU Foundation.</p><span class="tile-go">Give Now &rarr;</span></a>
      </div>
    </div>
  </div>
</section>

<?php if ($staff): ?>
<section class="alum-section tint">
  <div class="container">
    <div class="alum-section-head"><h2>Meet the Team</h2></div>
    <div class="row g-4">
      <?php foreach ($staff as $s): ?>
        <div class="col-md-4">
          <div class="faculty-card">
            <?php if ($s['photo']): ?>
              <img src="<?= h(UPLOADS_URL . '/' . $s['photo']) ?>" alt="<?= h($s['full_name']) ?>">
            <?php else: ?>
              <div class="faculty-placeholder"></div>
            <?php endif; ?>
            <h3><?= h($s['full_name']) ?></h3>
            <p class="role"><?= h($s['title']) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
