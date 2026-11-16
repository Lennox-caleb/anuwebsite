<?php
$pageTitle = 'School of Religion & Christian Ministry';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/school_slug.php';

$stmt = db()->prepare('SELECT * FROM schools WHERE slug = ? LIMIT 1');
$stmt->execute([school_slug_db()]);
$school = $stmt->fetch();

$progStmt = db()->prepare("SELECT p.* FROM programmes p WHERE p.school_id = (SELECT id FROM schools WHERE slug = ?) AND p.is_published = 1 ORDER BY p.level LIMIT 4");
$progStmt->execute([school_slug_db()]);
$programmes = $progStmt->fetchAll();

$heroStmt = db()->prepare("SELECT * FROM sliders WHERE unit_slug = ? AND is_published = 1 ORDER BY sort_order DESC, id DESC LIMIT 1");
$heroStmt->execute(['religion']);

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
// mechanism as this school's own about.php ('dean-religion'). Hidden entirely
// if nothing has been entered yet -- no invented name or message.
$leaderStmt = db()->prepare("SELECT * FROM staff WHERE unit_slug = 'dean-religion' AND is_published = 1 ORDER BY sort_order LIMIT 1");
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
<section class="rel-hero hero-fullscreen<?= hero_media_kind($hero) !== 'none' ? ' has-image' : '' ?>">
  <?= render_hero_media($hero, MAIN_SITE_URL . '/assets/uploads', 'hero-bg-image') ?>
  <div class="container">
    <p class="lead"><?= $school ? h($school['summary']) : 'Theology and ministry formation rooted in the Wesleyan-holiness tradition.' ?></p>
    <h1 class="mb-3">School of Religion &amp; Christian Ministry</h1>
    <div class="d-flex gap-3 mt-4 flex-wrap justify-content-center">
      <a href="<?= h(main_setting('apply_url')) ?>" class="btn btn-rel-cta btn-lg">Apply Now</a>
      <a href="<?= BASE_URL ?>/programmes.php" class="btn btn-rel-outline on-dark btn-lg">View Programmes</a>
    </div>
  </div>
</section>

<?php
require_once __DIR__ . '/../main-site/includes/school_messages.php';
sm_render_home(['leader_slug' => 'dean-religion', 'unit' => UNIT_SLUG, 'base' => BASE_URL, 'uploads' => UPLOADS_URL, 'css' => MAIN_SITE_URL . '/assets/css/school-messages.css']);
?>

<section class="rel-section">
  <div class="container">
    <div class="rel-section-head"><span class="eyebrow-label">Our calling</span><h2>Formation for Ministry &amp; Scholarship</h2></div>
    <div class="row g-4">
      <div class="col-md-4"><div class="rel-feature-card"><div class="icon-badge"><i class="bi bi-award"></i></div><h3>Holiness tradition</h3><p class="small text-muted mb-0">Theological training rooted in the Wesleyan-holiness heritage of the Church of the Nazarene.</p></div></div>
      <div class="col-md-4"><div class="rel-feature-card"><div class="icon-badge"><i class="bi bi-people"></i></div><h3>Pastoral &amp; ministry formation</h3><p class="small text-muted mb-0">Preparation for pastoral leadership, chaplaincy and Christian ministry.</p></div></div>
      <div class="col-md-4"><div class="rel-feature-card"><div class="icon-badge"><i class="bi bi-globe2"></i></div><h3>Scholarly depth</h3><p class="small text-muted mb-0">From undergraduate theology to Doctor of Ministry and PhD in Religion.</p></div></div>
    </div>
  </div>
</section>

<section class="rel-section">
  <div class="container">
    <div class="rel-section-head"><h2>Programmes</h2></div>

    <?php if ($totalProgrammeCount > 0): ?>
    <div class="rel-category-bar mb-4">
      <a href="<?= BASE_URL ?>/programmes.php" class="rel-category-chip active">All <span>(<?= $totalProgrammeCount ?>)</span></a>
      <?php foreach ($allowedLevels as $lvl): if (empty($levelCounts[$lvl])) continue; ?>
        <a href="<?= BASE_URL ?>/programmes.php?level=<?= urlencode($lvl) ?>" class="rel-category-chip"><?= h($lvl === 'Other' ? 'Short Courses' : $lvl) ?> <span>(<?= $levelCounts[$lvl] ?>)</span></a>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
      <p class="text-muted">Programme listings for this School will appear here soon.</p>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>/programmes.php" method="get" class="rel-programme-search mb-4">
      <input type="text" name="q" class="form-control" placeholder="Search programmes (e.g. a name or keyword)&hellip;" value="">
      <button type="submit" class="btn btn-rel-cta">Search</button>
    </form>

    <?php if ($featuredProgrammes): ?>
      <div class="rel-section-head mt-5"><h2>Our Programmes</h2></div>
      <div class="row g-4">
        <?php foreach ($featuredProgrammes as $fp): ?>
          <div class="col-md-4">
            <div class="rel-card h-100">
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
<section class="rel-section">
  <div class="container">
    <div class="rel-section-head"><h2>Calendar</h2></div>
    <div class="row g-4">
      <?php foreach ($upcomingEvents as $e): ?>
        <div class="col-md-4">
          <div class="rel-card h-100">
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
<section class="rel-section">
  <div class="container">
    <div class="rel-section-head"><h2>News &amp; Updates</h2><a href="<?= BASE_URL ?>/news.php" class="text-decoration-none">View all &rarr;</a></div>
    <div class="row g-4">
      <?php foreach ($latestNews as $n): ?>
        <div class="col-md-4">
          <div class="rel-card h-100">
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

<section class="rel-section apply-band">
  <div class="container text-center">
    <h2 class="mb-3">Start Your Career</h2>
    <p class="text-muted mb-4">Start your application to the School of Religion &amp; Christian Ministry today.</p>
    <a href="<?= h(main_setting('apply_url')) ?>" target="_blank" rel="noopener" class="btn btn-rel-cta btn-lg">Apply Now</a>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
