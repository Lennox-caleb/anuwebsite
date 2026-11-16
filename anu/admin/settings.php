<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-layout.php';
requireAdmin();

$db  = getDB();
$msg = '';

// Save settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
  $settings = $_POST['settings'] ?? [];
  foreach ($settings as $key => $value) {
    $db->prepare("UPDATE site_settings SET setting_value=? WHERE setting_key=?")->execute([trim($value), $key]);
  }
  $msg = 'Settings saved successfully!';
}

$allSettings = $db->query("SELECT * FROM site_settings ORDER BY page, label")->fetchAll();

// Group by page
$grouped = [];
foreach ($allSettings as $s) {
  $grouped[$s['page']][] = $s;
}

adminHead('Site Settings');
adminSidebar('settings');
?>

<?php adminTopBar('Site Settings', 'Configure global site options'); ?>

<?php if ($msg): ?>
  <div class="alert-anu-success mb-4"><i class="bi bi-check-circle-fill me-2"></i><?= e($msg) ?></div>
  <?php endif; ?>

  <form method="POST" class="anu-form">
    <input type="hidden" name="save_settings" value="1">
    
    <?php foreach ($grouped as $page => $settings): ?>
    <div class="admin-card mb-4">
      <h5><i class="bi bi-sliders me-2" style="color:var(--primary);"></i><?= ucwords($page) ?> Settings</h5>
      <div class="row g-3">
        <?php foreach ($settings as $s): ?>
        <div class="<?= $s['setting_type']==='textarea' ? 'col-12' : 'col-md-6' ?>">
          <label class="form-label"><?= e($s['label']) ?></label>
          
          <?php if ($s['setting_type'] === 'textarea'): ?>
          <textarea name="settings[<?= e($s['setting_key']) ?>]" class="form-control" rows="3"><?= e($s['setting_value']) ?></textarea>
          
          <?php elseif ($s['setting_type'] === 'color'): ?>
          <div style="display:flex;gap:10px;align-items:center;">
            <input type="color" value="<?= e($s['setting_value']) ?>" 
                   onchange="document.getElementById('txt_<?= $s['setting_key'] ?>').value=this.value"
                   style="width:50px;height:40px;border:none;border-radius:8px;cursor:pointer;padding:2px;">
            <input type="text" id="txt_<?= $s['setting_key'] ?>" 
                   name="settings[<?= e($s['setting_key']) ?>]" 
                   class="form-control" value="<?= e($s['setting_value']) ?>">
          </div>
          
          <?php elseif ($s['setting_type'] === 'boolean'): ?>
          <div class="form-check form-switch mt-2">
            <input type="checkbox" class="form-check-input" name="settings[<?= e($s['setting_key']) ?>]" 
                   value="1" <?= $s['setting_value'] ? 'checked' : '' ?>>
          </div>
          
          <?php else: ?>
          <input type="text" name="settings[<?= e($s['setting_key']) ?>]" 
                 class="form-control" value="<?= e($s['setting_value']) ?>">
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
    
    <div class="d-flex gap-3">
      <button type="submit" class="btn btn-primary-anu" style="border-radius:50px;padding:12px 40px;">
        <i class="bi bi-check-circle me-2"></i>Save All Settings
      </button>
      <a href="/anu/index.php" target="_blank" class="btn btn-outline-secondary" style="border-radius:50px;padding:12px 24px;font-size:0.9rem;">
        <i class="bi bi-eye me-1"></i>Preview Site
      </a>
    </div>
  </form>
</div>

<?php adminFoot(); ?>