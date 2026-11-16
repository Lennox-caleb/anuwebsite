<?php
/**
 * JSON endpoint for the instant search overlay in the header (see
 * assets/js/main.js and .search-overlay in style.css). Called on every
 * keystroke (debounced client-side), so results are capped small per
 * category -- the overlay always offers a "See all results" link to
 * search.php for the complete list. Uses the exact same anu_search()
 * function as the full search page, so results never disagree.
 */
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/search_logic.php';

header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');
if (mb_strlen($q) < 2) {
    echo json_encode(['q' => $q, 'total' => 0, 'items' => []]);
    exit;
}

$results = anu_search($q, 4);
$items = [];

foreach ($results['pages'] as $r) {
    $items[] = ['type' => 'Page', 'icon' => $r['icon'], 'title' => $r['title'], 'desc' => $r['desc'], 'url' => BASE_URL . '/' . $r['url']];
}
foreach ($results['schools'] as $r) {
    $items[] = ['type' => 'School', 'icon' => 'bi-bank', 'title' => $r['name'], 'desc' => $r['summary'] ? excerpt($r['summary'], 90) : '', 'url' => BASE_URL . '/schools/' . urlencode($r['slug'])];
}
foreach ($results['programmes'] as $r) {
    $items[] = ['type' => $r['level'] ?: 'Programme', 'icon' => 'bi-mortarboard', 'title' => $r['title'], 'desc' => $r['overview'] ? excerpt($r['overview'], 90) : '', 'url' => BASE_URL . '/program-single.php?slug=' . urlencode($r['slug'])];
}
foreach ($results['news'] as $r) {
    $items[] = ['type' => 'News', 'icon' => 'bi-newspaper', 'title' => $r['title'], 'desc' => $r['excerpt'] ? excerpt($r['excerpt'], 90) : '', 'url' => BASE_URL . '/news-single.php?slug=' . urlencode($r['slug'])];
}
foreach ($results['events'] as $r) {
    $items[] = ['type' => 'Event', 'icon' => 'bi-calendar-event', 'title' => $r['title'], 'desc' => $r['location'] ?: '', 'url' => BASE_URL . '/events.php#' . urlencode($r['slug'])];
}
foreach ($results['resources'] as $r) {
    $items[] = ['type' => $r['category'] ?: 'Resource', 'icon' => 'bi-file-earmark-text', 'title' => $r['title'], 'desc' => $r['description'] ? excerpt($r['description'], 90) : '', 'url' => BASE_URL . '/resources.php'];
}
foreach ($results['vacancies'] as $r) {
    $items[] = ['type' => 'Vacancy', 'icon' => 'bi-briefcase', 'title' => $r['title'], 'desc' => $r['department'] ?: '', 'url' => BASE_URL . '/vacancy-single.php?slug=' . urlencode($r['slug'])];
}
foreach ($results['people'] as $r) {
    $items[] = ['type' => 'Person', 'icon' => 'bi-person-badge', 'title' => $r['full_name'], 'desc' => public_text($r['title']), 'url' => BASE_URL . '/search.php?q=' . urlencode($r['full_name'])];
}

// Addresses without .php
$__clean = function ($u) { return preg_replace('~\.php(?=[?#]|$)~', '', preg_replace('~/index\.php(?=[?#]|$)~', '/', (string)$u)); };
foreach ($items as &$__it) { if (isset($__it['url'])) $__it['url'] = $__clean($__it['url']); } unset($__it);
echo json_encode(['q' => $q, 'total' => count($items), 'items' => $items, 'seeAllUrl' => $__clean(BASE_URL . '/search.php?q=') . urlencode($q)]);
