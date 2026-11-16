<?php
$pageTitle = 'Departments';
require_once __DIR__ . '/config/db.php';
$__hero = site_hero();

// Phase 5: real, admin-manageable departments for this school only --
// nothing invented. If this page is reached directly with none set yet,
// it says so honestly rather than listing placeholder names; the site's
// own nav never links here at all until at least one exists (see
// includes/header.php's department_count() check).
// Phase 5b fix: don't fatal if the departments table hasn't been
// migrated onto this database yet -- show the honest "coming soon"
// fallback instead (the nav item is already hidden via department_count()
// in that case too).
$departments = [];
if (schema_has_table('departments')) {
    $stmt = db()->prepare('SELECT * FROM departments WHERE unit_slug = ? AND is_published = 1 ORDER BY sort_order, name');
    $stmt->execute([UNIT_SLUG]);
    $departments = $stmt->fetchAll();
}

require __DIR__ . '/includes/header.php';
?>
<section class="biz-hero compact<?= $__hero && $__hero['image'] ? ' has-image' : '' ?>"<?= $__hero && $__hero['image'] ? ' style="background-image: linear-gradient(180deg, rgba(10,10,10,.55) 0%, rgba(10,10,10,.72) 100%), url(\'' . h(MAIN_SITE_URL . '/assets/uploads/' . $__hero['image']) . '\')"' : '' ?>>
  <div class="container">
    <div class="eyebrow mb-2">Academics</div>
    <h1>Departments</h1>
  </div>
</section>

<section class="biz-section">
  <div class="container">
    <?php if (!$departments): ?>
      <p class="text-muted">Department listings for the School of Business will appear here soon.</p>
    <?php else: ?>
      <div class="row g-4">
        <?php foreach ($departments as $d): ?>
          <div class="col-md-6">
            <div class="biz-card h-100">
              <h3><?= h($d['name']) ?></h3>
              <?php if ($d['summary']): ?><p class="text-muted small mb-0"><?= nl2br(h($d['summary'])) ?></p><?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
