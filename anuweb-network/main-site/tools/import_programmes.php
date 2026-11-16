#!/usr/bin/env php
<?php
/**
 * Re-runnable programme importer — pulls real programme data from
 * anu.ac.ke into the `programmes` table.
 *
 * Usage (run from the live server, as the web/deploy user, from the
 * main-site/ directory or anywhere — paths below are absolute to this
 * file):
 *
 *   php tools/import_programmes.php --dry-run
 *   php tools/import_programmes.php --dry-run --only=master-of-business-administration
 *   php tools/import_programmes.php
 *   php tools/import_programmes.php --only=bachelor-of-commerce
 *
 * --dry-run   Fetch and parse everything, print what WOULD change, write
 *             nothing to the database. Always try this first.
 * --only=X    Limit to one programme by its ANU source slug (the last
 *             path segment of https://www.anu.ac.ke/programs/X/). Repeat
 *             the flag to do more than one, or comma-separate.
 * --debug     Dump the parsed fields for each programme as they're found,
 *             not just a summary line. Use with --only on one slug the
 *             first time you run this on a new server, and compare the
 *             output against the real page before trusting a full run —
 *             see IMPORT_NOTES.md, which documents that this parser was
 *             built from real pages but could not be tested against live
 *             HTML from the sandbox that wrote it.
 *
 * Safe to re-run: matches existing rows by normalised title + level (same
 * normalisation as the phase6 dedup migration) and UPDATEs instead of
 * INSERTing again. Never overwrites a field an admin has hand-edited since
 * the last import (tracked via `edited_by_admin`; see the migration this
 * importer's companion SQL file adds those columns in). Never touches
 * database users, passwords, or config files.
 */

error_reporting(E_ALL & ~E_DEPRECATED);
require __DIR__ . '/../includes/functions.php';

// ---------------------------------------------------------------------
// CLI args
// ---------------------------------------------------------------------
$dryRun = false;
$onlySlugs = [];
$debug = false;
foreach ($argv as $arg) {
    if ($arg === '--dry-run') $dryRun = true;
    elseif ($arg === '--debug') $debug = true;
    elseif (strpos($arg, '--only=') === 0) {
        foreach (explode(',', substr($arg, 7)) as $s) {
            $s = trim($s);
            if ($s !== '') $onlySlugs[] = $s;
        }
    }
}

function out(string $line): void { fwrite(STDOUT, $line . "\n"); }
function err(string $line): void { fwrite(STDERR, $line . "\n"); }

$cacheDir = __DIR__ . '/import_cache';
if (!is_dir($cacheDir)) mkdir($cacheDir, 0775, true);

const ANU_INDEX_URL = 'https://www.anu.ac.ke/programs-offered-in-africa-nazarene-university/';
const ANU_REST_BASE = 'https://www.anu.ac.ke/wp-json/wp/v2/programs';
const USER_AGENT = 'ANU-Internal-Import-Tool/1.0 (+https://www.anu.ac.ke; internal content sync, contact webmaster)';
const THROTTLE_SECONDS = 2; // polite delay between requests to anu.ac.ke

// ---------------------------------------------------------------------
// HTTP fetch with local cache + throttling
// ---------------------------------------------------------------------
$lastFetch = 0;
function http_get(string $url): ?string
{
    global $cacheDir, $lastFetch;
    $cacheFile = $cacheDir . '/' . md5($url) . '.html';
    if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < 86400) {
        return file_get_contents($cacheFile);
    }
    $wait = THROTTLE_SECONDS - (microtime(true) - $lastFetch);
    if ($wait > 0) usleep((int) ($wait * 1_000_000));
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_USERAGENT => USER_AGENT,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $body = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);
    $lastFetch = microtime(true);
    if ($body === false || $httpCode >= 400) {
        err("  FETCH FAILED ($httpCode) $url" . ($curlErr ? " — $curlErr" : ''));
        return null;
    }
    file_put_contents($cacheFile, $body);
    return $body;
}

// ---------------------------------------------------------------------
// Normalisation — MUST match the SQL generated column in
// database/phase6-programme-dedup-2026-09.sql exactly, so importer
// matches land on the same rows the dedup migration already resolved.
// ---------------------------------------------------------------------
function normalize_title(string $t): string
{
    $t = str_replace('&', 'and', $t);
    $t = strtolower($t);
    return preg_replace('/[^a-z0-9]+/', '', $t);
}

