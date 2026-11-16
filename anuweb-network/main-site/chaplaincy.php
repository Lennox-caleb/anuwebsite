<?php
$pageTitle = 'Chaplaincy';
$pageDescription = "ANU's Office of Spiritual Development — chapel, worship, Bible study, mentoring and Christian Union.";
require_once __DIR__ . '/includes/functions.php';
$programs = db()->query('SELECT * FROM chaplaincy_programs WHERE is_published = 1 ORDER BY sort_order, id')->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<section class="about-sub-hero">
  <div class="container container-narrow text-center">
    <span class="hero-eyebrow d-inline-block mb-2">Life at ANU</span>
    <h1>Chaplaincy</h1>
    <p class="lead mb-0 max-ch-60 mx-auto">At ANU, spiritual life isn't a program on the side &mdash; it's
    part of everyday campus life. Whether you're exploring your faith or seeking to go deeper, our Office of
    Spiritual Development walks with you.</p>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="section-head reveal"><h2>Our Ministries</h2></div>
    <?php if (!$programs): ?>
      <p class="text-muted">Ministries will be listed here once added.</p>
    <?php else: ?>
      <div class="row g-4">
        <?php foreach ($programs as $i => $p): ?>
          <div class="col-md-6 col-lg-4">
            <div class="csr-pillar reveal reveal-<?= ($i % 3) + 1 ?>">
              <div class="icon-badge"><i class="bi <?= h($p['icon'] ?: 'bi-heart') ?>"></i></div>
              <h3 class="h5"><?= h($p['title']) ?></h3>
              <?php if ($p['description']): ?><p class="small text-muted mb-0"><?= h($p['description']) ?></p><?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="py-5 bg-cream border-top">
  <div class="container container-narrow text-center reveal">
    <h2>Get in Touch</h2>
    <p class="text-muted mb-4 max-ch-60 mx-auto">The Office of Spiritual Development is on the 2nd floor of
    Harmon's Building; the Christian Union office is on the 1st floor. Weekly Christian Union gatherings are
    every Wednesday evening &mdash; worship, Bible study and prayer, open to all students.</p>
    <div class="d-flex flex-wrap gap-3 justify-content-center">
      <a href="mailto:chaplain@anu.ac.ke" class="btn btn-apply btn-lg"><i class="bi bi-envelope me-1"></i> Email the Chaplaincy</a>
      <a href="mailto:christianunion@anu.ac.ke" class="btn btn-outline-dark btn-lg">Email Christian Union</a>
      <a href="<?= BASE_URL ?>/life-at-anu.php" class="btn btn-outline-dark btn-lg">&larr; Life at ANU</a>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
