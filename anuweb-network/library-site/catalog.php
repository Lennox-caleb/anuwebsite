<?php
$pageTitle = 'Catalog';
require_once __DIR__ . '/config/db.php';
$__hero = site_hero();
require __DIR__ . '/includes/header.php';
?>
<section class="lib-hero compact<?= $__hero && $__hero['image'] ? ' has-image' : '' ?>"<?= $__hero && $__hero['image'] ? ' style="background-image: linear-gradient(180deg, rgba(10,10,10,.55) 0%, rgba(10,10,10,.72) 100%), url(\'' . h(MAIN_SITE_URL . '/assets/uploads/' . $__hero['image']) . '\')"' : '' ?>><div class="container"><div class="eyebrow mb-2">Find it</div><h1>Catalog</h1></div></section>
<section class="lib-section">
  <div class="container container-narrow">
    <p class="text-muted"><?= nl2br(h(unit_content('catalog_intro', "A searchable library catalog (books, journals, e-resources) is a separate system from this website and should be linked here once the University's library management system is confirmed."))) ?></p>
    <p class="text-muted">In the meantime, everything currently published by the University is available through:</p>
    <div class="row g-4 mt-2">
      <div class="col-md-6">
        <div class="lib-card"><span class="icon"><i class="bi bi-journal-bookmark"></i></span><h3>Resources Library</h3><p class="small text-muted mb-0">Policies, handbooks, forms and past papers.</p><a href="<?= h(MAIN_SITE_URL) ?>/resources.php" class="small">Browse &rarr;</a></div>
      </div>
      <div class="col-md-6">
        <div class="lib-card"><span class="icon"><i class="bi bi-archive"></i></span><h3>University Repository</h3><p class="small text-muted mb-0">Theses, publications and official records.</p><a href="<?= h(main_setting('repository_url')) ?>" class="small">Visit &rarr;</a></div>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
