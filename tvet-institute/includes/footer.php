
<footer class="tvet-footer">
  <div class="container py-5">
    <div class="row gy-4">
      <div class="col-lg-3">
        <img src="<?= BASE_URL ?>/assets/images/anu-logo.png" alt="ANU logo" height="40" class="footer-logo-chip">
        <p class="small mt-3">Non-Degree Programmes, Africa Nazarene University, accredited, competency-based technical and vocational training for career-ready, Christ-centred graduates.</p>
        <div class="footer-social">
          <a target="_blank" rel="noopener" href="<?= h(setting('facebook_url')) ?>" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
          <a target="_blank" rel="noopener" href="<?= h(setting('twitter_url')) ?>" aria-label="X / Twitter"><i class="bi bi-twitter-x"></i></a>
          <a target="_blank" rel="noopener" href="<?= h(setting('instagram_url')) ?>" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
          <a target="_blank" rel="noopener" href="<?= h(setting('linkedin_url')) ?>" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
          <a target="_blank" rel="noopener" href="<?= h(setting('youtube_url')) ?>" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
        </div>
      </div>
      <div class="col-lg-3">
        <h4 class="h6 text-white">Quick Links</h4>
        <p class="small mb-1"><a href="<?= BASE_URL ?>/about.php">About the Programme</a></p>
        <p class="small mb-1"><a href="<?= BASE_URL ?>/leadership.php">Message from the Head</a></p>
        <p class="small mb-1"><a href="<?= BASE_URL ?>/programmes.php">Programmes</a></p>
        <p class="small mb-1"><a href="<?= BASE_URL ?>/admissions.php">Admissions</a></p>
        <p class="small mb-1"><a href="<?= h(apply_url()) ?>" target="_blank" rel="noopener">Apply Online</a></p>
        <p class="small mb-1"><a href="<?= BASE_URL ?>/downloads.php">Downloads &amp; Forms</a></p>
        <p class="small mb-1"><a href="<?= BASE_URL ?>/faqs.php">FAQs</a></p>
        <p class="small mb-1"><a href="<?= BASE_URL ?>/privacy-policy.php">Privacy Policy</a></p>
        <p class="small mb-1"><a href="<?= BASE_URL ?>/data-protection.php">Data Protection</a></p>
        <p class="small mb-1"><a href="<?= BASE_URL ?>/terms-conditions.php">Terms &amp; Conditions</a></p>
      </div>
      <div class="col-lg-3">
        <h4 class="h6 text-white">More from ANU</h4>
        <p class="small mb-1"><a target="_blank" rel="noopener" href="<?= h(setting('main_site_url')) ?>">Main University Website</a></p>
        <p class="small mb-1"><a target="_blank" rel="noopener" href="<?= h(setting('url_school_law')) ?>">School of Law</a></p>
        <p class="small mb-1"><a target="_blank" rel="noopener" href="<?= h(setting('url_school_business')) ?>">School of Business</a></p>
        <p class="small mb-1"><a target="_blank" rel="noopener" href="<?= h(setting('url_school_scitech')) ?>">School of Science &amp; Technology</a></p>
        <p class="small mb-1"><a target="_blank" rel="noopener" href="<?= h(setting('enaz_url')) ?>">eNaz Portal</a></p>
      </div>
      <div class="col-lg-3">
        <h4 class="h6 text-white">Contact</h4>
        <p class="small mb-1"><i class="bi bi-geo-alt"></i> <?= h(setting('address')) ?></p>
        <p class="small mb-1"><i class="bi bi-telephone"></i> <a href="tel:<?= h(setting('phone_primary')) ?>"><?= h(setting('phone_primary')) ?></a></p>
        <p class="small mb-1"><i class="bi bi-envelope"></i> <a href="mailto:<?= h(setting('email_admissions')) ?>"><?= h(setting('email_admissions')) ?></a></p>
        <p class="small mb-1"><i class="bi bi-patch-check"></i> Chartered by the Commission for University Education (CUE)</p>
      </div>
    </div>
  </div>
  <div class="back-to-main">
    &copy; <?= date('Y') ?> Africa Nazarene University, Non-Degree Programmes &middot;
    <a target="_blank" rel="noopener" href="<?= h(setting('main_site_url')) ?>">Back to the main ANU website</a>
  </div>
</footer>

<a href="<?= h(setting('whatsapp_url')) ?>" target="_blank" rel="noopener" class="whatsapp-float" aria-label="Chat on WhatsApp">
  <i class="bi bi-whatsapp"></i>
  <span class="pulse-dot"></span>
</a>

<?php require_once __DIR__ . '/hubspot_consent.php'; anu_hubspot_render(anu_hubspot_id(setting('hubspot_portal_id')), ['privacy' => BASE_URL . '/privacy-policy', 'dp' => BASE_URL . '/data-protection']); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<script src="<?= BASE_URL ?>/assets/js/a11y-widget.js?v=2" defer></script>
</body>
</html>
