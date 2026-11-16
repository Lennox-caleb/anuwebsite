<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (current_admin()) {
    redirect(ADMIN_URL . '/index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Session expired, please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        if (attempt_login($username, $password)) {
            redirect(ADMIN_URL . '/index.php');
        }
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Login | ANU Non-Degree Programmes</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="<?= ADMIN_URL ?>/assets/css/admin.css">
<link rel="stylesheet" href="/anuweb-network/main-site/assets/css/admin-theme.css?v=2">
<script src="/anuweb-network/main-site/assets/js/admin-theme.js?v=2" defer></script>
</head>
<body>
<div class="admin-login-wrap">
  <div class="admin-login-card">
    <img src="<?= BASE_URL ?>/assets/images/anu-logo.png" alt="ANU logo">
    <h1>Non-Degree Programmes Admin</h1>
    <p class="subtitle">Sign in to manage the site</p>
    <?php if ($error): ?><div class="alert alert-danger small"><?= h($error) ?></div><?php endif; ?>
    <form method="post">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" required autofocus>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button class="btn btn-admin-primary w-100 py-2" type="submit">Sign In</button>
    </form>
    <p class="text-center small text-muted mt-4 mb-0"><a href="<?= BASE_URL ?>/index.php">&larr; Back to public site</a></p>
  </div>
</div>
</body>
</html>
