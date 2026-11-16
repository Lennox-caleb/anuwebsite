<?php
$pageTitle = 'News & Updates';
require_once __DIR__ . '/includes/functions.php';

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 9;
$offset = ($page - 1) * $perPage;

$total = (int) db()->query("SELECT COUNT(*) c FROM news WHERE is_published = 1")->fetch()['c'];
$stmt = db()->prepare("SELECT * FROM news WHERE is_published = 1 ORDER BY published_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute();
$news = $stmt->fetchAll();
$totalPages = max(1, (int)ceil($total / $perPage));

require __DIR__ . '/includes/header.php';
?>
<section class="tvet-hero compact<?= page_banner_url('news') ? ' has-image' : '' ?>">
  <?php if ($b = page_banner_url('news')): ?><img class="hero-bg-image" src="<?= h($b) ?>" alt=""><?php endif; ?>
  <div class="container">
    <div class="eyebrow mb-2">Newsroom</div>
    <h1>News &amp; Updates</h1>
  </div>
</section>

<section class="tvet-section">
  <div class="container">
    <?php if (!$news): ?><p class="text-muted">No news articles published yet.</p><?php endif; ?>
    <div class="row g-4">
      <?php foreach ($news as $n): ?>
        <div class="col-md-4">
          <div class="news-card">
            <div class="thumb"><?php if ($n['image']): ?><img src="<?= h(upload_url('news', $n['image'])) ?>" alt=""><?php endif; ?></div>
            <div class="body">
              <span class="cat"><?= h($n['category']) ?></span>
              <h3><a class="text-dark" href="<?= BASE_URL ?>/news-single.php?slug=<?= h($n['slug']) ?>"><?= h($n['title']) ?></a></h3>
              <p class="small text-muted"><?= h(excerpt($n['excerpt'] ?? $n['body'], 100)) ?></p>
              <div class="date"><i class="bi bi-calendar3"></i> <?= fdate($n['published_at']) ?></div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
      <nav class="mt-5"><ul class="pagination justify-content-center">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
        <?php endfor; ?>
      </ul></nav>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
