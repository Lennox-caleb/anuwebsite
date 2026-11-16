<?php
$pageTitle = 'Core Values';
require_once __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container container-narrow text-center">
    <h1>Core Values</h1>
    <p class="text-muted mb-0">Character, Competence and Community, what shapes an ANU graduate.</p>
  </div>
</section>
<section class="py-5">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-4">
        <div class="card-anu reveal reveal-1 h-100"><div class="body">
          <div class="icon-badge"><i class="bi bi-person-heart"></i></div>
          <span class="tag">Character</span>
          <h3>Explore your faith for living</h3>
        </div></div>
      </div>
      <div class="col-md-4">
        <div class="card-anu reveal reveal-2 h-100"><div class="body">
          <div class="icon-badge"><i class="bi bi-mortarboard"></i></div>
          <span class="tag">Competence</span>
          <h3>Achieve your goals for your career</h3>
        </div></div>
      </div>
      <div class="col-md-4">
        <div class="card-anu reveal reveal-3 h-100"><div class="body">
          <div class="icon-badge"><i class="bi bi-people"></i></div>
          <span class="tag">Community</span>
          <h3>Find your place in the world</h3>
        </div></div>
      </div>
    </div>
    <a href="<?= BASE_URL ?>/about.php" class="btn btn-outline-anu mt-4">&larr; Back to About ANU</a>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
