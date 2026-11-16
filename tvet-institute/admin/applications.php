<?php
$pageTitle = 'Applications';
require_once __DIR__ . '/includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'update_status') {
    if (csrf_verify()) {
        $aid = (int)$_POST['id'];
        $status = $_POST['status'] ?? 'submitted';
        $notes = trim($_POST['admin_notes'] ?? '');
        db()->prepare('UPDATE applications SET status = ?, admin_notes = ? WHERE id = ?')->execute([$status, $notes, $aid]);
        flash_set('success', 'Application updated.');
    }
    redirect(ADMIN_URL . '/applications.php?view=' . (int)$_POST['id']);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'delete') {
    if (csrf_verify()) {
        db()->prepare('DELETE FROM applications WHERE id = ?')->execute([(int)$_POST['id']]);
        flash_set('success', 'Application deleted.');
    }
    redirect(ADMIN_URL . '/applications.php');
}

$statusFilter = trim($_GET['status'] ?? '');
$sql = "SELECT a.*, p.title AS programme_title FROM applications a LEFT JOIN programmes p ON p.id = a.programme_id WHERE 1=1";
$params = [];
if ($statusFilter !== '') { $sql .= " AND a.status = ?"; $params[] = $statusFilter; }
$sql .= " ORDER BY a.created_at DESC";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$applications = $stmt->fetchAll();

$viewId = (int)($_GET['view'] ?? 0);
$viewRecord = null;
if ($viewId) {
    $vs = db()->prepare("SELECT a.*, p.title AS programme_title FROM applications a LEFT JOIN programmes p ON p.id = a.programme_id WHERE a.id = ?");
    $vs->execute([$viewId]);
    $viewRecord = $vs->fetch();
}
$statuses = ['submitted' => 'Submitted', 'under_review' => 'Under Review', 'shortlisted' => 'Shortlisted', 'admitted' => 'Admitted', 'rejected' => 'Rejected'];
require __DIR__ . '/includes/admin-header.php';
?>

<?php if ($viewRecord): ?>
  <div class="admin-card mb-4">
    <div class="admin-card-header">
      <h2>Application <?= h($viewRecord['application_no']) ?></h2>
      <a href="applications.php" class="btn btn-sm btn-outline-secondary">&larr; Back to list</a>
    </div>
    <div class="row g-4">
      <div class="col-lg-7">
        <table class="table table-sm">
          <tr><th style="width:40%">Full Name</th><td><?= h($viewRecord['full_name']) ?></td></tr>
          <tr><th>Gender</th><td><?= h($viewRecord['gender']) ?></td></tr>
          <tr><th>Date of Birth</th><td><?= h($viewRecord['dob']) ?></td></tr>
          <tr><th>ID / Birth Cert No.</th><td><?= h($viewRecord['id_or_birth_cert_no']) ?></td></tr>
          <tr><th>Nationality</th><td><?= h($viewRecord['nationality']) ?></td></tr>
          <tr><th>Email</th><td><a href="mailto:<?= h($viewRecord['email']) ?>"><?= h($viewRecord['email']) ?></a></td></tr>
          <tr><th>Phone</th><td><a href="tel:<?= h($viewRecord['phone']) ?>"><?= h($viewRecord['phone']) ?></a></td></tr>
          <tr><th>County / Region</th><td><?= h($viewRecord['county']) ?></td></tr>
          <tr><th>Programme</th><td><?= h($viewRecord['programme_title'] ?? '-') ?></td></tr>
          <tr><th>Preferred Intake</th><td><?= h($viewRecord['preferred_intake']) ?></td></tr>
          <tr><th>Mode of Study</th><td><?= h($viewRecord['mode_of_study']) ?></td></tr>
          <tr><th>Academic Background</th><td><?= nl2br(h($viewRecord['education_background'])) ?></td></tr>
          <tr><th>Sponsor</th><td><?= h($viewRecord['sponsor']) ?></td></tr>
          <tr><th>Submitted</th><td><?= fdate($viewRecord['created_at'], 'd M Y, H:i') ?></td></tr>
        </table>
      </div>
      <div class="col-lg-5">
        <form method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="form" value="update_status">
          <input type="hidden" name="id" value="<?= (int)$viewRecord['id'] ?>">
          <label class="form-label">Status</label>
          <select name="status" class="form-select mb-3">
            <?php foreach ($statuses as $val => $label): ?>
              <option value="<?= $val ?>" <?= $viewRecord['status'] === $val ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
          </select>
          <label class="form-label">Internal Notes</label>
          <textarea name="admin_notes" rows="5" class="form-control mb-3"><?= h($viewRecord['admin_notes'] ?? '') ?></textarea>
          <button class="btn btn-admin-primary w-100" type="submit"><i class="bi bi-check-circle"></i> Update Application</button>
        </form>
      </div>
    </div>
  </div>
<?php endif; ?>

<div class="admin-card">
  <div class="admin-card-header">
    <h2>All Applications <span class="text-muted small">(<?= count($applications) ?>)</span></h2>
    <form method="get" class="d-flex gap-2">
      <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="">All statuses</option>
        <?php foreach ($statuses as $val => $label): ?>
          <option value="<?= $val ?>" <?= $statusFilter === $val ? 'selected' : '' ?>><?= $label ?></option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>
  <div class="table-responsive">
    <table class="table table-admin table-hover align-middle">
      <thead><tr><th>Ref #</th><th>Applicant</th><th>Programme</th><th>Phone</th><th>Status</th><th>Date</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
      <?php if (!$applications): ?><tr><td colspan="7" class="text-center text-muted py-4">No applications yet.</td></tr><?php endif; ?>
      <?php foreach ($applications as $a): ?>
        <tr>
          <td><a href="applications.php?view=<?= (int)$a['id'] ?>"><?= h($a['application_no']) ?></a></td>
          <td><?= h($a['full_name']) ?><div class="small text-muted"><?= h($a['email']) ?></div></td>
          <td class="small"><?= h($a['programme_title'] ?? '-') ?></td>
          <td class="small"><?= h($a['phone']) ?></td>
          <td><span class="badge badge-status-<?= h($a['status']) ?>"><?= h(ucwords(str_replace('_',' ',$a['status']))) ?></span></td>
          <td class="small text-muted"><?= fdate($a['created_at']) ?></td>
          <td class="text-end">
            <a href="applications.php?view=<?= (int)$a['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
            <form method="post" class="d-inline" onsubmit="return confirm('Delete this application?');">
              <?= csrf_field() ?>
              <input type="hidden" name="form" value="delete">
              <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
              <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/includes/admin-footer.php'; ?>
