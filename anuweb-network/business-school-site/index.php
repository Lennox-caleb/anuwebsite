<?php
$pageTitle = 'School of Business';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/school_slug.php';

$stmt = db()->prepare('SELECT * FROM schools WHERE slug = ? LIMIT 1');
$stmt->execute([school_slug_db()]);
$school = $stmt->fetch();

$progStmt = db()->prepare("SELECT p.* FROM programmes p WHERE p.school_id = (SELECT id FROM schools WHERE slug = ?) AND p.is_published = 1 ORDER BY p.level LIMIT 4");
$progStmt->execute([school_slug_db()]);
$programmes = $progStmt->fetchAll();

$heroStmt = db()->prepare("SELECT * FROM sliders WHERE unit_slug = ? AND is_published = 1 ORDER BY sort_order DESC, id DESC LIMIT 1");
$heroStmt->execute(['business']);

// Phase 5: this school's own upcoming events and latest news, if any --
// both simply don't render below when there's nothing real to show.
$upcomingEvents = [];
if (schema_has_column('events', 'unit_slug')) {
    $eventsStmt = db()->prepare("SELECT * FROM events WHERE unit_slug = ? AND is_published = 1 AND start_datetime >= NOW() ORDER BY start_datetime ASC LIMIT 3");
    $eventsStmt->execute([UNIT_SLUG]);
    $upcomingEvents = $eventsStmt->fetchAll();
}

$latestNews = [];
if (schema_has_column('news', 'unit_slug')) {
    $newsStmt = db()->prepare("SELECT * FROM news WHERE unit_slug = ? AND is_published = 1 ORDER BY published_at DESC LIMIT 3");
    $newsStmt->execute([UNIT_SLUG]);
    $latestNews = $newsStmt->fetchAll();
}
$hero = $heroStmt->fetch();

// Phase 5b: Dean's Welcome -- reuses the SAME "featured leader" staff
// mechanism as this school's own about.php ('dean-business'). Hidden entirely
// if nothing has been entered yet -- no invented name or message.
$leaderStmt = db()->prepare("SELECT * FROM staff WHERE unit_slug = 'dean-business' AND is_published = 1 ORDER BY sort_order LIMIT 1");
$leaderStmt->execute();
$leader = $leaderStmt->fetch();

// Phase 5b: programme category counts, same query as programmes.php's
// own chips -- never hardcoded, always reflects real published rows.
$allowedLevels = ['PhD','Masters','Undergraduate','Diploma','Certificate','Other'];
$countStmt = db()->prepare("SELECT p.level, COUNT(*) AS c FROM programmes p WHERE p.school_id = (SELECT id FROM schools WHERE slug = ?) AND p.is_published = 1 GROUP BY p.level");
$countStmt->execute([school_slug_db()]);
$levelCounts = [];
foreach ($countStmt->fetchAll() as $row) { $levelCounts[$row['level']] = (int) $row['c']; }
$totalProgrammeCount = array_sum($levelCounts);

// Phase 5b: Our Programmes -- admin-flagged only, hidden if none.
$featuredStmt = db()->prepare("SELECT p.* FROM programmes p WHERE p.school_id = (SELECT id FROM schools WHERE slug = ?) AND p.is_published = 1 ORDER BY FIELD(p.level,'PhD','Masters','Undergraduate','Diploma','Certificate','Other'), p.title");
$featuredStmt->execute([school_slug_db()]);
$featuredProgrammes = $featuredStmt->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<section class="biz-hero hero-fullscreen<?= hero_media_kind($hero) !== 'none' ? ' has-image' : '' ?>">
  <?= render_hero_media($hero, MAIN_SITE_URL . '/assets/uploads', 'hero-bg-image') ?>
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-7">
        <p class="lead"><?= $school ? h($school['summary']) : 'Business, commerce and management programmes for East Africa and beyond.' ?></p>
        <h1 class="mb-3">School of Business</h1>
        <div class="d-flex gap-3 mt-4 flex-wrap">
          <a href="<?= h(main_setting('apply_url')) ?>" class="btn btn-biz-cta btn-lg">Apply Now</a>
          <a href="<?= BASE_URL ?>/programmes.php" class="btn btn-biz-outline btn-lg">View Programmes</a>
        </div>
      </div>
      <div class="col-lg-5">
        <svg class="growth-bars" viewBox="0 0 320 220" width="100%">
          <rect x="20"  y="140" width="40" height="60" />
          <rect x="80"  y="100" width="40" height="100" />
          <rect x="140" y="60"  width="40" height="140" />
          <rect x="200" y="30"  width="40" height="170" />
          <rect x="260" y="10"  width="40" height="190" />
          <polyline points="40,140 100,100 160,60 220,30 280,10" fill="none" stroke="#000000" stroke-width="3"/>
          <circle cx="280" cy="10" r="6" fill="#000000"/>
        </svg>
      </div>
    </div>
  </div>
</section>