// ---------------------------------------------------------------------
// Step 1 — get the list of 33 programmes + their school grouping from the
// index page.
// ---------------------------------------------------------------------
function fetch_index(): array
{
    $html = http_get(ANU_INDEX_URL);
    if ($html === null) {
        err('Could not fetch the programmes index page. Aborting — cannot');
        err('safely import without a source list.');
        exit(1);
    }
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
    libxml_clear_errors();
    $xpath = new DOMXPath($dom);

    // The index page's content is a series of headings (school names, then
    // level names) followed by links to /programs/<slug>/. We don't know
    // the exact heading tags/classes (see IMPORT_NOTES.md), so we walk the
    // body in document order, tracking the most recent heading text seen
    // that matches a known school or level name, and attach every
    // programme link found after it to that school/level until the next
    // heading changes it.
    $knownSchools = [
        'school of law' => 'sol',
        'school of business' => 'sob',
        'school of science and technology' => 'sst',
        'school of humanities and social sciences' => 'shss',
        'school of religion and christian ministry' => 'scrm',
        'school of religion & christian ministry' => 'scrm',
    ];
    $knownLevels = ['doctoral' => 'PhD', "master's" => 'Masters', 'masters' => 'Masters',
        'undergraduate' => 'Undergraduate', 'diploma' => 'Diploma', 'certificate' => 'Certificate'];

    $body = $dom->getElementsByTagName('body')->item(0);
    if (!$body) return [];

    $programmes = [];
    $currentSchool = null;
    $currentLevel = 'Other';

    $walker = function (DOMNode $node) use (&$walker, &$currentSchool, &$currentLevel, &$programmes, $knownSchools, $knownLevels) {
        if ($node->nodeType === XML_ELEMENT_NODE) {
            $tag = strtolower($node->nodeName);
            $text = trim(preg_replace('/\s+/', ' ', $node->textContent));
            $textLower = strtolower($text);

            if (in_array($tag, ['h1', 'h2', 'h3', 'h4', 'strong', 'b'], true) && $text !== '') {
                foreach ($knownSchools as $needle => $slug) {
                    if (strpos($textLower, $needle) !== false) { $currentSchool = $slug; $currentLevel = 'Other'; }
                }
                foreach ($knownLevels as $needle => $level) {
                    if ($textLower === $needle || strpos($textLower, $needle) === 0) { $currentLevel = $level; }
                }
            }

            if ($tag === 'a' && $node->hasAttribute('href')) {
                $href = $node->getAttribute('href');
                if (preg_match('#/programs/([a-z0-9\-]+)/?$#i', $href, $m)) {
                    $title = trim(preg_replace('/\s+/', ' ', $node->textContent));
                    if ($title !== '' && $currentSchool !== null) {
                        $programmes[$m[1]] = [
                            'source_slug' => $m[1],
                            'source_url' => $href,
                            'index_title' => $title,
                            'school_slug' => $currentSchool,
                            'level' => $currentLevel,
                        ];
                    }
                    return; // don't descend into the link's own children again
                }
            }
        }
        foreach (iterator_to_array($node->childNodes ?? []) as $child) {
            $walker($child);
        }
    };
    $walker($body);

    return array_values($programmes);
}

// ---------------------------------------------------------------------
// Step 2 — for one programme, fetch its page and extract fields.
// ---------------------------------------------------------------------
function html_to_text(string $html): string
{
    // Turn common block/line-break tags into newlines BEFORE stripping,
    // so label/value pairs that were on their own block stay on their own
    // line once tags are gone -- this is what the field-extraction regexes
    // below rely on (see IMPORT_NOTES.md: parser matches visible label
    // text, not markup, since real markup could not be inspected).
    $html = preg_replace('#<(br|/p|/div|/li|/h[1-6]|/tr)\s*/?>#i', "\n", $html);
    $text = strip_tags($html);
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace('/[ \t]+/', ' ', $text);
    $text = preg_replace('/\n\s*\n+/', "\n", $text);
    return trim($text);
}

