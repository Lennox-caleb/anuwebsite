<?php
header('Cache-Control: no-store, no-cache, must-revalidate');
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/tracker.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/programs_lib.php';

$db       = getDB();
$settings = getAllSettings();
$applyUrl = $settings['apply_url'] ?? 'https://records.anu.ac.ke/wStartUpCreateUserNamePassword.jsp';

$programs = [];
try {
    $programs = prog_unique($db->query("SELECT * FROM programs WHERE is_active=1 ORDER BY school, FIELD(level,'PhD','Masters','Undergraduate','Diploma','Certificate','Executive'), name")->fetchAll());
} catch (Exception $e) {}

// The five schools, same names, summaries and pictures as the university site.
$schoolCfg = [
  'Law'        => ['bi-shield-fill',   'School of Law',                             'Legal education grounded in justice, ethics and advocacy.',                    'law',        '/anuweb-network/law-school-site/'],
  'Business'   => ['bi-briefcase-fill','School of Business',                        'Business, commerce and management programmes for East Africa and beyond.',     'business',   '/anuweb-network/business-school-site/'],
  'Science'    => ['bi-cpu-fill',      'School of Science and Technology',          'Computer science, IT and applied sciences.',                                   'science',    '/anuweb-network/scitech-school-site/'],
  'Humanities' => ['bi-people-fill',   'School of Humanities and Social Sciences',  'Peace, counseling, community development, media and education.',               'humanities', '/anuweb-network/humanities-school-site/'],
  'Religion'   => ['bi-book-fill',     'School of Religion and Christian Ministry', 'Theology and ministry formation rooted in the Wesleyan-holiness tradition.',   'religion',   '/anuweb-network/religion-school-site/'],
];
$levelIcons = ['PhD'=>'bi-award','Masters'=>'bi-mortarboard','Undergraduate'=>'bi-book','Diploma'=>'bi-journal-bookmark','Certificate'=>'bi-patch-check','Executive'=>'bi-star'];
$allowedLevels = ['PhD','Masters','Undergraduate','Diploma','Certificate','Executive'];

// filters (links, so they work without JavaScript and can be shared)
$fSchool = $_GET['school'] ?? ''; if (!isset($schoolCfg[$fSchool])) $fSchool = '';
$fLevel  = $_GET['level']  ?? ''; if (!in_array($fLevel, $allowedLevels, true)) $fLevel = '';
$fQ      = trim((string)($_GET['q'] ?? ''));
$counts  = array_fill_keys(array_keys($schoolCfg), 0);
foreach ($programs as $p) { if (isset($counts[$p['school']])) $counts[$p['school']]++; }
$presentLevels = array_values(array_filter($allowedLevels, fn($l) => count(array_filter($programs, fn($p) => $p['level'] === $l))));

$list = array_values(array_filter($programs, function ($p) use ($fSchool, $fLevel, $fQ) {
    if ($fSchool !== '' && $p['school'] !== $fSchool) return false;
    if ($fLevel  !== '' && $p['level']  !== $fLevel)  return false;
    if ($fQ !== '' && stripos($p['name'] . ' ' . ($p['description'] ?? ''), $fQ) === false) return false;
    return true;
}));
$perPage = 12; $total = count($list); $pages = max(1, (int)ceil($total / $perPage));
$page = min($pages, max(1, (int)($_GET['page'] ?? 1)));
$shown = array_slice($list, ($page - 1) * $perPage, $perPage);
function pg_qs(array $o): string {
    $q = array_merge(['school' => $_GET['school'] ?? '', 'level' => $_GET['level'] ?? '', 'q' => $_GET['q'] ?? '', 'page' => ''], $o);
    $q = array_filter($q, fn($v) => $v !== '' && $v !== null);
    return '/anu/programs.php' . ($q ? '?' . http_build_query($q) : '');
}

$heroSlides = getSlides('programs');
$heroData   = !empty($heroSlides) ? $heroSlides[0] : getHero('programs');
$heroBg     = !empty($heroData['bg_image']) ? imgUrl($heroData['bg_image']) : '';
$heroTitle  = !empty($heroData['title'])    ? $heroData['title']    : 'Our Programmes';
$heroSub    = !empty($heroData['subtitle']) ? $heroData['subtitle'] : count($programs) . ' programmes across ' . count(array_filter($counts)) . ' schools, 100% online.';

trackPage('programs', 'Programs');
renderHead('Programs'); renderNavbar('programs');
?>

<div class="pp-hero" <?= $heroBg ? "style=\"background-image:url('" . e($heroBg) . "')\"" : '' ?>>
  <div class="pp-hero-overlay"></div>
  <div class="container pp-hero-content">
    <div class="pp-hero-text" data-aos="fade-up">
      <span class="tm-eyebrow">Academic Catalogue</span>
      <h1 class="pp-hero-title"><?= e($heroTitle) ?></h1>
      <p class="pp-hero-sub"><?= e($heroSub) ?></p>
    </div>
  </div>
