<?php
/**
 * Schema-safety guard (Phase 5b fixes, 2026-09).
 *
 * Root cause of the "Unknown column 'unit_slug'" fatal error: PHP code that
 * assumed a migration had already run was deployed before that migration
 * actually ran on the live database. This file makes every page that reads
 * a "new" column/table check the real schema once per request (cached) and
 * fall back to the pre-migration behaviour instead of throwing a fatal
 * error, so a deploy that copies files before its migration (or where the
 * migration silently failed) degrades gracefully instead of crashing.
 *
 * This does NOT replace running migrations -- it's a safety net, not a
 * substitute. See deploy.sh: migrations now run FIRST and the script
 * refuses to copy files if a migration fails.
 *
 * Included directly by main-site/includes/functions.php, and required by
 * each unit site's config/db.php the same way hero_render.php is (every
 * site is deployed as a sibling folder under anuweb-network/).
 */

if (!function_exists('schema_has_column')) {
    /**
     * True if $table has a column named $column on the connected database.
     * Cached for the lifetime of the request -- one SHOW COLUMNS per
     * table+column combination actually used, not per call. $table/$column
     * must always be literal strings from our own code, never request
     * input, since they're interpolated into the SQL (SHOW COLUMNS FROM
     * doesn't accept a placeholder for the table name).
     */
    function schema_has_column(string $table, string $column): bool
    {
        static $cache = [];
        $key = $table . '.' . $column;
        if (!array_key_exists($key, $cache)) {
            try {
                // Deliberately NOT "SHOW COLUMNS FROM `t` LIKE ?" -- with
                // PDO::ATTR_EMULATE_PREPARES=false (this codebase's PDO
                // setting), MariaDB rejects a bound placeholder in a SHOW
                // statement's LIKE clause with a real syntax error, which
                // this function's own catch block was silently swallowing
                // as "column doesn't exist" -- a false negative on every
                // single call, column present or not. $column is always a
                // literal from our own code (never request input), so
                // interpolating it via quote() (which also strips the
                // wrapping quotes LIKE doesn't want) is safe.
                $quoted = trim(db()->quote($column), "'");
                $stmt = db()->query("SHOW COLUMNS FROM `$table` LIKE '$quoted'");
                $cache[$key] = (bool) $stmt->fetch();
            } catch (Throwable $e) {
                // Table itself might not exist yet either -- either way,
                // treat as "column not there" rather than fatal.
                $cache[$key] = false;
            }
        }
        return $cache[$key];
    }
}

if (!function_exists('schema_has_table')) {
    /** True if $table exists on the connected database. Cached per request. */
    function schema_has_table(string $table): bool
    {
        static $cache = [];
        if (!array_key_exists($table, $cache)) {
            try {
                $stmt = db()->query('SHOW TABLES LIKE ' . db()->quote($table));
                $cache[$table] = (bool) $stmt->fetch();
            } catch (Throwable $e) {
                $cache[$table] = false;
            }
        }
        return $cache[$table];
    }
}

if (!function_exists('unit_scope_sql')) {
    /**
     * SQL fragment (starting with " AND ...") that scopes a news/events/
     * sliders-style query by unit_slug -- or an empty string if that
     * column doesn't exist on $table yet, so the query falls back to its
     * old, pre-migration behaviour (every row treated as main-site) instead
     * of a fatal error.
     *
     * $unitSlug = null means "main-site rows only" (unit_slug IS NULL).
     * $unitSlug = 'law' etc. means "this unit's own rows only".
     */
    function unit_scope_sql(string $table, ?string $unitSlug = null): string
    {
        if (!schema_has_column($table, 'unit_slug')) {
            return '';
        }
        return $unitSlug === null
            ? ' AND unit_slug IS NULL'
            : ' AND unit_slug = ' . db()->quote($unitSlug);
    }
}

if (!function_exists('schema_check_report')) {
    /**
     * The full list of tables/columns this codebase currently depends on
     * that were introduced after the original install (i.e. the ones an
     * out-of-date live database could be missing), each checked live
     * against the connected database. Used by Admin > System Check.
     *
     * Add a new row here whenever a future phase adds a table/column that
     * older code paths need to keep working without it -- that's what
     * keeps this page (and the fail-safe fallbacks above) honest.
     */
    function schema_check_report(): array
    {
        $checks = [
            ['table' => 'news',            'column' => 'unit_slug',   'why' => 'Per-school News & Updates (Phase 5)'],
            ['table' => 'news',            'column' => 'category',    'why' => 'News category filter (Phase 0/2)'],
            ['table' => 'news',            'column' => 'is_featured', 'why' => 'Merged News & Events homepage block'],
            ['table' => 'events',          'column' => 'unit_slug',   'why' => 'Per-school upcoming events (Phase 5)'],
            ['table' => 'events',          'column' => 'category',    'why' => 'Events category filter (Phase 0/2)'],
            ['table' => 'sliders',         'column' => 'unit_slug',   'why' => 'Per-site hero image/video'],
            ['table' => 'programmes',      'column' => 'is_featured', 'why' => 'Featured programmes (homepage)'],
            ['table' => 'departments',     'column' => null,          'why' => 'School departments listing (Phase 5)'],
            ['table' => 'site_content',    'column' => null,          'why' => 'Per-unit editable hero/content text'],
            ['table' => 'photo_gallery',   'column' => null,          'why' => 'Photo Gallery (Orientation import)'],
        ];
        $report = [];
        foreach ($checks as $c) {
            $tableOk = schema_has_table($c['table']);
            $columnOk = $c['column'] === null ? null : ($tableOk && schema_has_column($c['table'], $c['column']));
            $report[] = [
                'table'  => $c['table'],
                'column' => $c['column'],
                'why'    => $c['why'],
                'table_ok'  => $tableOk,
                'column_ok' => $columnOk,
                'ok' => $c['column'] === null ? $tableOk : ($tableOk && $columnOk),
            ];
        }
        return $report;
    }
}
