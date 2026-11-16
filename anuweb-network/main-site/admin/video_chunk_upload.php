<?php
/**
 * Chunked, resumable hero-video upload (up to 2 GB).
 *
 * The browser (assets/js/chunk-upload.js) slices the file into 1 MB pieces
 * and sends each as its own small POST, so PHP's upload_max_filesize /
 * post_max_size never see more than ~1 MB at a time. Actions (JSON replies):
 *   status   GET  ?upload_id=...            -> which chunk numbers are already stored (resume)
 *   chunk    POST upload_id,index,total,name,chunk(file)
 *   finish   POST upload_id,total,name      -> joins parts, validates type, returns {file}
 * Every call needs a signed-in editor/marketing/super_admin and the CSRF token.
 */
require_once __DIR__ . '/includes/auth.php';
require_role(['editor','marketing','super_admin']);
current_admin();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

const CHUNK_MAX_TOTAL = 2147483648;   // 2 GB
const CHUNK_MAX_PIECE = 4194304;      // 4 MB hard cap per piece (client sends 1 MB)

function jfail(int $code, string $msg): void { http_response_code($code); echo json_encode(['ok' => false, 'error' => $msg]); exit; }

$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!is_string($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    jfail(403, 'Your session expired. Reload the page and try again.');
}

$uploadId = (string)($_REQUEST['upload_id'] ?? '');
if (!preg_match('/^[a-f0-9]{32}$/', $uploadId)) jfail(400, 'Bad upload id.');
$root = UPLOADS_PATH . '/_chunks';
$dir  = $root . '/' . $uploadId;
if (!is_dir($root)) { @mkdir($root, 0755, true); @file_put_contents($root . '/.htaccess', "Require all denied\n"); }

// housekeeping: drop abandoned uploads older than 24 h
foreach (glob($root . '/*', GLOB_ONLYDIR) ?: [] as $old) {
    if (filemtime($old) < time() - 86400) { foreach (glob($old . '/*') ?: [] as $f) @unlink($f); @rmdir($old); }
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'status') {
    $have = [];
    foreach (glob($dir . '/part-*') ?: [] as $f) $have[] = (int) substr(basename($f), 5);
    sort($have);
    echo json_encode(['ok' => true, 'have' => $have]); exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jfail(405, 'POST required.');
$total = (int)($_POST['total'] ?? 0);
if ($total < 1 || $total > (int) ceil(CHUNK_MAX_TOTAL / 1048576) + 1) jfail(400, 'File is larger than the 2 GB limit.');

if ($action === 'chunk') {
    $index = (int)($_POST['index'] ?? -1);
    if ($index < 0 || $index >= $total) jfail(400, 'Bad chunk number.');
    if (empty($_FILES['chunk']) || $_FILES['chunk']['error'] !== UPLOAD_ERR_OK) jfail(400, 'Chunk did not arrive (error ' . (int)($_FILES['chunk']['error'] ?? -1) . ').');
    if ($_FILES['chunk']['size'] > CHUNK_MAX_PIECE) jfail(400, 'Chunk too large.');
    if (!is_dir($dir) && !@mkdir($dir, 0755, true)) jfail(500, 'Server cannot create the upload folder (check permissions on assets/uploads).');
    if (!move_uploaded_file($_FILES['chunk']['tmp_name'], $dir . '/part-' . $index)) jfail(500, 'Could not store the chunk (disk full or permissions).');
    echo json_encode(['ok' => true, 'index' => $index]); exit;
}

if ($action === 'finish') {
    $parts = [];
    for ($i = 0; $i < $total; $i++) {
        $p = $dir . '/part-' . $i;
        if (!is_file($p)) jfail(409, 'Part ' . ($i + 1) . ' of ' . $total . ' is missing; resume the upload.');
        $parts[] = $p;
    }
    $tmp = $dir . '/joined.tmp';
    $out = fopen($tmp, 'wb');
    if (!$out) jfail(500, 'Could not create the output file.');
    $size = 0;
    foreach ($parts as $p) {
        $in = fopen($p, 'rb'); $size += stream_copy_to_stream($in, $out); fclose($in);
        if ($size > CHUNK_MAX_TOTAL) { fclose($out); @unlink($tmp); jfail(413, 'File is larger than the 2 GB limit.'); }
    }
    fclose($out);
    $mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $tmp);
    $allowed = ['video/mp4' => 'mp4', 'video/webm' => 'webm'];
    if (!isset($allowed[$mime])) {
        foreach (glob($dir . '/*') ?: [] as $f) @unlink($f); @rmdir($dir);
        jfail(415, 'Only MP4 or WEBM videos are allowed (this file looks like ' . $mime . ').');
    }
    $name = bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    if (!is_dir(UPLOADS_PATH)) @mkdir(UPLOADS_PATH, 0755, true);
    if (!rename($tmp, UPLOADS_PATH . '/' . $name)) jfail(500, 'Could not save the finished video.');
    foreach (glob($dir . '/*') ?: [] as $f) @unlink($f);
    @rmdir($dir);
    echo json_encode(['ok' => true, 'file' => $name, 'bytes' => $size]); exit;
}

jfail(400, 'Unknown action.');
