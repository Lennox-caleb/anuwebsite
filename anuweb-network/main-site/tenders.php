<?php
$pageTitle = 'Tenders & Procurement';
$pageDescription = "Current, closed and awarded tenders and procurement notices from Africa Nazarene University.";
require_once __DIR__ . '/includes/functions.php';

$rows = db()->query("SELECT * FROM tenders WHERE is_published = 1 ORDER BY FIELD(status,'open','awarded','closed'), closing_date DESC, sort_order")->fetchAll();
$open = array_filter($rows, fn($t) => $t['status'] === 'open');
$other = array_filter($rows, fn($t) => $t['status'] !== 'open');

$icons = [
    'pdf' => 'bi-file-earmark-pdf', 'doc' => 'bi-file-earmark-word', 'docx' => 'bi-file-earmark-word',
    'xls' => 'bi-file-earmark-excel', 'xlsx' => 'bi-file-earmark-excel',
];
$statusMeta = [
    'open'    => ['label' => 'Open',    'class' => 'tender-badge-open'],
    'closed'  => ['label' => 'Closed',  'class' => 'tender-badge-closed'],
    'awarded' => ['label' => 'Awarded', 'class' => 'tender-badge-awarded'],
];

require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container container-narrow text-center">
    <span class="hero-eyebrow d-inline-block mb-2" style="color: var(--anu-red);">Procurement</span>
    <h1>Tenders &amp; Procurement</h1>
    <p class="text-muted mb-0">Current tender opportunities, supplier notices, and procurement announcements
    from Africa Nazarene University.</p>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <?php if (!$rows): ?>
      <div class="text-center py-5">
        <i class="bi bi-file-earmark-ruled" style="font-size:2.2rem; color: var(--anu-line);"></i>
        <p class="text-muted mt-3">No tenders are open at this time. Please check back later.</p>
      </div>
    <?php endif; ?>

    <?php if ($open): ?>
    <div class="section-head reveal"><h2>Open Tenders</h2></div>
    <div class="row g-4 mb-4">
      <?php foreach ($open as $t):
        $ext = $t['document'] ? strtolower(pathinfo($t['document'], PATHINFO_EXTENSION)) : null;
        $icon = $icons[$ext] ?? 'bi-file-earmark-text';
        $meta = $statusMeta[$t['status']];
      ?>
        <div class="col-md-6 col-lg-4">
          <div class="tender-card reveal">
            <div class="tender-card-top">
              <span class="tender-badge <?= $meta['class'] ?>"><?= h($meta['label']) ?></span>
              <?php if ($t['reference_no']): ?><span class="tender-ref"><?= h($t['reference_no']) ?></span><?php endif; ?>
            </div>
            <h3 class="h5 mb-1"><?= h($t['title']) ?></h3>
            <?php if ($t['category']): ?><p class="tender-category mb-2"><?= h($t['category']) ?></p><?php endif; ?>
            <?php if ($t['summary']): ?><p class="small text-muted mb-3"><?= h(excerpt($t['summary'], 140)) ?></p><?php endif; ?>
            <?php if ($t['closing_date']): ?>
              <p class="tender-closing"><i class="bi bi-calendar-x me-1"></i> Closes <?= h(date('d M Y', strtotime($t['closing_date']))) ?></p>
            <?php endif; ?>
            <?php if ($t['document']): ?>
              <a href="<?= h(UPLOADS_URL . '/' . $t['document']) ?>" class="btn btn-outline-anu btn-sm mt-2" download>
                <i class="bi <?= $icon ?> me-1"></i> Download Document
              </a>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ($other): ?>
    <div class="section-head mt-5 reveal"><h2>Closed &amp; Awarded Tenders</h2></div>
    <div class="card-anu reveal">
      <div class="body p-0">
        <table class="table table-anu mb-0">
          <thead><tr><th>Title</th><th>Reference</th><th>Status</th><th>Closed</th><th class="text-end">Document</th></tr></thead>
          <tbody>
            <?php foreach ($other as $t): $meta = $statusMeta[$t['status']]; ?>
              <tr>
                <td><?= h($t['title']) ?></td>
                <td><?= h($t['reference_no']) ?></td>
                <td><span class="tender-badge <?= $meta['class'] ?>"><?= h($meta['label']) ?></span></td>
                <td><?= $t['closing_date'] ? h(date('d M Y', strtotime($t['closing_date']))) : '-' ?></td>
                <td class="text-end">
                  <?php if ($t['document']): ?>
                    <a href="<?= h(UPLOADS_URL . '/' . $t['document']) ?>" download>Download</a>
                  <?php else: ?>, <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
