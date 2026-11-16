<?php
$pageTitle = 'University Library';
require_once __DIR__ . '/config/db.php';

$staffStmt = db()->prepare("SELECT * FROM staff WHERE unit_slug = ? AND is_published = 1 ORDER BY sort_order LIMIT 3");
$staffStmt->execute([UNIT_SLUG]);
$staff = $staffStmt->fetchAll();

$heroStmt = db()->prepare("SELECT * FROM sliders WHERE unit_slug = ? AND is_published = 1 ORDER BY sort_order DESC, id DESC LIMIT 1");
$heroStmt->execute([UNIT_SLUG]);
$hero = $heroStmt->fetch();

require __DIR__ . '/includes/header.php';
?>
<section class="lib-hero<?= $hero && $hero['image'] ? ' has-image' : '' ?>"<?= $hero && $hero['image'] ? ' style="background-image: linear-gradient(90deg, rgba(0,0,0,.74) 0%, rgba(0,0,0,.46) 42%, rgba(0,0,0,.2) 78%), url(\'' . h(MAIN_SITE_URL . '/assets/uploads/' . $hero['image']) . '\')"' : '' ?>>
  <img class="hero-bg-image" src="<?= $hero && $hero['image'] ? h(MAIN_SITE_URL . '/assets/uploads/' . $hero['image']) : 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=1600&q=80' ?>" alt="" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.18;" onerror="this.style.display='none';">
  <div class="container" style="position:relative;">
    <div class="icon-badge"><i class="bi bi-book-half"></i></div>
    <p class="lead"><?= $hero && $hero['subtext'] ? h($hero['subtext']) : 'books, journals, digital resources and study spaces supporting learning and research.' ?></p>
    <h1 class="mb-3"><?= $hero && $hero['headline'] ? h($hero['headline']) : 'University Library' ?></h1>
    <div class="d-flex gap-3 mt-4 flex-wrap">
      <a href="<?= h(main_setting('repository_url')) ?>" class="btn btn-lib-cta btn-lg" target="_blank" rel="noopener">Visit e-Resources</a>
      <a href="<?= BASE_URL ?>/about.php" class="btn btn-lib-outline btn-lg">Learn More</a>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../main-site/includes/school_messages.php';
sm_render_home(['leader_slug' => 'head-library', 'unit' => UNIT_SLUG, 'base' => BASE_URL, 'uploads' => UPLOADS_URL, 'css' => MAIN_SITE_URL . '/assets/css/school-messages.css', 'heading' => 'Message from the University Librarian', 'eyebrow' => 'University Librarian', 'heads' => false, 'soon' => 'The University Librarian&rsquo;s message will be published here soon.']);
?>
<section class="lib-section">
  <div class="container">
    <div class="lib-section-head"><h2>Library Services</h2></div>
    <div class="row g-4">
      <div class="col-md-4">
        <a class="lib-card d-block text-decoration-none" href="<?= BASE_URL ?>/catalog.php"><span class="index">01</span><h3>Physical & digital collections</h3><p class="small text-muted mb-0">Books, journals and past student projects across every discipline.</p><span class="tile-go">Browse &rarr;</span></a>
      </div>
      <div class="col-md-4">
        <a class="lib-card d-block text-decoration-none" href="<?= BASE_URL ?>/catalog.php"><span class="index">02</span><h3>Research databases</h3><p class="small text-muted mb-0">Subscribed journals and databases accessible to students and faculty.</p><span class="tile-go">Browse &rarr;</span></a>
      </div>
      <div class="col-md-4">
        <a class="lib-card d-block text-decoration-none" href="<?= BASE_URL ?>/catalog.php"><span class="index">03</span><h3>Study spaces</h3><p class="small text-muted mb-0">Quiet reading rooms and group study areas across the library building.</p><span class="tile-go">Browse &rarr;</span></a>
      </div>
    </div>
  </div>
</section>

<?php if ($staff): ?>
<section class="lib-section tint">
  <div class="container">
    <div class="lib-section-head"><h2>Meet the Team</h2></div>
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
