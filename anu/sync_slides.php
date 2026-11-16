<?php
require_once __DIR__ . '/includes/config.php';
$db = getDB();
$db->exec("CREATE TABLE IF NOT EXISTS `hero_slides` (
    `id` int AUTO_INCREMENT PRIMARY KEY,`page_slug` varchar(100) NOT NULL DEFAULT 'home',
    `title` varchar(255),`subtitle` text,`cta_text` varchar(100),`cta_link` varchar(255),
    `cta2_text` varchar(100),`cta2_link` varchar(255),`bg_image` varchar(500),
    `overlay_color` varchar(30) DEFAULT 'rgba(10,20,60,0.72)',`badge_text` varchar(150),
    `sort_order` int DEFAULT 0,`is_active` tinyint(1) DEFAULT 1,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$sections = $db->query("SELECT * FROM hero_sections")->fetchAll();
$n=0;
foreach($sections as $s){
    $c=$db->prepare("SELECT COUNT(*) FROM hero_slides WHERE page_slug=?");$c->execute([$s['page_slug']]);
    if($c->fetchColumn()==0){
        $db->prepare("INSERT INTO hero_slides(page_slug,title,subtitle,cta_text,cta_link,cta2_text,cta2_link,bg_image,overlay_color,badge_text,sort_order,is_active) VALUES(?,?,?,?,?,?,?,?,?,?,0,1)")
           ->execute([$s['page_slug'],$s['title'],$s['subtitle'],$s['cta_text'],$s['cta_link'],$s['cta2_text']??'',$s['cta2_link']??'',$s['bg_image']??'',$s['overlay_color']??'rgba(10,20,60,0.72)','']);
        $n++;
    }
}
$total=$db->query("SELECT COUNT(*) FROM hero_slides")->fetchColumn();
echo "<p>✅ Table ready. Synced {$n} new slides. Total: {$total}</p>";
echo "<p><a href='/anu/index.php'>→ View Homepage</a> | <a href='/anu/admin/slides.php'>→ Manage Slides</a></p>";
