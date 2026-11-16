<?php
$pageTitle = 'Student Life';
require_once __DIR__ . '/config/db.php';

$staffStmt = db()->prepare("SELECT * FROM staff WHERE unit_slug = ? AND is_published = 1 ORDER BY sort_order LIMIT 3");
$staffStmt->execute([UNIT_SLUG]);
$staff = $staffStmt->fetchAll();

$heroStmt = db()->prepare("SELECT * FROM sliders WHERE unit_slug = ? AND is_published = 1 ORDER BY sort_order DESC, id DESC LIMIT 1");
$heroStmt->execute([UNIT_SLUG]);
$hero = $heroStmt->fetch();

require __DIR__ . '/includes/header.php';
?>
<section class="sl-hero<?= $hero && $hero['image'] ? ' has-image' : '' ?>"<?= $hero && $hero['image'] ? ' style="background-image: linear-gradient(90deg, rgba(0,0,0,.74) 0%, rgba(0,0,0,.46) 42%, rgba(0,0,0,.2) 78%), url(\'' . h(MAIN_SITE_URL . '/assets/uploads/' . $hero['image']) . '\')"' : '' ?>>
  <img class="hero-bg-image" src="<?= $hero && $hero['image'] ? h(MAIN_SITE_URL . '/assets/uploads/' . $hero['image']) : 'https://images.unsplash.com/photo-1529390079861-591de354faf5?auto=format&fit=crop&w=1600&q=80' ?>" alt="" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.18;" onerror="this.style.display='none';">
  <div class="container" style="position:relative;">
    <div class="icon-badge"><i class="bi bi-people"></i></div>
    <p class="lead"><?= $hero && $hero['subtext'] ? h($hero['subtext']) : 'clubs, societies, sports and community that make campus feel like home.' ?></p>
    <h1 class="mb-3"><?= $hero && $hero['headline'] ? h($hero['headline']) : 'Student Life' ?></h1>
    <div class="d-flex gap-3 mt-4 flex-wrap">
      <a href="<?= h(main_setting('student_portal_url')) ?>" class="btn btn-sl-cta btn-lg" target="_blank" rel="noopener">Get Involved</a>
      <a href="<?= BASE_URL ?>/about.php" class="btn btn-sl-outline btn-lg">Learn More</a>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../main-site/includes/school_messages.php';
sm_render_home(['leader_slug' => 'head-student-life', 'unit' => UNIT_SLUG, 'base' => BASE_URL, 'uploads' => UPLOADS_URL, 'css' => MAIN_SITE_URL . '/assets/css/school-messages.css', 'heading' => 'Message from the Dean of Students', 'eyebrow' => 'Dean of Students', 'heads' => false, 'soon' => 'The Dean of Students&rsquo; message will be published here soon.']); ?>


<section class="sl-section">
  <div class="container">
    <div class="sl-section-head"><h2>Life Beyond the Classroom</h2></div>
    <div class="row g-4">
      <div class="col-md-4">
        <a class="sl-card d-block text-decoration-none" href="<?= BASE_URL ?>/clubs.php"><span class="index">01</span><h3>Clubs & societies</h3><p class="small text-muted mb-0">Dozens of student-led clubs spanning faith, culture, academics and the arts.</p><span class="tile-go">Explore &rarr;</span></a>
      </div>
      <div class="col-md-4">
        <a class="sl-card d-block text-decoration-none" href="<?= BASE_URL ?>/sports.php"><span class="index">02</span><h3>Sports & recreation</h3><p class="small text-muted mb-0">Athletics, football, volleyball and more, on and off campus.</p><span class="tile-go">Explore &rarr;</span></a>
      </div>
      <div class="col-md-4">
        <a class="sl-card d-block text-decoration-none" href="<?= BASE_URL ?>/career-services.php"><span class="index">03</span><h3>Leadership & career growth</h3><p class="small text-muted mb-0">Student government, peer-mentorship and career services that build character and skills.</p><span class="tile-go">Explore &rarr;</span></a>
      </div>
    </div>
    <p class="text-center mt-4 mb-0">
      <a href="<?= BASE_URL ?>/accommodation.php" class="small me-3">Accommodation</a>&middot;
      <a href="<?= BASE_URL ?>/health-clinic.php" class="small mx-3">Health Clinic</a>&middot;
      <a href="<?= BASE_URL ?>/counseling.php" class="small mx-3">Counseling</a>&middot;
      <a href="<?= BASE_URL ?>/financial-aid.php" class="small mx-3">Financial Aid</a>&middot;
      <a href="<?= BASE_URL ?>/cultural-week.php" class="small mx-3">Cultural Week</a>&middot;
      <a href="<?= BASE_URL ?>/exchange-program.php" class="small ms-3">Exchange Programme</a>
    </p>
  </div>
</section>

<?php if ($staff): ?>
<section class="sl-section tint">
  <div class="container">
    <div class="sl-section-head"><h2>Meet the Team</h2></div>
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
