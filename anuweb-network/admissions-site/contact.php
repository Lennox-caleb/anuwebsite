<?php
$pageTitle = 'Contact Admissions';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/../main-site/includes/site_hardening.php';
$__hero = site_hero();

$success = false; if (function_exists("anu_was_sent") && anu_was_sent()) { $success = true; }
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $message   = trim($_POST['message'] ?? '');

    if ($full_name === '' || $message === '') $errors[] = 'Name and message are required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please provide a valid email address.';

    if (!$errors) {
        $stmt = db()->prepare('INSERT INTO contact_messages (full_name, email, subject, message, unit) VALUES (?,?,?,?,?)');
        if (anu_contact_should_save($email, $message)) { $stmt->execute([$full_name, $email, 'Admissions enquiry', $message, 'admissions']); }
        anu_prg_redirect();
    }
}

require __DIR__ . '/includes/header.php';
?>
<section class="adm-hero compact<?= $__hero && $__hero['image'] ? ' has-image' : '' ?>"<?= $__hero && $__hero['image'] ? ' style="background-image: linear-gradient(180deg, rgba(10,10,10,.55) 0%, rgba(10,10,10,.72) 100%), url(\'' . h(MAIN_SITE_URL . '/assets/uploads/' . $__hero['image']) . '\')"' : '' ?>>
  <div class="container">
    <div class="eyebrow mb-2">Questions?</div>
    <h1>Talk to Admissions</h1>
  </div>
</section>

<section class="adm-section">
  <div class="container">
    <div class="row g-4 g-lg-5">
      <div class="col-lg-6">
        <?php if ($success): ?>
          <div class="alert alert-success">Thank you, your message has been sent to the Admissions office.</div>
        <?php endif; ?>
        <?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= h($e) ?></div><?php endforeach; ?>
        <form method="post">
          <div style="position:absolute;left:-9999px;" aria-hidden="true"><label>Leave this empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
          <div class="mb-3"><label for="fld-full_name" class="form-label">Full Name</label><input id="fld-full_name" type="text" name="full_name" class="form-control" required></div>
          <div class="mb-3"><label for="fld-email" class="form-label">Email</label><input id="fld-email" type="email" name="email" class="form-control" required></div>
          <div class="mb-3"><label for="fld-message" class="form-label">Message</label><textarea id="fld-message" name="message" rows="5" class="form-control" required></textarea></div>
          <button class="btn btn-adm-cta btn-lg" type="submit">Send Message</button>
        </form>
      </div>
      <div class="col-lg-6">
        <div class="adm-req-row no-top">
          <i class="bi bi-telephone"></i>
          <div><strong><?= h(main_setting('phone_primary')) ?></strong></div>
        </div>
        <div class="adm-req-row">
          <i class="bi bi-envelope"></i>
          <div><strong><?= h(main_setting('email_admissions')) ?></strong></div>
        </div>
        <div class="adm-req-row">
          <i class="bi bi-geo-alt"></i>
          <div><?= h(main_setting('address')) ?></div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
