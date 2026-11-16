<?php
$pageTitle = 'Resources Library';
require_once __DIR__ . '/includes/functions.php';

$rows = db()->query("SELECT * FROM resources WHERE is_published = 1 ORDER BY category, title")->fetchAll();
$shelves = [];
foreach ($rows as $r) {
    $shelves[$r['category']][] = $r;
}
$order = ['Policies & Guidelines','Handbooks & Forms','Past Papers','Financial Aid Forms','Publications','Orientation','Other'];

$allMagazines = db()->query("SELECT * FROM publications_gallery WHERE is_published = 1 ORDER BY published_date DESC")->fetchAll();
$aspireIssues = array_slice(array_values(array_filter($allMagazines, fn($m) => $m['publication_type'] === 'ASPIRE')), 0, 6);
$voiceIssues  = array_slice(array_values(array_filter($allMagazines, fn($m) => $m['publication_type'] === 'ANU Voice')), 0, 6);
$gradIssues   = array_slice(array_values(array_filter($allMagazines, fn($m) => $m['publication_type'] === 'Graduation List')), 0, 6);
$otherIssues  = array_values(array_filter($allMagazines, fn($m) => !in_array($m['publication_type'], ['ASPIRE','ANU Voice','Graduation List'], true)));

$icons = [
    'pdf' => 'bi-file-earmark-pdf',
    'doc' => 'bi-file-earmark-word', 'docx' => 'bi-file-earmark-word',
    'xls' => 'bi-file-earmark-excel', 'xlsx' => 'bi-file-earmark-excel',
];

require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container container-narrow text-center">
    <h1>Resources Library</h1>
    <p class="text-muted mb-0">Official policies, handbooks, forms and past papers, organised on shelves by category.</p>
  </div>
</section>

<section class="py-4">
  <div class="container">
    <a href="<?= BASE_URL ?>/tenders.php" class="resources-callout reveal">
      <span class="icon-badge"><i class="bi bi-file-earmark-ruled"></i></span>
      <span class="resources-callout-text">
        <strong>Tenders &amp; Procurement</strong>
        <span>View current tender opportunities and procurement notices &rarr;</span>
      </span>
    </a>
  </div>
</section>
<?php
// Renders one row of magazine cover cards. $variant adds a modifier class
// ("" for ASPIRE's red branding, "voice" for ANU Voice's gold branding)
// so the two publications stay visually distinct rather than blending
// into one mixed grid.
function render_magazine_row(array $issues, string $variant = ''): void {
    foreach ($issues as $i => $mag):
        $cardClass = trim('mag-card reveal ' . ($variant ? 'mag-card-' . $variant . ' ' : '') . ($i > 0 ? 'reveal-' . min($i + 1, 4) : ''));
        ?>
        <div class="col-6 col-md-3 col-lg-2">
          <?php if ($mag['pdf_file']): ?>
            <a href="<?= h(UPLOADS_URL . '/' . $mag['pdf_file']) ?>" class="<?= h($cardClass) ?>" target="_blank" rel="noopener">
          <?php else: ?>
            <div class="<?= h($cardClass) ?>">
          <?php endif; ?>
            <?php if ($mag['thumbnail']): ?>
              <img src="<?= h(UPLOADS_URL . '/' . $mag['thumbnail']) ?>" alt="<?= h($mag['title']) ?>" class="cover">
            <?php else: ?>
              <span class="cover-placeholder"><i class="bi bi-journal-richtext"></i></span>
            <?php endif; ?>
            <div class="cover-body">
              <span class="cover-type"><?= h($mag['publication_type']) ?></span>
              <p class="cover-title"><?= h($mag['title']) ?></p>
              <?php if ($mag['issue_label']): ?><p class="cover-issue"><?= h($mag['issue_label']) ?></p><?php endif; ?>
            </div>
          <?php if ($mag['pdf_file']): ?></a><?php else: ?></div><?php endif; ?>
        </div>
    <?php endforeach;
}
?>
<?php if ($aspireIssues): ?>
<section class="py-5 bg-cream" id="aspire">
  <div class="container">
    <div class="section-head reveal">
      <h2>ASPIRE Magazine</h2>
      <a href="<?= BASE_URL ?>/aspire-magazine.php" class="see-all">View full archive &rarr;</a>
    </div>
    <div class="row g-4">
      <?php render_magazine_row($aspireIssues); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($voiceIssues): ?>
<section class="py-5" id="anu-voice">
  <div class="container">
    <div class="section-head reveal">
      <h2>ANU Voice</h2>
      <a href="<?= BASE_URL ?>/anu-voice.php" class="see-all">View full archive &rarr;</a>
    </div>
    <div class="row g-4">
      <?php render_magazine_row($voiceIssues, 'voice'); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($gradIssues): ?>
<section class="py-5 bg-cream" id="graduation-list">
  <div class="container">
    <div class="section-head reveal">
      <h2>Graduation List</h2>
      <a href="<?= BASE_URL ?>/graduation-list.php" class="see-all">View full archive &rarr;</a>
    </div>
    <div class="row g-4">
      <?php render_magazine_row($gradIssues, 'grad'); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($otherIssues): ?>
<section class="py-5" id="other-publications">
  <div class="container">
    <div class="section-head reveal">
      <h2>Other Publications</h2>
    </div>
    <div class="row g-4">
      <?php render_magazine_row($otherIssues); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="py-5">
  <div class="container">
    <?php if (!$rows): ?>
      <div class="text-center py-5">
        <i class="bi bi-file-earmark-text" style="font-size:2.2rem; color: var(--anu-line);"></i>
        <p class="text-muted mt-3">No resources have been published yet.</p>
      </div>
    <?php endif; ?>

    <?php foreach ($order as $cat): if (empty($shelves[$cat])) continue; ?>
      <div class="shelf-section mt-5 reveal">
        <p class="shelf-label"><?= h($cat) ?></p>
        <div class="shelf-row">
          <?php foreach ($shelves[$cat] as $i => $item):
            $ext = strtolower(pathinfo($item['file_name'], PATHINFO_EXTENSION));
            $icon = $icons[$ext] ?? 'bi-file-earmark';
            $spine = $i % 2 === 0 ? 'spine-red' : 'spine-black';
          ?>
            <a href="<?= h(UPLOADS_URL . '/' . $item['file_name']) ?>" class="book-card <?= $spine ?>" download title="<?= h($item['title']) ?><?= $item['description'] ? '-' . h($item['description']) : '' ?>">
              <span class="book-icon"><i class="bi <?= h($icon) ?>"></i></span>
              <span class="book-title"><?= h($item['title']) ?></span>
              <span class="book-meta"><?= strtoupper($ext) ?> &middot; <?= format_bytes((int)$item['file_size']) ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>