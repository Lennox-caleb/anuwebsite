<?php
$pageTitle = null; // homepage keeps the base site name only
require_once __DIR__ . '/includes/functions.php';

// Phase 5b fix: unit_slug is a real column on sliders/news/events, but a
// live database that hasn't had that migration run yet must not fatal-error
// here -- unit_scope_sql() checks the schema once (cached) and returns ''
// when the column isn't there yet, which falls back to the old, pre-Phase-5
// behaviour (every row treated as belonging to the main site).
$slidesScope = unit_scope_sql('sliders');
$newsScope   = unit_scope_sql('news');
$eventsScope = unit_scope_sql('events');

$slides   = db()->query("SELECT * FROM sliders WHERE is_published=1{$slidesScope} ORDER BY sort_order")->fetchAll();
$schools  = db()->query('SELECT * FROM schools WHERE is_published=1 ORDER BY sort_order LIMIT 5')->fetchAll();
$facts    = db()->query('SELECT * FROM fun_facts ORDER BY sort_order')->fetchAll();
// Merged "News & Events" homepage block: one featured story (admin's
// is_featured pick, or the most recent article if none is marked) plus
// two more recent articles, alongside upcoming events.
$featuredNews = db()->query("SELECT * FROM news WHERE is_published=1{$newsScope} AND is_featured=1 ORDER BY published_at DESC LIMIT 1")->fetch();
if (!$featuredNews) {
    $featuredNews = db()->query("SELECT * FROM news WHERE is_published=1{$newsScope} ORDER BY published_at DESC LIMIT 1")->fetch() ?: null;
}
$newsExclude = $featuredNews ? ' AND id != ' . (int) $featuredNews['id'] : '';
$news     = db()->query("SELECT * FROM news WHERE is_published=1{$newsScope}{$newsExclude} ORDER BY published_at DESC LIMIT 2")->fetchAll();
$events   = db()->query("SELECT * FROM events WHERE is_published=1{$eventsScope} AND start_datetime >= NOW() ORDER BY start_datetime ASC LIMIT 3")->fetchAll();
$partners = db()->query('SELECT * FROM partners WHERE is_published=1 ORDER BY sort_order')->fetchAll();

$pageDescription = setting('mission');
require __DIR__ . '/includes/header.php';
$__fallbackHero = SITE_ROOT . '/assets/images/campus-savannah.jpg';
?>
<?php require __DIR__ . '/includes/news_ticker.php'; ?>

