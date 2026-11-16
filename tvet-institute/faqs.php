<?php
$pageTitle = 'Frequently Asked Questions';
require_once __DIR__ . '/includes/functions.php';
$faqs = db()->query("SELECT * FROM faqs WHERE is_published = 1 ORDER BY sort_order")->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<section class="tvet-hero compact<?= page_banner_url('faqs') ? ' has-image' : '' ?>">
  <?php if ($b = page_banner_url('faqs')): ?><img class="hero-bg-image" src="<?= h($b) ?>" alt=""><?php endif; ?>
  <div class="container">
    <div class="eyebrow mb-2">Help</div>
    <h1>Frequently Asked Questions</h1>
  </div>
</section>

<section class="tvet-section">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <?php if (!$faqs): ?><p class="text-muted">FAQs will appear here once added via the Admin panel.</p><?php endif; ?>
        <div class="accordion" id="faqAccordion">
          <?php foreach ($faqs as $i => $f): ?>
            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button <?= $i === 0 ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?= (int)$f['id'] ?>">
                  <?= h($f['question']) ?>
                </button>
              </h2>
              <div id="faq<?= (int)$f['id'] ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>" data-bs-parent="#faqAccordion">
                <div class="accordion-body small text-muted"><?= nl2br(h($f['answer'])) ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="text-center mt-5">
          <p class="text-muted mb-2">Didn't find what you were looking for?</p>
          <a href="<?= BASE_URL ?>/contact.php" class="btn btn-tvet-cta">Contact Us</a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
