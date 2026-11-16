<?php
require_once __DIR__ . '/includes/functions.php';

$unitRaw = $_GET['unit'] ?? 'This Section';
$iconRaw = $_GET['icon'] ?? 'bi-hourglass-split';
// Keep this loose but safe: it only ever feeds a Bootstrap Icon class name.
$icon = preg_match('/^bi-[a-z0-9-]+$/', $iconRaw) ? $iconRaw : 'bi-hourglass-split';

$pageTitle = $unitRaw . ', Coming Soon';
$pageDescription = "The dedicated {$unitRaw} site is under development.";
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container container-narrow text-center">
    <div class="icon-badge mx-auto mb-3" style="width:76px;height:76px;font-size:2rem;">
      <i class="bi <?= h($icon) ?>"></i>
    </div>
    <h1><?= h($unitRaw) ?></h1>
    <p class="lead text-muted mb-0">This section's own dedicated site is under development.</p>
  </div>
</section>

<section class="py-5">
  <div class="container container-narrow text-center reveal">
    <p class="max-ch-60 mx-auto">In the meantime, you can reach us directly, or head back to the
    main site to keep browsing.</p>
    <div class="d-flex flex-wrap gap-3 justify-content-center mt-3">
      <a href="<?= BASE_URL ?>/contact.php" class="btn btn-apply btn-lg">Contact Us</a>
      <a href="<?= BASE_URL ?>/index.php" class="btn btn-outline-anu btn-lg">Back to Home</a>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
