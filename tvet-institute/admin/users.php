<?php
$pageTitle = 'Admin Users';
require_once __DIR__ . '/includes/auth.php';
require_role(['super_admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'delete') {
    if (csrf_verify()) {
        $delId = (int)$_POST['id'];
        if ($delId === (int)current_admin()['id']) {
            flash_set('danger', 'You cannot delete your own account while logged in.');
        } else {
            db()->prepare('DELETE FROM admins WHERE id = ?')->execute([$delId]);
            flash_set('success', 'Admin user deleted.');
        }
    }
    redirect(ADMIN_URL . '/users.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'toggle') {
    if (csrf_verify()) {
        db()->prepare('UPDATE admins SET is_active = 1 - is_active WHERE id = ?')->execute([(int)$_POST['id']]);
    }
    redirect(ADMIN_URL . '/users.php');
}

$users = db()->query('SELECT id, full_name, username, email, role, is_active, last_login_at, created_at FROM admins ORDER BY created_at')->fetchAll();
require __DIR__ . '/includes/admin-header.php';
?>
<div class="admin-card">
  <div class="admin-card-header">
    <h2>Admin Users <span class="text-muted small">(<?= count($users) ?>)</span></h2>
    <a href="user-edit.php" class="btn btn-admin-primary btn-sm"><i class="bi bi-person-plus"></i> Add Admin User</a>
  </div>
  <div class="table-responsive">
    <table class="table table-admin table-hover align-middle">
      <thead><tr><th>Name</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Last Login</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><?= h($u['full_name']) ?></td>
          <td><?= h($u['username']) ?></td>
          <td class="small"><?= h($u['email']) ?></td>
          <td><span class="badge bg-dark"><?= h(ucwords(str_replace('_',' ',$u['role']))) ?></span></td>
          <td>
            <form method="post" class="d-inline">
              <?= csrf_field() ?><input type="hidden" name="form" value="toggle"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
              <button class="btn btn-sm <?= $u['is_active'] ? 'btn-outline-success' : 'btn-outline-secondary' ?>" type="submit"><?= $u['is_active'] ? 'Active' : 'Disabled' ?></button>
            </form>
          </td>
          <td class="small text-muted"><?= $u['last_login_at'] ? fdate($u['last_login_at'], 'd M Y, H:i') : 'Never' ?></td>
          <td class="text-end">
            <a href="user-edit.php?id=<?= (int)$u['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
            <form method="post" class="d-inline" onsubmit="return confirm('Delete this admin user?');">
              <?= csrf_field() ?><input type="hidden" name="form" value="delete"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
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
