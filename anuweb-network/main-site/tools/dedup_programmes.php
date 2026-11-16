#!/usr/bin/env php
<?php
/**
 * Programme duplicate detector + merger. Replaces the earlier
 * database/phase6-programme-dedup-2026-09.sql, which hard-coded specific
 * programme ids and missed a duplicate group that existed on the live
 * database but not in the sandbox snapshot it was written from. This tool
 * finds duplicates by querying the real database at run time -- it can
 * never "miss" a group the way a hand-typed list can, because it doesn't
 * use a list at all.
 *
 * Usage:
 *   php tools/dedup_programmes.php --check    # changes NOTHING. Simulates
 *                                              # the merge in memory, writes
 *                                              # DUPLICATES_REPORT.md from a
 *                                              # live query, and exits
 *                                              # non-zero if any group would
 *                                              # still violate the unique
 *                                              # key after the simulated
 *                                              # merge (should never happen
 *                                              # if the merge logic is
 *                                              # correct -- this is the
 *                                              # safety net in case it
 *                                              # isn't).
 *   php tools/dedup_programmes.php --apply    # does the real work: creates
 *                                              # programmes_archive /
 *                                              # programme_redirects if
 *                                              # missing, merges every
 *                                              # duplicate group inside a
 *                                              # single transaction
 *                                              # (all-or-nothing), re-checks
 *                                              # afterwards, and only then
 *                                              # adds the
 *                                              # normalized_title/unique-key
 *                                              # protection. If ANYTHING
 *                                              # goes wrong the whole
 *                                              # transaction rolls back --
 *                                              # nothing is left half-done.
 *
 * Safe to run on a database that is already partially migrated (e.g. an
 * earlier attempt merged some groups before failing on one it didn't know
 * about) -- every run re-detects duplicates from what's actually in the
 * table right now, so an already-resolved group is simply not "duplicate"
 * any more and is left alone.
 *
 * Never touches database users, passwords, or config files.
 */

error_reporting(E_ALL & ~E_DEPRECATED);
require __DIR__ . '/../includes/functions.php';

$mode = null;
foreach ($argv as $a) {
    if ($a === '--check') $mode = 'check';
    if ($a === '--apply') $mode = 'apply';
}
if ($mode === null) {
    fwrite(STDERR, "Usage: php dedup_programmes.php --check | --apply\n");
    exit(2);
}

function out(string $l): void { fwrite(STDOUT, $l . "\n"); }
function err(string $l): void { fwrite(STDERR, $l . "\n"); }

// MUST match the generated column expression added to `programmes`
// (normalized_title) exactly, so a row matched here is the same row the
// database's own unique key would consider a duplicate.
function normalize_title(string $t): string
{
    $t = str_replace('&', 'and', $t);
    $t = strtolower($t);
    return preg_replace('/[^a-z0-9]+/', '', $t);
}

const MERGE_COLS = ['image', 'duration', 'mode', 'intake', 'overview', 'requirements',
    'fee_structure', 'fee_structure_file', 'course_units', 'planner_file'];
const ARCHIVE_COLS = ['id', 'school_id', 'unit_slug', 'image', 'title', 'slug', 'level',
    'duration', 'mode', 'intake', 'overview', 'requirements', 'fee_structure',
    'fee_structure_file', 'course_units', 'planner_file', 'is_published', 'created_at', 'is_featured'];

function completeness_score(array $row): int
{
    $fields = ['overview', 'requirements', 'fee_structure', 'fee_structure_file',
        'course_units', 'planner_file', 'duration', 'intake', 'image'];
    $score = 0;
    foreach ($fields as $f) {
        if (trim((string) ($row[$f] ?? '')) !== '') $score++;
    }
    return $score;
}

function group_key(array $row): string
{
    return ($row['school_id'] === null ? 'NULL' : (string) $row['school_id']) . '|' .
        $row['level'] . '|' . normalize_title($row['title']);
}

/** Build the merge plan (which row to keep, which to remove) from a live
 * read of `programmes`. Pure computation, no writes. */
