<?php
// Item 3 (this phase): full Director's Message page. Same header/footer/
// navbar as every other page (via includes/layout.php), reading the same
// "Leadership" staff record renderDirectorMessage() uses on the homepage
// -- so both places always agree. Shows a clearly labeled placeholder
// (never an invented real message) if no admin has added the Director's
// profile yet via Admin -> Team.
header('Cache-Control: no-store, no-cache, must-revalidate');
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/tracker.php';
require_once __DIR__ . '/includes/layout.php';

$dir = getDirector();
$name = $dir['name']; $title = $dir['title']; $photo = $dir['photo']; $message = $dir['bio'];

trackPage('directors-message', "Director's Message");
renderHead("Director's Message");
renderNavbar('');
?>
<section class="sm-hero">
  <div class="container">
    <span class="sm-eyebrow">ANU Online</span>
    <h1>Message from the Director</h1>
  </div>
</section>
<section class="sm-section">
  <div class="container sm-narrow">
    <article class="sm-article">
      <div class="sm-article-photo">
        <?php if ($photo): ?><img src="<?=e($photo)?>" alt="<?=e($name)?>"><?php else: ?><div class="sm-ph"><i class="bi bi-person-fill"></i></div><?php endif; ?>
      </div>
      <div>
        <?php if ($name !== ''): ?><h2><?=e($name)?></h2><?php endif; ?>
        <?php if ($title !== ''): ?><p class="sm-role"><?=e($title)?></p><?php endif; ?>
      </div>
    </article>
    <div class="sm-text">
      <?php if ($message !== ''): foreach (preg_split('/\R+/', $message) as $para): if (trim($para) === '') continue; ?>
        <p><?=e(trim($para))?></p>
      <?php endforeach; else: ?>
        <p class="text-muted">The Director&rsquo;s message will be published here soon.</p>
      <?php endif; ?>
    </div>
    <p class="mt-4"><a class="sm-back" href="/anu/index.php"><i class="bi bi-arrow-left"></i> Back to ANU Online home</a></p>
  </div>
</section>
<?php renderFooter(); ?>
