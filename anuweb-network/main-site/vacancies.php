<?php
$pageTitle = 'Vacancies';
$pageDescription = 'Current job openings at Africa Nazarene University.';
require_once __DIR__ . '/includes/functions.php';
$all = db()->query('SELECT * FROM vacancies WHERE is_published = 1 ORDER BY closing_date ASC, created_at DESC')->fetchAll();
$rows = array_values(array_filter($all, fn($v) => !$v['closing_date'] || $v['closing_date'] >= date('Y-m-d')));
$closedRows = array_reverse(array_values(array_filter($all, fn($v) => $v['closing_date'] && $v['closing_date'] < date('Y-m-d'))));
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container container-narrow text-center">
    <h1>Vacancies</h1>
    <p class="text-muted mb-0">Current job openings at Africa Nazarene University. ANU is an equal opportunity employer.</p>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <?php if (!$rows): ?>
      <div class="text-center py-5">
        <i class="bi bi-briefcase" style="font-size:2.2rem; color: var(--anu-line);"></i>
        <p class="text-muted mt-3">No vacancies are open at this time. Check back soon, or follow our <a href="<?= BASE_URL ?>/news.php">news page</a> for announcements.</p>
      </div>
    <?php endif; ?>

    <div class="row g-4">
      <?php foreach ($rows as $i => $v):
        $closingSoon = $v['closing_date'] && strtotime($v['closing_date']) <= strtotime('+7 days') && strtotime($v['closing_date']) >= strtotime('today');
      ?>
        <div class="col-md-6">
          <div class="card-anu reveal reveal-<?= ($i % 4) + 1 ?> h-100">
            <div class="body">
              <div class="icon-badge"><i class="bi bi-briefcase"></i></div>
              <span class="tag"><?= h($v['employment_type']) ?></span>
              <h3><a class="text-decoration-none" href="<?= BASE_URL ?>/vacancy-single.php?slug=<?= h($v['slug']) ?>"><?= h($v['title']) ?></a></h3>
              <?php if ($v['department']): ?><p class="small text-muted mb-1"><i class="bi bi-building"></i> <?= h($v['department']) ?></p><?php endif; ?>
              <?php if ($v['closing_date']): ?>
                <p class="small text-muted mb-0">
                  <i class="bi bi-calendar-event"></i> Closes <?= format_date($v['closing_date']) ?>
                  <?php if ($closingSoon): ?><span class="badge-closing-soon">Closing Soon</span><?php endif; ?>
                </p>
              <?php endif; ?>
              <a class="btn btn-outline-anu btn-sm mt-3" href="<?= BASE_URL ?>/vacancy-single.php?slug=<?= h($v['slug']) ?>">View Details &rarr;</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if ($closedRows): ?>
      <h2 class="h4 mt-5 mb-3">Closed vacancies</h2>
      <p class="small text-muted">These positions are no longer accepting applications. They are kept here for reference.</p>
      <div class="row g-4">
        <?php foreach ($closedRows as $v): ?>
          <div class="col-md-6">
            <div class="card-anu h-100" style="opacity:.8">
              <div class="body">
                <span class="tag"><?= h($v['employment_type']) ?></span>
                <h3><a class="text-decoration-none" href="<?= BASE_URL ?>/vacancy-single.php?slug=<?= h($v['slug']) ?>"><?= h($v['title']) ?></a></h3>
                <?php if ($v['department']): ?><p class="small text-muted mb-1"><i class="bi bi-building"></i> <?= h($v['department']) ?></p><?php endif; ?>
                <p class="small text-muted mb-0"><i class="bi bi-calendar-event"></i> Closed <?= format_date($v['closing_date']) ?></p>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
