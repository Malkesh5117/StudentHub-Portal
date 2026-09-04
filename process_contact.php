<?php
/**
 * StudentHub Portal - Contact Form Handler with CSV Storage (Practical 7)
 * Developed by Malkesh Patel (25DCS089)
 */

declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact.html');
    exit;
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = strtolower(trim((string) ($_POST['email'] ?? '')));
$subject = trim((string) ($_POST['subject'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $message === '') {
    die('Invalid contact form input. <a href="contact.html">Go back</a>');
}

$storageDir = __DIR__ . '/data/submissions';
if (!is_dir($storageDir)) {
    mkdir($storageDir, 0755, true);
}

$csvPath = $storageDir . '/contacts.csv';
$isNew = !file_exists($csvPath);

$file = fopen($csvPath, 'a');
if ($file !== false) {
    if ($isNew) {
        fputcsv($file, ['Timestamp', 'Name', 'Email', 'Subject', 'Message']);
    }
    fputcsv($file, [date('Y-m-d H:i:s'), $name, $email, $subject, $message]);
    fclose($file);
}

echo '<div style="font-family: sans-serif; text-align: center; padding: 3rem;">';
echo '<h2 style="color: #16a34a;">Thank You! Your inquiry has been received.</h2>';
echo '<p>Our student support coordinator will reach out to you shortly.</p>';
echo '<p><a href="index.html">Return to Homepage</a></p>';
echo '</div>';
