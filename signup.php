<?php
/**
 * StudentHub Portal - Form Processing with Server-Side Validation & File Storage (Practical 7)
 * Developed by Malkesh Patel (25DCS089)
 */

declare(strict_types=1);

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
$gender = (string) ($_POST['gender'] ?? '');
$terms = isset($_POST['terms']);

// Server-side validation
$errors = [];

if (preg_match("/^[A-Za-z][A-Za-z .'-]{2,39}$/", $fullName) !== 1) {
    $errors[] = 'Full name must be 3-40 alphabetic characters.';
}

if (preg_match('/^[a-z][a-z0-9_]{2,29}$/', $username) !== 1) {
    $errors[] = 'Username must be 3-30 lowercase characters starting with a letter.';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}

if (preg_match('/^[6-9]\d{9}$/', $mobile) !== 1) {
    $errors[] = 'Mobile must be a valid 10-digit number.';
}

if (strlen($password) < 8 || $password !== $confirmPassword) {
    $errors[] = 'Password must be at least 8 characters and match confirm password.';
}

if (!in_array($course, ['B.Tech CSE', 'B.Tech IT', 'B.Tech CE', 'BBA', 'B.Com'], true)) {
    $errors[] = 'Please select a valid course.';
}

if (!$terms) {
    $errors[] = 'You must accept the terms.';
}

$storageDir = __DIR__ . '/data/submissions';
if (!is_dir($storageDir)) {
    mkdir($storageDir, 0755, true);
}

if (!empty($errors)) {
    echo '<h2>Validation Errors:</h2><ul>';
    foreach ($errors as $err) {
        echo '<li>' . htmlspecialchars($err, ENT_QUOTES, 'UTF-8') . '</li>';
    }
    echo '</ul><p><a href="registration.html">← Go Back to Form</a></p>';
    exit;
}

// Store submission in JSON file
$record = [
    'full_name' => $fullName,
    'username' => $username,
    'email' => $email,
    'mobile' => $mobile,
    'course' => $course,
    'year' => $year,
    'gender' => $gender,
    'submitted_at' => date('Y-m-d H:i:s')
];

$filePath = $storageDir . '/registrations.json';
$existing = [];
if (file_exists($filePath)) {
    $existing = json_decode((string) file_get_contents($filePath), true) ?: [];
}
$existing[] = $record;
file_put_contents($filePath, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo '<div style="font-family: sans-serif; text-align: center; padding: 3rem;">';
echo '<h2 style="color: #16a34a;">Registration Submitted Successfully!</h2>';
echo '<p>Your details have been saved to local record storage (Practical 7).</p>';
echo '<p><a href="login.html" style="color: #2563eb;">Proceed to Login</a> | <a href="index.html">Back to Home</a></p>';
echo '</div>';