</div>

<!-- schools: one horizontal row, same pictures as the university site -->
<section class="sec pb-0">
  <div class="container">
    <div class="or-head"><h2>Our Five Schools</h2>
      <?php if ($fSchool !== ''): ?><a href="<?= e(pg_qs(['school' => '', 'page' => ''])) ?>#programmes">Show all schools &rarr;</a><?php endif; ?></div>
    <div class="sc-row">
      <?php foreach ($schoolCfg as $key => [$ic, $nm, $ds, $img, $site]): ?>
      <a class="sc-card <?= $fSchool === $key ? 'active' : '' ?>" href="<?= e(pg_qs(['school' => $fSchool === $key ? '' : $key, 'page' => ''])) ?>#programmes" aria-pressed="<?= $fSchool === $key ? 'true' : 'false' ?>">
        <span class="sc-media"><img src="/anu/assets/images/schools/<?= e($img) ?>.jpg" alt=""><span class="sc-count"><?= (int)$counts[$key] ?> <?= $counts[$key] === 1 ? 'programme' : 'programmes' ?></span></span>
        <span class="sc-body"><b><?= e($nm) ?></b><span><?= e($ds) ?></span></span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="sec" id="programmes">
  <div class="container">
    <form method="get" action="/anu/programs.php#programmes" class="pc-filter">
      <?php if ($fSchool !== ''): ?><input type="hidden" name="school" value="<?= e($fSchool) ?>"><?php endif; ?>
      <?php if ($fLevel !== ''): ?><input type="hidden" name="level" value="<?= e($fLevel) ?>"><?php endif; ?>
      <input type="search" name="q" value="<?= e($fQ) ?>" class="form-control" placeholder="Search programmes, e.g. Business Administration" aria-label="Search programmes">
      <button type="submit" class="btn-hero-solid"><i class="bi bi-search"></i> Search</button>
    </form>
    <div class="pc-levels">
      <a href="<?= e(pg_qs(['level' => '', 'page' => ''])) ?>#programmes" class="<?= $fLevel === '' ? 'on' : '' ?>">All Levels</a>
      <?php foreach ($presentLevels as $lv): ?>
        <a href="<?= e(pg_qs(['level' => $lv, 'page' => ''])) ?>#programmes" class="<?= $fLevel === $lv ? 'on' : '' ?>"><i class="bi <?= $levelIcons[$lv] ?? 'bi-mortarboard' ?>"></i> <?= e($lv) ?></a>
      <?php endforeach; ?>
    </div>
    <p class="text-muted small mb-4"><?= $total ?> programme<?= $total === 1 ? '' : 's' ?> found<?= $fSchool !== '' ? ' in ' . e($schoolCfg[$fSchool][1]) : '' ?>.</p>

    <div class="row g-4">
      <?php foreach ($shown as $p): ?>
      <div class="col-md-6 col-lg-4">
        <a class="pc-card" href="<?= e($applyUrl) ?>" target="_blank" rel="noopener">
          <span class="pc-thumb"><i class="bi <?= $levelIcons[$p['level']] ?? 'bi-mortarboard' ?>"></i></span>
          <span class="pc-body">
            <span class="pc-tag"><?= e($p['level']) ?></span>
            <h3><?= e($p['name']) ?></h3>
            <span class="pc-school"><i class="bi bi-bank"></i> <?= e($schoolCfg[$p['school']][1] ?? $p['school']) ?></span>
            <?php if (!empty($p['duration'])): ?><span class="pc-meta"><i class="bi bi-clock"></i> <?= e($p['duration']) ?> &middot; Online</span><?php endif; ?>
            <span class="pc-go">Apply now <i class="bi bi-arrow-right"></i></span>
          </span>
        </a>
      </div>
      <?php endforeach; ?>
      <?php if (!$shown): ?>
        <div class="col-12 text-center py-5"><i class="bi bi-mortarboard" style="font-size:2.2rem;color:#ccc"></i>
          <p class="text-muted mt-3">No programmes match this filter. <a href="/anu/programs.php#programmes">Clear filters</a></p></div>
      <?php endif; ?>
    </div>

    <?php if ($pages > 1): ?>
    <nav class="d-flex justify-content-center mt-5" aria-label="Programme pages"><ul class="pagination mb-0">
      <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="<?= e(pg_qs(['page' => max(1, $page - 1)])) ?>#programmes">&laquo; Prev</a></li>
      <?php for ($n = 1; $n <= $pages; $n++): ?><li class="page-item <?= $n === $page ? 'active' : '' ?>"><a class="page-link" href="<?= e(pg_qs(['page' => $n])) ?>#programmes"><?= $n ?></a></li><?php endfor; ?>
      <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>"><a class="page-link" href="<?= e(pg_qs(['page' => min($pages, $page + 1)])) ?>#programmes">Next &raquo;</a></li>
    </ul></nav>
    <?php endif; ?>
  </div>
</section>

<?php renderFooter(); ?>
