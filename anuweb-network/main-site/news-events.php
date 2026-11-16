<?php
$pageTitle = 'News & Events';
$pageDescription = 'The latest news, announcements and upcoming events from Africa Nazarene University.';
require_once __DIR__ . '/includes/functions.php';

// Featured story: an admin can mark one article as_featured (Phase
// news+events merge); if none is marked, the most recent article on the
// main site's own News page takes that slot instead -- no gap in the
// hero, and nothing invented.
// Phase 5b fix: unit_scope_sql()/schema_has_column() check the live schema
// once (cached) so this page degrades to pre-migration behaviour instead of
// a fatal error if unit_slug or category haven't been added yet.
$newsScope = unit_scope_sql('news');
$eventsScope = unit_scope_sql('events');
$hasNewsCategory = schema_has_column('news', 'category');

$featured = db()->query("SELECT * FROM news WHERE is_published=1{$newsScope} AND is_featured=1 ORDER BY published_at DESC LIMIT 1")->fetch();
if (!$featured) {
    $featured = db()->query("SELECT * FROM news WHERE is_published=1{$newsScope} ORDER BY published_at DESC LIMIT 1")->fetch() ?: null;
}

$perPage = 6;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$category = trim($_GET['category'] ?? '');
$catParams = [];
$catWhere = '';
if ($hasNewsCategory && $category !== '') { $catWhere = ' AND category = ?'; $catParams[] = $category; }

$excludeFeatured = $featured ? ' AND id != ' . (int)$featured['id'] : '';

$totalStmt = db()->prepare("SELECT COUNT(*) c FROM news WHERE is_published=1{$newsScope}{$excludeFeatured}{$catWhere}");
$totalStmt->execute($catParams);
$total = (int) $totalStmt->fetch()['c'];
$totalPages = max(1, (int) ceil($total / $perPage));

$stmt = db()->prepare("SELECT * FROM news WHERE is_published=1{$newsScope}{$excludeFeatured}{$catWhere} ORDER BY published_at DESC LIMIT ? OFFSET ?");
$pos = 1;
foreach ($catParams as $v) { $stmt->bindValue($pos++, $v); }
$stmt->bindValue($pos++, $perPage, PDO::PARAM_INT);
$stmt->bindValue($pos++, $offset, PDO::PARAM_INT);
$stmt->execute();
$newsList = $stmt->fetchAll();

