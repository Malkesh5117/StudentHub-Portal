<?php
/**
 * StudentHub Portal - Secure Login Handler (Practical 10)
 * Developed by Malkesh Patel (25DCS089)
 */

declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db_connect.php';

startAppSession();

if (!isset($_SESSION['user_id'])) {
    restoreRememberedUser($conn);
}

if (isset($_SESSION['user_id'], $_SESSION['role'])) {
    redirectToRole((string) $_SESSION['role']);
}

$status = (string) ($_GET['status'] ?? $_SESSION['auth_status'] ?? '');
unset($_SESSION['auth_status']);
$email = '';
$selectedRole = 'student';
$error = '';

if ($status === 'timeout') {
    $error = 'Your session has expired due to inactivity. Please sign in again.';
} elseif ($status === 'logged_out') {
    $error = 'You have successfully signed out of your account.';
} elseif ($status === 'login_required') {
    $error = 'Please sign in to access that protected portal area.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $selectedRole = (string) ($_POST['role'] ?? 'student');
    $remember = isset($_POST['remember_me']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '' || !in_array($selectedRole, ['student', 'admin'], true)) {
        $error = 'Please enter a valid email address and password.';
    } else {
        $stmt = $conn->prepare('SELECT student_id, full_name, password_hash, role FROM students WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->bind_result($userId, $fullName, $passwordHash, $accountRole);
        $found = $stmt->fetch();
        $stmt->close();

        if (!$found || !password_verify($password, (string) $passwordHash)) {
            $error = 'Invalid email address or password combination.';
        } elseif ($accountRole !== $selectedRole) {
            $error = "Selected role does not match account privileges ($accountRole account required).";
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $userId;
            $_SESSION['full_name'] = (string) $fullName;
            $_SESSION['role'] = (string) $accountRole;
            $_SESSION['last_activity'] = time();
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            if ($remember) {
                issueRememberToken($conn, (int) $userId);
            }

            redirectToRole((string) $accountRole);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>StudentHub | Secure Login</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <header class="site-header">
    <div class="topbar">AUTHENTICATION GATEWAY • CHARUSAT FTE</div>
    <div class="header-content">
      <h1>StudentHub</h1>
      <p>Secure Account Access</p>
    </div>
  </header>

  <nav class="navbar" aria-label="Main navigation">
    <a href="index.html">Home</a>
    <a href="about.html">About</a>
    <a href="registration.html">Registration</a>
    <a class="active" href="login.php">Login</a>
    <a href="events.html">Events</a>
    <a href="contact.html">Contact</a>
  </nav>

  <main class="section">
    <div class="container" style="max-width: 500px;">
      <section class="form-card">
        <h2>Sign In to Your Workspace</h2>
        <p class="muted">Enter your registered credentials to proceed.</p>

        <?php if ($error !== ''): ?>
          <div class="site-banner" style="margin-bottom: 1.25rem; border-radius: var(--radius-sm); text-align: left;">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
          </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>" />

          <div class="form-group">
            <label for="role">Account Type:</label>
            <select id="role" name="role" required>
              <option value="student" <?= $selectedRole === 'student' ? 'selected' : '' ?>>Student</option>
              <option value="admin" <?= $selectedRole === 'admin' ? 'selected' : '' ?>>Administrator</option>
            </select>
          </div>

          <div class="form-group">
            <label for="email">Institutional Email Address:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g. malkesh.patel@studenthub.edu" required />
          </div>

          <div class="form-group">
            <label for="password">Account Password:</label>
            <input type="password" id="password" name="password" placeholder="••••••••" required />
          </div>

          <div class="form-group checkbox-group" style="display: flex; align-items: center; gap: 0.5rem;">
            <input type="checkbox" id="remember_me" name="remember_me" value="1" />
            <label for="remember_me">Remember my session for 30 days</label>
          </div>

          <button class="btn btn-primary" type="submit" style="width: 100%; margin-top: 1rem;">Sign In to Portal</button>
        </form>

        <div style="margin-top: 1.5rem; text-align: center; font-size: 0.92rem;">
          <p>Don't have an account? <a href="registration.html">Register here</a></p>
          <p class="muted" style="margin-top: 0.5rem; font-size: 0.82rem;">Demo Student: <code>malkesh.patel@studenthub.edu</code> | Pass: <code>Password@123</code></p>
          <p class="muted" style="font-size: 0.82rem;">Demo Admin: <code>admin@studenthub.edu</code> | Pass: <code>Password@123</code></p>
        </div>
      </section>
    </div>
  </main>

  <footer class="footer">
    <div class="container">
      <p>&copy; 2026 StudentHub Portal • Malkesh Patel (25DCS089)</p>
    </div>
  </footer>
</body>
</html>
