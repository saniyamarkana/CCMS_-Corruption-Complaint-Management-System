<?php

function has_role_session(string $role): bool
{
    if ($role === 'citizen') {
        return !empty($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'citizen';
    }
    if ($role === 'officer') {
        return !empty($_SESSION['officer_id']) && ($_SESSION['role'] ?? '') === 'officer';
    }
    if ($role === 'admin') {
        return !empty($_SESSION['admin_logged_in'])
            && !empty($_SESSION['admin_id'])
            && ($_SESSION['role'] ?? '') === 'admin';
    }

    return false;
}

function redirect_authenticated_user(string $role, string $destination): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (has_role_session($role)) {
        header('Location: ' . $destination);
        exit;
    }
}

function require_role(string $role, string $loginPath): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');

    if (!has_role_session($role)) {
        header('Location: ' . $loginPath);
        exit;
    }
}
