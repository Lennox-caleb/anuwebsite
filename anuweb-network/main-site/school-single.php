<?php
require_once __DIR__ . '/includes/functions.php';

$slug = $_GET['slug'] ?? '';
$stmt = db()->prepare('SELECT * FROM schools WHERE slug = ? AND is_published = 1 LIMIT 1');
$stmt->execute([$slug]);
$school = $stmt->fetch();
if (!$school) {
    // Old long-form address (e.g. /schools/school-of-law) -> send to the current short one
    $legacy = ['school-of-law' => 'sol', 'school-of-business' => 'sob', 'school-of-science-and-technology' => 'sst',
               'school-of-humanities-and-social-sciences' => 'shss', 'school-of-religion-and-christian-ministry' => 'scrm'];
    if (isset($legacy[$slug])) { header('Location: ' . BASE_URL . '/schools/' . $legacy[$slug], true, 301); exit; }
}

if (!$school) {
    http_response_code(404);
    $pageTitle = 'Not found';
    require __DIR__ . '/includes/header.php';
    echo '<div class="container py-5"><h1>School not found</h1><p><a href="' . BASE_URL . '/schools.php">Back to schools</a></p></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $school['name'];
$pageDescription = $school['summary'] ?: ('Programmes offered by the ' . $school['name'] . ' at Africa Nazarene University.');
$progStmt = db()->prepare('SELECT * FROM programmes WHERE school_id = ? AND is_published = 1 ORDER BY level, title');
$progStmt->execute([$school['id']]);
$programmes = $progStmt->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <p class="mb-2"><a href="<?= BASE_URL ?>/schools.php" class="text-decoration-none">&larr; All Schools</a></p>
    <h1 class="mb-2"><?= h($school['name']) ?></h1>
    <p class="lead text-muted max-ch-60 mb-0"><?= h($school['summary']) ?></p>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="row g-4 g-lg-5">
      <div class="col-lg-8">
        <?php if ($school['image']): ?>
          <img src="<?= h(UPLOADS_URL . '/' . $school['image']) ?>" class="img-fluid rounded mb-4 reveal" alt="<?= h($school['name']) ?>">
        <?php endif; ?>
        <?php if ($school['description']): ?>
          <div class="max-ch-70 reveal"><?= nl2br(h($school['description'])) ?></div>
        <?php endif; ?>
      </div>
      <div class="col-lg-4">
        <div class="card-anu side-cta reveal">
          <div class="body">
            <p class="mb-1"><strong><?= count($programmes) ?></strong> programme<?= count($programmes) === 1 ? '' : 's' ?> in this school</p>
            <a href="<?= h(setting('apply_url')) ?>" target="_blank" rel="noopener" class="btn btn-apply w-100 mt-2">Apply Now</a>
            <a href="<?= BASE_URL ?>/contact.php" class="btn btn-outline-anu w-100 mt-2">Ask a Question</a>
          </div>
        </div>
      </div>
    </div>

    <div class="section-head mt-5 reveal"><h2>Programmes in this School</h2></div>
    <?php if (!$programmes): ?>
      <p class="text-muted">Programmes for this school will be listed here shortly.</p>
    <?php endif; ?>
    <div class="row g-4">
      <?php foreach ($programmes as $i => $p): ?>
        <div class="col-md-6 col-lg-4">
          <div class="card-anu reveal reveal-<?= ($i % 4) + 1 ?> h-100">
            <?php if ($p['image']): ?>
              <img class="thumb" src="<?= h(UPLOADS_URL . '/' . $p['image']) ?>" alt="">
            <?php else: ?>
              <div class="thumb-placeholder"><i class="bi bi-mortarboard"></i></div>
            <?php endif; ?>
            <div class="body">
              <span class="tag"><?= h($p['level']) ?></span>
              <h3><a class="text-decoration-none" href="<?= BASE_URL ?>/program-single.php?slug=<?= h($p['slug']) ?>"><?= h($p['title']) ?></a></h3>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
