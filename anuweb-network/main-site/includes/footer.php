
</main>

<footer class="site-footer">
  <div class="container">
    <div class="row gy-4">
      <div class="col-12 col-lg-4">
        <div class="footer-logo-chip mb-3">
          <img src="<?= BASE_URL ?>/assets/images/anu-logo.png" alt="ANU logo" height="40">
        </div>
        <p class="small text-gold mb-2"><?= h(setting('sponsor_note')) ?></p>
        <p class="small"><?= h(setting('mission')) ?></p>
        <div class="social-row mt-3">
          <a href="<?= h(setting('facebook_url')) ?>" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
          <a href="<?= h(setting('twitter_url')) ?>" aria-label="X / Twitter"><i class="bi bi-twitter-x"></i></a>
          <a href="<?= h(setting('instagram_url')) ?>" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
          <a href="<?= h(setting('linkedin_url')) ?>" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
          <a href="<?= h(setting('youtube_url')) ?>" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
          <a href="<?= h(setting('whatsapp_url')) ?>" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
        </div>
      </div>
      <div class="col-6 col-md-4 col-lg">
        <h4>About</h4>
        <ul>
          <li><a href="<?= BASE_URL ?>/who-we-are.php">Who We Are</a></li>
          <li><a href="<?= BASE_URL ?>/affiliation.php">Affiliation</a></li>
          <li><a href="<?= BASE_URL ?>/governance.php">Governance</a></li>
          <li><a href="<?= BASE_URL ?>/leadership.php">University Leadership</a></li>
          <li><a href="<?= BASE_URL ?>/about.php#campus">Main Campus</a></li>
        </ul>
      </div>
      <div class="col-6 col-md-4 col-lg">
        <h4>Academics</h4>
        <ul>
          <li><a href="<?= BASE_URL ?>/programs.php">Programmes</a></li>
          <li><a href="<?= BASE_URL ?>/schools.php">Schools</a></li>
          <li><a href="<?= h(setting('url_admissions')) ?>">Admissions</a></li>
          <?php if (setting('url_research')): ?><li><a href="<?= h(setting('url_research')) ?>">Research</a></li><?php endif; ?>
          <?php if (setting('url_library')): ?><li><a href="<?= h(setting('url_library')) ?>">Library</a></li><?php endif; ?>
        </ul>
      </div>
      <div class="col-6 col-md-4 col-lg">
        <h4>Quick Links</h4>
        <ul>
          <li><a target="_blank" rel="noopener" href="<?= h(setting('student_portal_url')) ?>">Student Portal</a></li>
          <li><a href="<?= BASE_URL ?>/orientation.php">Orientation</a></li>
          <li><a href="<?= BASE_URL ?>/resources.php">Resources</a></li>
          <li><a href="<?= BASE_URL ?>/tenders.php">Tenders &amp; Procurement</a></li>
          <li><a href="<?= h(setting('vacancies_url')) ?>">Vacancies</a></li>
          <li><a href="<?= BASE_URL ?>/news.php">News</a></li>
          <li><a href="<?= BASE_URL ?>/events.php">Events</a></li>
        </ul>
      </div>
      <div class="col-6 col-md-4 col-lg">
        <h4>Get Involved</h4>
        <ul>
          <li><a target="_blank" rel="noopener" href="<?= h(setting('alumni_url')) ?>">Alumni</a></li>
          <li><a target="_blank" rel="noopener" href="<?= h(setting('foundation_url')) ?>">ANU Foundation</a></li>
          <li><a target="_blank" rel="noopener" href="<?= h(setting('donate_url')) ?>">Donate</a></li>
          <li><a href="<?= BASE_URL ?>/csr.php">Corporate Social Responsibility</a></li>
        </ul>
      </div>
      <div class="col-6 col-md-4 col-lg">
        <h4>Contact</h4>
        <ul>
          <li><?= h(setting('address')) ?></li>
          <li><a href="tel:<?= h(setting('phone_primary')) ?>"><?= h(setting('phone_primary')) ?></a></li>
          <li><a href="mailto:<?= h(setting('email_marketing')) ?>"><?= h(setting('email_marketing')) ?></a></li>
          <li><a href="<?= BASE_URL ?>/contact.php">Contact Us</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-accreditation">
      <i class="bi bi-patch-check-fill"></i>
      Chartered by Kenya's Commission for University Education (CUE), 
      the first private university accredited under the University Act without external affiliation.
    </div>
    <div class="footer-bottom d-flex flex-wrap justify-content-between gap-2">
      <div>&copy; <?= date('Y') ?> Africa Nazarene University. All Rights Reserved.</div>
      <div class="footer-legal-links">
        <a href="<?= BASE_URL ?>/privacy.php">Privacy Policy</a>
        <span class="divider">|</span>
        <a href="<?= BASE_URL ?>/data-protection.php">Data Protection</a>
        <span class="divider">|</span>
        <a href="<?= BASE_URL ?>/terms.php">Terms &amp; Conditions</a>
        <span class="divider">|</span>
        <a href="<?= BASE_URL ?>/accreditation.php">Accreditation</a>
        <span class="divider">|</span>
        <a href="<?= BASE_URL ?>/sitemap.php">Sitemap</a>
      </div>
    </div>
  </div>
</footer>

<a href="<?= h(setting('whatsapp_url')) ?>" target="_blank" rel="noopener" class="whatsapp-float" aria-label="Chat on WhatsApp">
  <i class="bi bi-whatsapp"></i>
  <span class="pulse-dot"></span>
</a>

<button type="button" class="back-to-top" id="anuBackToTop" aria-label="Back to top">
  <i class="bi bi-arrow-up"></i>
</button>

<!-- Cookie / data-notice banner (Data Protection Act, 2019) -->
<div class="cookie-banner" id="anuCookieBanner" role="dialog" aria-live="polite" aria-label="Cookie and privacy notice">
  <div class="cookie-banner-inner">
    <p class="mb-2 mb-sm-0">
      We use cookies and similar technologies to keep the site secure and improve your experience,
      in line with our <a href="<?= BASE_URL ?>/privacy.php">Privacy Policy</a>, issued under the
      Data Protection Act, 2019.
    </p>
    <div class="cookie-banner-actions">
      <button type="button" class="btn btn-apply btn-sm" id="anuCookieAccept">Accept</button>
      <a href="<?= BASE_URL ?>/privacy.php" class="btn btn-outline-dark btn-sm">Learn more</a>
    </div>
  </div>
</div>

<?php require __DIR__ . '/popup.php'; ?>
<?php require_once __DIR__ . '/hubspot_consent.php'; anu_hubspot_render(anu_hubspot_network_id(), ['privacy' => BASE_URL . '/privacy', 'dp' => BASE_URL . '/data-protection']); ?>

<script src="<?= asset_v(BASE_URL, 'assets/vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= asset_v(BASE_URL, 'assets/js/main.js') ?>"></script>
<script src="<?= BASE_URL ?>/assets/js/a11y-widget.js?v=2" defer></script>
</body>
</html>
