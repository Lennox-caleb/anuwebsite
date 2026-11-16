<?php
require_once __DIR__ . '/../../includes/functions.php';

function current_admin(): ?array
{
    return $_SESSION['admin'] ?? null;
}

function require_login(): void
{
    if (!current_admin()) {
        redirect(ADMIN_URL . '/login.php');
    }
}

function require_role(array $roles): void
{
    require_login();
    $admin = current_admin();
    if (!in_array($admin['role'], $roles, true)) {
        flash_set('danger', 'You do not have permission to access that page.');
        redirect(ADMIN_URL . '/index.php');
    }
}

function attempt_login(string $username, string $password): bool
{
    $stmt = db()->prepare('SELECT * FROM admins WHERE username = ? AND is_active = 1 LIMIT 1');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        return false;
    }
    unset($admin['password_hash']);
    $_SESSION['admin'] = $admin;
    db()->prepare('UPDATE admins SET last_login_at = NOW() WHERE id = ?')->execute([$admin['id']]);
    return true;
}
