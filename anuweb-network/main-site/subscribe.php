<?php
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/index.php');
}
if (!csrf_check()) {
    redirect('/index.php?subscribed=error#subscribe');
}

// Spam trap: real visitors never see or fill this field.
if (trim($_POST['website'] ?? '') !== '') {
    redirect('/index.php?subscribed=1#subscribe');
}

// Gentle rate limit per visitor session (stops rapid-fire sign-ups).
$now = time();
$_SESSION['sub_attempts'] = array_values(array_filter($_SESSION['sub_attempts'] ?? [], fn($t) => $t > $now - 600));
if (count($_SESSION['sub_attempts']) >= 5) {
    redirect('/index.php?subscribed=wait#subscribe');
}
$_SESSION['sub_attempts'][] = $now;

$full_name = mb_substr(trim(preg_replace('/\s+/', ' ', $_POST['full_name'] ?? '')), 0, 150);
$email     = mb_strtolower(trim($_POST['email'] ?? ''));

if ($email === '' || strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect('/index.php?subscribed=invalid#subscribe');
}

$result = '1';
try {
    $q = db()->prepare('SELECT id, is_active FROM subscribers WHERE email = ?');
    $q->execute([$email]);
    $row = $q->fetch();
    if ($row) {
        if ((int)$row['is_active'] === 1) {
            $result = 'already';
        } else {
            db()->prepare('UPDATE subscribers SET is_active = 1, unsubscribed_at = NULL, full_name = COALESCE(NULLIF(?, ""), full_name) WHERE id = ?')
                ->execute([$full_name, $row['id']]);
            $result = 'back';
        }
    } else {
        db()->prepare('INSERT INTO subscribers (full_name, email, is_active, unsubscribe_token) VALUES (?,?,1,?)')
            ->execute([$full_name, $email, bin2hex(random_bytes(16))]);
    }
} catch (PDOException $e) {
    // Database not yet updated for unsubscribe support: fall back to the original simple insert.
    db()->prepare('INSERT IGNORE INTO subscribers (full_name, email) VALUES (?,?)')->execute([$full_name, $email]);
}

redirect('/index.php?subscribed=' . $result . '#subscribe');
