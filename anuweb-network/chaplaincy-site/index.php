<?php
$pageTitle = 'Chaplaincy';
require_once __DIR__ . '/config/db.php';

$staffStmt = db()->prepare("SELECT * FROM staff WHERE unit_slug = ? AND is_published = 1 ORDER BY sort_order LIMIT 3");
$staffStmt->execute([UNIT_SLUG]);
$staff = $staffStmt->fetchAll();

$heroStmt = db()->prepare("SELECT * FROM sliders WHERE unit_slug = ? AND is_published = 1 ORDER BY sort_order DESC, id DESC LIMIT 1");
$heroStmt->execute([UNIT_SLUG]);
$hero = $heroStmt->fetch();

require __DIR__ . '/includes/header.php';
?>
<section class="chap-hero<?= $hero && $hero['image'] ? ' has-image' : '' ?>"<?= $hero && $hero['image'] ? ' style="background-image: linear-gradient(90deg, rgba(0,0,0,.74) 0%, rgba(0,0,0,.46) 42%, rgba(0,0,0,.2) 78%), url(\'' . h(MAIN_SITE_URL . '/assets/uploads/' . $hero['image']) . '\')"' : '' ?>>
  <img class="hero-bg-image" src="<?= $hero && $hero['image'] ? h(MAIN_SITE_URL . '/assets/uploads/' . $hero['image']) : 'https://images.unsplash.com/photo-1438032005730-c779502df39b?auto=format&fit=crop&w=1600&q=80' ?>" alt="" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.18;" onerror="this.style.display='none';">
  <div class="container" style="position:relative;">
    <div class="icon-badge"><i class="bi bi-heart"></i></div>
    <p class="lead"><?= $hero && $hero['subtext'] ? h($hero['subtext']) : 'nurturing faith, character and spiritual formation across campus life.' ?></p>
    <h1 class="mb-3"><?= $hero && $hero['headline'] ? h($hero['headline']) : 'Chaplaincy' ?></h1>
    <div class="d-flex gap-3 mt-4 flex-wrap">
      <a href="mailto:<?= h(main_setting('email_marketing')) ?>" class="btn btn-chap-cta btn-lg" target="_blank" rel="noopener">Request Prayer</a>
      <a href="<?= BASE_URL ?>/about.php" class="btn btn-chap-outline btn-lg">Learn More</a>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../main-site/includes/school_messages.php';
sm_render_home(['leader_slug' => 'head-chaplaincy', 'unit' => UNIT_SLUG, 'base' => BASE_URL, 'uploads' => UPLOADS_URL, 'css' => MAIN_SITE_URL . '/assets/css/school-messages.css', 'heading' => 'Message from the University Chaplain', 'eyebrow' => 'University Chaplain', 'heads' => false, 'soon' => 'The University Chaplain&rsquo;s message will be published here soon.']); ?>


<section class="chap-section">
  <div class="container">
    <div class="chap-section-head"><h2>Spiritual Life on Campus</h2></div>
    <div class="row g-4">
      <div class="col-md-4">
        <a class="chap-card d-block text-decoration-none" href="<?= BASE_URL ?>/ministries.php"><span class="index">01</span><h3>Weekly chapel services</h3><p class="small text-muted mb-0">Worship gatherings open to the whole university community.</p><span class="tile-go">See Ministries &rarr;</span></a>
      </div>
      <div class="col-md-4">
        <a class="chap-card d-block text-decoration-none" href="<?= BASE_URL ?>/ministries.php"><span class="index">02</span><h3>Small groups & discipleship</h3><p class="small text-muted mb-0">Bible studies and mentoring circles across every residence and school.</p><span class="tile-go">See Ministries &rarr;</span></a>
      </div>
      <div class="col-md-4">
        <a class="chap-card d-block text-decoration-none" href="<?= BASE_URL ?>/ministries.php"><span class="index">03</span><h3>Pastoral care & counseling</h3><p class="small text-muted mb-0">Confidential, faith-grounded support for students in need.</p><span class="tile-go">See Ministries &rarr;</span></a>
      </div>
    </div>
  </div>
</section>

<?php if ($staff): ?>
<section class="chap-section tint">
  <div class="container">
    <div class="chap-section-head"><h2>Meet the Team</h2></div>
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
