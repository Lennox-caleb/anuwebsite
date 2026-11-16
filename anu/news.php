<?php
header('Cache-Control: no-store, no-cache, must-revalidate');
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/tracker.php';
require_once __DIR__ . '/includes/layout.php';

$db = getDB();

/* ── Single article view ── */
$articleId = (int)($_GET['id'] ?? 0);
if ($articleId) {
$articles=[]; $article=[];
try {
    $article = $db->prepare("SELECT * FROM news WHERE id=? AND is_published=1");
} catch(Exception $e) {}
    $article->execute([$articleId]);
    $article = $article->fetch();
    try { $db->prepare("UPDATE news SET views=COALESCE(views,0)+1 WHERE id=?")->execute([$articleId]); } catch(Exception $e){}
    $related = [];
    if ($article) {
        $r = $db->prepare("SELECT * FROM news WHERE is_published=1 AND id!=? AND category=? ORDER BY published_at DESC LIMIT 3");
        $r->execute([$articleId, $article['category']]);
        $related = $r->fetchAll();
        if (count($related) < 3) {
            $r2 = $db->prepare("SELECT * FROM news WHERE is_published=1 AND id!=? ORDER BY published_at DESC LIMIT ?");
            $r2->execute([$articleId, 3 - count($related)]);
            $related = array_merge($related, $r2->fetchAll());
        }
    }
}

/* ── List view ── */
$cat     = trim($_GET['cat'] ?? '');
$page    = max(1,(int)($_GET['page'] ?? 1));
$perPage = 9;
$offset  = ($page-1)*$perPage;

$catWhere = $cat ? " AND category=:cat" : "";
$total    = $db->prepare("SELECT COUNT(*) FROM news WHERE is_published=1$catWhere");
$cat ? $total->execute([':cat'=>$cat]) : $total->execute();
$total = (int)$total->fetchColumn();

$stmt = $db->prepare("SELECT * FROM news WHERE is_published=1$catWhere ORDER BY is_featured DESC, published_at DESC LIMIT $perPage OFFSET $offset");
$cat ? $stmt->execute([':cat'=>$cat]) : $stmt->execute();
$newsList = $stmt->fetchAll();
$totalPages = (int)ceil($total/$perPage);

