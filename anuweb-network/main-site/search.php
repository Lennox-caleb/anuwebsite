<?php
$pageTitle = 'Search';
$pageDescription = 'Search programmes, schools, news, events, resources, vacancies and people at Africa Nazarene University.';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/search_logic.php';

$q = trim($_GET['q'] ?? '');
$results = anu_search($q, 12);
$totalResults = array_sum(array_map('count', $results));

require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container container-narrow">
    <h1 class="text-center mb-3">Search ANU</h1>
    <form method="get" class="mb-2">
      <div class="input-group input-group-lg">
        <input type="text" name="q" class="form-control" placeholder="Search programmes, news, schools, events, resources, people&hellip;" value="<?= h($q) ?>" autofocus>
        <button class="btn btn-apply" type="submit"><i class="bi bi-search"></i> Search</button>
      </div>
    </form>
    <?php if ($q === ''): ?>
      <p class="text-center text-muted small mb-0">
        Try: <a href="?q=admissions">admissions</a> &middot; <a href="?q=business">business</a> &middot;
        <a href="?q=contact">contact</a> &middot; <a href="?q=csr">CSR</a> &middot; <a href="?q=privacy">privacy</a>
      </p>
    <?php endif; ?>
  </div>
</section>

<section class="py-5">
  <div class="container container-narrow">
    <?php if ($q !== ''): ?>
      <p class="text-muted"><?= $totalResults ?> result<?= $totalResults === 1 ? '' : 's' ?> for &ldquo;<?= h($q) ?>&rdquo;</p>

      <?php
      $sections = [
        'pages'      => ['label' => 'Pages',      'icon' => 'bi-file-text'],
        'schools'    => ['label' => 'Schools',     'icon' => 'bi-bank'],
        'programmes' => ['label' => 'Programmes',  'icon' => 'bi-mortarboard'],
        'people'     => ['label' => 'People',      'icon' => 'bi-person-badge'],
        'news'       => ['label' => 'News',        'icon' => 'bi-newspaper'],
        'events'     => ['label' => 'Events',      'icon' => 'bi-calendar-event'],
        'resources'  => ['label' => 'Resources',   'icon' => 'bi-file-earmark-text'],
        'vacancies'  => ['label' => 'Vacancies',   'icon' => 'bi-briefcase'],
      ];
      foreach ($sections as $key => $meta):
        if (!$results[$key]) continue;
      ?>
        <h2 class="h5 mt-4 d-flex align-items-center justify-content-between">
          <span><?= h($meta['label']) ?></span>
          <span class="search-category-count"><?= count($results[$key]) ?> found</span>
        </h2>

        <?php if ($key === 'pages'): foreach ($results[$key] as $r): ?>
          <a class="search-result-card" href="<?= BASE_URL ?>/<?= h($r['url']) ?>">
            <span class="result-icon"><i class="bi <?= h($r['icon']) ?>"></i></span>
            <div><span class="result-type">Page</span><h3><?= h($r['title']) ?></h3><p><?= h($r['desc']) ?></p></div>
          </a>
        <?php endforeach; endif; ?>

        <?php if ($key === 'schools'): foreach ($results[$key] as $r): ?>
          <a class="search-result-card" href="<?= BASE_URL ?>/schools/<?= h($r['slug']) ?>">
            <span class="result-icon"><i class="bi bi-bank"></i></span>
            <div><span class="result-type">School</span><h3><?= h($r['name']) ?></h3><?php if ($r['summary']): ?><p><?= h(excerpt($r['summary'], 120)) ?></p><?php endif; ?></div>
          </a>
        <?php endforeach; endif; ?>

        <?php if ($key === 'programmes'): foreach ($results[$key] as $r): ?>
          <a class="search-result-card" href="<?= BASE_URL ?>/program-single.php?slug=<?= h($r['slug']) ?>">
            <span class="result-icon"><i class="bi bi-mortarboard"></i></span>
            <div><span class="result-type"><?= h($r['level']) ?></span><h3><?= h($r['title']) ?></h3><?php if ($r['overview']): ?><p><?= h(excerpt($r['overview'], 120)) ?></p><?php endif; ?></div>
          </a>
        <?php endforeach; endif; ?>

        <?php if ($key === 'people'): foreach ($results[$key] as $r): ?>
          <div class="search-result-card">
            <span class="result-icon"><i class="bi bi-person-badge"></i></span>
            <div><span class="result-type">Person</span><h3><?= h($r['full_name']) ?></h3><?php $t = public_text($r['title']); if ($t): ?><p><?= h($t) ?></p><?php endif; ?></div>
          </div>
        <?php endforeach; endif; ?>

        <?php if ($key === 'news'): foreach ($results[$key] as $r): ?>
          <a class="search-result-card" href="<?= BASE_URL ?>/news-single.php?slug=<?= h($r['slug']) ?>">
            <span class="result-icon"><i class="bi bi-newspaper"></i></span>
            <div><span class="result-type"><?= format_date($r['published_at']) ?></span><h3><?= h($r['title']) ?></h3><?php if ($r['excerpt']): ?><p><?= h(excerpt($r['excerpt'], 120)) ?></p><?php endif; ?></div>
          </a>
        <?php endforeach; endif; ?>

        <?php if ($key === 'events'): foreach ($results[$key] as $r): ?>
          <a class="search-result-card" href="<?= BASE_URL ?>/events.php#<?= h($r['slug']) ?>">
            <span class="result-icon"><i class="bi bi-calendar-event"></i></span>
            <div><span class="result-type"><?= date('d M Y', strtotime($r['start_datetime'])) ?></span><h3><?= h($r['title']) ?></h3><?php if ($r['location']): ?><p><i class="bi bi-geo-alt"></i> <?= h($r['location']) ?></p><?php endif; ?></div>
          </a>
        <?php endforeach; endif; ?>

        <?php if ($key === 'resources'): foreach ($results[$key] as $r): ?>
          <a class="search-result-card" href="<?= BASE_URL ?>/resources.php">
            <span class="result-icon"><i class="bi bi-file-earmark-text"></i></span>
            <div><span class="result-type"><?= h($r['category']) ?></span><h3><?= h($r['title']) ?></h3><?php if ($r['description']): ?><p><?= h(excerpt($r['description'], 120)) ?></p><?php endif; ?></div>
          </a>
        <?php endforeach; endif; ?>

        <?php if ($key === 'vacancies'): foreach ($results[$key] as $r): ?>
          <a class="search-result-card" href="<?= BASE_URL ?>/vacancy-single.php?slug=<?= h($r['slug']) ?>">
            <span class="result-icon"><i class="bi bi-briefcase"></i></span>
            <div><span class="result-type"><?= h($r['department'] ?: 'Vacancy') ?></span><h3><?= h($r['title']) ?></h3><?php if ($r['closing_date']): ?><p>Closes <?= format_date($r['closing_date']) ?></p><?php endif; ?></div>
          </a>
        <?php endforeach; endif; ?>
      <?php endforeach; ?>

      <?php if ($totalResults === 0): ?>
        <div class="text-center py-5">
          <i class="bi bi-search" style="font-size:2.4rem; color: var(--anu-line);"></i>
          <p class="text-muted mt-3 mb-1">No results for &ldquo;<?= h($q) ?>&rdquo;.</p>
          <p class="text-muted small">Try a shorter or differently spelled term, or browse
          <a href="<?= BASE_URL ?>/programs.php">all programmes</a> or
          <a href="<?= BASE_URL ?>/sitemap.php">the sitemap</a>.</p>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
