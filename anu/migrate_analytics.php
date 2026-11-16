<?php
// ════════════════════════════════════════════════════════════════
//  WARNING: this is a one-time setup/maintenance tool with NO
//  access protection. Anyone who finds this URL can run it.
//  DELETE THIS FILE from your server once you're done using it.
// ════════════════════════════════════════════════════════════════

require_once __DIR__ . '/includes/config.php';
$db = getDB();

$tables = [

'page_views' => "CREATE TABLE IF NOT EXISTS `page_views` (
  `id`         BIGINT AUTO_INCREMENT PRIMARY KEY,
  `page`       VARCHAR(100) NOT NULL,
  `page_label` VARCHAR(150) DEFAULT NULL,
  `ref_id`     INT DEFAULT NULL COMMENT 'e.g. news article ID, program ID',
  `ip`         VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(300) DEFAULT NULL,
  `referrer`   VARCHAR(500) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_page (page),
  INDEX idx_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

'event_rsvp' => "CREATE TABLE IF NOT EXISTS `event_rsvp` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `event_id`   INT NOT NULL,
  `name`       VARCHAR(200) NOT NULL,
  `email`      VARCHAR(200) NOT NULL,
  `phone`      VARCHAR(30) DEFAULT NULL,
  `message`    TEXT DEFAULT NULL,
  `status`     ENUM('confirmed','attended','cancelled') DEFAULT 'confirmed',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_event (event_id),
  INDEX idx_email (email),
  UNIQUE KEY uniq_rsvp (event_id, email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

];

// Seed WhatsApp number setting
$settings = [
    ['whatsapp_number', '+254714785249', 'text', 'WhatsApp Number (with country code)', 'global'],
    ['ga4_id',          '',              'text', 'Google Analytics 4 Measurement ID',   'global'],
];

$ok = []; $fail = [];
foreach ($tables as $name => $sql) {
    try { $db->exec($sql); $ok[] = "✓ Table <strong>$name</strong> created"; }
    catch(Exception $e) { $fail[] = "✗ $name: ".$e->getMessage(); }
}
foreach ($settings as [$key,$val,$type,$label,$page]) {
    try {
        $db->prepare("INSERT INTO site_settings (setting_key,setting_value,setting_type,label,page)
                      VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE label=VALUES(label)")
           ->execute([$key,$val,$type,$label,$page]);
        $ok[] = "✓ Setting <strong>$key</strong> added";
    } catch(Exception $e) { $fail[] = "✗ $key: ".$e->getMessage(); }
}
?>
<!DOCTYPE html><html><head><title>Analytics Migration</title>
<style>
body{font-family:sans-serif;max-width:640px;margin:60px auto;padding:20px}
.ok{color:#2e7d32;margin:6px 0}.err{color:#c00;margin:6px 0}
h2{color:#cc0000;margin-bottom:20px}
.btn{display:inline-block;margin:6px 6px 0 0;padding:10px 24px;background:#cc0000;color:#fff;border-radius:50px;text-decoration:none;font-weight:700;font-size:.85rem}
.btn2{background:#1e1e2e}
</style></head><body>
<h2>Analytics & RSVP Migration</h2>
<?php foreach($ok   as $l) echo "<p class='ok'>$l</p>"; ?>
<?php foreach($fail as $l) echo "<p class='err'>".htmlspecialchars($l)."</p>"; ?>
<p style="margin-top:20px;color:#666;font-size:.85rem">Delete this file after running.</p>
<a href="/anu/admin/reports.php" class="btn">View Reports →</a>
<a href="/anu/admin/settings.php" class="btn btn2">Settings →</a>
</body></html>
