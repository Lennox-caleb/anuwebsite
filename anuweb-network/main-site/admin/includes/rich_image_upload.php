<?php
/**
 * Shared picture upload for the rich-text editors (newsletters, news).
 * rich_image_upload('newsletters') saves into assets/uploads/newsletters and answers JSON {url} or {error}.
 * JPG, PNG, GIF, WebP up to 5 MB; large pictures are scaled to 1200 px wide; files get random names.
 */
function rich_image_out(int $code, array $data): void { http_response_code($code); echo json_encode($data); exit; }

function rich_image_upload(string $folder): void
{
    header('Content-Type: application/json; charset=UTF-8');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') rich_image_out(405, ['error' => 'POST only.']);
    if (!csrf_check()) rich_image_out(403, ['error' => 'Your session expired. Reload the page and try again (save your work first).']);

    $f = $_FILES['image'] ?? null;
    if (!$f || ($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $code = $f['error'] ?? UPLOAD_ERR_NO_FILE;
        $msg = in_array($code, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
            ? 'That picture is larger than the server allows (limit ' . ini_get('upload_max_filesize') . '). Use a smaller picture.'
            : 'The picture did not upload. Please try again.';
        rich_image_out(400, ['error' => $msg]);
    }
    if ($f['size'] > 5 * 1024 * 1024) rich_image_out(400, ['error' => 'Pictures can be up to 5 MB.']);

    $info = @getimagesize($f['tmp_name']);
    $types = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif'];
    if (defined('IMAGETYPE_WEBP')) $types[IMAGETYPE_WEBP] = 'webp';
    if (!$info || !isset($types[$info[2]])) rich_image_out(400, ['error' => 'Please choose a JPG, PNG, GIF or WebP picture.']);
    $ext = $types[$info[2]];

    $dir = __DIR__ . '/../../assets/uploads/' . $folder;
    if (!is_dir($dir) && !@mkdir($dir, 0775, true)) rich_image_out(500, ['error' => 'The server could not create the pictures folder (assets/uploads/' . $folder . '). Ask your administrator to make it writable.']);
    $name = substr($folder, 0, 2) . '-' . date('Ymd') . '-' . bin2hex(random_bytes(6)) . '.' . $ext;
    $dest = $dir . '/' . $name;

    $saved = false;
    if ($info[0] > 1200 && $ext !== 'gif' && function_exists('imagecreatetruecolor')) {
        $create = ['jpg' => 'imagecreatefromjpeg', 'png' => 'imagecreatefrompng', 'webp' => 'imagecreatefromwebp'][$ext] ?? null;
        if ($create && function_exists($create) && ($src = @$create($f['tmp_name']))) {
            $w = 1200; $h = (int) round($info[1] * $w / $info[0]);
            $dst = imagecreatetruecolor($w, $h);
            if ($ext !== 'jpg') { imagealphablending($dst, false); imagesavealpha($dst, true); }
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $w, $h, $info[0], $info[1]);
            $saved = $ext === 'jpg' ? imagejpeg($dst, $dest, 85) : ($ext === 'png' ? imagepng($dst, $dest, 6) : imagewebp($dst, $dest, 85));
            imagedestroy($src); imagedestroy($dst);
        }
    }
    if (!$saved && !move_uploaded_file($f['tmp_name'], $dest)) rich_image_out(500, ['error' => 'The server could not save the picture.']);
    @chmod($dest, 0664);

    rich_image_out(200, ['url' => UPLOADS_URL . '/' . $folder . '/' . $name]);
}
