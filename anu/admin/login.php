<?php
require_once __DIR__ . '/../includes/config.php';
if (session_status()===PHP_SESSION_NONE) session_start();
if (!empty($_SESSION['admin_id'])) { header('Location: ' . SITE_URL . '/admin/dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $user = trim($_POST['username']??'');
    $pass = $_POST['password']??'';
    try {
        $db = getDB();
        $s  = $db->prepare("SELECT * FROM admin_users WHERE username=? AND is_active=1 LIMIT 1");
        $s->execute([$user]);
        $row = $s->fetch();
        if ($row && password_verify($pass, $row['password'])) {
            $_SESSION['admin_id']   = $row['id'];
            $_SESSION['admin_name'] = $row['full_name'] ?: $row['username'];
            $_SESSION['admin_role'] = $row['role'];
            header('Location: ' . SITE_URL . '/admin/dashboard.php'); exit;
        } else { $error = 'Invalid username or password.'; }
    } catch(Exception $e) { $error = 'Database error. Check setup.'; }
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Login | ANU Online</title>
<link rel="icon" type="image/png" sizes="32x32" href="/anu/assets/images/favicon-32.png?v=2">
<link rel="icon" type="image/png" sizes="48x48" href="/anu/assets/images/favicon-48.png?v=2">
<link rel="shortcut icon" href="/anu/assets/images/favicon.ico?v=2">
<link rel="apple-touch-icon" href="/anu/assets/images/favicon-180.png?v=2">
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:"Roboto",system-ui,sans-serif;background:linear-gradient(135deg,#100005 0%,#1a0000 60%,#0a0a18 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.card{background:#fff;border-radius:20px;padding:40px;width:100%;max-width:400px;box-shadow:0 32px 80px rgba(0,0,0,.5)}
.logo{text-align:center;margin-bottom:28px}
.logo-icon{width:60px;height:60px;border-radius:16px;background:linear-gradient(135deg,#cc0000,#880000);display:flex;align-items:center;justify-content:center;margin:0 auto 10px;font-size:1.6rem;font-weight:800;color:#fff;font-family:Georgia,serif}
.logo h2{font-size:1.3rem;font-weight:800;color:#1a0a0a;margin-bottom:2px}
.logo p{font-size:.8rem;color:#999}
.form-group{margin-bottom:16px}
label{display:block;font-size:.8rem;font-weight:600;color:#444;margin-bottom:5px}
.input-wrap{position:relative}
.input-wrap i{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:#aaa;font-size:.95rem}
input{width:100%;padding:11px 14px 11px 38px;border:1.5px solid #e0e0e8;border-radius:10px;font-size:.9rem;font-family:inherit;transition:.18s;outline:none;background:#fafafa}
input:focus{border-color:#cc0000;box-shadow:0 0 0 3px rgba(204,0,0,.1);background:#fff}
.error{background:#fff0f0;border:1px solid #ffd0d0;color:#c00;border-radius:8px;padding:10px 14px;font-size:.83rem;margin-bottom:14px;display:flex;gap:8px;align-items:center}
.btn{width:100%;padding:13px;background:linear-gradient(135deg,#cc0000,#880000);color:#fff;border:none;border-radius:10px;font-size:.95rem;font-weight:700;cursor:pointer;font-family:inherit;transition:.2s;margin-top:4px}
.btn:hover{opacity:.9;transform:translateY(-1px)}
.back{text-align:center;margin-top:18px;font-size:.8rem;color:#aaa}
.back a{color:#cc0000;font-weight:600}
</style>
<link rel="stylesheet" href="/anuweb-network/main-site/assets/css/admin-theme.css?v=2">
<script src="/anuweb-network/main-site/assets/js/admin-theme.js?v=2" defer></script>
</head>
<body>
<div class="card">
  <div class="logo">
    <img src="/anu/assets/images/anu-logo-white.png" alt="Africa Nazarene University Online" style="height:84px;width:auto;object-fit:contain;margin-bottom:0px;">
    <h2></h2>
    <p></p>
  </div>
  <?php if($error):?><div class="error"><i class="bi bi-exclamation-circle-fill"></i><?=htmlspecialchars($error)?></div><?php endif;?>
  <form method="POST">
    <div class="form-group">
      <label>Username</label>
      <div class="input-wrap">
        <i class="bi bi-person"></i>
        <input type="text" name="username" placeholder="Enter username" required autocomplete="username" value="<?=htmlspecialchars($_POST['username']??'')?>">
      </div>
    </div>
    <div class="form-group">
      <label>Password</label>
      <div class="input-wrap">
        <i class="bi bi-lock"></i>
        <input type="password" name="password" placeholder="Enter password" required autocomplete="current-password">
      </div>
    </div>
    <button type="submit" class="btn">Sign In <i class="bi bi-arrow-right ms-1"></i></button>
  </form>
  <div class="back"><a href="/anu/index.php"><i class="bi bi-arrow-left me-1"></i>Back to Website</a></div>
</div>
</body></html>
