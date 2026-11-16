<?php
// ════════════════════════════════════════════════════════════════
//  WARNING: this is a one-time setup/maintenance tool with NO
//  access protection. Anyone who finds this URL can run it.
//  DELETE THIS FILE from your server once you're done using it.
// ════════════════════════════════════════════════════════════════

require_once __DIR__ . '/includes/config.php';
$db = getDB();
$results = [];

// Add image column to programs if missing
try {
    $db->exec("ALTER TABLE programs ADD COLUMN image VARCHAR(500) DEFAULT NULL AFTER description");
    $results[] = "✓ Added image column to programs";
} catch(Exception $e) { $results[] = "— programs.image already exists"; }

// Create courses table
try {
    $db->exec("CREATE TABLE IF NOT EXISTS `program_courses` (
        `id` int AUTO_INCREMENT PRIMARY KEY,
        `program_id` int NOT NULL,
        `year_semester` varchar(100) DEFAULT NULL COMMENT 'e.g. Year 1 Sem 1',
        `code` varchar(30) DEFAULT NULL,
        `name` varchar(255) NOT NULL,
        `credit_hours` tinyint DEFAULT 3,
        `sort_order` int DEFAULT 0,
        `is_active` tinyint(1) DEFAULT 1,
        FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $results[] = "✓ Created program_courses table";
} catch(Exception $e) { $results[] = "— program_courses: ".$e->getMessage(); }

foreach($results as $r) echo "<p>$r</p>";
echo "<p><a href='/anu/admin/programs.php'>→ Go to Programs Admin</a></p>";
