<?php
$pageTitle = 'Contact Us';
require_once __DIR__ . '/includes/functions.php';

$success = false;
$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Your session expired. Please resubmit the form.';
    }
    $old = $_POST;
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $subject   = trim($_POST['subject'] ?? '');
    $message   = trim($_POST['message'] ?? '');

    if ($full_name === '' || $message === '') $errors[] = 'Name and message are required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please provide a valid email address.';

    if (!$errors) {
        $stmt = db()->prepare('INSERT INTO contact_messages (full_name, email, phone, subject, message) VALUES (?,?,?,?,?)');
        $stmt->execute([$full_name, $email, $phone ?: null, $subject ?: 'Non-Degree Programmes enquiry', $message]);
        $success = true;
        $old = [];
    }
}

require __DIR__ . '/includes/header.php';
?>
<section class="tvet-hero compact<?= page_banner_url('contact') ? ' has-image' : '' ?>">
  <?php if ($b = page_banner_url('contact')): ?><img class="hero-bg-image" src="<?= h($b) ?>" alt=""><?php endif; ?>
  <div class="container">
    <div class="eyebrow mb-2">Get in touch</div>
    <h1>Contact Non-Degree Programmes</h1>
  </div>
</section>

<section class="tvet-section">
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-6">
        <?php if ($success): ?>
          <div class="alert alert-success">Thank you, your message has been received. The Institute office will respond soon.</div>
        <?php endif; ?>
        <?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= h($e) ?></div><?php endforeach; ?>
        <form method="post" data-validate novalidate>
          <?= csrf_field() ?>
          <div class="mb-3"><label class="form-label">Full Name *</label><input required type="text" name="full_name" class="form-control" value="<?= h($old['full_name'] ?? '') ?>"></div>
          <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label">Email *</label><input required type="email" name="email" class="form-control" value="<?= h($old['email'] ?? '') ?>"></div>
            <div class="col-md-6 mb-3"><label class="form-label">Phone</label><input type="tel" name="phone" class="form-control" value="<?= h($old['phone'] ?? '') ?>"></div>
          </div>
          <div class="mb-3"><label class="form-label">Subject</label><input type="text" name="subject" class="form-control" value="<?= h($old['subject'] ?? '') ?>"></div>
          <div class="mb-3"><label class="form-label">Message *</label><textarea required name="message" rows="5" class="form-control"><?= h($old['message'] ?? '') ?></textarea></div>
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" required id="contactConsent">
            <label class="form-check-label small" for="contactConsent">I consent to Africa Nazarene University processing my personal data to respond to this enquiry, in accordance with the <a href="<?= BASE_URL ?>/privacy-policy.php" target="_blank">Privacy Policy</a>.</label>
          </div>
          <button class="btn btn-tvet-cta btn-lg" type="submit">Send Message</button>
        </form>
      </div>
      <div class="col-lg-6">
        <div class="tvet-card mb-4" style="padding-top:1.7rem">
          <h3 class="h5">Reach the Institute</h3>
          <p class="small mb-2"><i class="bi bi-geo-alt text-red"></i> <?= h(setting('address')) ?></p>
          <p class="small mb-2"><i class="bi bi-telephone text-red"></i> <a href="tel:<?= h(setting('phone_primary')) ?>"><?= h(setting('phone_primary')) ?></a> / <a href="tel:<?= h(setting('phone_secondary')) ?>"><?= h(setting('phone_secondary')) ?></a></p>
          <p class="small mb-2"><i class="bi bi-envelope text-red"></i> <a href="mailto:<?= h(setting('email_admissions')) ?>"><?= h(setting('email_admissions')) ?></a></p>
          <p class="small mb-0"><i class="bi bi-whatsapp text-red"></i> <a target="_blank" rel="noopener" href="<?= h(setting('whatsapp_url')) ?>">Chat on WhatsApp</a></p>
        </div>
        <div class="ratio ratio-4x3 rounded overflow-hidden border">
          <iframe src="<?= h(setting('google_map_embed')) ?>" style="border:0" allowfullscreen loading="lazy"></iframe>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
