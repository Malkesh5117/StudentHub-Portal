<?php
/**
 * StudentHub Portal - RESTful JSON Student API (Practical 13)
 * Developed by Malkesh Patel (25DCS089)
 */

declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db_connect.php';

startAppSession();

if (!isset($_SESSION['user_id'])) {
    restoreRememberedUser($conn);
}

function sendJsonResponse(int $statusCode, array $payload): never
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
    sendJsonResponse(401, ['error' => 'Authentication is required to access the API.']);
}

if ($_SESSION['role'] !== 'admin') {
    sendJsonResponse(403, ['error' => 'Administrator privileges are required.']);
}

$_SESSION['last_activity'] = time();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if (!in_array($method, ['GET', 'POST', 'PUT', 'DELETE'], true)) {
    header('Allow: GET, POST, PUT, DELETE');
    sendJsonResponse(405, ['error' => 'Method not allowed.']);
}

// GET: List or search students
if ($method === 'GET') {
    $search = trim((string) ($_GET['search'] ?? ''));
    $sql = 'SELECT student_id, full_name, username, email, mobile, course, year_level FROM students';
    if ($search !== '') {
        $sql .= ' WHERE full_name LIKE ? OR email LIKE ?';
        $stmt = $conn->prepare($sql);
        $like = '%' . $search . '%';
        $stmt->bind_param('ss', $like, $like);
    } else {
        $sql .= ' ORDER BY student_id DESC LIMIT 50';
        $stmt = $conn->prepare($sql);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();
    sendJsonResponse(200, ['status' => 'success', 'data' => $students]);
}

// POST: Create student
if ($method === 'POST') {
    $body = json_decode((string) file_get_contents('php://input'), true) ?: $_POST;
    $name = trim((string) ($body['full_name'] ?? ''));
    $email = strtolower(trim((string) ($body['email'] ?? '')));
    $mobile = trim((string) ($body['mobile'] ?? ''));
    $course = (string) ($body['course'] ?? 'B.Tech CSE');
    $year = (string) ($body['year'] ?? '2nd Year');

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || preg_match('/^[6-9]\d{9}$/', $mobile) !== 1) {
        sendJsonResponse(422, ['error' => 'Valid full name, email, and 10-digit mobile number are required.']);
    }

    $username = 'std_' . bin2hex(random_bytes(4));
    $tempPwd = password_hash('Password@123', PASSWORD_BCRYPT);

    $stmt = $conn->prepare('INSERT INTO students (full_name, username, email, mobile, password_hash, course, year_level, gender, role) VALUES (?, ?, ?, ?, ?, ?, ?, "Other", "student")');
    $stmt->bind_param('sssssss', $name, $username, $email, $mobile, $tempPwd, $course, $year);
    if ($stmt->execute()) {
        $newId = (int) $conn->insert_id;
        $stmt->close();
        sendJsonResponse(201, ['status' => 'success', 'message' => 'Student created', 'student_id' => $newId]);
    } else {
        $stmt->close();
        sendJsonResponse(409, ['error' => 'A student with that email or username already exists.']);
    }
}

// DELETE: Remove student
if ($method === 'DELETE') {
    $body = json_decode((string) file_get_contents('php://input'), true) ?? [];
    $id = (int) ($body['student_id'] ?? $_GET['id'] ?? 0);
    if ($id <= 0) {
        sendJsonResponse(400, ['error' => 'Missing or invalid student_id.']);
    }
    $stmt = $conn->prepare('DELETE FROM students WHERE student_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    sendJsonResponse(200, ['status' => 'success', 'deleted' => $affected > 0]);
}
