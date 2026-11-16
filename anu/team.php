<?php
header('Cache-Control: no-store, no-cache, must-revalidate');
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/tracker.php';
require_once __DIR__ . '/includes/layout.php';

$db = getDB();

// Real staff only — added via Admin → Team. Nothing shown is invented;
// an empty department simply doesn't render a group.
$allStaff = getStaff();
$groups = [];
foreach ($allStaff as $m) {
    $dept = $m['department'] ?: 'Team';
    $groups[$dept][] = $m;
}
// Show Leadership first, then Chaplaincy, then everything else alphabetically.
$order = ['Leadership', 'Chaplaincy'];
uksort($groups, function($a, $b) use ($order) {
    $ia = array_search($a, $order); $ib = array_search($b, $order);
    if ($ia === false) $ia = 99; if ($ib === false) $ib = 99;
    return $ia <=> $ib ?: strcmp($a, $b);
});

$heroSlides = getSlides('team');
$heroData   = !empty($heroSlides) ? $heroSlides[0] : getHero('team');
$heroBg     = !empty($heroData['bg_image']) ? imgUrl($heroData['bg_image']) : '';
$heroOverlay= !empty($heroData['overlay_color']) ? $heroData['overlay_color'] : 'rgba(10,0,0,0.6)';

trackPage('team', 'Meet Our Team');
renderHead('Meet ANU Online Team'); renderNavbar('about');
?>

<!-- ═══ HERO ═══ -->
<div class="pp-hero tm-hero" <?= $heroBg ? "style=\"background-image:url('{$heroBg}')\"" : '' ?>>
  <div class="pp-hero-overlay"></div>
  <div class="container pp-hero-content">
    <div class="pp-hero-text" data-aos="fade-up">
      <span class="tm-eyebrow">Our People</span>
      <h1 class="pp-hero-title">Meet the ANU Online Team</h1>
      <p class="pp-hero-sub">The leadership, faculty and support staff who make your learning journey possible.</p>
    </div>
  </div>
</div>

<section class="sec">
  <div class="container">
    <?php if (empty($allStaff)): ?>
      <p class="text-muted text-center">Team profiles will appear here once added via the Admin Panel.</p>
    <?php else: ?>
      <?php foreach ($groups as $dept => $members): ?>
      <div class="or-head mt-4"><h2><?= e($dept) ?></h2></div>
      <div class="row g-4 board-grid mb-5">
        <?php foreach ($members as $m):
          $ph = !empty($m['photo']) ? imgUrl($m['photo']) : ''; ?>
        <div class="col-6 col-md-4 col-lg-3">
          <div class="board-card">
            <div class="board-card-media">
              <?php if ($ph): ?><img src="<?= e($ph) ?>" alt="<?= e($m['name']) ?>"><?php else: ?><div class="board-card-placeholder" aria-hidden="true"><i class="bi bi-person"></i></div><?php endif; ?>
              <div class="board-card-caption">
                <h3><?= e($m['name']) ?></h3>
                <?php if (!empty($m['title'])): ?><p class="board-card-role"><?= e($m['title']) ?></p><?php endif; ?>
              </div>
            </div>
            <?php if (!empty($m['bio'])): ?><p class="board-card-bio"><?= e(mb_strimwidth(strip_tags($m['bio']), 0, 110, '…')) ?></p><?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>

<?php renderFooter(); ?>