function build_plan(): array
{
    $all = db()->query('SELECT * FROM programmes')->fetchAll();
    $groups = [];
    foreach ($all as $row) {
        $groups[group_key($row)][] = $row;
    }
    $dupGroups = array_filter($groups, fn ($g) => count($g) > 1);

    $plan = [];
    foreach ($dupGroups as $key => $rows) {
        usort($rows, function ($a, $b) {
            $pa = (int) $a['is_published'];
            $pb = (int) $b['is_published'];
            if ($pa !== $pb) return $pb <=> $pa; // published first
            $sa = completeness_score($a);
            $sb = completeness_score($b);
            if ($sa !== $sb) return $sb <=> $sa; // most complete first
            return $a['id'] <=> $b['id']; // stable, deterministic tie-break
        });
        $keep = $rows[0];
        $remove = array_slice($rows, 1);
        $plan[] = ['key' => $key, 'keep' => $keep, 'remove' => $remove];
    }
    return $plan;
}

/** Cross-school same-title collisions at the same level -- reported only,
 * never merged (different school_id means they never collide with the
 * per-school unique key anyway, and which school is correct isn't a
 * question the database can answer on its own). */
function cross_school_collisions(): array
{
    $all = db()->query('SELECT * FROM programmes')->fetchAll();
    $byLevelTitle = [];
    foreach ($all as $row) {
        $byLevelTitle[$row['level'] . '|' . normalize_title($row['title'])][] = $row;
    }
    $found = [];
    foreach ($byLevelTitle as $rows) {
        $schools = [];
        foreach ($rows as $r) $schools[$r['school_id'] ?? 'NULL'][] = $r;
        if (count($schools) > 1) $found[] = $rows;
    }
    return $found;
}

/** Same school_id + level, titles that are NOT an exact normalised match
 * (so the unique key does not catch them and this tool will not auto-merge
 * them -- an automated merge here risks silently combining two genuinely
 * different programmes on a wording heuristic, which is worse than leaving
 * a real duplicate for a human to confirm) but that look like the same
 * programme published under two slightly different titles -- most
 * commonly "X" vs "X (ABBREVIATION)". Reported for your review only. */
function strip_trailing_parenthetical(string $t): string
{
    return trim(preg_replace('/\s*\([^)]*\)\s*$/', '', $t));
}

function near_duplicate_candidates(array $plan): array
{
    $merging = [];
    foreach ($plan as $g) {
        $merging[$g['keep']['id']] = true;
        foreach ($g['remove'] as $r) $merging[$r['id']] = true;
    }
    $all = db()->query('SELECT * FROM programmes')->fetchAll();
    $byGroup = [];
    foreach ($all as $row) {
        $byGroup[($row['school_id'] ?? 'NULL') . '|' . $row['level']][] = $row;
    }
    $found = [];
    $seenPairs = [];
    foreach ($byGroup as $rows) {
        $n = count($rows);
        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                $a = $rows[$i];
                $b = $rows[$j];
                if (isset($merging[$a['id']]) && isset($merging[$b['id']])) continue; // already an exact-match pair
                if (normalize_title($a['title']) === normalize_title($b['title'])) continue; // exact match, not "near"
                $aStripped = normalize_title(strip_trailing_parenthetical($a['title']));
                $bStripped = normalize_title(strip_trailing_parenthetical($b['title']));
                if ($aStripped !== '' && $bStripped !== '' && $aStripped === $bStripped) {
                    $pairKey = min($a['id'], $b['id']) . '-' . max($a['id'], $b['id']);
                    if (!isset($seenPairs[$pairKey])) {
                        $seenPairs[$pairKey] = true;
                        $found[] = [$a, $b];
                    }
                }
            }
        }
    }
    return $found;
}

