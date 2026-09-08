<?php
/**
 * StudentHub Portal - Database Connection Handler (Practical 8)
 * Developed by Malkesh Patel (25DCS089)
 */

declare(strict_types=1);

$dbHost = getenv('STUDENTHUB_DB_HOST') ?: 'localhost';
$dbUser = getenv('STUDENTHUB_DB_USER') ?: 'root';
$dbPass = getenv('STUDENTHUB_DB_PASSWORD') !== false ? (string) getenv('STUDENTHUB_DB_PASSWORD') : '';
$dbName = getenv('STUDENTHUB_DB_NAME') ?: 'studenthub';

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($dbHost, $dbUser, $dbPass, $dbName);

if ($conn->connect_errno) {
    http_response_code(503);
    die('<!DOCTYPE html><html><head><title>Service Unavailable</title><link rel="stylesheet" href="style.css"></head><body>'
      . '<div class="container" style="text-align:center; padding: 4rem 1rem;">'
      . '<h2>Database Service Unavailable</h2>'
      . '<p class="muted">Could not connect to MySQL server. Please verify MySQL is running in XAMPP and database "studenthub" is imported.</p>'
      . '<p><a href="index.html" class="btn btn-primary">Return to Home</a></p>'
      . '</div></body></html>');
}

$conn->set_charset('utf8mb4');
