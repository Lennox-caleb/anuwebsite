<?php
$pageTitle = 'Submit a Research Proposal';
require_once __DIR__ . '/config/db.php';

$success = false; $errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name      = trim($_POST['full_name'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $phone          = trim($_POST['phone'] ?? '');
    $research_area  = trim($_POST['research_area'] ?? '');
    $proposal_title = trim($_POST['proposal_title'] ?? '');
    $abstract       = trim($_POST['abstract'] ?? '');

    if ($full_name === '') $errors[] = 'Your full name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please provide a valid email address.';
    if ($proposal_title === '') $errors[] = 'A proposal title is required.';
    if ($abstract === '') $errors[] = 'Please provide an abstract or summary.';

    $attachmentName = null;
    if (!empty($_FILES['attachment']['name'])) {
        $file = $_FILES['attachment'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'The attachment failed to upload. Please try again.';
        } else {
            $allowedExt = ['pdf', 'doc', 'docx'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExt, true)) {
                $errors[] = 'Attachment must be a PDF or Word document.';
            } elseif ($file['size'] > 15 * 1024 * 1024) {
                $errors[] = 'Attachment must be under 15MB.';
            } else {
                $uploadsDir = __DIR__ . '/../main-site/assets/uploads/';
                $attachmentName = 'proposal-' . bin2hex(random_bytes(8)) . '.' . $ext;
                if (!move_uploaded_file($file['tmp_name'], $uploadsDir . $attachmentName)) {
                    $errors[] = 'Could not save the attachment. Please try again.';
                    $attachmentName = null;
                }
            }
        }
    }

    if (!$errors) {
        $stmt = db()->prepare('INSERT INTO research_proposals (full_name, email, phone, research_area, proposal_title, abstract, attachment) VALUES (?,?,?,?,?,?,?)');
        $stmt->execute([$full_name, $email, $phone, $research_area, $proposal_title, $abstract, $attachmentName]);
        $success = true;
    }
}

require __DIR__ . '/includes/header.php';
?>
<section class="res-hero compact">
  <div class="container">
    <div class="eyebrow mb-2">Research</div>
    <h1>Submit a Research Proposal</h1>
  </div>
</section>

<section class="res-section">
  <div class="container container-narrow">
    <?php if ($success): ?>
      <div class="alert alert-success">
        Thank you &mdash; your proposal has been submitted. The Institute of Research will review it and follow up by email.
      </div>
    <?php else: ?>
      <?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= h($e) ?></div><?php endforeach; ?>
      <p class="text-muted">Share a summary of your proposed research. You can optionally attach a fuller document (PDF or Word, up to 15MB).</p>
      <form method="post" enctype="multipart/form-data">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Full Name</label>
            <input type="text" name="full_name" class="form-control" value="<?= h($_POST['full_name'] ?? '') ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= h($_POST['email'] ?? '') ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Phone (optional)</label>
            <input type="text" name="phone" class="form-control" value="<?= h($_POST['phone'] ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Research Area</label>
            <input type="text" name="research_area" class="form-control" placeholder="e.g. Public Health, Theology, Education" value="<?= h($_POST['research_area'] ?? '') ?>">
          </div>
        </div>
        <div class="mb-3 mt-3">
          <label class="form-label">Proposal Title</label>
          <input type="text" name="proposal_title" class="form-control" value="<?= h($_POST['proposal_title'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Abstract / Summary</label>
          <textarea name="abstract" rows="6" class="form-control" required><?= h($_POST['abstract'] ?? '') ?></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">Full Proposal Document (PDF/Word, optional)</label>
          <input type="file" name="attachment" class="form-control" accept=".pdf,.doc,.docx">
        </div>
        <button type="submit" class="btn btn-res-cta btn-lg">Submit Proposal</button>
      </form>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
