<?php
require_once __DIR__ . '/includes/auth.php';
require_super_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$roles = ['super_admin' => 'Super Admin', 'editor' => 'Editor', 'marketing' => 'Marketing'];
$u = ['full_name'=>'','email'=>'','username'=>'','role'=>'editor','is_active'=>1];
$errors = [];

if ($id) {
    $stmt = db()->prepare('SELECT * FROM admin_users WHERE id = ?');
    $stmt->execute([$id]);
    if ($found = $stmt->fetch()) $u = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = csrf_error_message();
    } else {
        $u['full_name']  = trim($_POST['full_name'] ?? '');
        $u['email']      = trim($_POST['email'] ?? '');
        $u['username']   = trim($_POST['username'] ?? '');
        $u['role']       = array_key_exists($_POST['role'] ?? '', $roles) ? $_POST['role'] : 'editor';
        $u['is_active']  = isset($_POST['is_active']) ? 1 : 0;
        $password        = $_POST['password'] ?? '';

        if ($u['full_name'] === '' || $u['username'] === '') $errors[] = 'Name and username are required.';
        if (!filter_var($u['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
        if (!$id && $password === '') $errors[] = 'A password is required for new accounts.';
        if ($password !== '' && strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';

        // Username uniqueness
        $check = db()->prepare('SELECT id FROM admin_users WHERE username = ? AND id != ?');
        $check->execute([$u['username'], $id]);
        if ($check->fetch()) $errors[] = 'That username is already taken.';

        // Prevent removing your own super_admin / deactivating yourself
        $me = current_admin();
        if ($id === (int)$me['id']) {
            if ($u['role'] !== 'super_admin') $errors[] = "You can't change your own role away from Super Admin.";
            if (!$u['is_active']) $errors[] = "You can't deactivate your own account.";
        }

        if (!$errors) {
            if ($id) {
                if ($password !== '') {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = db()->prepare('UPDATE admin_users SET full_name=?, email=?, username=?, role=?, is_active=?, password_hash=? WHERE id=?');
                    $stmt->execute([$u['full_name'],$u['email'],$u['username'],$u['role'],$u['is_active'],$hash,$id]);
                } else {
                    $stmt = db()->prepare('UPDATE admin_users SET full_name=?, email=?, username=?, role=?, is_active=? WHERE id=?');
                    $stmt->execute([$u['full_name'],$u['email'],$u['username'],$u['role'],$u['is_active'],$id]);
                }
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = db()->prepare('INSERT INTO admin_users (full_name, email, username, password_hash, role, is_active) VALUES (?,?,?,?,?,?)');
                $stmt->execute([$u['full_name'],$u['email'],$u['username'],$hash,$u['role'],$u['is_active']]);
            }
            redirect('/admin/users.php');
        }
    }
}

$pageTitle = $id ? 'Edit Admin User' : 'New Admin User';
require __DIR__ . '/includes/admin_header.php';
?>
<?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= h($e) ?></div><?php endforeach; ?>
<form method="post" class="card-anu">
  <div class="body">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Full name</label>
        <input type="text" name="full_name" class="form-control" value="<?= h($u['full_name']) ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="<?= h($u['email']) ?>" required>
      </div>
    </div>
    <div class="row g-3 mt-1">
      <div class="col-md-6">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" value="<?= h($u['username']) ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Role</label>
        <select name="role" class="form-select">
          <?php foreach ($roles as $val => $label): ?>
            <option value="<?= h($val) ?>" <?= $u['role']===$val?'selected':'' ?>><?= h($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="mb-3 mt-3">
      <label class="form-label"><?= $id ? 'New password (leave blank to keep current)' : 'Password' ?></label>
      <input type="password" name="password" class="form-control" minlength="8" <?= $id ? '' : 'required' ?>>
      <div class="form-text">At least 8 characters.</div>
    </div>
    <div class="form-check my-3">
      <input type="checkbox" name="is_active" id="act" class="form-check-input" <?= $u['is_active'] ? 'checked' : '' ?>>
      <label class="form-check-label" for="act">Active (can log in)</label>
    </div>
    <button class="btn btn-apply" type="submit">Save</button>
    <a href="<?= BASE_URL ?>/admin/users.php" class="btn btn-outline-anu">Cancel</a>
  </div>
</form>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
