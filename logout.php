<?php
/**
 * StudentHub Portal - Logout Handler (Practical 10)
 * Developed by Malkesh Patel (25DCS089)
 */

declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db_connect.php';

startAppSession();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validCsrfToken($_POST['csrf_token'] ?? null)) {
        header('Location: login.php?status=csrf_error');
        exit;
    }
}

revokeRememberToken($conn);
$_SESSION = [];
setcookie(session_name(), '', sessionCookieOptions(time() - 3600));
session_destroy();

header('Location: login.php?status=logged_out');
exit;
