<?php
// Item C (follow-up fixes): a dedicated, focused Director's Message admin
// screen -- photo upload with preview, name, title, and the full message
// (the homepage/full-page excerpt is auto-derived from this by
// renderDirectorMessage(), same logic as before). Backed by the existing
// `staff` table (one row with department='Leadership'), so no schema
// change is needed and the public-facing rendering logic is untouched.
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-layout.php';
requireAdmin();
$db = getDB();
$msg = ''; $msgType = 'success';

// Find the existing Director row, if any.
$dir = $db->query("SELECT * FROM staff WHERE department='Leadership' ORDER BY id ASC LIMIT 1")->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $name  = trim($_POST['name'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $bio   = trim($_POST['bio'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (!$name) {
        $msg = 'Name is required.'; $msgType = 'error';
    } else {
        $photoPath = null;
        if (!empty($_FILES['photo']['name'])) {
            // JPG/PNG/WebP, max 2MB per the spec.
            if ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
                $msg = 'Photo must be 2MB or smaller.'; $msgType = 'error';
            } else {
                $photoPath = handleUpload('photo', 'director', ['jpg', 'jpeg', 'png', 'webp']);
                if (!$photoPath) { $msg = 'Photo upload failed -- check the file is a JPG/PNG/WebP under 2MB.'; $msgType = 'error'; }
            }
        }
        if ($msgType !== 'error') {
            if ($dir) {
                if (!$photoPath) $photoPath = $dir['photo']; // keep existing photo if none uploaded
                $db->prepare("UPDATE staff SET name=?, title=?, bio=?, email=?, photo=?, department='Leadership' WHERE id=?")
                   ->execute([$name, $title, $bio, $email, $photoPath, $dir['id']]);
                $msg = "Director's Message updated!";
            } else {
                $db->prepare("INSERT INTO staff (name, title, department, bio, email, photo, is_featured, sort_order) VALUES (?,?, 'Leadership', ?, ?, ?, 1, 0)")
                   ->execute([$name, $title, $bio, $email, $photoPath]);
                $msg = "Director's Message added!";
            }
            $dir = $db->query("SELECT * FROM staff WHERE department='Leadership' ORDER BY id ASC LIMIT 1")->fetch();
        }
    }
}

$words = $dir ? preg_split('/\s+/', trim((string)($dir['bio'] ?? '')), -1, PREG_SPLIT_NO_EMPTY) : [];
$excerptPreview = $words ? (count($words) > 80 ? implode(' ', array_slice($words, 0, 80)) . '…' : implode(' ', $words)) : '';

adminHead("Director's Message");
adminSidebar('director-message');
?>
<?php adminTopBar("Director's Message", 'Shown on the homepage before "Your Journey Starts" and on the full message page'); ?>
<?php if ($msg): ?><div class="alert-anu-<?= $msgType ?> mb-4"><?= e($msg) ?></div><?php endif; ?>

<?php if (!$dir): ?>
<div class="alert-anu-error mb-4"><i class="bi bi-exclamation-triangle-fill me-2"></i>No Director's Message set yet -- the homepage is currently showing a placeholder with a TODO badge. Fill in the form below to replace it.</div>
<?php endif; ?>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="admin-card">
      <h5 class="mb-3"><i class="bi bi-pencil-square me-2" style="color:var(--primary);"></i><?= $dir ? 'Edit' : 'Add' ?> Director's Message</h5>
      <form method="POST" enctype="multipart/form-data" class="anu-form">
        <input type="hidden" name="save" value="1">
        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label">Full Name *</label>
            <input type="text" name="name" class="form-control" required value="<?= e($dir['name'] ?? '') ?>" placeholder="e.g. Dr. Jane Wanjiru">
          </div>
          <div class="col-md-4">
            <label class="form-label">Email (optional)</label>
            <input type="email" name="email" class="form-control" value="<?= e($dir['email'] ?? '') ?>">
          </div>
          <div class="col-12">
            <label class="form-label">Title *</label>
            <input type="text" name="title" class="form-control" value="<?= e($dir['title'] ?? '') ?>" placeholder="e.g. Director, ANU Online">
          </div>
          <div class="col-12">
            <label class="form-label">Full Message *</label>
            <textarea name="bio" class="form-control" rows="10" placeholder="Write the Director's full welcome message here. The first ~80 words are shown automatically as the homepage excerpt; the full text appears on the Read Full Message page."><?= e($dir['bio'] ?? '') ?></textarea>
            <div class="form-text">Word count updates the homepage excerpt automatically -- no separate excerpt field needed.</div>
          </div>
          <div class="col-md-6">
            <label class="form-label">Photo (JPG/PNG/WebP, max 2MB)</label>
            <div class="img-upload-wrap" onclick="document.getElementById('dirPhoto').click()">
              <?php if (!empty($dir['photo'])): ?>
                <img src="/<?= e($dir['photo']) ?>" alt="Current photo" style="width:100%;height:160px;object-fit:cover">
              <?php else: ?>
                <div class="img-placeholder"><i class="bi bi-person-circle"></i><span>Upload photo</span></div>
              <?php endif; ?>
              <div class="img-upload-overlay"><i class="bi bi-camera" style="font-size:1.5rem;"></i></div>
            </div>
            <input type="file" name="photo" id="dirPhoto" class="img-upload-input" style="display:none;" accept="image/jpeg,image/png,image/webp">
            <div class="form-text">Leave empty to keep the current photo.</div>
          </div>
          <div class="col-12">
            <button type="submit" class="btn btn-primary-anu" style="border-radius:50px;padding:12px 40px;"><i class="bi bi-check-circle me-2"></i>Save Director's Message</button>
          </div>
        </div>
      </form>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="admin-card">
      <h5 class="mb-3"><i class="bi bi-eye me-2" style="color:var(--primary);"></i>Homepage Preview</h5>
      <div style="background:#f8f8f8;border-radius:14px;padding:20px;">
        <?php if ($dir): ?>
          <?php if (!empty($dir['photo'])): ?>
            <img src="/<?= e($dir['photo']) ?>" style="width:72px;height:72px;border-radius:50%;object-fit:cover;margin-bottom:12px;">
          <?php endif; ?>
          <div style="font-weight:700;"><?= e($dir['name']) ?></div>
          <div style="color:#888;font-size:.85rem;margin-bottom:10px;"><?= e($dir['title']) ?></div>
          <blockquote style="font-size:.88rem;color:#555;font-style:italic;">"<?= e($excerptPreview ?: 'No message text yet.') ?>"</blockquote>
        <?php else: ?>
          <p class="text-muted mb-0">Nothing saved yet -- the public site shows a placeholder.</p>
        <?php endif; ?>
      </div>
      <a href="/anu/directors-message.php" target="_blank" class="btn btn-sm btn-outline-secondary mt-3" style="border-radius:50px;"><i class="bi bi-box-arrow-up-right me-1"></i>View Full Message Page</a>
    </div>
  </div>
</div>

<?php adminFoot(); ?>
