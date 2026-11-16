<?php
$pageTitle = 'Apply Online';
require_once __DIR__ . '/includes/functions.php';
// Applications are made on the main ANU portal; old /apply links go straight there.
header('Location: ' . apply_url(), true, 302);
exit;

$programmes = db()->query("SELECT id, title, slug, level FROM programmes WHERE is_published = 1 ORDER BY title")->fetchAll();
$preselectSlug = trim($_GET['programme'] ?? '');

$success = false;
$applicationNo = null;
$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Your session expired. Please resubmit the form.';
    }
    $old = $_POST;

    $full_name  = trim($_POST['full_name'] ?? '');
    $gender     = trim($_POST['gender'] ?? '');
    $dob        = trim($_POST['dob'] ?? '');
    $id_no      = trim($_POST['id_or_birth_cert_no'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $county     = trim($_POST['county'] ?? '');
    $nationality = trim($_POST['nationality'] ?? 'Kenyan');
    $programme_id = (int)($_POST['programme_id'] ?? 0);
    $intake     = trim($_POST['preferred_intake'] ?? '');
    $mode       = trim($_POST['mode_of_study'] ?? '');
    $education  = trim($_POST['education_background'] ?? '');
    $sponsor    = trim($_POST['sponsor'] ?? '');

    if ($full_name === '') $errors[] = 'Full name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please provide a valid email address.';
    if ($phone === '') $errors[] = 'Phone number is required.';
    if ($programme_id <= 0) $errors[] = 'Please select a programme.';
    if ($education === '') $errors[] = 'Please summarise your academic background.';

    if (!$errors) {
        $applicationNo = generate_application_no();
        $stmt = db()->prepare("INSERT INTO applications
            (application_no, full_name, gender, dob, id_or_birth_cert_no, email, phone, county, nationality, programme_id, preferred_intake, mode_of_study, education_background, sponsor)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $applicationNo, $full_name, $gender ?: null, $dob ?: null, $id_no ?: null,
            $email, $phone, $county ?: null, $nationality ?: 'Kenyan', $programme_id,
            $intake ?: null, $mode ?: null, $education, $sponsor ?: null
        ]);
        $success = true;
        $old = [];
    }
}

require __DIR__ . '/includes/header.php';
?>
<section class="tvet-hero compact<?= page_banner_url('apply') ? ' has-image' : '' ?>">
  <?php if ($b = page_banner_url('apply')): ?><img class="hero-bg-image" src="<?= h($b) ?>" alt=""><?php endif; ?>
  <div class="container">
    <div class="eyebrow mb-2">Admissions</div>
    <h1>Apply Online</h1>
    <p class="lead">Complete the form below to begin your application. Our Admissions team will contact you with next steps.</p>
  </div>
</section>

<section class="tvet-section">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-9">

        <?php if ($success): ?>
          <div class="alert alert-success">
            <h4 class="alert-heading"><i class="bi bi-check-circle"></i> Application received!</h4>
            <p class="mb-1">Thank you, your application reference number is <strong><?= h($applicationNo) ?></strong>. Please quote this number in any correspondence with the Admissions Office.</p>
            <p class="mb-0 small">A confirmation has been logged and our team will reach out via the email/phone you provided.</p>
          </div>
        <?php endif; ?>

        <?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= h($e) ?></div><?php endforeach; ?>

        <form method="post" data-validate novalidate class="needs-validation">
          <?= csrf_field() ?>

          <div class="form-section-title">Personal Details</div>
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Full Name *</label><input required type="text" name="full_name" class="form-control" value="<?= h($old['full_name'] ?? '') ?>"></div>
            <div class="col-md-3"><label class="form-label">Gender</label>
              <select name="gender" class="form-select">
                <option value="">Select</option>
                <option <?= (($old['gender'] ?? '') === 'Male') ? 'selected' : '' ?>>Male</option>
                <option <?= (($old['gender'] ?? '') === 'Female') ? 'selected' : '' ?>>Female</option>
              </select>
            </div>
            <div class="col-md-3"><label class="form-label">Date of Birth</label><input type="date" name="dob" class="form-control" value="<?= h($old['dob'] ?? '') ?>"></div>
            <div class="col-md-6"><label class="form-label">National ID / Birth Certificate No.</label><input type="text" name="id_or_birth_cert_no" class="form-control" value="<?= h($old['id_or_birth_cert_no'] ?? '') ?>"></div>
            <div class="col-md-6"><label class="form-label">Nationality</label><input type="text" name="nationality" class="form-control" value="<?= h($old['nationality'] ?? 'Kenyan') ?>"></div>
          </div>

          <div class="form-section-title">Contact Details</div>
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Email Address *</label><input required type="email" name="email" class="form-control" value="<?= h($old['email'] ?? '') ?>"></div>
            <div class="col-md-6"><label class="form-label">Phone Number *</label><input required type="tel" name="phone" class="form-control" placeholder="07XX XXX XXX" value="<?= h($old['phone'] ?? '') ?>"></div>
            <div class="col-md-6"><label class="form-label">County / Region</label><input type="text" name="county" class="form-control" placeholder="e.g. Kajiado, or your home country" value="<?= h($old['county'] ?? '') ?>"></div>
          </div>

          <div class="form-section-title">Programme Choice</div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Programme *</label>
              <select required name="programme_id" class="form-select">
                <option value="">Select a programme</option>
                <?php foreach ($programmes as $p): ?>
                  <option value="<?= (int)$p['id'] ?>" <?= (($old['programme_id'] ?? '') == $p['id'] || (!isset($old['programme_id']) && $preselectSlug === $p['slug'])) ? 'selected' : '' ?>>
                    <?= h($p['title']) ?>, <?= h($p['level']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Preferred Intake</label>
              <select name="preferred_intake" class="form-select">
                <option value="">Select</option>
                <?php foreach (['January','May','August','Rolling'] as $i): ?>
                  <option <?= (($old['preferred_intake'] ?? '') === $i) ? 'selected' : '' ?>><?= $i ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Mode of Study</label>
              <select name="mode_of_study" class="form-select">
                <option value="">Select</option>
                <?php foreach (['Full-Time','Evening','Weekend'] as $m): ?>
                  <option <?= (($old['mode_of_study'] ?? '') === $m) ? 'selected' : '' ?>><?= $m ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-section-title">Academic Background</div>
          <div class="row g-3">
            <div class="col-12"><label class="form-label">Summarise your academic background *</label><textarea required name="education_background" rows="3" class="form-control" placeholder="e.g. KCSE 2024, mean grade C, Mokoyeti Secondary School"><?= h($old['education_background'] ?? '') ?></textarea></div>
            <div class="col-md-6"><label class="form-label">Sponsor (if any)</label><input type="text" name="sponsor" class="form-control" placeholder="Self, parent/guardian, HELB, employer, NGO..." value="<?= h($old['sponsor'] ?? '') ?>"></div>
          </div>

          <div class="form-check mt-4">
            <input class="form-check-input" type="checkbox" required id="declare">
            <label class="form-check-label small" for="declare">I confirm that the information provided is accurate to the best of my knowledge.</label>
          </div>
          <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" required id="consent">
            <label class="form-check-label small" for="consent">I consent to Africa Nazarene University collecting and processing my personal data for admissions purposes, in accordance with the <a href="<?= BASE_URL ?>/privacy-policy.php" target="_blank">Privacy Policy</a> and the Data Protection Act, 2019.</label>
          </div>

          <button class="btn btn-tvet-cta btn-lg mt-4" type="submit"><i class="bi bi-send"></i> Submit Application</button>
        </form>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
