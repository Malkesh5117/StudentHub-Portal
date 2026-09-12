<?php
/**
 * StudentHub Portal - Secure Registration with Password Hashing & Duplicate Checks (Practical 9)
 * Developed by Malkesh Patel (25DCS089)
 */

declare(strict_types=1);
require_once __DIR__ . '/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: registration.html');
    exit;
}

$fullName = trim((string) ($_POST['fullName'] ?? ''));
$username = strtolower(trim((string) ($_POST['username'] ?? '')));
$email = strtolower(trim((string) ($_POST['email'] ?? '')));
$mobile = trim((string) ($_POST['mobile'] ?? ''));
$password = (string) ($_POST['password'] ?? '');
$confirmPassword = (string) ($_POST['confirmPassword'] ?? '');
$course = (string) ($_POST['course'] ?? '');
$year = (string) ($_POST['year'] ?? '');
$gender = (string) ($_POST['gender'] ?? 'Other');
$terms = isset($_POST['terms']);

// Server-side strict validation
$isValid = preg_match("/^[A-Za-z][A-Za-z .'-]{2,39}$/", $fullName) === 1
    && preg_match('/^[a-z][a-z0-9_]{2,29}$/', $username) === 1
    && filter_var($email, FILTER_VALIDATE_EMAIL) !== false
    && preg_match('/^[6-9]\d{9}$/', $mobile) === 1
    && strlen($password) >= 8
    && hash_equals($password, $confirmPassword)
    && in_array($course, ['B.Tech CSE', 'B.Tech IT', 'B.Tech CE', 'BBA', 'B.Com'], true)
    && in_array($year, ['1st Year', '2nd Year', '3rd Year', '4th Year'], true)
    && in_array($gender, ['Male', 'Female', 'Other'], true)
    && $terms;

if (!$isValid) {
    die('<!DOCTYPE html><html><head><title>Registration Error</title><link rel="stylesheet" href="style.css"></head><body>'
      . '<div class="container" style="max-width:500px; padding:3rem 1rem; text-align:center;">'
      . '<div class="card">'
      . '<h2 style="color:var(--danger)">Validation Error</h2>'
      . '<p>Please ensure all fields are filled with valid values according to the format requirements.</p>'
      . '<p><a href="registration.html" class="btn btn-secondary">← Back to Form</a></p>'
      . '</div></div></body></html>');
}

// Check duplicate username or email
$checkStmt = $conn->prepare('SELECT student_id, email, username FROM students WHERE email = ? OR username = ? LIMIT 1');
$checkStmt->bind_param('ss', $email, $username);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    $checkStmt->close();
    die('<!DOCTYPE html><html><head><title>Account Exists</title><link rel="stylesheet" href="style.css"></head><body>'
      . '<div class="container" style="max-width:500px; padding:3rem 1rem; text-align:center;">'
      . '<div class="card">'
      . '<h2 style="color:var(--warning)">Account Already Exists</h2>'
      . '<p>An account with that institutional email address or username is already registered in the system.</p>'
      . '<p><a href="login.html" class="btn btn-primary">Sign In</a> | <a href="registration.html" class="btn btn-secondary">Try Again</a></p>'
      . '</div></div></body></html>');
}
$checkStmt->close();

// Secure BCRYPT hashing
$passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

$conn->begin_transaction();
try {
    $insertStmt = $conn->prepare(
        'INSERT INTO students (full_name, username, email, mobile, password_hash, course, year_level, gender, role) '
        . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?, "student")'
    );
    $insertStmt->bind_param('ssssssss', $fullName, $username, $email, $mobile, $passwordHash, $course, $year, $gender);
    $insertStmt->execute();
    $newId = (int) $conn->insert_id;
    $insertStmt->close();

    $roleStmt = $conn->prepare("INSERT INTO user_roles (user_id, role) VALUES (?, 'student')");
    $roleStmt->bind_param('i', $newId);
    $roleStmt->execute();
    $roleStmt->close();

    $conn->commit();

    echo '<!DOCTYPE html><html><head><title>Registration Successful</title><link rel="stylesheet" href="style.css"></head><body>';
    echo '<div class="container" style="max-width:550px; padding:4rem 1rem; text-align:center;">';
    echo '<div class="card">';
    echo '<h2 style="color:var(--success); margin-bottom:1rem;">Welcome to StudentHub!</h2>';
    echo '<p>Your student account has been created successfully with secure credentials.</p>';
    echo '<p class="muted">You can now sign in using your email (' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . ') and password.</p>';
    echo '<div style="margin-top:1.5rem;"><a href="login.html" class="btn btn-primary">Proceed to Login</a></div>';
    echo '</div></div></body></html>';
} catch (Exception $e) {
    $conn->rollback();
    die('Database insertion failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
