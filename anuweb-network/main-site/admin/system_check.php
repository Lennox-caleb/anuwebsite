<?php
/**
 * Admin > System Check (Phase 5b incident fix, 2026-09).
 *
 * Lists every table/column newer code depends on and whether it's actually
 * present on the connected database right now -- so a deploy that copied
 * files before its migration ran (the "Unknown column 'unit_slug'"
 * incident) shows up here as MISSING rows instead of only being noticed
 * when a page fatal-errors for a visitor.
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../includes/migrator.php';
require_super_admin();

// Super admin can apply the consolidated, re-runnable migration from here.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        flash_set('danger', csrf_error_message());
    } else {
        $res = run_migrations();
        $failed = array_filter($res, fn($r) => !$r['ok']);
        $applied = count(array_filter($res, fn($r) => $r['changed']));
        if ($failed) {
            $f = reset($failed);
            error_log('System Check migration failed: ' . $f['id'] . ' ' . $f['result']);
            flash_set('danger', 'Migration step ' . $f['id'] . ' failed; later steps were not run. Details are in the server log.');
        } else {
            flash_set('success', $applied ? "Migrations applied: $applied change(s) made." : 'Database already up to date; nothing needed changing.');
        }
    }
    redirect('/admin/system_check.php');
}

$pageTitle = 'System Check';
require __DIR__ . '/includes/admin_header.php';

// Data health: what is stored vs what the public site is allowed to show.
$modules = [
  'News' => ['news', 'is_published'], 'Events' => ['events', 'is_published'], 'Hero images' => ['sliders', 'is_published'],
  'Schools' => ['schools', 'is_published'], 'Programmes' => ['programmes', 'is_published'], 'Staff' => ['staff', 'is_published'],
  'Departments' => ['departments', 'is_published'], 'Leadership' => ['leadership', 'is_published'],
  'Governance' => ['governance_members', 'is_published'], 'Research' => ['research_publications', 'is_published'],
  'Photo gallery' => ['photo_gallery', 'is_published'], 'CSR gallery' => ['csr_gallery', 'is_published'],
  'Chaplaincy' => ['chaplaincy_programs', 'is_published'], 'Tenders' => ['tenders', 'is_published'],
  'Vacancies' => ['vacancies', 'is_published'], 'Magazines' => ['publications_gallery', 'is_published'],
  'Partners' => ['partners', 'is_published'], 'Testimonials' => ['testimonials', 'is_published'], 'Resources' => ['resources', 'is_published'],
  'Announcements' => ['announcements', 'is_active'], 'Site content blocks' => ['site_content', null],
];
$health = [];
foreach ($modules as $label => [$tbl, $flag]) {
    $row = ['label' => $label, 'table' => $tbl, 'exists' => mig_table_exists($tbl), 'total' => null, 'live' => null, 'hidden' => null, 'nullflag' => null];
    if ($row['exists']) {
        try {
            $row['total'] = (int) db()->query("SELECT COUNT(*) FROM `$tbl`")->fetchColumn();
            if ($flag && mig_column($tbl, $flag)) {
                $row['live'] = (int) db()->query("SELECT COUNT(*) FROM `$tbl` WHERE `$flag` = 1")->fetchColumn();
                $row['nullflag'] = (int) db()->query("SELECT COUNT(*) FROM `$tbl` WHERE `$flag` IS NULL")->fetchColumn();
                $row['hidden'] = $row['total'] - $row['live'];
            }
        } catch (Throwable $e) { error_log('System check count failed for ' . $tbl . ': ' . $e->getMessage()); }
    }
    $health[] = $row;
}
// Duplicate check (read-only): same title/name entered more than once. Nothing is deleted automatically.
$dupDefs = [
  'News' => ['news', 'title'], 'Events' => ['events', 'title, start_datetime'], 'Programmes' => ['programmes', 'title, school_id'],
  'Staff' => ['staff', 'full_name, unit_slug'], 'Schools' => ['schools', 'name'], 'Partners' => ['partners', 'name'],
  'Vacancies' => ['vacancies', 'title'], 'Resources' => ['resources', 'title, category'], 'Departments' => ['departments', 'name, unit_slug'],
  'Governance' => ['governance_members', 'full_name, category'], 'Leadership' => ['leadership', 'full_name, role_type'],
  'Testimonials' => ['testimonials', 'person_name, quote'], 'Tenders' => ['tenders', 'title'],
];
$dups = [];
foreach ($dupDefs as $label => [$tbl, $cols]) {
    if (!mig_table_exists($tbl)) continue;
    try {
        $rows = db()->query("SELECT $cols, COUNT(*) AS c FROM `$tbl` GROUP BY $cols HAVING c > 1 ORDER BY c DESC LIMIT 5")->fetchAll();
        foreach ($rows as $r) { $c = $r['c']; unset($r['c']); $dups[] = ['module' => $label, 'what' => implode(' / ', array_map(fn($v) => (string)$v, $r)), 'count' => $c]; }
    } catch (Throwable $e) { error_log('dup check ' . $tbl . ': ' . $e->getMessage()); }
}
$ledger = [];
try { migration_ledger_init(); $ledger = db()->query('SELECT id, description, result, applied_at FROM schema_migrations ORDER BY id')->fetchAll(); } catch (Throwable $e) {}

$report = schema_check_report();
$missingCount = count(array_filter($report, fn($r) => !$r['ok']));
?>
<div class="card-anu mb-4">
  <div class="body">
    <?php if ($missingCount === 0): ?>
      <div class="d-flex align-items-center gap-2 text-success">
        <i class="bi bi-check-circle-fill fs-4"></i>
        <div>
          <strong>All required tables and columns are present.</strong>
          <div class="small text-muted">Checked against the database this admin panel is currently connected to.</div>
        </div>
      </div>
    <?php else: ?>
      <div class="d-flex align-items-center gap-2 text-danger">
        <i class="bi bi-exclamation-triangle-fill fs-4"></i>
        <div>
          <strong><?= $missingCount ?> item<?= $missingCount === 1 ? '' : 's' ?> missing from this database.</strong>
          <div class="small text-muted">The pages that depend on these have a safe fallback and won't show a fatal error, but the feature they list will be hidden or degraded until the matching migration is run. See the deploy package's <code>database/</code> folder and run its <code>.sql</code> file(s), then reload this page.</div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="card-anu mb-4">
  <div class="body">
    <h2 class="h6">Database migrations</h2>
    <p class="small text-muted mb-2">One re-runnable step list that creates any missing table/column the admin modules need. It never deletes data. Back up the database first (deploy.sh does this automatically).</p>
    <form method="post" class="mb-3">
      <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
      <button class="btn btn-dark btn-sm" onclick="return confirm('Run the database migration now? Make sure you have a backup.');">Run / re-check migrations</button>
    </form>
    <?php if ($ledger): ?>
      <div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Step</th><th>What</th><th>Last change</th><th>When</th></tr></thead><tbody>
      <?php foreach ($ledger as $l): ?><tr><td><code><?= h($l['id']) ?></code></td><td><?= h($l['description']) ?></td><td class="small text-muted"><?= h($l['result']) ?></td><td class="small"><?= h($l['applied_at']) ?></td></tr><?php endforeach; ?>
      </tbody></table></div>
    <?php else: ?><p class="small text-warning mb-0">No migration has been recorded on this database yet.</p><?php endif; ?>
  </div>
</div>

<div class="card-anu mb-4">
  <div class="body p-0">
    <div class="p-3 pb-0"><h2 class="h6 mb-1">Data health: stored vs. shown on the public site</h2>
    <p class="small text-muted">"Stored" is every row in the database. "Visible" is rows marked published/active. "Hidden" rows exist but are deliberately not shown; "No status" rows have an empty status flag and may never display.</p></div>
    <div class="table-responsive"><table class="table table-anu mb-0">
      <thead><tr><th>Module</th><th>Table</th><th class="text-end">Stored</th><th class="text-end">Visible</th><th class="text-end">Hidden</th><th class="text-end">No status</th></tr></thead>
      <tbody>
      <?php foreach ($health as $hrow): ?>
        <tr>
          <td><?= h($hrow['label']) ?></td>
          <td><code><?= h($hrow['table']) ?></code></td>
          <?php if (!$hrow['exists']): ?>
            <td colspan="4" class="text-danger small">Table missing, run the migration above</td>
          <?php else: ?>
            <td class="text-end"><?= (int) $hrow['total'] ?></td>
            <td class="text-end"><?= $hrow['live'] === null ? '&ndash;' : (int) $hrow['live'] ?></td>
            <td class="text-end"><?= $hrow['hidden'] === null ? '&ndash;' : (int) $hrow['hidden'] ?></td>
            <td class="text-end"><?= $hrow['nullflag'] ? '<span class="badge bg-warning text-dark">' . (int) $hrow['nullflag'] . '</span>' : ($hrow['nullflag'] === null ? '&ndash;' : '0') ?></td>
          <?php endif; ?>
        </tr>
      <?php endforeach; ?>
      </tbody></table></div>
  </div>
</div>

<div class="card-anu mb-4">
  <div class="body">
    <h2 class="h6">Duplicate check</h2>
    <?php if (!$dups): ?>
      <p class="small text-success mb-0"><i class="bi bi-check-circle-fill"></i> No duplicate records found (same title/name entered twice).</p>
    <?php else: ?>
      <p class="small text-muted">These entries exist more than once. Review them in their module and delete the extra copy, nothing is removed automatically.</p>
      <table class="table table-sm mb-0"><thead><tr><th>Module</th><th>Entry</th><th class="text-end">Copies</th></tr></thead><tbody>
      <?php foreach ($dups as $d): ?><tr><td><?= h($d['module']) ?></td><td><?= h($d['what']) ?></td><td class="text-end"><span class="badge bg-warning text-dark"><?= (int)$d['count'] ?></span></td></tr><?php endforeach; ?>
      </tbody></table>
    <?php endif; ?>
  </div>
</div>

<div class="card-anu">
  <div class="body p-0">
    <div class="table-responsive">
      <table class="table table-anu mb-0">
        <thead>
          <tr>
            <th>Status</th>
            <th>Table</th>
            <th>Column</th>
            <th>Used for</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($report as $r): ?>
            <tr>
              <td>
                <?php if ($r['ok']): ?>
                  <span class="badge bg-success">OK</span>
                <?php else: ?>
                  <span class="badge bg-danger">MISSING</span>
                <?php endif; ?>
              </td>
              <td><code><?= h($r['table']) ?></code><?= !$r['table_ok'] ? ' <span class="text-danger small">(table not found)</span>' : '' ?></td>
              <td><?= $r['column'] ? '<code>' . h($r['column']) . '</code>' : '<span class="text-muted">(whole table)</span>' ?></td>
              <td class="text-muted small"><?= h($r['why']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
