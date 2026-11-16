<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/admin-header.php';

$stats = [
    'programmes' => db()->query("SELECT COUNT(*) c FROM programmes WHERE is_published=1")->fetch()['c'],
    'applications' => db()->query("SELECT COUNT(*) c FROM applications")->fetch()['c'],
    'new_applications' => db()->query("SELECT COUNT(*) c FROM applications WHERE status='submitted'")->fetch()['c'],
    'messages' => db()->query("SELECT COUNT(*) c FROM contact_messages WHERE status='new'")->fetch()['c'],
    'staff' => db()->query("SELECT COUNT(*) c FROM staff WHERE is_published=1")->fetch()['c'],
    'news' => db()->query("SELECT COUNT(*) c FROM news WHERE is_published=1")->fetch()['c'],
];

$recentApplications = db()->query("SELECT a.*, p.title AS programme_title FROM applications a LEFT JOIN programmes p ON p.id = a.programme_id ORDER BY a.created_at DESC LIMIT 6")->fetchAll();
$recentMessages = db()->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>

<div class="row g-3 mb-4">
  <div class="col-6 col-lg-2">
    <div class="stat-tile"><b><?= (int)$stats['programmes'] ?></b><span>Programmes</span></div>
  </div>
  <div class="col-6 col-lg-2">
    <div class="stat-tile gold"><b><?= (int)$stats['applications'] ?></b><span>Applications</span></div>
  </div>
  <div class="col-6 col-lg-2">
    <div class="stat-tile"><b><?= (int)$stats['new_applications'] ?></b><span>New Applications</span><br><a href="applications.php">Review &rarr;</a></div>
  </div>
  <div class="col-6 col-lg-2">
    <div class="stat-tile black"><b><?= (int)$stats['messages'] ?></b><span>Unread Messages</span><br><a href="messages.php">View &rarr;</a></div>
  </div>
  <div class="col-6 col-lg-2">
    <div class="stat-tile"><b><?= (int)$stats['staff'] ?></b><span>Staff Published</span></div>
  </div>
  <div class="col-6 col-lg-2">
    <div class="stat-tile gold"><b><?= (int)$stats['news'] ?></b><span>News Articles</span></div>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="admin-card">
      <div class="admin-card-header">
        <h2>Recent Applications</h2>
        <a href="applications.php" class="btn btn-sm btn-outline-secondary">View all</a>
      </div>
      <div class="table-responsive">
        <table class="table table-admin">
          <thead><tr><th>Ref #</th><th>Applicant</th><th>Programme</th><th>Status</th><th>Date</th></tr></thead>
          <tbody>
          <?php if (!$recentApplications): ?><tr><td colspan="5" class="text-muted text-center py-4">No applications yet.</td></tr><?php endif; ?>
          <?php foreach ($recentApplications as $a): ?>
            <tr>
              <td><a href="applications.php?view=<?= (int)$a['id'] ?>"><?= h($a['application_no']) ?></a></td>
              <td><?= h($a['full_name']) ?></td>
              <td class="small"><?= h($a['programme_title'] ?? '-') ?></td>
              <td><span class="badge badge-status-<?= h($a['status']) ?>"><?= h(ucwords(str_replace('_',' ',$a['status']))) ?></span></td>
              <td class="small text-muted"><?= fdate($a['created_at']) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="admin-card">
      <div class="admin-card-header">
        <h2>Recent Messages</h2>
        <a href="messages.php" class="btn btn-sm btn-outline-secondary">View all</a>
      </div>
      <?php if (!$recentMessages): ?><p class="text-muted text-center py-4 mb-0">No messages yet.</p><?php endif; ?>
      <?php foreach ($recentMessages as $m): ?>
        <div class="d-flex justify-content-between border-bottom py-2">
          <div>
            <strong class="d-block small"><?= h($m['full_name']) ?></strong>
            <span class="text-muted small"><?= h(excerpt($m['message'], 60)) ?></span>
          </div>
          <span class="badge badge-status-<?= h($m['status']) ?> align-self-start"><?= h(ucfirst($m['status'])) ?></span>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="admin-card mt-4">
      <div class="admin-card-header"><h2>Quick Actions</h2></div>
      <div class="d-grid gap-2">
        <a href="programme-edit.php" class="btn btn-admin-primary"><i class="bi bi-plus-circle"></i> Add Programme</a>
        <a href="crud.php?entity=news&action=new" class="btn btn-admin-dark"><i class="bi bi-plus-circle"></i> Add News Article</a>
        <a href="crud.php?entity=sliders&action=new" class="btn btn-admin-gold"><i class="bi bi-plus-circle"></i> Add Homepage Slide</a>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