// Category chips: only real categories admins have actually used show up
// -- anu.ac.ke itself has no fixed category taxonomy, so we don't invent
// one either (see Phase 0 audit). Empty array (not a fatal error) if the
// category column isn't migrated yet.
$categories = $hasNewsCategory
    ? db()->query("SELECT DISTINCT category FROM news WHERE is_published=1{$newsScope} AND category IS NOT NULL AND category != '' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN)
    : [];

$events = db()->query("SELECT * FROM events WHERE is_published=1{$eventsScope} AND start_datetime >= NOW() ORDER BY start_datetime ASC LIMIT 5")->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container container-narrow text-center">
    <h1>News &amp; Events</h1>
    <p class="text-muted mb-0">Announcements, milestones, stories and what's happening across ANU.</p>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="row gy-5">
      <div class="col-lg-8">

        <?php if ($featured): ?>
          <a class="news-card reveal mb-5" style="align-items:stretch;" href="<?= BASE_URL ?>/news-single.php?slug=<?= h($featured['slug']) ?>">
            <?php if ($featured['cover_image']): ?>
              <img class="thumb" style="width:280px;height:auto;" src="<?= h(UPLOADS_URL . '/' . $featured['cover_image']) ?>" alt="">
            <?php else: ?>
              <span class="thumb-placeholder" style="width:280px;"><i class="bi bi-newspaper"></i></span>
            <?php endif; ?>
            <div class="content">
              <span class="date-tag">Featured &middot; <?= format_date($featured['published_at']) ?><?= $featured['category'] ? ' &middot; ' . h($featured['category']) : '' ?></span>
              <h3 style="font-size:1.4rem;"><?= h($featured['title']) ?></h3>
              <p class="excerpt"><?= h(excerpt($featured['excerpt'] ?: $featured['body'], 200)) ?></p>
              <span class="go">Read More &rarr;</span>
            </div>
          </a>
        <?php endif; ?>

        <?php if ($categories): ?>
          <div class="d-flex flex-wrap gap-2 mb-4">
            <a href="?category=" class="btn btn-sm <?= $category===''?'btn-apply':'btn-outline-anu' ?>">All</a>
            <?php foreach ($categories as $c): ?>
              <a href="?category=<?= urlencode($c) ?>" class="btn btn-sm <?= $category===$c?'btn-apply':'btn-outline-anu' ?>"><?= h($c) ?></a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <div class="row g-4">
          <?php foreach ($newsList as $i => $n): ?>
            <div class="col-md-6">
              <a class="card-anu reveal reveal-<?= ($i % 4) + 1 ?> d-block h-100 text-decoration-none" href="<?= BASE_URL ?>/news-single.php?slug=<?= h($n['slug']) ?>">
                <?php if ($n['cover_image']): ?>
                  <img class="thumb" src="<?= h(UPLOADS_URL . '/' . $n['cover_image']) ?>" alt="">
                <?php else: ?>
                  <div class="thumb-placeholder"><i class="bi bi-newspaper"></i></div>
                <?php endif; ?>
                <div class="body">
                  <span class="tag"><?= format_date($n['published_at']) ?><?= $n['category'] ? ' &middot; ' . h($n['category']) : '' ?></span>
                  <h3 class="text-black"><?= h($n['title']) ?></h3>
                  <p class="small text-muted mb-0"><?= h(excerpt($n['excerpt'] ?: $n['body'], 100)) ?></p>
                </div>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
        <?php if (!$newsList && !$featured): ?><p class="text-muted">No news posted yet.</p><?php endif; ?>

        <?php if ($totalPages > 1): ?>
          <nav class="mt-5">
            <ul class="pagination justify-content-center">
              <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                  <a class="page-link <?= $p===$page ? 'page-link-active' : 'page-link-default' ?>" href="?page=<?= $p ?><?= $category !== '' ? '&category=' . urlencode($category) : '' ?>"><?= $p ?></a>
                </li>
              <?php endfor; ?>
            </ul>
          </nav>
        <?php endif; ?>

        <div class="text-center mt-4">
          <a href="<?= BASE_URL ?>/news.php" class="see-all">Browse all news &rarr;</a>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="section-head">
          <h2>Upcoming Events</h2>
        </div>
        <?php if (!$events): ?>
          <p class="text-muted">No upcoming events posted yet.</p>
        <?php endif; ?>
        <?php foreach ($events as $e): ?>
          <div class="card-anu mb-3">
            <div class="body">
              <div class="d-flex align-items-start gap-3 mb-2">
                <span class="event-date-badge">
                  <span class="day"><?= date('d', strtotime($e['start_datetime'])) ?></span>
                  <span class="mon"><?= date('M', strtotime($e['start_datetime'])) ?></span>
                </span>
                <div>
                  <h3 class="mb-1" style="font-size:1rem;"><?= h($e['title']) ?></h3>
                  <?php if ($e['location']): ?><p class="small text-muted mb-0"><i class="bi bi-geo-alt"></i> <?= h($e['location']) ?></p><?php endif; ?>
                </div>
              </div>
              <?php if ($e['description']): ?><p class="small mb-0"><?= h(excerpt($e['description'], 100)) ?></p><?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
        <div class="mt-2">
          <a href="<?= BASE_URL ?>/events.php" class="see-all">View more events &rarr;</a>
        </div>

        <div class="section-head mt-5">
          <h2>Photo Gallery</h2>
        </div>
        <p class="small text-muted">See photos from recent events and graduations.</p>
        <a href="<?= BASE_URL ?>/photo-gallery.php" class="see-all">Browse the gallery &rarr;</a>

        <div class="section-head mt-5">
          <h2>Videos</h2>
        </div>
        <p class="small text-muted">Watch official ANU videos on our YouTube channel.</p>
        <a href="<?= h(setting('youtube_url')) ?>" target="_blank" rel="noopener" class="see-all">Watch on YouTube &rarr;</a>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