<!-- HERO CAROUSEL -->
<section class="hero-carousel-wrap">
  <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
    <?php if (count($slides) > 1): ?>
    <div class="carousel-indicators">
      <?php foreach ($slides as $i => $s): ?>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $i ?>" class="<?= $i===0?'active':'' ?>" aria-current="<?= $i===0?'true':'false' ?>" aria-label="Slide <?= $i+1 ?>"></button>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <div class="carousel-inner">
      <?php if ($slides): foreach ($slides as $i => $s):
        // The image (or the site-wide stock fallback) always doubles as
        // the video/YouTube poster frame and as a CSS background behind
        // it, so a slow connection, an autoplay block, or a broken
        // video/embed still shows a real photo instead of a blank slide.
        $posterUrl = $s['image'] ? (UPLOADS_URL . '/' . $s['image']) : (file_exists($__fallbackHero) ? BASE_URL . '/assets/images/campus-savannah.jpg' : '');
        $mediaKind = hero_media_kind($s);
        $mediaHtml = render_hero_media($s, UPLOADS_URL, 'hero-media');
        // Text and buttons are all optional (Phase 2) -- a hero can be
        // media-only. Only render the content overlay div at all when
        // there's actually something in it, so a text-free slide has no
        // empty container or dead spacing sitting on top of the media.
        $hasText = !empty($s['headline']) || !empty($s['subtext']) || !empty($s['link_text']) || !empty($s['link2_text'] ?? '');
      ?>
        <div class="carousel-item hero hero-fullscreen <?= $i===0?'active':'' ?>"<?= ($mediaKind === 'youtube' || $mediaKind === 'video') && $posterUrl ? ' style="background-image:url(\'' . h($posterUrl) . '\'); background-size:cover; background-position:center top;"' : '' ?>>
          <?php if ($mediaHtml): ?>
            <?= $mediaHtml ?>
          <?php elseif (file_exists($__fallbackHero)): ?>
            <img class="hero-media" src="<?= BASE_URL ?>/assets/images/campus-savannah.jpg" alt="ANU main campus near Nairobi National Park">
          <?php else: ?>
            <div class="hero-media-fallback" aria-hidden="true"></div>
          <?php endif; ?>
          <?php if ($hasText): ?>
          <div class="container hero-content">
            <?php if ($s['subtext']): ?><p class="lead mb-3"><?= h($s['subtext']) ?></p><?php endif; ?>
            <?php if ($s['headline']): ?><h1 class="mb-0"><?= h($s['headline']) ?></h1><?php endif; ?>
            <?php if ($s['headline'] && !$s['subtext']): ?><div class="mb-4"></div><?php endif; ?>
            <?php if (!empty($s['link_text']) || !empty($s['link2_text'] ?? '')): ?>
              <div class="d-flex flex-wrap gap-3<?= $s['headline'] || $s['subtext'] ? '' : ' mt-0' ?>">
                <?php if (!empty($s['link_text'])): ?>
                  <a href="<?= h(resolve_link($s['link_url'] ?: '#')) ?>" class="btn btn-apply btn-lg"<?= is_external_link($s['link_url'] ?: '') ? ' target="_blank" rel="noopener"' : '' ?>><?= h($s['link_text']) ?></a>
                <?php endif; ?>
                <?php if (!empty($s['link2_text'] ?? '')): ?>
                  <a href="<?= h(resolve_link($s['link2_url'] ?? '#')) ?>" class="btn btn-outline-light btn-lg"<?= is_external_link($s['link2_url'] ?? '') ? ' target="_blank" rel="noopener"' : '' ?>><?= h($s['link2_text']) ?></a>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
      <?php endforeach; else: ?>
        <div class="carousel-item hero active">
          <?php if (file_exists($__fallbackHero)): ?>
            <img class="hero-media" src="<?= BASE_URL ?>/assets/images/campus-savannah.jpg" alt="ANU main campus near Nairobi National Park">
          <?php else: ?>
            <div class="hero-media-fallback" aria-hidden="true"></div>
          <?php endif; ?>
          <div class="container hero-content">
            <h1 class="mb-0"><?= h(setting('tagline')) ?></h1>
          </div>
        </div>
      <?php endif; ?>
    </div>
    <div class="hero-scroll-cue" aria-hidden="true">Scroll</div>
    <?php if (count($slides) > 1): ?>
    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Previous slide</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Next slide</span>
    </button>
    <?php endif; ?>
  </div>
</section>

<!-- TRUST / COMPLIANCE STRIP -->
<section class="trust-strip">
  <div class="container">
    <div class="row g-3">
      <div class="col-6 col-md-3">
        <div class="trust-item"><i class="bi bi-patch-check-fill"></i> Chartered by the Commission for University Education (CUE)</div>
      </div>
      <div class="col-6 col-md-3">
        <div class="trust-item"><i class="bi bi-shield-lock-fill"></i> Data handled per the Data Protection Act, 2019</div>
      </div>
      <div class="col-6 col-md-3">
        <div class="trust-item"><i class="bi bi-mortarboard-fill"></i> Sponsored by the Church of the Nazarene</div>
      </div>
      <div class="col-6 col-md-3">
        <div class="trust-item"><i class="bi bi-globe-americas"></i> Serving students across Kenya and internationally</div>
      </div>
    </div>
  </div>
