<?php
/**
 * Safely handle a single document upload (for the Resources library).
 * Returns ['filename' => ..., 'size' => bytes] on success, null if no
 * file was submitted, or throws RuntimeException with a user-facing
 * message on failure.
 */
function handle_document_upload(string $fieldName): ?array
{
    if (empty($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$fieldName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed. Please try again.');
    }

    $maxBytes = 100 * 1024 * 1024; // 100MB (PHP upload_max_filesize/post_max_size must also allow this)
    if ($file['size'] > $maxBytes) {
        throw new RuntimeException('File is too large (max 100MB).');
    }

    $allowed = [
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
    ];

    // finfo_close() is deprecated as of PHP 8.5 -- finfo objects are freed
    // automatically once they go out of scope, so it's no longer needed.
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);

    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Only PDF, Word or Excel files are allowed.');
    }

    if (!is_dir(UPLOADS_PATH)) {
        mkdir(UPLOADS_PATH, 0755, true);
    }

    $filename = bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    $destination = UPLOADS_PATH . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Could not save the uploaded file.');
    }

    return ['filename' => $filename, 'size' => $file['size']];
}
