<?php
// ════════════════════════════════════════════════════════════════
//  WARNING: this is a one-time setup/maintenance tool with NO
//  access protection. Anyone who finds this URL can run it.
//  DELETE THIS FILE from your server once you're done using it.
// ════════════════════════════════════════════════════════════════

require_once __DIR__ . '/includes/config.php';
$db = getDB();

$rows = [
    ['portal_url', 'https://records.anu.ac.ke/', 'text', 'Student Portal URL (records)', 'global'],
    ['enaz_url',   'https://enaz.anu.ac.ke',    'text', 'eNaz LMS URL',                 'global'],
    ['apply_url',  'https://records.anu.ac.ke/wStartUpCreateUserNamePassword.jsp', 'text', 'Apply Now URL', 'global'],
];

$ok = [];
foreach ($rows as [$key, $val, $type, $label, $page]) {
    try {
        $db->prepare("INSERT INTO site_settings (setting_key,setting_value,setting_type,label,page)
                      VALUES (?,?,?,?,?)
                      ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value), label=VALUES(label)")
           ->execute([$key, $val, $type, $label, $page]);
        $ok[] = "✓ $key → $val";
    } catch(Exception $e) {
        $ok[] = "✗ $key: ".$e->getMessage();
    }
}
?>
<!DOCTYPE html><html><head><title>Portal URL Migration</title>
<style>body{font-family:sans-serif;max-width:600px;margin:60px auto;padding:20px}
.ok{color:#2e7d32}.err{color:#c00}
.btn{display:inline-block;margin-top:20px;padding:10px 24px;background:#ff0000;color:#fff;border-radius:50px;text-decoration:none;font-weight:700}</style>
</head><body>
<h2>Portal URL Migration</h2>
<?php foreach($ok as $line): ?>
<p class="<?= str_starts_with($line,'✓')?'ok':'err' ?>"><?= htmlspecialchars($line) ?></p>
<?php endforeach; ?>
<p style="margin-top:20px;color:#666">You can now manage these URLs from <strong>Admin → Settings</strong>.<br>Delete this file after running.</p>
<a href="/anu/admin/settings.php" class="btn">Go to Settings →</a>
</body></html>
