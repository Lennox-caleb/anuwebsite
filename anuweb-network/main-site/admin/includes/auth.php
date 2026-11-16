<?php
require_once __DIR__ . '/../../includes/functions.php';

if (empty($_SESSION['admin_id'])) {
    redirect('/admin/login.php');
}

/** Current logged-in admin row, cached per request. */
function current_admin(): array
{
    static $admin = null;
    if ($admin === null) {
        $stmt = db()->prepare('SELECT id, full_name, email, username, role, is_active FROM admin_users WHERE id = ?');
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch() ?: [];
        if (!$admin || !$admin['is_active']) {
            session_destroy();
            redirect('/admin/login.php');
        }
    }
    return $admin;
}

/** Guard pages that only super_admin should reach (e.g. managing other admin accounts). */
function require_super_admin(): void
{
    $admin = current_admin();
    if ($admin['role'] !== 'super_admin') {
        http_response_code(403);
        require __DIR__ . '/admin_header.php';
        echo '<div class="alert alert-danger">You need super admin access to view this page.</div>';
        require __DIR__ . '/admin_footer.php';
        exit;
    }
}

/**
 * Guard a page to a specific set of roles. Call this BEFORE requiring
 * admin_header.php (it renders its own minimal page if access is denied,
 * so it doesn't depend on the full admin shell being loaded yet).
 */
function require_role(array $allowedRoles): void
{
    $admin = current_admin();
    if (!in_array($admin['role'], $allowedRoles, true)) {
        http_response_code(403);
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Access Denied</title>'
           . '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>'
           . '<body class="d-flex align-items-center justify-content-center min-vh-100">'
           . '<div class="text-center"><h1 class="h3">403, Access Denied</h1>'
           . '<p class="text-muted">Your admin role (<strong>' . h($admin['role']) . '</strong>) doesn\'t have permission to view this page.</p>'
           . '<a href="' . BASE_URL . '/admin/dashboard.php" class="btn btn-dark">Back to Dashboard</a></div></body></html>';
        exit;
    }
}
