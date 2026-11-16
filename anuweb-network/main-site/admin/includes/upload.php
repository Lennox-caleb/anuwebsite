<?php
/**
 * Safely handle a single image upload from an admin form.
 * Returns the new filename on success, null if no file was submitted,
 * or throws a RuntimeException with a user-facing message on failure.
 */
function handle_image_upload(string $fieldName): ?string
{
    if (empty($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$fieldName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed. Please try again.');
    }

    $maxBytes = 4 * 1024 * 1024; // 4MB
    if ($file['size'] > $maxBytes) {
        throw new RuntimeException('Image is too large (max 4MB).');
    }

    $allowed = [
        'image/jpeg'    => 'jpg',
        'image/png'     => 'png',
        'image/webp'    => 'webp',
        'image/svg+xml' => 'svg',
    ];

    // finfo_close() is deprecated as of PHP 8.5 -- finfo objects are freed
    // automatically once they go out of scope, so it's no longer needed.
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);

    // Some servers' fileinfo reports an SVG as text/plain or text/xml
    // instead of image/svg+xml (SVG is just XML text, so this is common).
    // Fall back to checking the extension + sniffing the file content for
    // an <svg> tag before rejecting it.
    $isSvgByExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) === 'svg';
    if (!isset($allowed[$mime]) && $isSvgByExt && in_array($mime, ['text/plain', 'text/xml', 'application/xml'], true)) {
        $head = file_get_contents($file['tmp_name'], false, null, 0, 4096);
        if ($head !== false && stripos($head, '<svg') !== false) {
            $mime = 'image/svg+xml';
        }
    }

    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Only JPG, PNG, WEBP or SVG images are allowed.');
    }

    if (!is_dir(UPLOADS_PATH)) {
        mkdir(UPLOADS_PATH, 0755, true);
    }

    $filename = bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    $destination = UPLOADS_PATH . '/' . $filename;

    if ($mime === 'image/svg+xml') {
        // SVG is XML, so a malicious file can carry <script> tags or
        // on*="" event-handler attributes. Strip those out before saving
        // rather than trusting the upload as-is.
        $svg = file_get_contents($file['tmp_name']);
        if ($svg === false) {
            throw new RuntimeException('Could not read the uploaded file.');
        }
        $svg = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $svg);
        $svg = preg_replace('/\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $svg);
        $svg = preg_replace('#<!DOCTYPE[^>]*ENTITY[^>]*>#is', '', $svg);
        if (stripos($svg, '<svg') === false) {
            throw new RuntimeException('That file does not look like a valid SVG image.');
        }
        if (file_put_contents($destination, $svg) === false) {
            throw new RuntimeException('Could not save the uploaded file.');
        }
    } elseif (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Could not save the uploaded file.');
    }

    return $filename;
}

/**
 * Safely handle a single hero-video upload from an admin form. Same
 * contract as handle_image_upload() above: returns the new filename, null
 * if nothing was submitted, or throws with a user-facing message.
 */
function handle_video_upload(string $fieldName): ?string
{
    if (empty($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$fieldName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Video upload failed. Please try again.');
    }

    $maxBytes = 20 * 1024 * 1024; // 20MB -- keep hero videos short/compressed
    if ($file['size'] > $maxBytes) {
        throw new RuntimeException('Video is too large (max 20MB). Trim or compress it first -- a hero background clip should be a short, muted loop.');
    }

    $allowed = [
        'video/mp4'  => 'mp4',
        'video/webm' => 'webm',
    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);

    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Only MP4 or WEBM videos are allowed.');
    }

    if (!is_dir(UPLOADS_PATH)) {
        mkdir(UPLOADS_PATH, 0755, true);
    }

    $filename = bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    $destination = UPLOADS_PATH . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Could not save the uploaded file.');
    }

    return $filename;
}
