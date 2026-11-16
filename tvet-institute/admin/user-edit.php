<?php
$pageTitle = 'Add / Edit Admin User';
require_once __DIR__ . '/includes/auth.php';
require_role(['super_admin']);

$id = (int)($_GET['id'] ?? 0);
$user = ['role' => 'editor', 'is_active' => 1];
if ($id > 0) {
    $stmt = db()->prepare('SELECT * FROM admins WHERE id = ?');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found) { flash_set('danger', 'User not found.'); redirect(ADMIN_URL . '/users.php'); }
    $user = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash_set('danger', 'Session expired.');
        redirect(ADMIN_URL . '/user-edit.php' . ($id ? "?id=$id" : ''));
    }
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'editor';
    $password = (string)($_POST['password'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($full_name === '' || $username === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash_set('danger', 'Please provide a valid full name, username and email.');
        redirect(ADMIN_URL . '/user-edit.php' . ($id ? "?id=$id" : ''));
    }

    try {
        if ($id > 0) {
            if ($password !== '') {
                db()->prepare('UPDATE admins SET full_name=?, username=?, email=?, role=?, is_active=?, password_hash=? WHERE id=?')
                    ->execute([$full_name, $username, $email, $role, $is_active, password_hash($password, PASSWORD_DEFAULT), $id]);
            } else {
                db()->prepare('UPDATE admins SET full_name=?, username=?, email=?, role=?, is_active=? WHERE id=?')
                    ->execute([$full_name, $username, $email, $role, $is_active, $id]);
            }
            flash_set('success', 'Admin user updated.');
        } else {
            if ($password === '') {
                flash_set('danger', 'Password is required for a new user.');
                redirect(ADMIN_URL . '/user-edit.php');
            }
            db()->prepare('INSERT INTO admins (full_name, username, email, password_hash, role, is_active) VALUES (?,?,?,?,?,?)')
                ->execute([$full_name, $username, $email, password_hash($password, PASSWORD_DEFAULT), $role, $is_active]);
            flash_set('success', 'Admin user created.');
        }
    } catch (Throwable $e) {
        flash_set('danger', 'Could not save user (username/email may already be taken).');
        redirect(ADMIN_URL . '/user-edit.php' . ($id ? "?id=$id" : ''));
    }
    redirect(ADMIN_URL . '/users.php');
}
require __DIR__ . '/includes/admin-header.php';
?>
<div class="admin-card" style="max-width:640px">
  <div class="admin-card-header">
    <h2><?= $id ? 'Edit' : 'Add' ?> Admin User</h2>
    <a href="users.php" class="btn btn-sm btn-outline-secondary">&larr; Back to list</a>
  </div>
  <form method="post">
    <?= csrf_field() ?>
    <div class="mb-3"><label class="form-label">Full Name *</label><input required type="text" name="full_name" class="form-control" value="<?= h($user['full_name'] ?? '') ?>"></div>
    <div class="row">
      <div class="col-md-6 mb-3"><label class="form-label">Username *</label><input required type="text" name="username" class="form-control" value="<?= h($user['username'] ?? '') ?>"></div>
      <div class="col-md-6 mb-3"><label class="form-label">Email *</label><input required type="email" name="email" class="form-control" value="<?= h($user['email'] ?? '') ?>"></div>
    </div>
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Role</label>
        <select name="role" class="form-select">
          <option value="editor" <?= ($user['role'] ?? '') === 'editor' ? 'selected' : '' ?>>Editor (content)</option>
          <option value="registrar" <?= ($user['role'] ?? '') === 'registrar' ? 'selected' : '' ?>>Registrar (applications)</option>
          <option value="super_admin" <?= ($user['role'] ?? '') === 'super_admin' ? 'selected' : '' ?>>Super Admin (full access)</option>
        </select>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label"><?= $id ? 'New Password (leave blank to keep current)' : 'Password *' ?></label>
        <input type="password" name="password" class="form-control" <?= $id ? '' : 'required' ?>>
      </div>
    </div>
    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" id="active" name="is_active" value="1" <?= !empty($user['is_active']) ? 'checked' : '' ?>>
      <label class="form-check-label" for="active">Account Active</label>
    </div>
    <button type="submit" class="btn btn-admin-primary"><i class="bi bi-check-circle"></i> Save</button>
  </form>
</div>
<?php require __DIR__ . '/includes/admin-footer.php'; ?>
