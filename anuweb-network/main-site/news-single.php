<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/rich_text.php';

$slug = $_GET['slug'] ?? '';
$newsScope = unit_scope_sql('news'); // Phase 5b fix: falls back to '' instead of a fatal error pre-migration.
$stmt = db()->prepare("SELECT * FROM news WHERE slug = ? AND is_published = 1{$newsScope} LIMIT 1");
$stmt->execute([$slug]);
$article = $stmt->fetch();

if (!$article) {
    http_response_code(404);
    $pageTitle = 'Not found';
    require __DIR__ . '/includes/header.php';
    echo '<div class="container py-5"><h1>Article not found</h1><p><a href="' . BASE_URL . '/news.php">Back to news</a></p></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}
$pageTitle = $article['title'];
$pageDescription = $article['excerpt'] ?: excerpt($article['body'], 160);

$related = db()->prepare("SELECT title, slug, cover_image, published_at FROM news WHERE is_published=1{$newsScope} AND slug != ? ORDER BY published_at DESC LIMIT 3");
$related->execute([$slug]);
$relatedArticles = $related->fetchAll();

$extraHead = '<link rel="stylesheet" href="' . asset_v(BASE_URL, 'assets/css/news-article.css') . '">';
require __DIR__ . '/includes/header.php';
$bodyHtml = anu_rt_public_html((string)$article['body']);
$readMins = max(1, (int) ceil(str_word_count(strip_tags($bodyHtml ?: (string)$article['excerpt'])) / 200));
$shareUrl = urlencode(SITE_URL . '/news-single?slug=' . $article['slug']);
$shareText = urlencode($article['title']);
?>
<article class="news-article">
  <header class="news-article-head">
    <div class="container">
      <div class="news-article-narrow">
        <a href="<?= BASE_URL ?>/news.php" class="news-back"><i class="bi bi-arrow-left"></i> All News</a>
        <?php if (!empty($article['category'])): ?><span class="tag news-cat"><?= h($article['category']) ?></span><?php endif; ?>
        <h1><?= h($article['title']) ?></h1>
        <div class="news-meta">
          <span><i class="bi bi-calendar3"></i> <?= format_date($article['published_at']) ?></span>
          <span><i class="bi bi-person"></i> <?= h($article['author']) ?></span>
          <span><i class="bi bi-clock"></i> <?= $readMins ?> min read</span>
        </div>
      </div>
    </div>
  </header>

  <div class="container">
    <?php if ($article['cover_image']): ?>
      <figure class="news-cover"><img src="<?= h(UPLOADS_URL . '/' . $article['cover_image']) ?>" alt="<?= h($article['title']) ?>"></figure>
    <?php endif; ?>

    <div class="news-article-narrow">
      <div class="news-body">
        <?= $bodyHtml !== '' ? $bodyHtml : '<p>' . h($article['excerpt']) . '</p>' ?>
      </div>

      <div class="news-share">
        <span class="news-share-label">Share this story</span>
        <a class="social-row-mini" href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrl ?>" target="_blank" rel="noopener" aria-label="Share on Facebook"><i class="bi bi-facebook"></i></a>
        <a class="social-row-mini" href="https://twitter.com/intent/tweet?url=<?= $shareUrl ?>&text=<?= $shareText ?>" target="_blank" rel="noopener" aria-label="Share on X"><i class="bi bi-twitter-x"></i></a>
        <a class="social-row-mini" href="https://wa.me/?text=<?= $shareText ?>%20<?= $shareUrl ?>" target="_blank" rel="noopener" aria-label="Share on WhatsApp"><i class="bi bi-whatsapp"></i></a>
        <a class="news-back ms-auto" href="<?= BASE_URL ?>/news.php"><i class="bi bi-arrow-left"></i> Back to all news</a>
      </div>
    </div>

    <?php if ($relatedArticles): ?>
      <div class="section-head mt-5 reveal"><h2 class="h4">More News</h2></div>
      <div class="row g-4">
        <?php foreach ($relatedArticles as $r): ?>
          <div class="col-md-4">
            <a class="card-anu reveal d-block h-100 text-decoration-none" href="<?= BASE_URL ?>/news-single.php?slug=<?= h($r['slug']) ?>">
              <?php if ($r['cover_image']): ?>
                <img class="thumb" src="<?= h(UPLOADS_URL . '/' . $r['cover_image']) ?>" alt="">
              <?php else: ?>
                <div class="thumb-placeholder"><i class="bi bi-newspaper"></i></div>
              <?php endif; ?>
              <div class="body">
                <span class="tag"><?= format_date($r['published_at']) ?></span>
                <h3 class="text-black" style="font-size:.98rem;"><?= h($r['title']) ?></h3>
              </div>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</article>
<?php require __DIR__ . '/includes/footer.php'; ?>