</section>

<!-- FUN FACTS -->
<section class="facts-strip">
  <div class="container">
    <div class="row">
      <?php foreach ($facts as $f):
        $num = null; $suffix = '';
        if (preg_match('/^([\d,]+)(.*)$/', trim($f['stat_value']), $m)) {
            $num = (int) str_replace(',', '', $m[1]);
            $suffix = trim($m[2]);
        }
      ?>
        <div class="col-6 col-lg-3 fact">
          <?php if ($num !== null): ?>
            <div class="value" data-count="<?= $num ?>" data-suffix="<?= h($suffix) ?>"><?= h($f['stat_value']) ?></div>
          <?php else: ?>
            <div class="value"><?= h($f['stat_value']) ?></div>
          <?php endif; ?>
          <div class="label"><?= h($f['stat_label']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- SCHOOLS -->
<section class="py-5">
  <div class="container">
    <div class="section-head reveal">
      <h2>Our Five Schools</h2>
      <a href="<?= BASE_URL ?>/schools.php" class="see-all">View all schools &rarr;</a>
    </div>
    <div class="schools-strip">
    <?php foreach ($schools as $i => $s): ?>
      <a class="school-row reveal" href="<?= BASE_URL ?>/schools/<?= h($s['slug']) ?>">
        <span class="num"><?= str_pad($i+1, 2, '0', STR_PAD_LEFT) ?></span>
        <?php if ($s['image']): ?>
          <img class="thumb" src="<?= h(UPLOADS_URL . '/' . $s['image']) ?>" alt="">
        <?php else: ?>
          <span class="thumb-placeholder"><i class="bi bi-mortarboard"></i></span>
        <?php endif; ?>
        <div>
          <p class="title"><?= h($s['name']) ?></p>
          <p class="desc"><?= h($s['summary']) ?></p>
        </div>
        <span class="arrow">&rarr;</span>
      </a>
    <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- NEWS & EVENTS (merged) -->
<section class="py-5">
  <div class="container">
    <div class="row gy-5">
      <div class="col-lg-8">
        <div class="section-head reveal">
          <h2>News &amp; Events</h2>
          <a href="<?= BASE_URL ?>/news-events.php" class="see-all">View all &rarr;</a>
        </div>
        <?php if ($featuredNews): ?>
          <a class="news-card reveal mb-3" href="<?= BASE_URL ?>/news-single.php?slug=<?= h($featuredNews['slug']) ?>">
            <?php if ($featuredNews['cover_image']): ?>
              <img class="thumb" src="<?= h(UPLOADS_URL . '/' . $featuredNews['cover_image']) ?>" alt="">
            <?php else: ?>
              <span class="thumb-placeholder"><i class="bi bi-newspaper"></i></span>
            <?php endif; ?>
            <div class="content">
              <span class="date-tag">Featured &middot; <?= format_date($featuredNews['published_at']) ?></span>
              <h3><?= h($featuredNews['title']) ?></h3>
              <p class="excerpt"><?= h(excerpt($featuredNews['excerpt'] ?: $featuredNews['body'], 110)) ?></p>
              <span class="go">Read More &rarr;</span>
            </div>
          </a>
        <?php endif; ?>
        <?php foreach ($news as $n): ?>
          <a class="news-card reveal" href="<?= BASE_URL ?>/news-single.php?slug=<?= h($n['slug']) ?>">
            <?php if ($n['cover_image']): ?>
              <img class="thumb" src="<?= h(UPLOADS_URL . '/' . $n['cover_image']) ?>" alt="">
            <?php else: ?>
              <span class="thumb-placeholder"><i class="bi bi-newspaper"></i></span>
            <?php endif; ?>
            <div class="content">
              <span class="date-tag"><?= format_date($n['published_at']) ?></span>
              <h3><?= h($n['title']) ?></h3>
              <p class="excerpt"><?= h(excerpt($n['excerpt'] ?: $n['body'], 110)) ?></p>
              <span class="go">Read More &rarr;</span>
            </div>
          </a>
        <?php endforeach; ?>
        <?php if (!$featuredNews && !$news): ?><p class="text-muted">No news posted yet.</p><?php endif; ?>
      </div>
      <div class="col-lg-4">
        <div class="section-head">
          <h2>Upcoming Events</h2>
        </div>
        <?php if (!$events): ?>
          <p class="event-list-empty mb-3">No upcoming events posted yet.</p>
        <?php else: ?>
          <div class="event-list mb-3 reveal">
            <?php foreach ($events as $e): ?>
              <a class="event-row" href="<?= BASE_URL ?>/events.php#<?= h($e['slug']) ?>">
                <div class="event-date">
                  <span class="d"><?= date('d', strtotime($e['start_datetime'])) ?></span>
                  <span class="m"><?= date('M', strtotime($e['start_datetime'])) ?></span>
                </div>
                <div class="event-info">
                  <h3><?= h($e['title']) ?></h3>
                  <?php if ($e['location']): ?><p class="event-loc"><i class="bi bi-geo-alt"></i> <?= h($e['location']) ?></p><?php endif; ?>
                </div>
                <span class="event-arrow"><i class="bi bi-chevron-right"></i></span>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/events.php" class="see-all">View more events &rarr;</a>
      </div>
    </div>
  </div>
</section>

<!-- WHY CHOOSE ANU -->
<section class="py-5 why-choose-section">
  <div class="container">
    <div class="section-head reveal"><h2>Why Choose ANU</h2></div>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="card-anu reveal reveal-1"><div class="body">
          <div class="icon-badge"><i class="bi bi-award"></i></div>
          <span class="tag">Faith-Centred</span>
          <h3>Christian Values Foundation</h3>
          <p class="small text-muted mb-0">A Church of the Nazarene sponsored university, forming graduates with both academic excellence and Christian character.</p>
        </div></div>
      </div>
      <div class="col-md-4">
        <div class="card-anu reveal reveal-2"><div class="body">
          <div class="icon-badge"><i class="bi bi-mortarboard"></i></div>
          <span class="tag">Established 1994</span>
          <h3>Proven Track Record</h3>
          <p class="small text-muted mb-0">From a founding class of 62 to a network of 14,000+ alumni today, the first private university accredited under the University Act without external affiliation.</p>
        </div></div>
      </div>
      <div class="col-md-4">
        <div class="card-anu reveal reveal-3"><div class="body">
          <div class="icon-badge"><i class="bi bi-signpost-split"></i></div>
          <span class="tag">Many Pathways</span>
          <h3>A Path for Every Learner</h3>
          <p class="small text-muted mb-0">Five schools, Non-Degree, ANU Online and Short Courses, certificate through PhD, on campus or online.</p>
        </div></div>
      </div>
    </div>
  </div>
</section>

<!-- CSR TEASER -->
<section class="cta-band reveal">
  <div class="container d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
      <span class="tag mb-2 d-inline-block">Beyond the Classroom</span>
      <h2 class="h3 mb-2">Our Commitment to Community</h2>
      <p class="mb-0">From health outreach to environmental stewardship, see how ANU lives out its mission beyond campus.</p>
    </div>
    <a href="<?= BASE_URL ?>/csr.php" class="btn btn-apply btn-lg flex-shrink-0">Our CSR Impact</a>
  </div>
</section>

<!-- PARTNERS -->
<?php if ($partners): ?>
<section class="py-5 border-top">
  <div class="container text-center">
    <p class="mega-heading mb-4">Our Partners</p>
    <div class="d-flex flex-wrap justify-content-center align-items-center gap-5">
      <?php foreach ($partners as $p): ?>
        <?php if ($p['website_url']): ?>
          <a href="<?= h($p['website_url']) ?>" target="_blank" rel="noopener" class="partner-logo">
            <img src="<?= h(UPLOADS_URL . '/' . $p['logo']) ?>" alt="<?= h($p['name']) ?>">
          </a>
        <?php else: ?>
          <span class="partner-logo">
            <img src="<?= h(UPLOADS_URL . '/' . $p['logo']) ?>" alt="<?= h($p['name']) ?>">
          </span>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- NEWSLETTER -->
<section class="py-5 bg-cream border-top" id="subscribe">
  <div class="container container-narrow text-center">
    <h2>Stay up to date</h2>
    <p class="text-muted mb-4">Subscribe for admissions deadlines, events and university news.</p>
    <?php
      $__sub = $_GET['subscribed'] ?? '';
      $__subMsgs = [
        '1'       => ['success', 'Thank you! You are subscribed to ANU updates.'],
        'back'    => ['success', 'Welcome back! You are subscribed again.'],
        'already' => ['info',    'You are already subscribed with that email address.'],
        'invalid' => ['warning', 'Please enter a valid email address.'],
        'wait'    => ['warning', 'Too many attempts. Please try again in a few minutes.'],
        'error'   => ['warning', 'Your session expired. Please try again.'],
      ];
      if (isset($__subMsgs[$__sub])): ?>
      <div class="alert alert-<?= $__subMsgs[$__sub][0] ?> py-2" role="status"><?= h($__subMsgs[$__sub][1]) ?></div>
    <?php endif; ?>
    <form action="<?= BASE_URL ?>/subscribe.php" method="post" class="row g-2 justify-content-center">
      <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
      <div style="position:absolute;left:-9999px;" aria-hidden="true"><label>Leave this empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
      <div class="col-sm-4"><label class="visually-hidden" for="subName">Full name</label><input type="text" id="subName" name="full_name" class="form-control" placeholder="Full name" maxlength="150" autocomplete="name" required></div>
      <div class="col-sm-4"><label class="visually-hidden" for="subEmail">Email address</label><input type="email" id="subEmail" name="email" class="form-control" placeholder="Email address" maxlength="150" autocomplete="email" required></div>
      <div class="col-sm-auto"><button class="btn btn-apply w-100" type="submit">Subscribe</button></div>
      <div class="col-12"><p class="small text-muted mb-0">We email occasional news and deadlines. Unsubscribe any time. See our <a href="<?= BASE_URL ?>/privacy.php">Privacy Policy</a>.</p></div>
    </form>
  </div>
</section>

<!-- EXPLORE THE NETWORK (quick, visible access to every unit site) -->
<section class="py-5 bg-cream border-top">
  <div class="container">
    <div class="section-head">
      <h2>Explore Our Network</h2>
      <span class="see-all see-all-muted">One click to any school or unit site</span>
    </div>
    <div class="row g-3">
      <?php
      $networkLinks = [
          ['School of Law', setting('url_school_law')],
          ['School of Business', setting('url_school_business')],
          ['School of Science & Technology', setting('url_school_scitech')],
          ['School of Humanities & Social Sciences', setting('url_school_humanities')],
          ['School of Religion & Christian Ministry', setting('url_school_religion')],
          ['Non-Degree Programmes', setting('url_tvet')],
          ['ANU Online', setting('url_anu_online')],
          ['Centre for Short Courses & Professional Studies', setting('url_sepc')],
          ['Institute of Research', setting('url_research')],
          ['Admissions', setting('url_admissions')],
          ['Student Life', setting('url_student_life')],
          ['Chaplaincy', setting('url_chaplaincy')],
          ['Library', setting('url_library')],
          ['Alumni', setting('alumni_url')],
      ];
      foreach ($networkLinks as [$label, $url]): ?>
        <div class="col-6 col-md-4 col-lg-3">
          <a href="<?= h($url) ?>" class="network-tile">
            <span><?= h($label) ?></span>
            <i class="bi bi-arrow-right"></i>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
