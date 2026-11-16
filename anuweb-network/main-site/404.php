<?php
http_response_code(404);
$pageTitle = 'Page Not Found';
require_once __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/header.php';
?>
<section class="py-5">
  <div class="container container-narrow text-center py-5">
    <div class="text-red font-display error-code">404</div>
    <h1>We couldn't find that page</h1>
    <p class="text-muted">The page you're looking for may have moved or no longer exists.</p>
    <a href="<?= BASE_URL ?>/index.php" class="btn btn-apply btn-lg mt-3">Back to Home</a>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
