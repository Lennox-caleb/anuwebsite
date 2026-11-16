<?php
// ════════════════════════════════════════════════════════════════
//  WARNING: this is a one-time setup/maintenance tool with NO
//  access protection. Anyone who finds this URL can run it.
//  DELETE THIS FILE from your server once you're done using it.
// ════════════════════════════════════════════════════════════════

require_once __DIR__ . '/includes/config.php';
$db = getDB(); $r = [];

// Tutorials / resource library table
try {
    $db->exec("CREATE TABLE IF NOT EXISTS `support_resources` (
        `id` int AUTO_INCREMENT PRIMARY KEY,
        `category` varchar(100) NOT NULL DEFAULT 'General',
        `type` enum('video','pdf','link','article') DEFAULT 'video',
        `title` varchar(255) NOT NULL,
        `description` text,
        `thumbnail` varchar(500) DEFAULT NULL,
        `url` varchar(1000) NOT NULL COMMENT 'YouTube URL, PDF path, or external link',
        `duration` varchar(30) DEFAULT NULL COMMENT 'e.g. 4:32 or 12 pages',
        `audience` enum('all','students','staff','new') DEFAULT 'all',
        `sort_order` int DEFAULT 0,
        `views` int DEFAULT 0,
        `is_featured` tinyint(1) DEFAULT 0,
        `is_active` tinyint(1) DEFAULT 1,
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $r[] = "✓ support_resources table created";
} catch(Exception $e){ $r[] = "— support_resources: ".$e->getMessage(); }

// Seed sample resources
$seeds = [
    ['Getting Started','video','How to Log Into eNaz','Step-by-step guide to accessing your eNaz account for the first time.',null,'https://www.youtube.com/watch?v=dQw4w9WgXcQ','3:45','new',1,1],
    ['Getting Started','video','Navigating Your Dashboard','A full walkthrough of the student dashboard — find your courses, grades, and messages.',null,'https://www.youtube.com/watch?v=dQw4w9WgXcQ','5:12','new',2,1],
    ['Academic','video','Submitting Assignments Online','How to upload and submit assignments through the eNaz LMS before the deadline.',null,'https://www.youtube.com/watch?v=dQw4w9WgXcQ','4:30','students',3,1],
    ['Academic','pdf','Student Academic Calendar 2025','Full trimester dates, exam periods, and submission deadlines for 2025.',null,'#','12 pages','all',4,1],
    ['Finance','video','How to Pay Fees via M-Pesa','Complete guide to paying your tuition fees using M-Pesa Paybill.',null,'https://www.youtube.com/watch?v=dQw4w9WgXcQ','3:20','students',5,1],
    ['Finance','link','HELB Portal — Apply for Funding','Access the HELB student portal to apply for or manage your education loan.',null,'https://www.helb.co.ke','External','students',6,0],
    ['Technical','video','Troubleshooting Login Issues','What to do if you cannot log in — password reset, browser tips, and who to call.',null,'https://www.youtube.com/watch?v=dQw4w9WgXcQ','2:55','all',7,1],
    ['Library','video','Accessing Digital Journals','How to search and access full-text research journals from the ANU online library.',null,'https://www.youtube.com/watch?v=dQw4w9WgXcQ','6:10','students',8,1],
];
foreach($seeds as $s){
    try {
        $db->prepare("INSERT IGNORE INTO support_resources (category,type,title,description,thumbnail,url,duration,audience,sort_order,is_active) VALUES (?,?,?,?,?,?,?,?,?,?)")
           ->execute($s);
    } catch(Exception $e){}
}
$r[] = "✓ Sample resources seeded";

foreach($r as $line) echo "<p>$line</p>";
echo "<p><b>Done!</b> <a href='/anu/support.php'>→ Support Page</a> | <a href='/anu/admin/support_resources.php'>→ Admin Resources</a></p>";
