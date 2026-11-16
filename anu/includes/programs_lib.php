<?php
/** Programme helpers: the same programme is never shown twice, whatever is stored in the database. */
if (!function_exists('prog_norm')) {
    /** "Master of Business Administration (MBA)" and "Master of Business Administration" are the same programme. */
    function prog_norm(string $name): string {
        $n = mb_strtolower(preg_replace_callback('/\(([^)]*)\)/u', fn($m) => (strlen($m[1]) <= 12 && preg_match_all('/[A-Z]/', $m[1]) >= 2) ? ' ' : ' ' . $m[1] . ' ', trim($name)), 'UTF-8');
        $n = str_replace(['&', '.'], [' and ', ''], $n);
        $n = preg_replace('/[^a-z0-9]+/u', ' ', $n);
        $n = trim(preg_replace('/\s+/', ' ', $n));
        $n = str_replace(['doctor of philosophy', 'counselling', 'applied it', 'business administration and management'],
                         ['phd', 'counseling', 'applied information technology', 'business administration'], $n);
            // names that anu.ac.ke itself lists once, but that appear under a second name on some pages
        $alias = ['master of education in educational administration and leadership' => 'master of education administration',
                  'master of science in peace governance and security studies' => 'master of science in governance peace and security',
                  'master of science in governance peace and security studies' => 'master of science in governance peace and security',
                  'master of science in environmental resource management' => 'master of science in environmental and natural resource management'];
        if (isset($alias[$n])) $n = $alias[$n];
        return $n === 'mba' ? 'master of business administration' : $n;
    }
    /** Keeps the first row of each programme (rows should already be in the order you want kept). */
    function prog_unique(array $rows, string $nameKey = 'name'): array {
        $seen = []; $out = [];
        foreach ($rows as $r) {
            $k = prog_norm((string)($r[$nameKey] ?? ''));
            if ($k === '' || isset($seen[$k])) continue;
            $seen[$k] = true; $out[] = $r;
        }
        return $out;
    }
}
