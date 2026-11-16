<?php
// ════════════════════════════════════════════════════════════════
//  WARNING: this is a one-time setup/maintenance tool with NO
//  access protection. Anyone who finds this URL can run it.
//  DELETE THIS FILE from your server once you're done using it.
// ════════════════════════════════════════════════════════════════

require_once __DIR__ . '/includes/config.php';
$db = getDB();

// Add hero_slides table
$db->exec("CREATE TABLE IF NOT EXISTS `hero_slides` (
    `id` int AUTO_INCREMENT PRIMARY KEY,
    `page_slug` varchar(100) NOT NULL DEFAULT 'home',
    `title` varchar(255),
    `subtitle` text,
    `cta_text` varchar(100),
    `cta_link` varchar(255),
    `cta2_text` varchar(100),
    `cta2_link` varchar(255),
    `bg_image` varchar(500),
    `overlay_color` varchar(30) DEFAULT 'rgba(10,20,60,0.62)',
    `badge_text` varchar(150),
    `sort_order` int DEFAULT 0,
    `is_active` tinyint(1) DEFAULT 1,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Seed 3 home slides
$slides = [
    ['home','Transform Your Future with ANU Online','Study from anywhere in the world. Accredited programs designed for the modern era.','Explore Programs','/anu/programs.php','Apply Now','https://records.anu.ac.ke/wStartUpCreateUserNamePassword.jsp','','rgba(10,20,60,0.65)','Accredited · Flexible · Faith-Centered',1],
    ['home','50+ Programs. One Flexible Platform.','From Business to Theology to Computer Science — find your calling with ANU Online.','View Programs','/anu/programs.php','Learn More','#why','','rgba(100,0,0,0.6)','Nazarene Education Worldwide',2],
    ['home','Faith-Centered. Career-Ready.','Join 5,000+ students from 15 countries building careers grounded in Christian values.','Join Us Today','https://records.anu.ac.ke/wStartUpCreateUserNamePassword.jsp','Our Story','#why','','rgba(0,60,30,0.65)','25 Years of Excellence',3],
];

$stmt = $db->prepare("INSERT IGNORE INTO hero_slides (page_slug,title,subtitle,cta_text,cta_link,cta2_text,cta2_link,bg_image,overlay_color,badge_text,sort_order) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
foreach($slides as $s) $stmt->execute($s);

echo "✅ hero_slides table created and seeded. <a href='/anu/index.php'>View site</a>";
