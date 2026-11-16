<?php
/** Turns the saved plain text into a tidy layout: [Heading] -> bold heading, "- " -> bullets, "1." -> numbered list. */
function job_text_html(string $text): string
{
    $out = ''; $list = null;   // 'ul' | 'ol' | null
    $close = function () use (&$out, &$list) { if ($list) { $out .= "</$list>"; $list = null; } };
    $linkify = function (string $s): string {
        $s = h($s);
        return preg_replace('~(https?://[^\s<]+)~', '<a href="$1" target="_blank" rel="noopener">$1</a>', $s);
    };
    foreach (preg_split('/\R/', trim($text)) as $line) {
        $line = trim($line);
        if ($line === '') { $close(); continue; }
        if (preg_match('/^\[(.+)\]$/', $line, $m)) { $close(); $out .= '<h3 class="h6 fw-bold mt-4 mb-2">' . h($m[1]) . '</h3>'; continue; }
        if (preg_match('/^[-\x{2022}*]\s+(.+)$/u', $line, $m)) { if ($list !== 'ul') { $close(); $out .= '<ul class="job-list">'; $list = 'ul'; } $out .= '<li>' . $linkify($m[1]) . '</li>'; continue; }
        if (preg_match('/^\d+[.)]\s+(.+)$/', $line, $m)) { if ($list !== 'ol') { $close(); $out .= '<ol class="job-list">'; $list = 'ol'; } $out .= '<li>' . $linkify($m[1]) . '</li>'; continue; }
        $close(); $out .= '<p class="mb-2">' . $linkify($line) . '</p>';
    }
    $close();
    return $out;
}

require_once __DIR__ . '/includes/functions.php';

$slug = $_GET['slug'] ?? '';
$stmt = db()->prepare('SELECT * FROM vacancies WHERE slug = ? AND is_published = 1 LIMIT 1');
$stmt->execute([$slug]);
$v = $stmt->fetch();

if (!$v) {
    http_response_code(404);
    $pageTitle = 'Not found';
    require __DIR__ . '/includes/header.php';
    echo '<div class="container py-5"><h1>Vacancy not found</h1><p><a href="' . BASE_URL . '/vacancies.php">Back to vacancies</a></p></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}
$pageTitle = $v['title'];
$pageDescription = $v['description'] ? excerpt($v['description'], 160) : ($v['title'] . ' - vacancy at Africa Nazarene University.');
$closingSoon = $v['closing_date'] && strtotime($v['closing_date']) <= strtotime('+7 days');
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container container-narrow">
    <p class="mb-2"><a href="<?= BASE_URL ?>/vacancies.php" class="text-decoration-none">&larr; All Vacancies</a></p>
    <span class="tag"><?= h($v['employment_type']) ?></span>
    <h1 class="mt-2 mb-2"><?= h($v['title']) ?></h1>
    <p class="text-muted mb-0">
      <?php if ($v['department']): ?><i class="bi bi-building"></i> <?= h($v['department']) ?><?php endif; ?>
      <?php if ($v['closing_date']): ?>
        &middot; <i class="bi bi-calendar-event"></i> Closes <?= format_date($v['closing_date']) ?>
        <?php if ($closingSoon): ?><span class="badge-closing-soon">Closing Soon</span><?php endif; ?>
      <?php endif; ?>
    </p>
  </div>
</section>

<section class="py-5">
  <div class="container container-narrow">
    <div class="row g-4">
      <div class="col-md-8 reveal">
        <?php if ($v['description']): ?>
          <h2 class="h4">Job Description</h2>
          <div class="job-text"><?= job_text_html((string)$v['description']) ?></div>
        <?php endif; ?>

        <?php if ($v['requirements']): ?>
          <h2 class="h4 mt-4">Requirements</h2>
          <div class="job-text"><?= job_text_html((string)$v['requirements']) ?></div>
        <?php endif; ?>

        <?php if ($v['how_to_apply']): ?>
          <h2 class="h4 mt-4">How to Apply</h2>
          <div class="job-text"><?= job_text_html((string)$v['how_to_apply']) ?></div>
        <?php else: ?>
          <h2 class="h4 mt-4">How to Apply</h2>
          <p class="text-muted">Send your application, CV and required documents to the address on our <a href="<?= BASE_URL ?>/contact.php">Contact page</a>.</p>
        <?php endif; ?>
      </div>
      <div class="col-md-4">
        <div class="card-anu side-cta reveal">
          <div class="body">
            <?php if ($v['closing_date']): ?>
              <p class="mb-1"><strong>Closing Date</strong></p>
              <p class="text-muted"><?= format_date($v['closing_date']) ?></p>
              <?php if ($v['closing_date'] < date('Y-m-d')): ?><p class="small"><strong>This position has closed.</strong></p><?php endif; ?>
            <?php endif; ?>
            <?php if ($v['attachment']): ?>
              <a href="<?= h(UPLOADS_URL . '/' . $v['attachment']) ?>" class="btn btn-apply w-100" download>
                <i class="bi bi-download"></i> Download Job Description
              </a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/contact.php" class="btn btn-outline-anu w-100 mt-2">Contact HR</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
