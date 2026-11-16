<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once __DIR__ . '/../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok'=>false,'msg'=>'Method not allowed']); exit;
}
if (!checkRateLimit('rsvp', 15)) {
    echo json_encode(['ok'=>false,'msg'=>'Please wait a moment before submitting again.']); exit;
}

$event_id = (int)($_POST['event_id'] ?? 0);
$name     = trim($_POST['name']     ?? '');
$email    = trim($_POST['email']    ?? '');
$phone    = trim($_POST['phone']    ?? '');
$message  = trim($_POST['message']  ?? '');

if (!$event_id || !$name || !$email) {
    echo json_encode(['ok'=>false,'msg'=>'Name and email are required.']); exit;
}
if (empty($_POST['consent'])) {
    echo json_encode(['ok'=>false,'msg'=>'Please agree to the Privacy Policy to continue.']); exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['ok'=>false,'msg'=>'Please enter a valid email address.']); exit;
}

$db = getDB();

// Verify event exists
try {
    $ev = $db->prepare("SELECT id, title FROM events WHERE id=? AND is_active=1");
    $ev->execute([$event_id]);
    $event = $ev->fetch();
    if (!$event) { echo json_encode(['ok'=>false,'msg'=>'Event not found.']); exit; }
} catch(Exception $e) {
    echo json_encode(['ok'=>false,'msg'=>'Database error.']); exit;
}

// Insert or update RSVP
try {
    $db->prepare(
        "INSERT INTO event_rsvp (event_id, name, email, phone, message)
         VALUES (?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE name=VALUES(name), phone=VALUES(phone), message=VALUES(message), status='confirmed'"
    )->execute([$event_id, $name, $email, $phone ?: null, $message ?: null]);

    echo json_encode([
        'ok'    => true,
        'msg'   => "You're registered for \"{$event['title']}\"! We'll send reminders to {$email}.",
        'event' => $event['title'],
    ]);
} catch(Exception $e) {
    error_log('RSVP save failed: ' . $e->getMessage());
    echo json_encode(['ok'=>false,'msg'=>'Could not save registration. Please try again shortly.']);
}
?>
