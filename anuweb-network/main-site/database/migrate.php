<?php
// CLI runner:  php database/migrate.php
// Exit code 0 = all steps ok, 1 = a step failed (deploy.sh stops on this).
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../includes/migrator.php';
$fail = false;
foreach (run_migrations() as $r) {
    printf("[%s] %s - %s\n", $r['ok'] ? ($r['changed'] ? 'APPLIED' : 'ok') : 'FAILED', $r['id'], $r['result']);
    if (!$r['ok']) $fail = true;
}
echo $fail ? "MIGRATION FAILED\n" : "Migrations complete.\n";
exit($fail ? 1 : 0);
