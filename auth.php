<?php
/**
 * StudentHub Portal - Authentication & Session Security Handler (Practical 10)
 * Developed by Malkesh Patel (25DCS089)
 */

declare(strict_types=1);

const AUTH_IDLE_TIMEOUT_SECONDS = 1800; // 30 minutes
const REMEMBER_COOKIE_NAME = 'studenthub_remember';

function isSecureRequest(): bool
{
    return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' && $_SERVER['HTTPS'] !== 'off';
}

function sessionCookieOptions(int $expires = 0): array
{
    return [
        'expires' => $expires,
        'path' => '/',
        'secure' => isSecureRequest(),
        'httponly' => true,
        'samesite' => 'Lax'
    ];
}

function startAppSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('STUDENTHUBSESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => isSecureRequest(),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();

    if (isset($_SESSION['user_id'], $_SESSION['last_activity'])
        && time() - (int) $_SESSION['last_activity'] > AUTH_IDLE_TIMEOUT_SECONDS) {
        $_SESSION = [];
        setcookie(session_name(), '', sessionCookieOptions(time() - 3600));
        session_destroy();
        session_id('');
        session_start();
        $_SESSION['auth_status'] = 'timeout';
    }

    if (isset($_SESSION['user_id'])) {
        $_SESSION['last_activity'] = time();
    }
}

function csrfToken(): string
{
    if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validCsrfToken(mixed $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && is_string($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function clearRememberCookie(): void
{
    setcookie(REMEMBER_COOKIE_NAME, '', sessionCookieOptions(time() - 3600));
}

function rememberSelector(): ?string
{
    $cookie = $_COOKIE[REMEMBER_COOKIE_NAME] ?? '';
    if (!is_string($cookie) || !str_contains($cookie, ':')) {
        return null;
    }
    [$selector, $validator] = explode(':', $cookie, 2);
    if (preg_match('/^[a-f0-9]{24}$/', $selector) !== 1
        || preg_match('/^[a-f0-9]{64}$/', $validator) !== 1) {
        return null;
    }
    return $selector;
}

function revokeRememberToken(mysqli $conn): void
{
    $selector = rememberSelector();
    if ($selector !== null) {
        $statement = $conn->prepare('DELETE FROM remember_tokens WHERE selector = ?');
        $statement->bind_param('s', $selector);
        $statement->execute();
        $statement->close();
    }
    clearRememberCookie();
}

function issueRememberToken(mysqli $conn, int $userId): void
{
    $selector = bin2hex(random_bytes(12));
    $validator = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $validator);
    $expiresAt = date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 30);

    $statement = $conn->prepare(
        'INSERT INTO remember_tokens (student_id, selector, token_hash, expires_at) VALUES (?, ?, ?, ?)'
    );
    $statement->bind_param('isss', $userId, $selector, $tokenHash, $expiresAt);
    $statement->execute();
    $statement->close();

    setcookie(REMEMBER_COOKIE_NAME, $selector . ':' . $validator, sessionCookieOptions(time() + 60 * 60 * 24 * 30));
}

function restoreRememberedUser(mysqli $conn): bool
{
    if (isset($_SESSION['user_id'])) {
        return true;
    }
    $cookie = $_COOKIE[REMEMBER_COOKIE_NAME] ?? '';
    if (!is_string($cookie) || !str_contains($cookie, ':')) {
        return false;
    }
    [$selector, $validator] = explode(':', $cookie, 2);
    if (preg_match('/^[a-f0-9]{24}$/', $selector) !== 1
        || preg_match('/^[a-f0-9]{64}$/', $validator) !== 1) {
        clearRememberCookie();
        return false;
    }

    $statement = $conn->prepare(
        'SELECT rt.student_id, rt.token_hash, rt.expires_at, s.full_name, s.role '
        . 'FROM remember_tokens AS rt '
        . 'JOIN students AS s ON s.student_id = rt.student_id '
        . 'WHERE rt.selector = ? LIMIT 1'
    );
    $statement->bind_param('s', $selector);
    $statement->execute();
    $statement->bind_result($userId, $tokenHash, $expiresAt, $fullName, $role);
    $found = $statement->fetch();
    $statement->close();

    if (!$found || strtotime((string) $expiresAt) <= time()
        || !hash_equals((string) $tokenHash, hash('sha256', $validator))) {
        revokeRememberToken($conn);
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $userId;
    $_SESSION['full_name'] = (string) $fullName;
    $_SESSION['role'] = (string) $role;
    $_SESSION['last_activity'] = time();
    return true;
}

function redirectToRole(string $role): never
{
    header('Location: ' . ($role === 'admin' ? 'admin_dashboard.php' : 'Dashboard.php'));
    exit;
}

function requireRole(string $role): void
{
    if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
        $status = $_SESSION['auth_status'] ?? 'login_required';
        header('Location: login.php?status=' . rawurlencode((string) $status));
        exit;
    }
    if ($_SESSION['role'] !== $role) {
        redirectToRole((string) $_SESSION['role']);
    }
    $_SESSION['last_activity'] = time();
}
