<?php
$pageTitle = 'Home';
$pageDescription = 'Africa Nazarene University Non-Degree Programmes -- career-focused Diploma and Certificate courses in Business, IT, Social Sciences, Theology and English.';
require_once __DIR__ . '/includes/functions.php';

$hero = db()->query("SELECT * FROM sliders WHERE is_published = 1 ORDER BY sort_order ASC LIMIT 1")->fetch();
$featuredProgrammes = db()->query("SELECT p.*, d.name AS dept_name FROM programmes p LEFT JOIN departments d ON d.id = p.department_id WHERE p.is_published = 1 AND p.is_featured = 1 ORDER BY p.sort_order LIMIT 6")->fetchAll();
if (!$featuredProgrammes) {
    $featuredProgrammes = db()->query("SELECT p.*, d.name AS dept_name FROM programmes p LEFT JOIN departments d ON d.id = p.department_id WHERE p.is_published = 1 ORDER BY p.sort_order LIMIT 6")->fetchAll();
}
$departments = db()->query("SELECT * FROM departments ORDER BY sort_order")->fetchAll();
$head = db()->query("SELECT * FROM staff WHERE category = 'leadership' AND is_published = 1 ORDER BY sort_order LIMIT 1")->fetch();
$latestNews = db()->query("SELECT * FROM news WHERE is_published = 1 ORDER BY published_at DESC LIMIT 3")->fetchAll();
$testimonials = db()->query("SELECT * FROM testimonials WHERE is_published = 1 ORDER BY sort_order LIMIT 3")->fetchAll();
$partners = db()->query("SELECT * FROM partners ORDER BY sort_order")->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<section class="tvet-hero<?= $hero && $hero['image'] ? ' has-image' : '' ?>">
  <?php if ($hero && $hero['image']): ?>
    <img class="hero-bg-image" src="<?= h(upload_url('sliders', $hero['image'])) ?>" alt="">
  <?php endif; ?>
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-7">
        <div class="eyebrow mb-2">Africa Nazarene University</div>
        <h1 class="mb-3"><?= $hero ? h($hero['title']) : 'Skills that build careers.' ?></h1>
        <p class="lead mb-4"><?= $hero ? h($hero['subtitle']) : h(setting('mission')) ?></p>
        <div class="d-flex gap-3 flex-wrap">
          <a href="<?= h(apply_url()) ?>" target="_blank" rel="noopener" class="btn btn-tvet-cta btn-lg">Apply Now</a>
          <a href="<?= BASE_URL ?>/programmes.php" class="btn btn-tvet-outline <?= $hero && $hero['image'] ? 'on-dark' : '' ?> btn-lg">View Programmes</a>
        </div>
      </div>
      <div class="col-lg-5 hero-stats">
        <div class="row g-3">
          <div class="col-6"><div class="stat"><b><?= h(setting('total_programmes','24')) ?>+</b><span>Accredited Programmes</span></div></div>
          <div class="col-6"><div class="stat"><b><?= h(setting('total_students','1200')) ?>+</b><span>Enrolled Learners</span></div></div>
          <div class="col-6"><div class="stat"><b><?= h(setting('total_trainers','45')) ?>+</b><span>Expert Trainers</span></div></div>
          <div class="col-6"><div class="stat"><b><?= h(setting('years_experience','12')) ?>+</b><span>Years of Training</span></div></div>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="partners-strip">
  <div class="container">
    <p class="text-center small text-uppercase fw-bold text-muted mb-3" style="letter-spacing:.08em">Proudly Part Of</p>
    <div class="row g-3">
      <?php foreach ($partners as $p): ?>
        <div class="col-6 col-md-2-4 col-lg">
          <div class="chip"><?= h($p['name']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/school-messages.css">
<section class="sm-section">
  <div class="container">
    <a class="sm-dean" href="<?= BASE_URL ?>/leadership.php">
      <div class="sm-dean-photo">
        <?php if ($head && $head['photo']): ?>
          <img src="<?= h(upload_url('staff', $head['photo'])) ?>" alt="<?= h($head['full_name']) ?>">
        <?php else: ?>
          <span class="sm-ph" aria-hidden="true"><i class="bi bi-person"></i></span>
        <?php endif; ?>
      </div>
      <div class="sm-dean-body">
        <span class="sm-eyebrow">Welcome</span>
        <h2>Message from the Head</h2>
        <?php if ($head && trim((string)$head['bio']) !== ''): ?><p class="sm-quote"><?= h(excerpt($head['bio'], 280)) ?></p><?php endif; ?>
        <?php if ($head && trim((string)$head['full_name']) !== ''): ?><p class="sm-sign"><strong><?= h($head['full_name']) ?></strong><?php if ($head['title']): ?><span><?= h($head['title']) ?></span><?php endif; ?></p><?php endif; ?>
        <span class="sm-more">Read the full message <i class="bi bi-arrow-right"></i></span>
      </div>
    </a>
  </div>
</section>

<section class="tvet-section">
  <div class="container">
    <div class="tvet-section-head">
      <div>
        <span class="eyebrow">Why Train Here</span>
        <h2>A hands-on, competency-based education</h2>
      </div>
    </div>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="tvet-card"><i class="bi bi-tools card-icon"></i><h3>Hands-on workshops</h3><p class="small text-muted mb-0">Modern, fully-equipped workshops and labs -- practical, skills-first training rather than classroom theory alone.</p></div>
      </div>
      <div class="col-md-4">
        <div class="tvet-card"><i class="bi bi-patch-check card-icon"></i><h3>Career-ready qualifications</h3><p class="small text-muted mb-0">Diploma and Certificate programmes designed with industry relevance, awarded by Africa Nazarene University.</p></div>
      </div>
      <div class="col-md-4">
        <div class="tvet-card"><i class="bi bi-heart card-icon"></i><h3>Christian values foundation</h3><p class="small text-muted mb-0">Technical training shaped by the same holistic, Christ-centred education ethos as the wider University.</p></div>
      </div>
    </div>
  </div>
</section>

<section class="tvet-section alt">
  <div class="container">
    <div class="tvet-section-head">
      <div>
        <span class="eyebrow">Departments</span>
        <h2>Choose your career cluster</h2>
      </div>
      <a href="<?= BASE_URL ?>/programmes.php" class="btn btn-tvet-outline btn-sm">All Programmes <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="row g-4">
      <?php foreach ($departments as $d): ?>
        <div class="col-6 col-md-4 col-lg">
          <a class="dept-card" href="<?= BASE_URL ?>/programmes.php?department=<?= h($d['slug']) ?>">
            <i class="bi <?= h($d['icon']) ?>"></i>
            <h3><?= h($d['name']) ?></h3>
            <p><?= h($d['description']) ?></p>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="tvet-section">
  <div class="container">
    <div class="tvet-section-head">
      <div>
        <span class="eyebrow">Programmes</span>
        <h2>Featured programmes</h2>
      </div>
      <a href="<?= BASE_URL ?>/programmes.php" class="btn btn-tvet-outline btn-sm">View all <?= h(setting('total_programmes','24')) ?>+ <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="row g-4">
      <?php foreach ($featuredProgrammes as $p): ?>
        <div class="col-md-6 col-lg-4">
          <div class="programme-card">
            <div class="thumb-area">
              <?php if ($p['image']): ?><img src="<?= h(upload_url('programmes', $p['image'])) ?>" alt="<?= h($p['title']) ?>"><?php endif; ?>
              <span class="level-badge <?= level_badge_class($p['level']) ?>"><?= h($p['level']) ?></span>
            </div>
            <div class="body">
              <h3><?= h($p['title']) ?></h3>
              <div class="meta"><i class="bi bi-clock"></i> <?= h($p['duration']) ?> &nbsp; <i class="bi bi-mortarboard"></i> <?= h($p['dept_name'] ?? 'General') ?></div>
              <p class="small text-muted"><?= h(excerpt($p['overview'] ?? '', 100)) ?></p>
              <div class="stretched-link-wrap">
                <a class="fw-bold small" href="<?= BASE_URL ?>/programme-single.php?slug=<?= h($p['slug']) ?>">View details <i class="bi bi-arrow-right"></i></a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<?php if ($testimonials): ?>
<section class="tvet-section">
  <div class="container">
    <div class="tvet-section-head">
      <div><span class="eyebrow">Student Voices</span><h2>What our graduates say</h2></div>
    </div>
    <div class="row g-4">
      <?php foreach ($testimonials as $t): ?>
        <div class="col-md-4">
          <div class="testimonial-card">
            <span class="quote-mark">&ldquo;</span>
            <p><?= h($t['quote']) ?></p>
            <div class="who"><?= h($t['full_name']) ?></div>
            <div class="prog"><?= h($t['programme']) ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($latestNews): ?>
<section class="tvet-section dark">
  <div class="container">
    <div class="tvet-section-head">
      <div><span class="eyebrow">Latest</span><h2>News &amp; updates</h2></div>
      <a href="<?= BASE_URL ?>/news.php" class="btn btn-tvet-outline on-dark btn-sm">All news <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="row g-4">
      <?php foreach ($latestNews as $n): ?>
        <div class="col-md-4">
          <div class="news-card">
            <div class="thumb"><?php if ($n['image']): ?><img src="<?= h(upload_url('news', $n['image'])) ?>" alt=""><?php endif; ?></div>
            <div class="body">
              <span class="cat"><?= h($n['category']) ?></span>
              <h3><a class="text-dark" href="<?= BASE_URL ?>/news-single.php?slug=<?= h($n['slug']) ?>"><?= h($n['title']) ?></a></h3>
              <p class="small text-muted"><?= h(excerpt($n['excerpt'] ?? $n['body'], 90)) ?></p>
              <div class="date"><i class="bi bi-calendar3"></i> <?= fdate($n['published_at']) ?></div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="tvet-section" style="padding-top:0">
  <div class="container">
    <div class="p-5 rounded-4 text-center" style="background: linear-gradient(120deg, var(--tvet-red), var(--tvet-red-dark)); color:#fff;">
      <h2 class="text-white mb-2">Ready to start your career journey?</h2>
      <p class="mb-4" style="color:rgba(255,255,255,.85)">Applications for the next intake are now open. Join a community of hands-on, career-ready graduates.</p>
      <a href="<?= h(apply_url()) ?>" target="_blank" rel="noopener" class="btn btn-gold btn-lg">Apply Online Now</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
