<?php
$pageTitle = 'News';
$pageDescription = 'The latest news and announcements from Africa Nazarene University.';
require_once __DIR__ . '/includes/functions.php';

$perPage = 9;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$newsScope = unit_scope_sql('news'); // Phase 5b fix: '' if unit_slug isn't migrated yet, instead of a fatal error.

$total = (int) db()->query("SELECT COUNT(*) c FROM news WHERE is_published=1{$newsScope}")->fetch()['c'];
$totalPages = max(1, (int)ceil($total / $perPage));

$stmt = db()->prepare("SELECT * FROM news WHERE is_published=1{$newsScope} ORDER BY published_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$news = $stmt->fetchAll();

// Feature the first article on page 1 only (still counts toward the same
// page's LIMIT/OFFSET, so pagination stays consistent across pages).
$featured = null;
if ($page === 1 && $news) {
    $featured = array_shift($news);
}

require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container container-narrow text-center">
    <h1>University News</h1>
    <p class="text-muted mb-0">Announcements, milestones and stories from across ANU.</p>
  </div>
</section>

<section class="py-5">
  <div class="container">

    <?php if ($featured): ?>
      <a class="news-card reveal mb-5" style="align-items:stretch;" href="<?= BASE_URL ?>/news-single.php?slug=<?= h($featured['slug']) ?>">
        <?php if ($featured['cover_image']): ?>
          <img class="thumb" style="width:280px;height:auto;" src="<?= h(UPLOADS_URL . '/' . $featured['cover_image']) ?>" alt="">
        <?php else: ?>
          <span class="thumb-placeholder" style="width:280px;"><i class="bi bi-newspaper"></i></span>
        <?php endif; ?>
        <div class="content">
          <span class="date-tag">Latest &middot; <?= format_date($featured['published_at']) ?></span>
          <h3 style="font-size:1.4rem;"><?= h($featured['title']) ?></h3>
          <p class="excerpt"><?= h(excerpt($featured['excerpt'] ?: $featured['body'], 200)) ?></p>
          <span class="go">Read More &rarr;</span>
        </div>
      </a>
    <?php endif; ?>

    <div class="row g-4">
      <?php foreach ($news as $i => $n): ?>
        <div class="col-md-6 col-lg-4">
          <a class="card-anu reveal reveal-<?= ($i % 4) + 1 ?> d-block h-100 text-decoration-none" href="<?= BASE_URL ?>/news-single.php?slug=<?= h($n['slug']) ?>">
            <?php if ($n['cover_image']): ?>
              <img class="thumb" src="<?= h(UPLOADS_URL . '/' . $n['cover_image']) ?>" alt="">
            <?php else: ?>
              <div class="thumb-placeholder"><i class="bi bi-newspaper"></i></div>
            <?php endif; ?>
            <div class="body">
              <span class="tag"><?= format_date($n['published_at']) ?></span>
              <h3 class="text-black"><?= h($n['title']) ?></h3>
              <p class="small text-muted mb-0"><?= h(excerpt($n['excerpt'] ?: $n['body'], 100)) ?></p>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
    <?php if (!$news && !$featured): ?><p class="text-muted">No news posted yet.</p><?php endif; ?>

    <?php if ($totalPages > 1): ?>
      <nav class="mt-5">
        <ul class="pagination justify-content-center">
          <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <li class="page-item <?= $p === $page ? 'active' : '' ?>">
              <a class="page-link <?= $p===$page ? 'page-link-active' : 'page-link-default' ?>" href="?page=<?= $p ?>"><?= $p ?></a>
            </li>
          <?php endfor; ?>
        </ul>
      </nav>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
