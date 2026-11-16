
</main>
<footer class="law-footer">
  <div class="container">
    <div class="row gy-4">
      <div class="col-lg-3">
        <img src="<?= BASE_URL ?>/assets/images/anu-logo.png" alt="ANU logo" height="40">
        <p class="small text-muted mt-2">School of Law, Africa Nazarene University, legal education grounded in justice, ethics and advocacy.</p>
      </div>
      <div class="col-lg-3">
        <h4 class="h6">Quick Links</h4>
        <p class="small mb-1"><a href="<?= BASE_URL ?>/programmes.php">Programmes</a></p>
        <p class="small mb-1"><a href="<?= BASE_URL ?>/faculty.php">Faculty &amp; Staff</a></p>
        <p class="small mb-1"><a href="<?= BASE_URL ?>/contact.php">Contact the School</a></p>
        <p class="small mb-1"><a target="_blank" rel="noopener" href="<?= h(main_setting('student_portal_url')) ?>">Student Portal</a></p>
        <p class="small mb-1"><a target="_blank" rel="noopener" href="<?= h(main_setting('enaz_url')) ?>">eNaz</a></p>
        <p class="small mb-1"><a target="_blank" rel="noopener" href="<?= h(main_setting('url_library')) ?>">Library</a></p>
        <p class="small mb-1"><a target="_blank" rel="noopener" href="<?= h(main_setting('apply_url')) ?>">Apply Now</a></p>
      </div>
      <div class="col-lg-3">
        <h4 class="h6">More from ANU</h4>
        <p class="small mb-1"><a target="_blank" rel="noopener" href="<?= h(main_setting('url_school_business')) ?>">School of Business</a></p>
        <p class="small mb-1"><a target="_blank" rel="noopener" href="<?= h(main_setting('url_school_scitech')) ?>">School of Science and Technology</a></p>
        <p class="small mb-1"><a target="_blank" rel="noopener" href="<?= h(main_setting('url_school_humanities')) ?>">School of Humanities and Social Sciences</a></p>
        <p class="small mb-1"><a target="_blank" rel="noopener" href="<?= h(main_setting('url_school_religion')) ?>">School of Religion and Christian Ministry</a></p>
      </div>
      <div class="col-lg-3">
        <h4 class="h6">Contact</h4>
        <p class="small mb-1"><?= h(main_setting('address')) ?></p>
        <p class="small mb-1"><a href="tel:<?= h(main_setting('phone_primary')) ?>"><?= h(main_setting('phone_primary')) ?></a></p>
      </div>
    </div>
  </div>
  <div class="back-to-main mt-4">
    &copy; <?= date('Y') ?> Africa Nazarene University &middot;
    <a target="_blank" rel="noopener" href="<?= h(MAIN_SITE_URL) ?>/index.php">Back to the main ANU website</a>
    &middot; <a href="<?= BASE_URL ?>/privacy.php">Privacy Policy</a> &middot; <a href="<?= BASE_URL ?>/data-protection.php">Data Protection</a>
  </div>
</footer>

<a href="<?= h(main_setting('whatsapp_url')) ?>" target="_blank" rel="noopener" class="whatsapp-float" aria-label="Chat on WhatsApp">
  <i class="bi bi-whatsapp"></i>
  <span class="pulse-dot"></span>
</a>

<button type="button" class="back-to-top" id="unitBackToTop" aria-label="Back to top"><i class="bi bi-arrow-up"></i></button>

<script src="<?= asset_v(BASE_URL, 'assets/vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= asset_v(BASE_URL, 'assets/js/main.js') ?>"></script>
<?php if (is_file(__DIR__ . '/../../main-site/includes/announcements_lib.php')) { require_once __DIR__ . '/../../main-site/includes/announcements_lib.php'; anu_popup_render(defined('UNIT_SLUG') ? UNIT_SLUG : 'main'); } ?>
<?php if (is_file(__DIR__ . '/../../main-site/includes/hubspot_consent.php')) { require_once __DIR__ . '/../../main-site/includes/hubspot_consent.php'; anu_hubspot_render(anu_hubspot_network_id(), ['privacy' => BASE_URL . '/privacy', 'dp' => BASE_URL . '/data-protection']); } ?>
<script src="<?= h(MAIN_SITE_URL) ?>/assets/js/a11y-widget.js?v=2" defer></script>
</body>
</html>