function extract_field(string $text, string $label, array $stopLabels): ?string
{
    $stopPattern = implode('|', array_map(fn ($l) => preg_quote($l, '/'), $stopLabels));
    $pattern = '/' . preg_quote($label, '/') . '\s*[:\-]?\s*(.*?)(?:\n\s*(?:' . $stopPattern . ')\s*[:\-]?|\z)/is';
    if (preg_match($pattern, $text, $m)) {
        $val = trim($m[1]);
        $val = preg_replace('/\s+/', ' ', $val);
        return $val !== '' ? $val : null;
    }
    return null;
}

function fetch_and_parse_programme(array $indexEntry, bool $debug): array
{
    $slug = $indexEntry['source_slug'];
    $result = [
        'source_slug' => $slug,
        'source_url' => $indexEntry['source_url'],
        'title' => $indexEntry['index_title'],
        'level' => $indexEntry['level'],
        'school_slug' => $indexEntry['school_slug'],
        'overview' => null, 'duration' => null, 'mode' => null, 'intake' => null,
        'requirements' => null, 'course_units' => null, 'career_prospects' => null,
        'fetch_ok' => false,
    ];

    // Try REST first for canonical link/date -- but its content.rendered is
    // known (from IMPORT_NOTES.md) to be empty on this site's "programs"
    // post type, so we still fetch the rendered HTML page for content.
    $restUrl = ANU_REST_BASE . '?slug=' . urlencode($slug);
    $restJson = http_get($restUrl);
    if ($restJson !== null) {
        $rest = json_decode($restJson, true);
        if (is_array($rest) && !empty($rest[0])) {
            $entry = $rest[0];
            $result['title'] = html_entity_decode(strip_tags($entry['title']['rendered'] ?? $result['title']));
            $result['source_url'] = $entry['link'] ?? $result['source_url'];
            $result['wp_modified'] = $entry['modified'] ?? null;
        }
    }

    $pageHtml = http_get($result['source_url']);
    if ($pageHtml === null) {
        return $result; // fetch_ok stays false; caller reports as UNVERIFIED/skipped
    }
    $result['fetch_ok'] = true;
    $text = html_to_text($pageHtml);

    $result['overview'] = extract_field($text, 'Description of the Program', ['Objectives of the Program', 'Units Offered', 'Key Program Details', 'Duration']);
    $result['duration'] = extract_field($text, 'Duration', ['Mode of Study', 'Intake', 'Admission Requirements']);
    $result['mode'] = extract_field($text, 'Mode of Study', ['Intake', 'Admission Requirements', 'Duration']);
    $result['intake'] = extract_field($text, 'Intake', ['Admission Requirements', 'Units Required', 'Location', 'Mode of Study']);
    $result['requirements'] = extract_field($text, 'Admission Requirements', ['Units Required', 'Location', 'School/Department', 'Units Offered']);
    if ($result['requirements'] === null) {
        $result['requirements'] = extract_field($text, 'Entry Requirements', ['Units Required', 'Location', 'Units Offered']);
    }
    $result['course_units'] = extract_field($text, 'Units Offered', ['Key Program Details', 'Duration', 'Concentration']);
    $result['career_prospects'] = extract_field($text, 'Career Prospects', ['Duration', 'Mode of Study', 'Admission Requirements']);

    if ($debug) {
        out('  --- parsed fields for ' . $slug . ' ---');
        foreach (['title', 'level', 'school_slug', 'duration', 'mode', 'intake'] as $k) {
            out('    ' . $k . ': ' . ($result[$k] ?? '(none)'));
        }
        out('    overview: ' . (isset($result['overview']) ? substr($result['overview'], 0, 120) . '...' : '(none)'));
        out('    requirements: ' . (isset($result['requirements']) ? substr($result['requirements'], 0, 160) . '...' : '(none)'));
        out('    course_units: ' . (isset($result['course_units']) ? substr($result['course_units'], 0, 120) . '...' : '(none)'));
    }

    return $result;
}

// ---------------------------------------------------------------------
// Main
// ---------------------------------------------------------------------
out('=== ANU programme importer ===');
out($dryRun ? '(DRY RUN — no database changes will be made)' : '(LIVE — matching rows will be updated/inserted)');
out('');

