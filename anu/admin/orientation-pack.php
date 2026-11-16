<?php
// Item B (follow-up fixes): admin screen to upload/replace the Orientation
// Pack file (or point it at an external URL instead), stored in the same
// `orientation_pack_url` site_settings row the utility bar / homepage card
// already read. Public side is now /orientation-pack.php, a dedicated
// landing page with a Download button, so the link never dies even before
// a real file is uploaded (see that file for the placeholder state).
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-layout.php';
requireAdmin();
$db = getDB();
$msg = ''; $msgType = 'success';

$current = getSetting('orientation_pack_url', '#');
$isRealLink = $current && $current !== '#';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $externalUrl = trim($_POST['external_url'] ?? '');
    $newValue = null;

    if (!empty($_FILES['pack_file']['name'])) {
        if ($_FILES['pack_file']['size'] > 15 * 1024 * 1024) {
            $msg = 'File must be 15MB or smaller.'; $msgType = 'error';
        } else {
            $uploaded = handleUpload('pack_file', 'orientation', ['pdf']);
            if ($uploaded) { $newValue = '/' . $uploaded; }
            else { $msg = 'Upload failed -- make sure the file is a .pdf.'; $msgType = 'error'; }
        }
    } elseif ($externalUrl) {
        $newValue = $externalUrl;
    }

    if ($newValue && $msgType !== 'error') {
        $exists = $db->prepare("SELECT 1 FROM site_settings WHERE setting_key='orientation_pack_url'");
        $exists->execute();
        if ($exists->fetch()) {
            $db->prepare("UPDATE site_settings SET setting_value=? WHERE setting_key='orientation_pack_url'")->execute([$newValue]);
        } else {
            $db->prepare("INSERT INTO site_settings (setting_key,setting_value,setting_type,label,page) VALUES ('orientation_pack_url',?,'text','Orientation Pack file/URL','global')")->execute([$newValue]);
        }
        $msg = 'Orientation link updated!';
        $current = $newValue;
        $isRealLink = true;
    } elseif ($msgType !== 'error') {
        $msg = 'Choose a file to upload or enter a URL.'; $msgType = 'error';
    }
}

function save_setting_row(PDO $db, string $key, string $value, string $label): void {
    $x = $db->prepare("SELECT 1 FROM site_settings WHERE setting_key=?"); $x->execute([$key]);
    if ($x->fetch()) $db->prepare("UPDATE site_settings SET setting_value=? WHERE setting_key=?")->execute([$value, $key]);
    else $db->prepare("INSERT INTO site_settings (setting_key,setting_value,setting_type,label,page) VALUES (?,?,'text',?,'global')")->execute([$key, $value, $label]);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_meeting'])) {
    $mu = trim($_POST['meeting_url'] ?? '');
    if ($mu !== '' && !preg_match('~^https?://~i', $mu)) { $msg = 'The meeting link must start with http:// or https://'; $msgType = 'error'; }
    else {
        save_setting_row($db, 'orientation_meeting_url', $mu, 'Orientation meeting link');
        save_setting_row($db, 'orientation_meeting_details', trim($_POST['meeting_details'] ?? ''), 'Orientation meeting details');
        $msg = 'Meeting details saved!';
    }
}
$meetUrl = getSetting('orientation_meeting_url', '');
$meetDetails = getSetting('orientation_meeting_details', '');

adminHead('Orientation');
adminSidebar('orientation-pack');
?>
<?php adminTopBar('Orientation', 'The file/link shown in the utility bar and the homepage "Get the Orientation" card'); ?>
<?php if ($msg): ?><div class="alert-anu-<?= $msgType ?> mb-4"><?= e($msg) ?></div><?php endif; ?>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="admin-card">
      <h5 class="mb-3"><i class="bi bi-file-earmark-arrow-down-fill me-2" style="color:var(--primary);"></i>Upload or Link the Pack</h5>
      <form method="POST" enctype="multipart/form-data" class="anu-form">
        <input type="hidden" name="save" value="1">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Upload PDF (recommended, max 15MB)</label>
            <input type="file" name="pack_file" class="form-control" accept="application/pdf">
          </div>
          <div class="col-12" style="text-align:center;color:#999;font-size:.8rem;">— or —</div>
          <div class="col-12">
            <label class="form-label">External URL (e.g. a Google Drive / OneDrive link)</label>
            <input type="url" name="external_url" class="form-control" placeholder="https://...">
          </div>
          <div class="col-12">
            <button type="submit" class="btn btn-primary-anu" style="border-radius:50px;padding:12px 40px;"><i class="bi bi-check-circle me-2"></i>Save</button>
          </div>
        </div>
      </form>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="admin-card">
      <h5 class="mb-3"><i class="bi bi-eye me-2" style="color:var(--primary);"></i>Current Status</h5>
      <?php if ($isRealLink): ?>
        <div class="badge-status badge-active mb-2">Live</div>
        <p style="font-size:.85rem;word-break:break-all;color:#555;"><?= e($current) ?></p>
        <a href="<?= e($current) ?>" target="_blank" class="btn btn-sm btn-outline-secondary" style="border-radius:50px;"><i class="bi bi-box-arrow-up-right me-1"></i>Open it</a>
      <?php else: ?>
        <div class="badge-status badge-inactive mb-2">Not set</div>
        <p class="text-muted" style="font-size:.85rem;">Visitors currently see a friendly "coming soon" page instead of a dead link.</p>
      <?php endif; ?>
      <a href="/anu/orientation-pack.php" target="_blank" class="btn btn-sm btn-outline-secondary mt-2" style="border-radius:50px;"><i class="bi bi-box-arrow-up-right me-1"></i>View Public Page</a>
    </div>
  </div>
</div>

<div class="row g-4 mt-1">
  <div class="col-lg-7">
    <div class="admin-card">
      <h5 class="mb-3"><i class="bi bi-camera-video me-2" style="color:var(--primary);"></i>Orientation Meeting</h5>
      <form method="POST" class="anu-form">
        <input type="hidden" name="save_meeting" value="1">
        <div class="mb-3"><label class="form-label">Meeting link (Zoom, Google Meet or Teams)</label>
          <input type="url" name="meeting_url" class="form-control" value="<?= e($meetUrl) ?>" placeholder="https://..."></div>
        <div class="mb-3"><label class="form-label">Date, time and instructions</label>
          <textarea name="meeting_details" rows="4" class="form-control" placeholder="e.g. Saturday 10:00 AM (EAT). Join five minutes early."><?= e($meetDetails) ?></textarea></div>
        <button type="submit" class="btn btn-primary-anu" style="border-radius:50px;padding:12px 40px;"><i class="bi bi-check-circle me-2"></i>Save meeting details</button>
      </form>
    </div>
  </div>
  <div class="col-lg-5"><div class="admin-card"><p class="text-muted mb-0" style="font-size:.85rem;">The link and details appear in the "Orientation Meeting Details" section of the public Orientation page, with a "Join" button. The Director's welcome on that page comes from Admin &rarr; Team (department "Leadership").</p></div></div>
</div>

<?php adminFoot(); ?>
