<?php
require_once __DIR__ . '/includes/functions.php';
header('Content-Type: application/xml; charset=utf-8');
// Addresses in the sitemap are written without .php (index -> the site root).
ob_start(function ($xml) {
    return preg_replace_callback('~<loc>(.*?)</loc>~s', function ($m) {
        $u = preg_replace('~\.php(?=[?#]|$)~', '', $m[1]);
        $u = preg_replace('~/index(?=[?#]|$)~', '/', $u);
        return '<loc>' . $u . '</loc>';
    }, $xml);
});

$base = 'https://www.anu.ac.ke';
$staticPages = ['/index.php','/about.php','/who-we-are.php','/affiliation.php','/our-history.php','/schools.php','/programs.php','/news.php','/events.php','/contact.php','/governance.php','/resources.php','/tenders.php','/aspire-magazine.php','/anu-voice.php','/graduation-list.php','/life-at-anu.php','/financial-aid.php','/accreditation.php','/privacy.php','/data-protection.php','/terms.php','/csr.php'];

$schools = db()->query("SELECT slug FROM schools WHERE is_published=1")->fetchAll();
$programmes = db()->query("SELECT slug FROM programmes WHERE is_published=1")->fetchAll();
$news = db()->query("SELECT slug, published_at FROM news WHERE is_published=1")->fetchAll();

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

foreach ($staticPages as $p) {
    echo "  <url><loc>{$base}{$p}</loc></url>\n";
}
foreach ($schools as $s) {
    echo "  <url><loc>{$base}/schools/" . urlencode($s['slug']) . "</loc></url>\n";
}
foreach ($programmes as $p) {
    echo "  <url><loc>{$base}/program-single.php?slug=" . urlencode($p['slug']) . "</loc></url>\n";
}
foreach ($news as $n) {
    echo "  <url><loc>{$base}/news-single.php?slug=" . urlencode($n['slug']) . "</loc><lastmod>" . date('Y-m-d', strtotime($n['published_at'])) . "</lastmod></url>\n";
}

echo '</urlset>';