$cats = $db->query("SELECT DISTINCT category FROM news WHERE is_published=1 AND category IS NOT NULL AND category!='' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

$featured = null;
if (!$cat && $page===1 && !$articleId) {
    $f = $db->query("SELECT * FROM news WHERE is_published=1 AND is_featured=1 ORDER BY published_at DESC LIMIT 1")->fetch();
    $featured = $f ?: ($newsList[0] ?? null);
    if ($featured) $newsList = array_filter($newsList, fn($n) => $n['id'] !== $featured['id']);
}

$recentList = $db->query("SELECT id,title,featured_image,published_at,category FROM news WHERE is_published=1 ORDER BY published_at DESC LIMIT 5")->fetchAll();

$heroSlides = getSlides('news');
$heroData   = !empty($heroSlides) ? $heroSlides[0] : getHero('news');
$heroBg     = !empty($heroData['bg_image']) ? imgUrl($heroData['bg_image']) : '';

renderHead('News & Stories'); renderNavbar('news');

/* ═══ SINGLE ARTICLE VIEW ═══ */
if ($articleId && !empty($article)): ?>

<div class="nws-art-hero" <?= $heroBg ? "style=\"background-image:url('{$heroBg}');background-size:cover;background-position:center\"" : '' ?>>
  <div class="container nws-art-hero-inner">
    <a href="/anu/news.php" class="nws-back-btn"><i class="bi bi-arrow-left"></i> All Stories</a>
    <div class="nws-cat-pill" style="margin-top:20px"><?= e($article['category']) ?></div>
    <h1 class="nws-art-title"><?= e($article['title']) ?></h1>
    <div class="nws-art-meta">
      <span><i class="bi bi-calendar3"></i><?= formatDate($article['published_at']) ?></span>
      <span><i class="bi bi-person-fill"></i><?= e($article['author']) ?></span>
      <?php if($article['views'] ?? 0): ?>
      <span><i class="bi bi-eye-fill"></i><?= number_format($article['views']) ?> views</span>
      <?php endif; ?>
    </div>
  </div>
</div>

<div style="background:var(--light);padding:64px 0 96px">
  <div class="container">
    <div class="nws-art-layout">
      <article class="nws-art-main">
        <?php if ($article['featured_image']): ?>
        <img src="<?= imgUrl($article['featured_image']) ?>" alt="<?= e($article['title']) ?>"
             class="nws-art-hero-img" onerror="this.style.display='none'">
        <?php endif; ?>
        <div class="nws-art-body"><?= $article['content'] ?></div>
        <div class="nws-share-row">
          <span>Share this story:</span>
          <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/anu/news?id=' . $articleId) ?>" target="_blank" class="nws-share-btn" style="background:#1877F2"><i class="bi bi-facebook"></i></a>
          <a href="https://twitter.com/intent/tweet?url=<?= urlencode(((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/anu/news?id=' . $articleId) ?>&text=<?= urlencode($article['title']) ?>" target="_blank" class="nws-share-btn" style="background:#000"><i class="bi bi-twitter-x"></i></a>
          <a href="https://wa.me/?text=<?= urlencode($article['title'].' '.((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/anu/news?id=' . $articleId) ?>" target="_blank" class="nws-share-btn" style="background:#25D366"><i class="bi bi-whatsapp"></i></a>
          <a href="mailto:?subject=<?= urlencode($article['title']) ?>&body=<?= urlencode(((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/anu/news?id=' . $articleId) ?>" class="nws-share-btn" style="background:#555"><i class="bi bi-envelope-fill"></i></a>
        </div>
        <div style="padding-top:28px;border-top:1px solid rgba(0,0,0,.07);margin-top:8px">
          <a href="/anu/news.php" class="nws-back-btn-dark"><i class="bi bi-arrow-left"></i> Back to All Stories</a>
        </div>
      </article>

      <aside class="nws-art-sidebar">
        <div class="nws-sidebar-card">
          <div class="nws-sidebar-hdr">Latest Stories</div>
          <?php foreach($recentList as $rn): ?>
          <a href="/anu/news.php?id=<?= $rn['id'] ?>" class="nws-sidebar-item <?= $rn['id']==$articleId?'active':'' ?>">
            <div class="nws-sidebar-thumb">
              <img src="<?= $rn['featured_image'] ? imgUrl($rn['featured_image']) : '/anu/assets/images/default-news.jpg' ?>" alt="" onerror="this.src='/anu/assets/images/default-news.jpg'">
            </div>
            <div class="nws-sidebar-item-body">
              <div class="nws-sidebar-item-cat"><?= e($rn['category']) ?></div>
              <div class="nws-sidebar-item-title"><?= e(mb_strimwidth($rn['title'],0,65,'…')) ?></div>
              <div class="nws-sidebar-item-date"><i class="bi bi-calendar3"></i><?= formatDate($rn['published_at']) ?></div>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
        <?php if(!empty($cats)): ?>
        <div class="nws-sidebar-card">
          <div class="nws-sidebar-hdr">Browse By Category</div>
          <div style="display:flex;flex-wrap:wrap;gap:8px;padding:16px">
            <a href="/anu/news.php" class="nws-cat-pill nws-cat-pill-sm">All</a>
            <?php foreach($cats as $c): ?>
            <a href="/anu/news.php?cat=<?= urlencode($c) ?>" class="nws-cat-pill nws-cat-pill-sm"><?= e($c) ?></a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
        <div class="nws-sidebar-cta">
          <div class="nws-sidebar-cta-icon"><i class="bi bi-mortarboard-fill"></i></div>
          <h5>Ready to Enrol?</h5>
          <p>Explore ANU Online programs and start your journey today.</p>
          <a href="/anu/programs.php" class="nws-sidebar-cta-btn">View Programs</a>
        </div>
      </aside>
    </div>

    <?php if (!empty($related)): ?>
    <div style="margin-top:72px">
      <div class="sec-tag" style="margin-bottom:8px">You Might Also Like</div>
      <h3 style="font-family:var(--fh);font-size:clamp(1.5rem,2.5vw,2rem);color:var(--dark);margin-bottom:32px">Related Stories</h3>
      <div class="nws-grid">
        <?php foreach(array_slice($related,0,3) as $rn): ?>
        <div class="nws-card">
          <a href="/anu/news.php?id=<?= $rn['id'] ?>" class="nws-card-img">
            <img src="<?= $rn['featured_image'] ? imgUrl($rn['featured_image']) : '/anu/assets/images/default-news.jpg' ?>" alt="<?= e($rn['title']) ?>" loading="lazy" onerror="this.src='/anu/assets/images/default-news.jpg'">
          </a>
          <div class="nws-card-body">
            <div class="nws-card-cat"><?= e($rn['category']) ?></div>
            <a href="/anu/news.php?id=<?= $rn['id'] ?>" class="nws-card-title"><?= e($rn['title']) ?></a>
            <p class="nws-card-excerpt"><?= e(mb_strimwidth($rn['excerpt']??'',0,100,'…')) ?></p>
          </div>
          <div class="nws-card-footer">
            <span class="nws-card-date"><i class="bi bi-calendar3"></i><?= formatDate($rn['published_at']) ?></span>
            <a href="/anu/news.php?id=<?= $rn['id'] ?>" class="nws-read-btn">Read <i class="bi bi-arrow-right"></i></a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php else: /* ═══ LIST VIEW ═══ */ ?>

<div class="nws-hero" <?= $heroBg ? "style=\"background-image:url('{$heroBg}');background-size:cover;background-position:center\"" : '' ?>>
  <div class="nws-hero-noise"></div>
  <div class="container nws-hero-inner">
    <div class="row align-items-end g-4">
      <div class="col-lg-8" data-aos="fade-right">
        <div class="nws-hero-eyebrow"><span class="nws-hero-dot"></span>Live from ANU Online</div>
        <h1 class="nws-hero-title">Stories, News &amp; <span style="color:var(--gold)">Announcements</span></h1>
        <p class="nws-hero-sub">The latest from Africa Nazarene University Online — student achievements, academic updates, and community stories.</p>
        <div class="nws-search-wrap">
          <i class="bi bi-search"></i>
          <input type="text" id="nwsSearch" placeholder="Search stories…" oninput="nwsDoSearch(this.value)" autocomplete="off">
          <?php if($total): ?><span class="nws-search-count"><?= $total ?> stories</span><?php endif; ?>
        </div>
      </div>
      <div class="col-lg-4 text-lg-end" data-aos="fade-left">
        <div class="nws-hero-stat-stack">
          <div class="nws-hero-stat">
            <span class="nws-hero-stat-num"><?= $total ?></span>
            <span class="nws-hero-stat-label">Published</span>
          </div>
          <div class="nws-hero-stat">
            <span class="nws-hero-stat-num"><?= count($cats) ?></span>
            <span class="nws-hero-stat-label">Categories</span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php if (!empty($cats)): ?>
  <div class="nws-pill-band">
    <div class="container">
      <div class="nws-filter">
        <a href="/anu/news.php" class="nws-pill <?= !$cat?'active':'' ?>"><i class="bi bi-grid-fill"></i> All</a>
        <?php foreach($cats as $c): ?>
        <a href="?cat=<?= urlencode($c) ?>" class="nws-pill <?= $cat===$c?'active':'' ?>"><?= e($c) ?></a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<div style="background:var(--light);padding:72px 0 96px">
  <div class="container">
    <div class="nws-content-layout">
      <div class="nws-feed" id="nwsFeed">

        <?php if ($featured): ?>
        <div class="nws-featured" data-aos="fade-up">
          <a href="/anu/news.php?id=<?= $featured['id'] ?>" class="nws-feat-img-wrap">
            <img src="<?= $featured['featured_image'] ? imgUrl($featured['featured_image']) : '/anu/assets/images/default-news.jpg' ?>"
                 alt="<?= e($featured['title']) ?>" onerror="this.src='/anu/assets/images/default-news.jpg'">
            <div class="nws-feat-img-overlay"></div>
            <?php if($featured['is_featured']): ?>
            <span class="nws-feat-badge"><i class="bi bi-star-fill me-1"></i>Featured</span>
            <?php endif; ?>
          </a>
          <div class="nws-feat-body">
            <div class="nws-cat-pill"><?= e($featured['category']) ?></div>
            <a href="/anu/news.php?id=<?= $featured['id'] ?>" class="nws-feat-title"><?= e($featured['title']) ?></a>
            <p class="nws-feat-excerpt"><?= e(mb_strimwidth($featured['excerpt']??'',0,200,'…')) ?></p>
            <div class="nws-feat-meta">
              <span><i class="bi bi-calendar3"></i><?= formatDate($featured['published_at']) ?></span>
              <span><i class="bi bi-person-fill"></i><?= e($featured['author']) ?></span>
            </div>
            <a href="/anu/news.php?id=<?= $featured['id'] ?>" class="nws-feat-cta">Read Full Story <i class="bi bi-arrow-right"></i></a>
          </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($newsList)): ?>
        <div class="nws-grid" id="nwsGrid">
          <?php foreach($newsList as $i=>$n): ?>
          <div class="nws-card" data-search="<?= e(strtolower($n['title'].' '.$n['category'].' '.($n['excerpt']??''))) ?>"
               data-aos="fade-up" data-aos-delay="<?= ($i%3)*60 ?>">
            <a href="/anu/news.php?id=<?= $n['id'] ?>" class="nws-card-img">
              <img src="<?= $n['featured_image'] ? imgUrl($n['featured_image']) : '/anu/assets/images/default-news.jpg' ?>"
                   alt="<?= e($n['title']) ?>" loading="lazy" onerror="this.src='/anu/assets/images/default-news.jpg'">
              <?php if($n['is_featured']): ?><span class="nws-card-star"><i class="bi bi-star-fill"></i></span><?php endif; ?>
            </a>
            <div class="nws-card-body">
              <div class="nws-card-cat"><?= e($n['category']) ?></div>
              <a href="/anu/news.php?id=<?= $n['id'] ?>" class="nws-card-title"><?= e($n['title']) ?></a>
              <p class="nws-card-excerpt"><?= e(mb_strimwidth($n['excerpt']??'',0,100,'…')) ?></p>
            </div>
            <div class="nws-card-footer">
              <span class="nws-card-date"><i class="bi bi-calendar3"></i><?= formatDate($n['published_at']) ?></span>
              <a href="/anu/news.php?id=<?= $n['id'] ?>" class="nws-read-btn">Read <i class="bi bi-arrow-right"></i></a>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <div id="nwsEmpty" style="display:none;text-align:center;padding:60px 0;color:var(--muted)">
          <i class="bi bi-search" style="font-size:2.5rem;display:block;margin-bottom:12px;color:#ddd"></i>
          No stories match that search.
        </div>
        <?php elseif(!$featured): ?>
        <div class="text-center py-5" style="color:var(--muted)">
          <i class="bi bi-newspaper" style="font-size:3rem;display:block;margin-bottom:14px;color:#ddd"></i>
          <p>No stories published yet — check back soon!</p>
        </div>
        <?php endif; ?>

        <?php if ($totalPages > 1): ?>
        <div class="nws-pager">
          <?php if($page>1): ?><a href="?<?=$cat?"cat=".urlencode($cat)."&":"" ?>page=<?=$page-1?>"><i class="bi bi-chevron-left"></i></a><?php endif; ?>
          <?php for($i=1;$i<=$totalPages;$i++): ?>
            <?php if($i===$page): ?><span class="active"><?=$i?></span>
            <?php elseif($i===1||$i===$totalPages||abs($i-$page)<=2): ?><a href="?<?=$cat?"cat=".urlencode($cat)."&":"" ?>page=<?=$i?>"><?=$i?></a>
            <?php elseif(abs($i-$page)===3): ?><span style="border:none;color:var(--muted)">…</span><?php endif; ?>
          <?php endfor; ?>
          <?php if($page<$totalPages): ?><a href="?<?=$cat?"cat=".urlencode($cat)."&":"" ?>page=<?=$page+1?>"><i class="bi bi-chevron-right"></i></a><?php endif; ?>
        </div>
        <?php endif; ?>
      </div>

      <aside class="nws-sidebar">
        <div class="nws-sidebar-card">
          <div class="nws-sidebar-hdr">Latest Stories</div>
          <?php foreach($recentList as $rn): ?>
          <a href="/anu/news.php?id=<?= $rn['id'] ?>" class="nws-sidebar-item">
            <div class="nws-sidebar-thumb">
              <img src="<?= $rn['featured_image'] ? imgUrl($rn['featured_image']) : '/anu/assets/images/default-news.jpg' ?>" alt="" onerror="this.src='/anu/assets/images/default-news.jpg'">
            </div>
            <div class="nws-sidebar-item-body">
              <div class="nws-sidebar-item-cat"><?= e($rn['category']) ?></div>
              <div class="nws-sidebar-item-title"><?= e(mb_strimwidth($rn['title'],0,65,'…')) ?></div>
              <div class="nws-sidebar-item-date"><i class="bi bi-calendar3"></i><?= formatDate($rn['published_at']) ?></div>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
        <?php if(!empty($cats)): ?>
        <div class="nws-sidebar-card">
          <div class="nws-sidebar-hdr">Categories</div>
          <div style="padding:8px 16px 16px">
            <?php foreach($cats as $c): ?>
            <a href="?cat=<?= urlencode($c) ?>" class="nws-cat-link <?= $cat===$c?'active':'' ?>">
              <span><?= e($c) ?></span><i class="bi bi-chevron-right"></i>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
        <div class="nws-sidebar-cta">
          <div class="nws-sidebar-cta-icon"><i class="bi bi-mortarboard-fill"></i></div>
          <h5>Ready to Enrol?</h5>
          <p>Explore programs and start your ANU Online journey today.</p>
          <a href="/anu/programs.php" class="nws-sidebar-cta-btn">View Programs</a>
        </div>
        <div class="nws-newsletter-card">
          <i class="bi bi-envelope-paper-fill nws-nl-icon"></i>
          <h6>Stay in the Loop</h6>
          <p>Get the latest ANU Online news delivered to your inbox.</p>
          <a href="/anu/contact.php" class="nws-nl-btn">Subscribe <i class="bi bi-arrow-right"></i></a>
        </div>
      </aside>
    </div>
  </div>
