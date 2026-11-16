<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['editor','marketing','super_admin']);
$pageTitle = 'Research Proposals';
require __DIR__ . '/includes/admin_header.php';

if (isset($_GET['status_id'], $_GET['status_val']) && csrf_check()) {
    $stmt = db()->prepare('UPDATE research_proposals SET status = ? WHERE id = ?');
    $stmt->execute([$_GET['status_val'], (int)$_GET['status_id']]);
    redirect('/admin/proposals.php');
}

$rows = db()->query('SELECT * FROM research_proposals ORDER BY submitted_at DESC')->fetchAll();
$statuses = ['New','Under Review','Accepted','Declined'];
?>
<p class="text-muted small">Submissions from the Institute of Research site's "Submit a Proposal" form.</p>
<div class="card-anu">
  <div class="body p-0">
    <table class="table table-anu mb-0">
      <thead><tr><th>Submitted</th><th>Name</th><th>Title</th><th>Area</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td class="small"><?= format_date($r['submitted_at']) ?></td>
          <td><?= h($r['full_name']) ?><br><span class="small text-muted"><?= h($r['email']) ?></span></td>
          <td><?= h($r['proposal_title']) ?></td>
          <td><?= h($r['research_area']) ?></td>
          <td>
            <form method="get" class="d-inline">
              <input type="hidden" name="status_id" value="<?= (int)$r['id'] ?>">
              <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
              <select name="status_val" class="form-select form-select-sm" onchange="this.form.submit()">
                <?php foreach ($statuses as $s): ?>
                  <option value="<?= h($s) ?>" <?= $r['status']===$s?'selected':'' ?>><?= h($s) ?></option>
                <?php endforeach; ?>
              </select>
            </form>
          </td>
          <td class="text-end">
            <?php if ($r['attachment']): ?>
              <a href="<?= BASE_URL ?>/assets/uploads/<?= h($r['attachment']) ?>" class="btn btn-sm btn-outline-anu" download>Download</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="6" class="text-muted">No proposals submitted yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
