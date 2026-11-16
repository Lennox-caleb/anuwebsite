<?php
require_once __DIR__ . '/includes/functions.php';

$slug = trim($_GET['slug'] ?? '');
$stmt = db()->prepare("SELECT * FROM news WHERE slug = ? AND is_published = 1");
$stmt->execute([$slug]);
$article = $stmt->fetch();

if (!$article) {
    http_response_code(404);
    $pageTitle = 'Article Not Found';
    require __DIR__ . '/includes/header.php';
    echo '<section class="tvet-section"><div class="container text-center"><h1>Article not found</h1><a href="' . BASE_URL . '/news.php" class="btn btn-tvet-cta">Back to News</a></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $article['title'];
$pageDescription = excerpt($article['excerpt'] ?? $article['body'], 155);
$more = db()->prepare("SELECT * FROM news WHERE id != ? AND is_published = 1 ORDER BY published_at DESC LIMIT 3");
$more->execute([$article['id']]);
$moreNews = $more->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<section class="tvet-hero compact<?= $article['image'] ? ' has-image' : '' ?>">
  <?php if ($article['image']): ?><img class="hero-bg-image" src="<?= h(upload_url('news', $article['image'])) ?>" alt=""><?php endif; ?>
  <div class="container">
    <div class="eyebrow mb-2"><?= h($article['category']) ?></div>
    <h1><?= h($article['title']) ?></h1>
    <p class="small <?= $article['image'] ? 'text-white-50' : 'text-muted' ?>"><i class="bi bi-calendar3"></i> <?= fdate($article['published_at'], 'd F Y') ?></p>
  </div>
</section>

<section class="tvet-section">
  <div class="container container-narrow">
    <div class="news-body">
      <?= nl2br(h($article['body'])) ?>
    </div>

    <div class="d-flex gap-2 mt-5">
      <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" target="_blank" rel="noopener" class="btn btn-tvet-outline btn-sm"><i class="bi bi-facebook"></i> Share</a>
      <a href="https://wa.me/?text=<?= urlencode($article['title']) ?>" target="_blank" rel="noopener" class="btn btn-tvet-outline btn-sm"><i class="bi bi-whatsapp"></i> Share</a>
    </div>
  </div>

  <?php if ($moreNews): ?>
    <div class="container mt-5">
      <div class="tvet-section-head"><h2 class="h4">More News</h2></div>
      <div class="row g-4">
        <?php foreach ($moreNews as $n): ?>
          <div class="col-md-4">
            <div class="news-card">
              <div class="thumb"><?php if ($n['image']): ?><img src="<?= h(upload_url('news', $n['image'])) ?>" alt=""><?php endif; ?></div>
              <div class="body">
                <span class="cat"><?= h($n['category']) ?></span>
                <h3><a class="text-dark" href="<?= BASE_URL ?>/news-single.php?slug=<?= h($n['slug']) ?>"><?= h($n['title']) ?></a></h3>
                <div class="date"><i class="bi bi-calendar3"></i> <?= fdate($n['published_at']) ?></div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