function write_report(array $plan, array $crossCollisions, array $nearDuplicates, bool $simulatedOnly): void
{
    $lines = [];
    $lines[] = '# Programme Duplicates Report';
    $lines[] = '';
    $lines[] = 'Generated ' . date('Y-m-d H:i:s') . ' by `tools/dedup_programmes.php` directly ' .
        'from a live query against `programmes` on **this** database -- not hand-typed, not ' .
        'carried over from an earlier session. Detected by normalised title (lowercase, ' .
        'punctuation stripped, "&"->"and") within the same `school_id` + `level`.';
    $lines[] = '';
    if ($simulatedOnly) {
        $lines[] = '**This is a `--check` (dry-run) report — nothing in the database has been ' .
            'changed.** Run `--apply` to actually perform the merge described below.';
        $lines[] = '';
    }

    if (!$plan) {
        $lines[] = 'No same-school duplicate groups found in this database.';
    } else {
        $lines[] = '## Same-school duplicate groups (' . count($plan) . ' found)';
        $lines[] = '';
        foreach ($plan as $g) {
            $keep = $g['keep'];
            $lines[] = '### "' . $keep['title'] . '" — school_id ' . ($keep['school_id'] ?? 'NULL') . ', ' . $keep['level'];
            $lines[] = '- **KEEP** id ' . $keep['id'] . ' — "' . $keep['title'] . '" (published=' .
                $keep['is_published'] . ', completeness score ' . completeness_score($keep) . '/9)';
            foreach ($g['remove'] as $r) {
                $lines[] = '- REMOVE id ' . $r['id'] . ' — "' . $r['title'] . '" (published=' .
                    $r['is_published'] . ', completeness score ' . completeness_score($r) . '/9) ' .
                    '— merged into id ' . $keep['id'] . ', archived, old slug `' . $r['slug'] .
                    '` redirects to `' . $keep['slug'] . '`';
            }
            $lines[] = '';
        }
    }

    $lines[] = '## Cross-school title collisions (NOT merged by this tool)';
    $lines[] = '';
    if (!$crossCollisions) {
        $lines[] = 'None found in this database beyond any already flagged for your decision ' .
            'separately (e.g. Criminal Justice and Security Management, Monitoring and ' .
            'Evaluation — see FINAL-REPORT.md/prior correspondence if those still exist here).';
    } else {
        foreach ($crossCollisions as $rows) {
            $parts = array_map(fn ($r) => 'id ' . $r['id'] . ' (school_id ' . ($r['school_id'] ?? 'NULL') . ')', $rows);
            $lines[] = '- "' . $rows[0]['title'] . '" (' . $rows[0]['level'] . '): ' . implode(', ', $parts) .
                ' — different schools, same title/level. This tool never touches these; the correct ' .
                'school has to come from anu.ac.ke or your own confirmation, not from data completeness.';
        }
    }
    $lines[] = '';

    $lines[] = '## Near-duplicate candidates (NOT auto-merged — same school/level, titles differ ' .
        'by more than punctuation, e.g. "X" vs "X (ABBREVIATION)")';
    $lines[] = '';
    if (!$nearDuplicates) {
        $lines[] = 'None found.';
    } else {
        $lines[] = 'This tool only auto-merges an **exact** normalised-title match, on purpose -- ' .
            'merging on a wording heuristic risks silently combining two different programmes. ' .
            'These pairs look like the same programme published under two titles, but need a human ' .
            'to confirm before merging:';
        $lines[] = '';
        foreach ($nearDuplicates as [$a, $b]) {
            $lines[] = '- id ' . $a['id'] . ' "' . $a['title'] . '" vs id ' . $b['id'] . ' "' . $b['title'] . '"' .
                ' (school_id ' . ($a['school_id'] ?? 'NULL') . ', ' . $a['level'] . ')';
        }
    }
    $lines[] = '';

    file_put_contents(__DIR__ . '/../DUPLICATES_REPORT.md', implode("\n", $lines) . "\n");
}

// ---------------------------------------------------------------------
// Pre-flight simulation shared by both modes: what would the database
// look like after applying $plan? Used by --check (as the whole check)
// and by --apply (as the final safety net before adding the unique key).
// ---------------------------------------------------------------------
function simulate_post_merge_groups(array $plan): array
{
    // Read current rows, then mentally apply the plan: remove rows will
    // be gone, kept rows stay (their title/school/level never change).
    $all = db()->query('SELECT id, school_id, level, title FROM programmes')->fetchAll();
    $removedIds = [];
    foreach ($plan as $g) {
        foreach ($g['remove'] as $r) $removedIds[$r['id']] = true;
    }
    $after = array_filter($all, fn ($row) => !isset($removedIds[$row['id']]));
    $groups = [];
    foreach ($after as $row) {
        $groups[group_key($row)][] = $row;
    }
    return array_filter($groups, fn ($g) => count($g) > 1);
}

$plan = build_plan();
$crossCollisions = cross_school_collisions();
$nearDuplicates = near_duplicate_candidates($plan);