</div>

<section class="cta-band">
  <div class="container text-center" style="position:relative;z-index:1">
    <div class="sec-tag" style="background:rgba(250,200,35,.18);color:#fac823">Stay Updated</div>
    <h2 class="mt-3" style="color:#fff;font-family:var(--fh);font-size:clamp(2rem,4vw,3rem)">Never Miss a Story</h2>
    <p style="color:rgba(255,255,255,.68);max-width:440px;margin:14px auto 0;line-height:1.75">Follow ANU Online for the latest news, student achievements, and academic updates.</p>
    <div class="d-flex gap-3 justify-content-center flex-wrap mt-4">
      <a href="/anu/contact.php" class="btn-cta-w">Get In Touch <i class="bi bi-arrow-right"></i></a>
      <a href="/anu/programs.php" class="btn-cta-o">Explore Programs</a>
    </div>
  </div>
</section>

<script>
function nwsDoSearch(q) {
  q = q.trim().toLowerCase();
  var cards = document.querySelectorAll('#nwsGrid .nws-card'), shown = 0;
  cards.forEach(function(c) {
    var match = !q || c.dataset.search.includes(q);
    c.style.display = match ? '' : 'none';
    if (match) shown++;
  });
  var empty = document.getElementById('nwsEmpty');
  if (empty) empty.style.display = (shown===0 && q) ? 'block' : 'none';
}
</script>

<?php endif; renderFooter(); ?>
