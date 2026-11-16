<?php
$pageTitle = 'Site Settings';
require_once __DIR__ . '/includes/auth.php';
require_login();

$groups = [
    'Site Identity' => ['site_name', 'site_tagline', 'mission', 'vision'],
    'Utility Navbar & Portals' => ['main_site_url', 'enaz_url', 'student_portal_url', 'apply_url'],
    'Contact Details' => ['phone_primary', 'phone_secondary', 'email_admissions', 'email_general', 'email_dpo', 'address', 'whatsapp_url', 'google_map_embed'],
    'Chat & Enquiries (HubSpot)' => ['hubspot_portal_id'],
    'Social Media' => ['facebook_url', 'twitter_url', 'instagram_url', 'linkedin_url', 'youtube_url'],
    'Homepage Stats' => ['total_students', 'total_programmes', 'total_trainers', 'years_experience'],
    'Other ANU Schools (Footer Links)' => ['url_school_law', 'url_school_business', 'url_school_scitech'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash_set('danger', 'Session expired, please try again.');
        redirect(ADMIN_URL . '/settings.php');
    }
    $stmt = db()->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
    foreach ($_POST as $key => $value) {
        if ($key === 'csrf_token') continue;
        $stmt->execute([$key, trim($value)]);
    }
    flash_set('success', 'Settings saved successfully.');
    redirect(ADMIN_URL . '/settings.php');
}

$all = [];
foreach (db()->query('SELECT * FROM settings')->fetchAll() as $row) { $all[$row['setting_key']] = $row['setting_value']; }

$labels = [
    'site_name' => 'Site Name', 'site_tagline' => 'Tagline', 'mission' => 'Mission Statement', 'vision' => 'Vision Statement',
    'main_site_url' => 'ANU Main Site URL', 'enaz_url' => 'eNaz Portal URL', 'student_portal_url' => 'Student Portal URL', 'apply_url' => 'External Apply URL (legacy)',
    'phone_primary' => 'Primary Phone', 'phone_secondary' => 'Secondary Phone', 'email_admissions' => 'Admissions Email', 'email_general' => 'General Email', 'email_dpo' => 'Data Protection Officer Email',
    'hubspot_portal_id' => 'HubSpot Hub ID (numbers only; leave empty to turn off)',
    'address' => 'Postal / Physical Address', 'whatsapp_url' => 'WhatsApp Link', 'google_map_embed' => 'Google Maps Embed URL',
    'facebook_url' => 'Facebook URL', 'twitter_url' => 'X (Twitter) URL', 'instagram_url' => 'Instagram URL', 'linkedin_url' => 'LinkedIn URL', 'youtube_url' => 'YouTube URL',
    'total_students' => 'Total Students (stat)', 'total_programmes' => 'Total Programmes (stat)', 'total_trainers' => 'Total Trainers (stat)', 'years_experience' => 'Years of Experience (stat)',
    'url_school_law' => 'School of Law URL', 'url_school_business' => 'School of Business URL', 'url_school_scitech' => 'School of Science & Technology URL',
];
$textareas = ['mission', 'vision', 'address'];
require __DIR__ . '/includes/admin-header.php';
?>
<form method="post">
  <?= csrf_field() ?>
  <?php foreach ($groups as $groupLabel => $keys): ?>
    <div class="admin-card mb-4">
      <div class="admin-card-header"><h2><?= h($groupLabel) ?></h2></div>
      <div class="row g-3">
        <?php foreach ($keys as $key): ?>
          <div class="col-md-6">
            <label class="form-label"><?= h($labels[$key] ?? $key) ?></label>
            <?php if (in_array($key, $textareas, true)): ?>
              <textarea name="<?= h($key) ?>" rows="2" class="form-control"><?= h($all[$key] ?? '') ?></textarea>
            <?php else: ?>
              <input type="text" name="<?= h($key) ?>" class="form-control" value="<?= h($all[$key] ?? '') ?>">
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>
  <button type="submit" class="btn btn-admin-primary btn-lg"><i class="bi bi-check-circle"></i> Save All Settings</button>
</form>
<?php require __DIR__ . '/includes/admin-footer.php'; ?>
