<?php
$pageTitle = 'Contact Us';
require_once __DIR__ . '/includes/functions.php';

$success = false; if (function_exists("anu_was_sent") && anu_was_sent()) { $success = true; }
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $full_name = trim($_POST['full_name'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $subject   = trim($_POST['subject'] ?? '');
        $message   = trim($_POST['message'] ?? '');

        if ($full_name === '' || $message === '') $errors[] = 'Name and message are required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please provide a valid email address.';

        if (!$errors) {
            $stmt = db()->prepare('INSERT INTO contact_messages (full_name, email, subject, message) VALUES (?,?,?,?)');
            if (anu_contact_should_save($email, $message)) { $stmt->execute([$full_name, $email, $subject, $message]); }
            anu_prg_redirect();
        }
    }
}

require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container container-narrow text-center">
    <h1>Contact Us</h1>
    <p class="text-muted mb-0">We'll route your message to the right office.</p>
  </div>
</section>
<section class="py-5">
  <div class="container">
    <div class="row g-4 g-lg-5">
      <div class="col-lg-6 reveal">
        <h2 class="h4">Send a Message</h2>

        <?php if ($success): ?>
          <div class="alert alert-success">Thank you, your message has been sent. We'll get back to you shortly.</div>
        <?php endif; ?>
        <?php foreach ($errors as $err): ?>
          <div class="alert alert-danger"><?= h($err) ?></div>
        <?php endforeach; ?>

        <form method="post" novalidate>
          <div style="position:absolute;left:-9999px;" aria-hidden="true"><label>Leave this empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
          <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
          <div class="mb-3">
            <label for="fld-full_name" class="form-label">Full Name</label>
            <input id="fld-full_name" type="text" name="full_name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="fld-email" class="form-label">Email</label>
            <input id="fld-email" type="email" name="email" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="fld-subject" class="form-label">Subject</label>
            <select id="fld-subject" name="subject" class="form-select">
              <option>Admission</option>
              <option>Finance</option>
              <option>Student Affairs</option>
              <option>Complaints</option>
              <option>Graduation</option>
              <option>General Inquiries</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="fld-message" class="form-label">Message</label>
            <textarea id="fld-message" name="message" rows="5" class="form-control" required></textarea>
          </div>
          <button class="btn btn-apply btn-lg" type="submit">Send Message</button>
        </form>
      </div>
      <div class="col-lg-6 reveal reveal-1">
        <div class="card-anu h-100">
          <div class="body">
            <h3 class="h4">Reach Us Directly</h3>
            <p><i class="bi bi-geo-alt text-red me-1"></i> <?= h(setting('address')) ?></p>
            <p><i class="bi bi-telephone text-red me-1"></i> <a href="tel:<?= h(setting('phone_primary')) ?>"><?= h(setting('phone_primary')) ?></a> / <a href="tel:<?= h(setting('phone_secondary')) ?>"><?= h(setting('phone_secondary')) ?></a></p>
            <p><i class="bi bi-envelope text-red me-1"></i> <a href="mailto:<?= h(setting('email_marketing')) ?>"><?= h(setting('email_marketing')) ?></a></p>
            <p class="mb-3"><i class="bi bi-envelope text-red me-1"></i> <a href="mailto:<?= h(setting('email_complaints')) ?>"><?= h(setting('email_complaints')) ?></a> (complaints)</p>
            <div class="d-flex flex-wrap gap-2 mb-3">
              <a href="<?= h(setting('whatsapp_url')) ?>" target="_blank" rel="noopener" class="btn btn-outline-anu btn-sm"><i class="bi bi-whatsapp"></i> WhatsApp</a>
              <a href="<?= h(setting('student_portal_url')) ?>" target="_blank" rel="noopener" class="btn btn-outline-anu btn-sm"><i class="bi bi-mortarboard"></i> Student Portal</a>
              <a href="<?= h(setting('apply_url')) ?>" target="_blank" rel="noopener" class="btn btn-outline-anu btn-sm"><i class="bi bi-pencil-square"></i> Apply</a>
            </div>
            <div class="ratio ratio-4x3">
              <iframe title="Map showing Africa Nazarene University" src="https://www.google.com/maps?q=Africa+Nazarene+University&output=embed" class="map-frame" allowfullscreen loading="lazy"></iframe>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
