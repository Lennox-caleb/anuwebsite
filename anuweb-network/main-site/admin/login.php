<?php
require_once __DIR__ . '/../includes/functions.php';

if (!empty($_SESSION['admin_id'])) {
    redirect('/admin/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $error = 'Session expired, please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Lockout: 5 failed sign-ins for this username from this address within
        // 15 minutes blocks further attempts until the window passes. If the
        // login_attempts table is missing (migration not run) login still works.
        $ip = substr($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', 0, 45);
        $locked = false;
        try {
            $c = db()->prepare('SELECT COUNT(*) FROM login_attempts WHERE username = ? AND ip = ? AND attempted_at > (NOW() - INTERVAL 15 MINUTE)');
            $c->execute([mb_substr($username, 0, 100), $ip]);
            $locked = (int) $c->fetchColumn() >= 5;
        } catch (Throwable $e) { error_log('login_attempts unavailable: ' . $e->getMessage()); }
        if ($locked) {
            $error = 'Too many failed sign-in attempts. Please wait 15 minutes and try again.';
        } else {
        $stmt = db()->prepare('SELECT * FROM admin_users WHERE username = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $user['id'];
            try { db()->prepare('DELETE FROM login_attempts WHERE username = ? AND ip = ?')->execute([mb_substr($username, 0, 100), $ip]); } catch (Throwable $e) {}
            db()->prepare('UPDATE admin_users SET last_login = NOW() WHERE id = ?')->execute([$user['id']]);
            redirect('/admin/dashboard.php');
        } else {
            $error = 'Invalid username or password.';
            try {
                db()->prepare('INSERT INTO login_attempts (username, ip) VALUES (?, ?)')->execute([mb_substr($username, 0, 100), $ip]);
            } catch (Throwable $e) { error_log('login_attempts insert failed: ' . $e->getMessage()); }
        }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Admin Sign In | Africa Nazarene University</title>
<link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/images/anu-logo.png">
<style>
  :root{ --red:#FF0000; --red-dark:#c8161d; --gold:#FAC823; --ink:#16161a; --muted:#6b6b76; --line:#e4e4ea; }
  *{ box-sizing:border-box; }
  html{ background:var(--red-dark); }
  body{
    margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px 16px;
    font-family:"Roboto","Segoe UI",system-ui,-apple-system,Arial,sans-serif; color:var(--ink);
    background-color:var(--red-dark);
    background-image:linear-gradient(rgba(0,0,0,.10),rgba(0,0,0,.18)),url('<?= BASE_URL ?>/assets/images/admin-bg.jpg');
    background-size:cover; background-position:center; background-repeat:no-repeat; background-attachment:fixed;
  }
  .auth-wrap{ width:100%; max-width:420px; }
  .auth-card{
    background:#fff; border-radius:20px; padding:34px 32px 28px;
    box-shadow:0 30px 60px -20px rgba(0,0,0,.55), 0 0 0 1px rgba(255,255,255,.4);
    position:relative; overflow:hidden;
  }
  .auth-card::before{ content:""; position:absolute; left:0; right:0; top:0; height:6px; background:linear-gradient(90deg,var(--red) 0 70%,var(--gold) 70% 100%); }
  .auth-logo{ display:block; width:230px; max-width:80%; height:auto; margin:6px auto 22px; }
  h1{ font-size:1.45rem; line-height:1.2; margin:0 0 6px; text-align:center; font-weight:700; letter-spacing:-.01em; }
  .sub{ text-align:center; color:var(--muted); font-size:.92rem; margin:0 0 24px; }
  .alert{ background:#fff1f1; border:1px solid #ffc9c9; color:#9b0d0d; border-radius:10px; padding:.7rem .9rem; font-size:.9rem; margin-bottom:18px; display:flex; gap:.55rem; align-items:flex-start; }
  .alert svg{ flex:0 0 18px; margin-top:1px; }
  label{ display:block; font-size:.82rem; font-weight:600; margin:0 0 6px; color:#33333b; }
  .field{ position:relative; margin-bottom:18px; }
  .field .ico{ position:absolute; left:14px; top:50%; transform:translateY(-50%); width:18px; height:18px; color:#9a9aa6; pointer-events:none; }
  .field input{
    width:100%; height:48px; border:1.5px solid var(--line); border-radius:12px; background:#fafafb;
    padding:0 46px 0 42px; font-size:1rem; color:var(--ink); font-family:inherit; transition:border-color .15s, box-shadow .15s, background .15s;
  }
  .field input:focus{ outline:none; border-color:var(--red); background:#fff; box-shadow:0 0 0 4px rgba(255,0,0,.12); }
  .toggle{ position:absolute; right:6px; top:50%; transform:translateY(-50%); border:0; background:transparent; width:38px; height:38px; border-radius:8px; cursor:pointer; color:#7b7b88; display:grid; place-items:center; }
  .toggle:hover{ background:#f0f0f3; color:#111; }
  .toggle:focus-visible{ outline:3px solid var(--gold); }
  .submit{
    width:100%; height:50px; border:0; border-radius:12px; cursor:pointer; font-size:1rem; font-weight:700; letter-spacing:.01em;
    color:#fff; background:var(--red); font-family:inherit; margin-top:4px; transition:transform .1s, box-shadow .15s, background .15s;
    box-shadow:0 8px 18px -8px rgba(255,0,0,.7);
  }
  .submit:hover{ background:#e60000; box-shadow:0 10px 22px -8px rgba(255,0,0,.8); }
  .submit:active{ transform:translateY(1px); }
  .submit:focus-visible{ outline:3px solid var(--gold); outline-offset:2px; }
  .auth-foot{ margin-top:22px; padding-top:16px; border-top:1px solid var(--line); display:flex; justify-content:space-between; align-items:center; gap:10px; font-size:.82rem; color:var(--muted); flex-wrap:wrap; }
  .auth-foot a{ color:var(--ink); font-weight:600; text-decoration:none; }
  .auth-foot a:hover{ color:var(--red); text-decoration:underline; }
  .lock{ display:inline-flex; align-items:center; gap:6px; }
  .copy{ text-align:center; color:rgba(255,255,255,.85); font-size:.78rem; margin-top:16px; }
  @media (max-width:480px){ .auth-card{ padding:28px 20px 22px; border-radius:16px; } }
  @media (prefers-reduced-motion:reduce){ *{ transition:none !important; } }
</style>
<link rel="stylesheet" href="/anuweb-network/main-site/assets/css/admin-theme.css?v=2">
<script src="/anuweb-network/main-site/assets/js/admin-theme.js?v=2" defer></script>
</head>
<body>
<main class="auth-wrap">
  <div class="auth-card">
    <img class="auth-logo" src="<?= BASE_URL ?>/assets/images/anu-logo.png" alt="Africa Nazarene University">
    <h1>Welcome back</h1>
    <p class="sub">Sign in to manage the university website</p>

    <?php if ($error): ?>
      <div class="alert" role="alert">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v5M12 16.5v.01"/></svg>
        <span><?= h($error) ?></span>
      </div>
    <?php endif; ?>

    <form method="post" autocomplete="on">
      <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
      <label for="username">Username</label>
      <div class="field">
        <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-7 8-7s8 3 8 7"/></svg>
        <input type="text" id="username" name="username" required autofocus autocomplete="username" placeholder="Enter your username" value="<?= h($_POST['username'] ?? '') ?>">
      </div>
      <label for="password">Password</label>
      <div class="field">
        <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="10" width="16" height="11" rx="2.5"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
        <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="Enter your password">
        <button type="button" class="toggle" id="pwToggle" aria-label="Show password" aria-pressed="false">
          <svg id="eyeOn" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
          <svg id="eyeOff" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M3 3l18 18M10.6 6.2A9.7 9.7 0 0 1 12 6c6.4 0 10 6 10 6a17 17 0 0 1-3.2 3.9M6.2 7.6C3.6 9.3 2 12 2 12s3.6 7 10 7c1.6 0 3-.4 4.3-1"/></svg>
        </button>
      </div>
      <button class="submit" type="submit">Sign In</button>
    </form>

    <div class="auth-foot">
      <span class="lock"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="10" width="16" height="11" rx="2.5"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>Authorised staff only</span>
      <a href="<?= BASE_URL ?>/index.php">&larr; Back to website</a>
    </div>
  </div>
</main>
<script>
(function(){
  var b=document.getElementById('pwToggle'), i=document.getElementById('password');
  if(!b||!i) return;
  b.addEventListener('click',function(){
    var show=i.type==='password'; i.type=show?'text':'password';
    document.getElementById('eyeOn').style.display=show?'none':'';
    document.getElementById('eyeOff').style.display=show?'':'none';
    b.setAttribute('aria-pressed',show?'true':'false'); b.setAttribute('aria-label',show?'Hide password':'Show password');
  });
})();
</script>
</body>
</html>
