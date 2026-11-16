<?php
$pageTitle = 'Regional Chapters';
require_once __DIR__ . '/config/db.php';
$__hero = site_hero();
require __DIR__ . '/includes/header.php';
$chapters = ['Kenya','USA & Canada','Zimbabwe','South Sudan','Departmental Chapters'];
?>
<section class="alum-hero compact<?= $__hero && $__hero['image'] ? ' has-image' : '' ?>"<?= $__hero && $__hero['image'] ? ' style="background-image: linear-gradient(180deg, rgba(10,10,10,.55) 0%, rgba(10,10,10,.72) 100%), url(\'' . h(MAIN_SITE_URL . '/assets/uploads/' . $__hero['image']) . '\')"' : '' ?>><div class="container"><div class="eyebrow mb-2">Connect</div><h1>Regional Chapters</h1></div></section>
<section class="alum-section">
  <div class="container">
    <p class="text-muted max-ch-60"><?= nl2br(h(unit_content('chapters_intro', 'ANU alumni stay connected through regional and departmental chapters around the world.'))) ?></p>
    <div class="row g-4 mt-2">
      <?php foreach ($chapters as $c): ?>
        <div class="col-6 col-md-4 col-lg-3">
          <div class="alum-card text-center"><span class="icon"><i class="bi bi-people"></i></span><h3 class="h6 mb-0"><?= h($c) ?></h3></div>
        </div>
      <?php endforeach; ?>
    </div>
    <p class="text-muted small mt-4"><?= nl2br(h(unit_content('chapters_closing', 'Chapter leader contact details should be confirmed and published here by the Alumni Affairs Office.'))) ?></p>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
