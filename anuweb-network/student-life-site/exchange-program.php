<?php
$pageTitle = 'Exchange Program';
require_once __DIR__ . '/config/db.php';
$__hero = site_hero();

// All text is editable in Admin > Manage Unit Sites > Student Life > Exchange Program page.
// Defaults below are the approved "Exchange Program - Updated Write-up" (ANU - Mount Vernon Nazarene University).
$paras = function (string $t): array { return array_values(array_filter(array_map('trim', preg_split('/\R{2,}/', $t)), 'strlen')); };
$lines = function (string $t): array { return array_values(array_filter(array_map('trim', preg_split('/\R/', $t)), 'strlen')); };
$reg   = include __DIR__ . '/../main-site/includes/exchange_defaults.php';   // single source of the default text (also used by the admin editor)
$c = fn(string $k) => unit_content($k, $reg[$k] ?? '');

$email = trim($c('exchange_v2_apply_email'));
$intro = $paras($c('exchange_v2_intro'));
$overview = $paras($c('exchange_v2_overview'));
$apply = $paras($c('exchange_v2_apply'));
$elig = $lines($c('exchange_v2_eligibility'));
$faqs = [];
foreach ($lines($c('exchange_v2_faqs')) as $ln) { $pp = explode('|', $ln, 2); if (count($pp) === 2) $faqs[] = [trim($pp[0]), trim($pp[1])]; }
// turn the e-mail address inside text into a mailto link (text is escaped first)
$linkify = fn(string $t) => $email !== '' ? str_replace(h($email), '<a href="mailto:' . h($email) . '">' . h($email) . '</a>', h($t)) : h($t);

require __DIR__ . '/includes/header.php';
?>
<section class="sl-hero compact<?= $__hero && $__hero['image'] ? ' has-image' : '' ?>"<?= $__hero && $__hero['image'] ? ' style="background-image: linear-gradient(180deg, rgba(10,10,10,.55) 0%, rgba(10,10,10,.72) 100%), url(\'' . h(MAIN_SITE_URL . '/assets/uploads/' . $__hero['image']) . '\'); background-size: cover; background-position: center;"' : '' ?>>
  <div class="container">
    <div class="eyebrow mb-2">Explore</div>
    <h1>Exchange Program</h1>
    <p class="lead">ANU &amp; Mount Vernon Nazarene University (Ohio, USA)</p>
  </div>
</section>

<section class="sl-section">
  <div class="container container-narrow">
    <div class="sl-section-head"><h2>Transformative Exchange Program</h2></div>
    <p class="fw-bold">Africa Nazarene University Exchange Program with Mount Vernon Nazarene University in the United States</p>
    <?php foreach ($intro as $p): ?><p><?= nl2br(h($p)) ?></p><?php endforeach; ?>
  </div>
</section>

<section class="sl-section">
  <div class="container container-narrow">
    <div class="sl-section-head"><h2>Program Overview</h2></div>
    <?php foreach ($overview as $p): ?><p><?= nl2br(h($p)) ?></p><?php endforeach; ?>
    <div class="row g-4 mt-1">
      <div class="col-md-6"><div class="sl-card h-100"><div class="icon-badge"><i class="bi bi-mortarboard"></i></div><h3>Academic Excellence</h3><p class="mb-0"><?= nl2br(h($c('exchange_v2_academic'))) ?></p></div></div>
      <div class="col-md-6"><div class="sl-card h-100"><div class="icon-badge"><i class="bi bi-person-arms-up"></i></div><h3>Individual Growth</h3><p class="mb-0"><?= nl2br(h($c('exchange_v2_growth'))) ?></p></div></div>
    </div>
  </div>
</section>

<section class="sl-section" id="apply">
  <div class="container container-narrow">
    <div class="sl-section-head"><h2>Application Process</h2></div>
    <?php foreach ($apply as $p): ?><p><?= nl2br($linkify($p)) ?></p><?php endforeach; ?>
    <?php if ($email !== ''): ?><a class="btn btn-sl-cta mt-2" href="mailto:<?= h($email) ?>?subject=Exchange%20Program%20enquiry">Email the Study Abroad office</a><?php endif; ?>
  </div>
</section>

<?php if ($elig): ?>
<section class="sl-section">
  <div class="container container-narrow">
    <div class="sl-section-head"><h2>Eligibility Criteria</h2></div>
    <p>To be eligible for the Exchange Program, applicants must:</p>
    <ul class="exchange-checklist">
      <?php foreach ($elig as $e): ?><li><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span><?= h($e) ?></span></li><?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<?php if ($faqs): ?>
<section class="sl-section">
  <div class="container container-narrow">
    <div class="sl-section-head"><h2>FAQs</h2></div>
    <div class="accordion exchange-faq" id="exchangeFaq">
      <?php foreach ($faqs as $i => [$q, $a]): ?>
      <div class="accordion-item">
        <h3 class="accordion-header"><button class="accordion-button<?= $i ? ' collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?= $i ?>" aria-expanded="<?= $i ? 'false' : 'true' ?>" aria-controls="faq<?= $i ?>"><?= h($q) ?></button></h3>
        <div id="faq<?= $i ?>" class="accordion-collapse collapse<?= $i ? '' : ' show' ?>" data-bs-parent="#exchangeFaq"><div class="accordion-body"><?= nl2br(h($a)) ?></div></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="sl-section apply-band">
  <div class="container text-center container-narrow">
    <p class="mb-4"><?= nl2br(h($c('exchange_v2_conclusion'))) ?></p>
    <?php if ($email !== ''): ?><a class="btn btn-sl-cta btn-lg" href="mailto:<?= h($email) ?>?subject=Exchange%20Program%20enquiry">Express your interest</a><?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
