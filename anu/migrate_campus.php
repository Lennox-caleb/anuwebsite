<?php
// ════════════════════════════════════════════════════════════════
//  WARNING: this is a one-time setup/maintenance tool with NO
//  access protection. Anyone who finds this URL can run it.
//  DELETE THIS FILE from your server once you're done using it.
// ════════════════════════════════════════════════════════════════

require_once __DIR__ . '/includes/config.php';
$db = getDB(); $r = [];
try { $db->exec("ALTER TABLE clubs ADD COLUMN category VARCHAR(80) DEFAULT NULL AFTER name"); $r[]="✓ clubs.category added"; }
catch(Exception $e){ $r[]="— clubs.category: already exists"; }
try { $db->exec("ALTER TABLE prayer_requests ADD COLUMN is_private TINYINT(1) DEFAULT 1 AFTER request"); $r[]="✓ prayer_requests.is_private added"; }
catch(Exception $e){ $r[]="— prayer_requests.is_private: already exists"; }
foreach($r as $line) echo "<p>$line</p>";
echo "<p><a href='/anu/campus-life.php'>→ Campus Life</a> | <a href='/anu/chaplaincy.php'>→ Chaplaincy</a> | <a href='/anu/clubs.php'>→ Clubs</a></p>";