<?php
require_once __DIR__ . '/../main-site/includes/school_messages.php';
sm_render_home(['leader_slug' => 'dean-business', 'unit' => UNIT_SLUG, 'base' => BASE_URL, 'uploads' => UPLOADS_URL, 'css' => MAIN_SITE_URL . '/assets/css/school-messages.css']);
?>

<section class="biz-section">
  <div class="container">
    <div class="biz-section-head"><h2>Why Study Business at ANU</h2></div>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="biz-feature-card"><div class="icon-badge"><i class="bi bi-award"></i></div><h3>Regional business focus</h3><p class="small text-muted mb-0">Curriculum grounded in the realities of East African markets, trade and entrepreneurship.</p></div>
      </div>
      <div class="col-md-4">
        <div class="biz-feature-card"><div class="icon-badge"><i class="bi bi-people"></i></div><h3>Ethical leadership</h3><p class="small text-muted mb-0">Management education shaped by a values-driven, Christian foundation.</p></div>
      </div>
      <div class="col-md-4">
        <div class="biz-feature-card"><div class="icon-badge"><i class="bi bi-globe2"></i></div><h3>Career-ready</h3><p class="small text-muted mb-0">Programmes spanning commerce, procurement, international business and IT-business hybrids.</p></div>
      </div>
    </div>
  </div>
</section>

<section class="biz-section">
  <div class="container">
    <div class="biz-section-head"><h2>Programmes</h2></div>

    <?php if ($totalProgrammeCount > 0): ?>
    <div class="biz-category-bar mb-4">
      <a href="<?= BASE_URL ?>/programmes.php" class="biz-category-chip active">All <span>(<?= $totalProgrammeCount ?>)</span></a>
      <?php foreach ($allowedLevels as $lvl): if (empty($levelCounts[$lvl])) continue; ?>
        <a href="<?= BASE_URL ?>/programmes.php?level=<?= urlencode($lvl) ?>" class="biz-category-chip"><?= h($lvl === 'Other' ? 'Short Courses' : $lvl) ?> <span>(<?= $levelCounts[$lvl] ?>)</span></a>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
      <p class="text-muted">Programme listings for this School will appear here soon.</p>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>/programmes.php" method="get" class="biz-programme-search mb-4">
      <input type="text" name="q" class="form-control" placeholder="Search programmes (e.g. a name or keyword)&hellip;" value="">
      <button type="submit" class="btn btn-biz-cta">Search</button>
    </form>

    <?php if ($featuredProgrammes): ?>
      <div class="biz-section-head mt-5"><h2>Our Programmes</h2></div>
      <div class="row g-4">
        <?php foreach ($featuredProgrammes as $fp): ?>
          <div class="col-md-4">
            <div class="biz-card h-100">
              <?php if ($fp['image']): ?><img class="mb-2" style="width:100%;aspect-ratio:16/9;object-fit:cover;border-radius:10px" src="<?= h(MAIN_SITE_URL . '/assets/uploads/' . $fp['image']) ?>" alt="<?= h($fp['title']) ?>" loading="lazy"><?php endif; ?>
              <span class="level"><?= h($fp['level'] === 'Other' ? 'Short course' : $fp['level']) ?></span>
              <h3 class="mt-1"><?= h($fp['title']) ?></h3>
              <a href="<?= h(MAIN_SITE_URL) ?>/program-single.php?slug=<?= h($fp['slug']) ?>" class="text-decoration-none">Details &rarr;</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>



<?php if ($upcomingEvents): ?>
<section class="biz-section">
  <div class="container">
    <div class="biz-section-head"><h2>Calendar</h2></div>
    <div class="row g-4">
      <?php foreach ($upcomingEvents as $e): ?>
        <div class="col-md-4">
          <div class="biz-card h-100">
            <span class="text-muted small"><?= date('d M Y', strtotime($e['start_datetime'])) ?></span>
            <h3 class="mt-1"><?= h($e['title']) ?></h3>
            <?php if ($e['location']): ?><p class="small text-muted mb-0"><i class="bi bi-geo-alt"></i> <?= h($e['location']) ?></p><?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($latestNews): ?>
<section class="biz-section">
  <div class="container">
    <div class="biz-section-head"><h2>News &amp; Updates</h2><a href="<?= BASE_URL ?>/news.php" class="text-decoration-none">View all &rarr;</a></div>
    <div class="row g-4">
      <?php foreach ($latestNews as $n): ?>
        <div class="col-md-4">
          <div class="biz-card h-100">
            <span class="text-muted small"><?= date('d M Y', strtotime($n['published_at'])) ?></span>
            <h3 class="mt-1"><?= h($n['title']) ?></h3>
            <?php if ($n['excerpt']): ?><p class="small text-muted mb-0"><?= h(excerpt($n['excerpt'], 100)) ?></p><?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="biz-section apply-band">
  <div class="container text-center">
    <h2 class="mb-3">Start Your Career</h2>
    <p class="text-muted mb-4">Start your application to the School of Business today.</p>
    <a href="<?= h(main_setting('apply_url')) ?>" target="_blank" rel="noopener" class="btn btn-biz-cta btn-lg">Apply Now</a>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
