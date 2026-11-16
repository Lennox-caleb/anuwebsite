<?php
$pageTitle = 'Schools';
$pageDescription = "Africa Nazarene University's five Schools.";
require_once __DIR__ . '/includes/functions.php';

$schools = db()->query('SELECT * FROM schools WHERE is_published=1 ORDER BY sort_order')->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container container-narrow text-center">
    <h1>Our Five Schools</h1>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="row g-4">
      <?php foreach ($schools as $i => $s): ?>
        <div class="col-md-6 col-lg-4">
          <a class="school-card reveal reveal-<?= ($i % 4) + 1 ?> d-block text-decoration-none" href="<?= BASE_URL ?>/schools/<?= h($s['slug']) ?>">
            <div class="school-card-media">
              <?php if ($s['image']): ?>
                <img src="<?= h(UPLOADS_URL . '/' . $s['image']) ?>" alt="<?= h($s['name']) ?>">
              <?php else: ?>
                <i class="bi bi-bank"></i>
              <?php endif; ?>
              <span class="school-card-num"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></span>
            </div>
            <div class="school-card-body">
              <h3><?= h($s['name']) ?></h3>
              <p><?= h($s['summary']) ?></p>
              <span class="tile-go">Explore School &rarr;</span>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
      <?php if (!$schools): ?><p class="text-muted">Schools will be listed here once added.</p><?php endif; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
