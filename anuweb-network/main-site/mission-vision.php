<?php
$pageTitle = 'Mission & Vision';
require_once __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container container-narrow text-center">
    <h1>Mission &amp; Vision</h1>
  </div>
</section>
<section class="py-5">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-6">
        <div class="card-anu reveal reveal-1 h-100"><div class="body">
          <div class="icon-badge"><i class="bi bi-bullseye"></i></div>
          <h2 class="h4">Mission</h2>
          <p class="mb-0"><?= h(setting('mission')) ?></p>
        </div></div>
      </div>
      <div class="col-md-6">
        <div class="card-anu reveal reveal-2 h-100"><div class="body">
          <div class="icon-badge"><i class="bi bi-eye"></i></div>
          <h2 class="h4">Vision</h2>
          <p class="mb-0"><?= h(setting('vision')) ?></p>
        </div></div>
      </div>
    </div>
    <a href="<?= BASE_URL ?>/about.php" class="btn btn-outline-anu mt-4">&larr; Back to About ANU</a>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