if ($mode === 'check') {
    write_report($plan, $crossCollisions, $nearDuplicates, true);
    out('CHECK (dry run, nothing changed):');
    out('  ' . count($plan) . ' same-school duplicate group(s) found (exact title match -- will be auto-merged by --apply).');
    out('  ' . count($nearDuplicates) . ' near-duplicate candidate(s) found (NOT auto-merged -- see report).');
    out('  ' . count($crossCollisions) . ' cross-school title collision(s) found (not touched).');

    $remaining = simulate_post_merge_groups($plan);
    if ($remaining) {
        err('');
        err('ABORT: simulating the merge plan still leaves ' . count($remaining) . ' group(s) ' .
            'that would violate the uniq_normalized_title_school_level key:');
        foreach ($remaining as $key => $rows) {
            err('  - ' . $key . ': ids ' . implode(',', array_column($rows, 'id')));
        }
        err('This should not happen if the merge logic is correct -- do not run --apply until ' .
            'this is understood. See DUPLICATES_REPORT.md for the full plan.');
        exit(1);
    }
    out('  Simulated merge leaves zero duplicate groups -- safe to run --apply.');
    out('  Wrote DUPLICATES_REPORT.md from this live database.');
    exit(0);
}

// ---------------------------------------------------------------------
// --apply
// ---------------------------------------------------------------------
db()->exec("CREATE TABLE IF NOT EXISTS programmes_archive (
  id INT NOT NULL,
  school_id INT NULL,
  unit_slug VARCHAR(50) NULL,
  image VARCHAR(255) NULL,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL,
  level ENUM('PhD','Masters','Undergraduate','Diploma','Certificate','Other') NOT NULL,
  duration VARCHAR(100) NULL,
  mode VARCHAR(50) NULL,
  intake VARCHAR(100) NULL,
  overview TEXT NULL,
  requirements TEXT NULL,
  fee_structure TEXT NULL,
  fee_structure_file VARCHAR(255) NULL,
  course_units TEXT NULL,
  planner_file VARCHAR(255) NULL,
  is_published TINYINT(1) NULL,
  created_at TIMESTAMP NULL,
  is_featured TINYINT(1) NULL,
  removed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  kept_id INT NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

db()->exec("CREATE TABLE IF NOT EXISTS programme_redirects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  old_slug VARCHAR(255) NOT NULL,
  kept_slug VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_old_slug (old_slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

if (!$plan) {
    out('APPLY: no duplicate groups found -- nothing to merge.');
} else {
    out('APPLY: merging ' . count($plan) . ' duplicate group(s)...');
    db()->beginTransaction();
    try {
        foreach ($plan as $g) {
            $keep = $g['keep'];
            $keepId = $keep['id'];

            // Re-read the keep row fresh, inside the transaction, and lock
            // it -- and confirm it's still the same programme we planned
            // around (rule: never act on a row that changed since we
            // looked at it).
            $stmt = db()->prepare('SELECT * FROM programmes WHERE id = ? FOR UPDATE');
            $stmt->execute([$keepId]);
            $keepRow = $stmt->fetch();
            if (!$keepRow || normalize_title($keepRow['title']) !== normalize_title($keep['title'])) {
                throw new RuntimeException('Keep row id ' . $keepId . ' changed since detection (expected "' .
                    $keep['title'] . '") -- aborting for safety, nothing committed.');
            }

            foreach ($g['remove'] as $rm) {
                $rmId = $rm['id'];
                $stmt = db()->prepare('SELECT * FROM programmes WHERE id = ? FOR UPDATE');
                $stmt->execute([$rmId]);
                $rmRow = $stmt->fetch();
                if (!$rmRow) {
                    out('  id ' . $rmId . ' no longer exists (already resolved by an earlier run) -- skipping.');
                    continue;
                }
                if (normalize_title($rmRow['title']) !== normalize_title($rm['title'])) {
                    throw new RuntimeException('Row id ' . $rmId . ' title changed since detection ' .
                        '("' . $rmRow['title'] . '" vs expected "' . $rm['title'] . '") -- refusing to ' .
                        'delete a different programme than planned. Aborting, nothing committed.');
                }

                // Merge: copy non-empty fields from the row being removed
                // into the kept row, only where the kept row is empty.
                $sets = [];
                $params = [];
                foreach (MERGE_COLS as $c) {
                    $keepEmpty = trim((string) ($keepRow[$c] ?? '')) === '';
                    $rmHasValue = trim((string) ($rmRow[$c] ?? '')) !== '';
                    if ($keepEmpty && $rmHasValue) {
                        $sets[] = "$c = ?";
                        $params[] = $rmRow[$c];
                        $keepRow[$c] = $rmRow[$c]; // reflect locally for any later remove in this group
                    }
                }
                if ($sets) {
                    $params[] = $keepId;
                    db()->prepare('UPDATE programmes SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute($params);
                }

                // Archive (idempotent: if an earlier partial run already
                // archived this id, just refresh kept_id).
                $vals = [];
                foreach (ARCHIVE_COLS as $c) $vals[] = $rmRow[$c];
                $vals[] = $keepId;
                $placeholders = implode(',', array_fill(0, count(ARCHIVE_COLS), '?'));
                db()->prepare('INSERT INTO programmes_archive (' . implode(',', ARCHIVE_COLS) . ', kept_id) ' .
                    "VALUES ($placeholders, ?) ON DUPLICATE KEY UPDATE kept_id = VALUES(kept_id)")
                    ->execute($vals);

                // Redirect old slug -> the kept row's current slug.
                db()->prepare('INSERT INTO programme_redirects (old_slug, kept_slug) VALUES (?, ?) ' .
                    'ON DUPLICATE KEY UPDATE kept_slug = VALUES(kept_slug)')
                    ->execute([$rmRow['slug'], $keepRow['slug']]);

                // Final re-check of title immediately before DELETE.
                $t = db()->prepare('SELECT title FROM programmes WHERE id = ?');
                $t->execute([$rmId]);
                $liveTitle = $t->fetchColumn();
                if ($liveTitle === false || normalize_title($liveTitle) !== normalize_title($rm['title'])) {
                    throw new RuntimeException('Row id ' . $rmId . ' changed unexpectedly immediately ' .
                        'before delete -- aborting, nothing committed.');
                }
                db()->prepare('DELETE FROM programmes WHERE id = ?')->execute([$rmId]);
                out('  merged id ' . $rmId . ' ("' . $rm['title'] . '") into id ' . $keepId);
            }
        }

        // Safety net: re-verify, from a fresh query inside the same
        // transaction, that no duplicate group remains. If one does,
        // roll back everything -- nothing is left half-done.
        $post = db()->query('SELECT id, school_id, level, title FROM programmes')->fetchAll();
        $postGroups = [];
        foreach ($post as $row) $postGroups[group_key($row)][] = $row;
        $remaining = array_filter($postGroups, fn ($g) => count($g) > 1);
        if ($remaining) {
            throw new RuntimeException('Post-merge check found ' . count($remaining) . ' duplicate ' .
                'group(s) still remaining -- aborting, nothing committed. Groups: ' .
                implode('; ', array_keys($remaining)));
        }

        db()->commit();
        out('APPLY: merge committed (' . count($plan) . ' group(s)).');
    } catch (Throwable $e) {
        db()->rollBack();
        err('APPLY FAILED, rolled back -- database unchanged: ' . $e->getMessage());
        exit(1);
    }
}

// Add the unique-key protection ONLY after confirming, with a fresh query,
// that no duplicate group exists right now.
$final = db()->query('SELECT id, school_id, level, title FROM programmes')->fetchAll();
$finalGroups = [];
foreach ($final as $row) $finalGroups[group_key($row)][] = $row;
$finalDup = array_filter($finalGroups, fn ($g) => count($g) > 1);
if ($finalDup) {
    err('REFUSING to add the unique key -- ' . count($finalDup) . ' duplicate group(s) still exist:');
    foreach ($finalDup as $key => $rows) err('  - ' . $key . ': ids ' . implode(',', array_column($rows, 'id')));
    exit(1);
}

if (!schema_has_column('programmes', 'normalized_title')) {
    db()->exec("ALTER TABLE programmes ADD COLUMN normalized_title VARCHAR(255) " .
        "GENERATED ALWAYS AS (REGEXP_REPLACE(LOWER(REPLACE(title, '&', 'and')), '[^a-z0-9]+', '')) STORED");
    out('Added normalized_title generated column.');
}
$keyExists = (int) db()->query("SELECT COUNT(*) FROM information_schema.STATISTICS WHERE " .
    "TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'programmes' AND INDEX_NAME = 'uniq_normalized_title_school_level'")
    ->fetchColumn();
if (!$keyExists) {
    db()->exec('ALTER TABLE programmes ADD UNIQUE KEY uniq_normalized_title_school_level (normalized_title, school_id, level)');
    out('Added uniq_normalized_title_school_level unique key.');
} else {
    out('Unique key already present.');
}

$finalCross = cross_school_collisions();
$finalNear = near_duplicate_candidates([]);
write_report($plan, $finalCross, $finalNear, false);
out('Wrote DUPLICATES_REPORT.md reflecting what was actually done.');
out('Done.');
