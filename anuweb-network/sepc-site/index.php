<?php
$pageTitle = 'Centre for Short Courses & Professional Studies';
require_once __DIR__ . '/config/db.php';

// Requirement 6 (Phase 7c): Dean's/Director's Message on the homepage,
// reusing the SAME "head-sepc" leader record already shown on about.php.
// Hidden entirely if nothing has been entered yet -- no invented name or
// message (unlike about.php's own placeholder text, this homepage block
// follows the same hide-if-empty rule as every school homepage's welcome
// block).
$leaderStmt = db()->prepare("SELECT * FROM staff WHERE unit_slug = 'head-sepc' AND is_published = 1 ORDER BY sort_order LIMIT 1");
$leaderStmt->execute();
$leader = $leaderStmt->fetch();

$heroStmt = db()->prepare("SELECT * FROM sliders WHERE unit_slug = ? AND is_published = 1 ORDER BY sort_order DESC, id DESC LIMIT 1");
$heroStmt->execute([UNIT_SLUG]);
$hero = $heroStmt->fetch();

$listStmt = db()->prepare("SELECT * FROM programmes WHERE unit_slug = ? AND is_published = 1 ORDER BY level LIMIT 4");
$listStmt->execute([UNIT_SLUG]);
$listings = $listStmt->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<section class="sepc-hero<?= $hero && $hero['image'] ? ' has-image' : '' ?>"<?= $hero && $hero['image'] ? ' style="background-image: linear-gradient(90deg, rgba(0,0,0,.74) 0%, rgba(0,0,0,.46) 42%, rgba(0,0,0,.2) 78%), url(\'' . h(MAIN_SITE_URL . '/assets/uploads/' . $hero['image']) . '\')"' : '' ?>>
  <img class="hero-bg-image" src="<?= $hero && $hero['image'] ? h(MAIN_SITE_URL . '/assets/uploads/' . $hero['image']) : 'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1600&q=80' ?>" alt="" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.18;" onerror="this.style.display='none';">
  <div class="container" style="position:relative;">
    <div class="icon-badge"><i class="bi bi-journal-bookmark"></i></div>
    <p class="lead"><?= $hero && $hero['subtext'] ? h($hero['subtext']) : 'practical, career-focused short courses and professional certifications.' ?></p>
    <h1 class="mb-3"><?= $hero && $hero['headline'] ? h($hero['headline']) : 'Centre for Short Courses & Professional Studies' ?></h1>
    <div class="d-flex gap-3 mt-4 flex-wrap">
      <a href="<?= h(main_setting('apply_url')) ?>" class="btn btn-sepc-cta btn-lg" target="_blank" rel="noopener">Enroll Now</a>
      <a href="<?= BASE_URL ?>/about.php" class="btn btn-sepc-outline btn-lg">Learn More</a>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../main-site/includes/school_messages.php';
sm_render_home(['leader_slug' => 'head-sepc', 'unit' => UNIT_SLUG, 'base' => BASE_URL, 'uploads' => UPLOADS_URL, 'css' => MAIN_SITE_URL . '/assets/css/school-messages.css', 'heading' => 'Message from the Head of SEPC', 'eyebrow' => 'SEPC', 'heads' => false, 'soon' => 'The message from the Head of SEPC will be published here soon.']);
?>

<section class="sepc-section">
  <div class="container">
    <div class="sepc-section-head"><h2>Why Choose SEPC</h2></div>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="sepc-feature-card"><div class="icon-badge"><i class="bi bi-award"></i></div><h3>Flexible scheduling</h3><p class="small text-muted mb-0">Evening, weekend and block-release classes designed around working professionals.</p></div>
      </div>
      <div class="col-md-4">
        <div class="sepc-feature-card"><div class="icon-badge"><i class="bi bi-people"></i></div><h3>Industry-recognized certificates</h3><p class="small text-muted mb-0">Short courses mapped to real workplace skills and professional bodies.</p></div>
      </div>
      <div class="col-md-4">
        <div class="sepc-feature-card"><div class="icon-badge"><i class="bi bi-globe2"></i></div><h3>Fast-track upskilling</h3><p class="small text-muted mb-0">Focused, practical programmes that fit alongside a full-time job.</p></div>
      </div>
    </div>
  </div>
</section>

<section class="sepc-section">
  <div class="container">
    <div class="sepc-section-head"><h2>Short Courses</h2></div>
    <?php if ($listings): foreach ($listings as $item): ?>
      <div class="sepc-programme-row">
        <div class="d-flex align-items-center">
          <?php if ($item['image']): ?>
            <img class="thumb" src="<?= h(MAIN_SITE_URL . '/assets/uploads/' . $item['image']) ?>" alt="">
          <?php else: ?>
            <span class="thumb-placeholder"></span>
          <?php endif; ?>
          <div><span class="level"><?= h($item['level']) ?></span><strong><?= h($item['title']) ?></strong></div>
        </div>
        <a href="<?= BASE_URL ?>/courses.php" class="text-decoration-none">Details &rarr;</a>
      </div>
    <?php endforeach; else: ?>
      <p class="text-muted">Listings for Centre for Short Courses & Professional Studies will appear here soon.</p>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
