<?php
/**
 * Phase 7b -- Admissions Enquiries form. "Wired to settings email" per
 * this codebase's own established pattern (see contact.php and the old
 * admissions-site/contact.php): the message is stored in the shared
 * `contact_messages` table tagged unit='admissions', and the office
 * email/phone shown alongside it come live from the `settings` table
 * (email_admissions / phone_primary) -- there is no outbound SMTP
 * mailer anywhere in this codebase to wire a form to instead, so this
 * matches the same safe, already-working mechanism every other contact
 * form on this site uses, rather than inventing a new one.
 */
$pageTitle = 'Admissions Enquiries';
$pageDescription = 'Ask the Admissions office a question about applying to Africa Nazarene University.';
require_once __DIR__ . '/includes/functions.php';

$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = csrf_error_message();
    } else {
        $full_name = trim($_POST['full_name'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $message   = trim($_POST['message'] ?? '');

        if ($full_name === '' || $message === '') $errors[] = 'Name and message are required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please provide a valid email address.';

        if (!$errors) {
            $stmt = db()->prepare('INSERT INTO contact_messages (full_name, email, subject, message, unit) VALUES (?,?,?,?,?)');
            $stmt->execute([$full_name, $email, 'Admissions enquiry', $message, 'admissions']);
            $success = true;
        }
    }
}

require __DIR__ . '/includes/header.php';
?>
<section class="about-sub-hero">
  <div class="container container-narrow text-center">
    <span class="hero-eyebrow d-inline-block mb-2">Questions?</span>
    <h1>Talk to Admissions</h1>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="row g-4 g-lg-5">
      <div class="col-lg-6">
        <?php if ($success): ?>
          <div class="alert alert-success">Thank you, your message has been sent to the Admissions office.</div>
        <?php endif; ?>
        <?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= h($e) ?></div><?php endforeach; ?>
        <form method="post" novalidate>
          <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
          <div class="mb-3"><label for="fld-full_name" class="form-label">Full Name</label><input id="fld-full_name" type="text" name="full_name" class="form-control" required></div>
          <div class="mb-3"><label for="fld-email" class="form-label">Email</label><input id="fld-email" type="email" name="email" class="form-control" required></div>
          <div class="mb-3"><label for="fld-message" class="form-label">Message</label><textarea id="fld-message" name="message" rows="5" class="form-control" required></textarea></div>
          <button class="btn btn-apply btn-lg" type="submit">Send Message</button>
        </form>
      </div>
      <div class="col-lg-6">
        <div class="card-anu h-100"><div class="body">
          <h3 class="h5 mb-3">Reach Admissions Directly</h3>
          <div class="program-fact"><span class="program-fact-icon"><i class="bi bi-envelope"></i></span><span><strong>Email</strong><span><?= h(setting('email_admissions')) ?></span></span></div>
          <div class="program-fact"><span class="program-fact-icon"><i class="bi bi-telephone"></i></span><span><strong>Phone</strong><span><?= h(setting('phone_primary')) ?></span></span></div>
          <div class="program-fact"><span class="program-fact-icon"><i class="bi bi-geo-alt"></i></span><span><strong>Address</strong><span><?= h(setting('address')) ?></span></span></div>
          <a href="<?= BASE_URL ?>/admissions.php" class="btn btn-outline-anu w-100 mt-2">&larr; Back to Admissions</a>
        </div></div>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