out('Fetching programme index...');
$index = fetch_index();
if (!$index) {
    err('Index page returned zero programme links — parser found nothing to');
    err('recognise. Not proceeding (would risk wiping/misassigning data on a');
    err('false-empty read). Check IMPORT_NOTES.md and re-run with --debug.');
    exit(1);
}
out('Found ' . count($index) . ' programme links on the index page (expected 33).');
if (count($index) !== 33) {
    out('*** COUNT MISMATCH — see IMPORT_REPORT.md notes section. Continuing');
    out('*** anyway; every programme found is still processed and reported.');
}

if ($onlySlugs) {
    $index = array_values(array_filter($index, fn ($p) => in_array($p['source_slug'], $onlySlugs, true)));
    out('--only filter applied: processing ' . count($index) . ' programme(s).');
}

$schoolIdBySlug = [];
foreach (db()->query('SELECT id, slug FROM schools') as $row) {
    $schoolIdBySlug[$row['slug']] = (int) $row['id'];
}

$report = ['updated' => [], 'inserted' => [], 'skipped_fetch_failed' => [], 'unmapped_school' => [], 'unchanged' => []];
$requirementsSeen = []; // normalised requirements text => first programme title that had it

foreach ($index as $i => $entry) {
    out('[' . ($i + 1) . '/' . count($index) . '] ' . $entry['index_title'] . ' (' . $entry['source_slug'] . ')');
    $parsed = fetch_and_parse_programme($entry, $debug);

    if (!$parsed['fetch_ok']) {
        out('  SKIPPED — could not fetch programme page.');
        $report['skipped_fetch_failed'][] = $entry['index_title'] . ' (' . $entry['source_slug'] . ')';
        continue;
    }

    $schoolId = $schoolIdBySlug[$parsed['school_slug']] ?? null;
    if ($schoolId === null) {
        $report['unmapped_school'][] = $parsed['title'] . ' — index grouped it under "' . $parsed['school_slug'] . '", no matching row in schools table';
    }

    if (!empty($parsed['requirements'])) {
        $normReq = strtolower(preg_replace('/[^a-z0-9]+/', '', $parsed['requirements']));
        if (isset($requirementsSeen[$normReq]) && $requirementsSeen[$normReq] !== $parsed['title']) {
            out('  Requirements text matches "' . $requirementsSeen[$normReq] . '" — will link to shared block instead of duplicating.');
        } else {
            $requirementsSeen[$normReq] = $parsed['title'];
        }
    }

    $norm = normalize_title($parsed['title']);
    $existingStmt = db()->prepare('SELECT * FROM programmes WHERE level = ? AND (school_id <=> ?) ' .
        'AND REGEXP_REPLACE(LOWER(REPLACE(title, "&", "and")), "[^a-z0-9]+", "") = ?');
    $existingStmt->execute([$parsed['level'], $schoolId, $norm]);
    $existing = $existingStmt->fetch();

    if ($dryRun) {
        out('  ' . ($existing ? 'WOULD UPDATE id ' . $existing['id'] : 'WOULD INSERT new row') .
            ' — duration=' . ($parsed['duration'] ?? '[TO CONFIRM]') .
            ', mode=' . ($parsed['mode'] ?? '[TO CONFIRM]') .
            ', intake=' . ($parsed['intake'] ?? '[TO CONFIRM]'));
        continue;
    }

    // Never overwrite a field an admin has hand-edited since the last
    // import (tracked by edited_by_admin, added by this importer's
    // migration if not already present). Only fields that are still at
    // their last-imported value (or were never imported) get refreshed.
    $editedFields = [];
    if ($existing && !empty($existing['admin_edited_fields'])) {
        $editedFields = array_filter(explode(',', $existing['admin_edited_fields']));
    }

    $setFields = [
        'duration' => $parsed['duration'], 'mode' => $parsed['mode'] ?: null,
        'intake' => $parsed['intake'], 'overview' => $parsed['overview'],
        'requirements' => $parsed['requirements'], 'course_units' => $parsed['course_units'],
    ];
    foreach ($editedFields as $f) unset($setFields[$f]); // admin's hand edit wins

    if ($existing) {
        $sets = [];
        $params = [];
        foreach ($setFields as $col => $val) {
            if ($val === null) continue; // don't blank out an existing value with an empty scrape
            $sets[] = "$col = ?";
            $params[] = $val;
        }
        if (!$sets) {
            $report['unchanged'][] = $parsed['title'];
            out('  Nothing new to update.');
            continue;
        }
        $sets[] = 'source_url = ?';
        $params[] = $parsed['source_url'];
        $sets[] = 'imported_at = NOW()';
        $params[] = $existing['id'];
        db()->prepare('UPDATE programmes SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute($params);
        $report['updated'][] = $parsed['title'] . ' (id ' . $existing['id'] . ')';
        out('  Updated id ' . $existing['id'] . '.');
    } else {
        $slug = slugify($parsed['title']);
        $base = $slug; $n = 1;
        while (true) {
            $c = db()->prepare('SELECT id FROM programmes WHERE slug = ?');
            $c->execute([$slug]);
            if (!$c->fetch()) break;
            $slug = $base . '-' . (++$n);
        }
        db()->prepare('INSERT INTO programmes (title, slug, level, school_id, overview, requirements, duration, mode, intake, course_units, is_published, source_url, imported_at) VALUES (?,?,?,?,?,?,?,?,?,?,1,?,NOW())')
            ->execute([$parsed['title'], $slug, $parsed['level'], $schoolId, $parsed['overview'], $parsed['requirements'], $parsed['duration'], $parsed['mode'] ?: 'Physical', $parsed['intake'], $parsed['course_units'], $parsed['source_url']]);
        $report['inserted'][] = $parsed['title'] . ' (new, slug ' . $slug . ')';
        out('  Inserted new row, slug ' . $slug . '.');
    }
}

// ---------------------------------------------------------------------
// Shared requirements dedup pass (only in live mode, after all rows exist)
// ---------------------------------------------------------------------
if (!$dryRun) {
    out('');
    out('Deduplicating shared requirements text...');
    if (schema_has_table('general_requirements')) {
        $rows = db()->query("SELECT id, requirements FROM programmes WHERE requirements IS NOT NULL AND requirements != ''")->fetchAll();
        $groups = [];
        foreach ($rows as $r) {
            $norm = strtolower(preg_replace('/[^a-z0-9]+/', '', $r['requirements']));
            $groups[$norm][] = $r;
        }
        foreach ($groups as $norm => $group) {
            if (count($group) < 2) continue; // not shared, leave as programme-specific text
            $text = $group[0]['requirements'];
            $grStmt = db()->prepare('SELECT id FROM general_requirements WHERE body_hash = ?');
            $hash = md5($norm);
            $grStmt->execute([$hash]);
            $gr = $grStmt->fetch();
            if (!$gr) {
                db()->prepare('INSERT INTO general_requirements (title, body, body_hash) VALUES (?,?,?)')
                    ->execute(['General admission requirements', $text, $hash]);
                $grId = db()->lastInsertId();
            } else {
                $grId = $gr['id'];
            }
            $ids = array_column($group, 'id');
            $in = implode(',', array_fill(0, count($ids), '?'));
            db()->prepare("UPDATE programmes SET general_requirements_id = ? WHERE id IN ($in)")
                ->execute(array_merge([$grId], $ids));
            out('  Linked ' . count($ids) . ' programmes to shared requirements block #' . $grId . '.');
        }
    } else {
        out('  general_requirements table not present -- run database/phase6-general-requirements-2026-09.sql first.');
    }
}

// ---------------------------------------------------------------------
// Report
// ---------------------------------------------------------------------
out('');
out('=== Summary ===');
out('Updated: ' . count($report['updated']));
out('Inserted: ' . count($report['inserted']));
out('Unchanged: ' . count($report['unchanged']));
out('Skipped (fetch failed): ' . count($report['skipped_fetch_failed']));
out('Unmapped school: ' . count($report['unmapped_school']));

file_put_contents(__DIR__ . '/../IMPORT_RUN_LOG.json', json_encode($report, JSON_PRETTY_PRINT));
out('');
out('Full detail written to main-site/IMPORT_RUN_LOG.json — use this to fill in IMPORT_REPORT.md.');
