<?php
/**
 * StudentHub Portal - Student Management CRUD Module (Practical 11)
 * Developed by Malkesh Patel (25DCS089)
 */

declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db_connect.php';

startAppSession();

if (!isset($_SESSION['user_id'])) {
    restoreRememberedUser($conn);
}

requireRole('admin');

// Handle CSV Export
if (isset($_GET['action']) && $_GET['action'] === 'export_csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=studenthub_students_' . date('Ymd_His') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Full Name', 'Username', 'Email', 'Mobile', 'Program', 'Year', 'Gender', 'Role', 'Registered On']);
    $query = $conn->query('SELECT student_id, full_name, username, email, mobile, course, year_level, gender, role, created_at FROM students ORDER BY student_id DESC');
    while ($row = $query->fetch_assoc()) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

$flash = null;

// Handle POST actions: Add, Update, Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validCsrfToken($_POST['csrf_token'] ?? null)) {
        $flash = ['type' => 'danger', 'msg' => 'Your request expired. Please refresh the page.'];
    } else {
        $action = (string) ($_POST['action'] ?? '');

        if ($action === 'add') {
            $name = trim((string) ($_POST['full_name'] ?? ''));
            $email = strtolower(trim((string) ($_POST['email'] ?? '')));
            $mobile = trim((string) ($_POST['mobile'] ?? ''));
            $course = (string) ($_POST['course'] ?? 'B.Tech CSE');
            $year = (string) ($_POST['year'] ?? '2nd Year');
            $username = 'std_' . bin2hex(random_bytes(4));
            $tempPwd = password_hash('Password@123', PASSWORD_BCRYPT);

            if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || preg_match('/^[6-9]\d{9}$/', $mobile) !== 1) {
                $flash = ['type' => 'danger', 'msg' => 'Please provide a valid name, email, and 10-digit mobile number.'];
            } else {
                $stmt = $conn->prepare('INSERT INTO students (full_name, username, email, mobile, password_hash, course, year_level, gender, role) VALUES (?, ?, ?, ?, ?, ?, ?, "Other", "student")');
                $stmt->bind_param('sssssss', $name, $username, $email, $mobile, $tempPwd, $course, $year);
                if ($stmt->execute()) {
                    $newId = (int) $conn->insert_id;
                    $rStmt = $conn->prepare("INSERT INTO user_roles (user_id, role) VALUES (?, 'student')");
                    $rStmt->bind_param('i', $newId);
                    $rStmt->execute();
                    $rStmt->close();
                    $flash = ['type' => 'success', 'msg' => 'Student record added successfully! Default password is Password@123'];
                } else {
                    $flash = ['type' => 'danger', 'msg' => 'Failed to add student (email/username may already exist).'];
                }
                $stmt->close();
            }
        } elseif ($action === 'delete') {
            $id = (int) ($_POST['student_id'] ?? 0);
            if ($id > 0) {
                $stmt = $conn->prepare('DELETE FROM students WHERE student_id = ?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $stmt->close();
                $flash = ['type' => 'success', 'msg' => "Student record #$id has been removed."];
            }
        } elseif ($action === 'update') {
            $id = (int) ($_POST['student_id'] ?? 0);
            $name = trim((string) ($_POST['full_name'] ?? ''));
            $email = strtolower(trim((string) ($_POST['email'] ?? '')));
            $mobile = trim((string) ($_POST['mobile'] ?? ''));
            $course = (string) ($_POST['course'] ?? 'B.Tech CSE');

            if ($id > 0 && $name !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $stmt = $conn->prepare('UPDATE students SET full_name = ?, email = ?, mobile = ?, course = ? WHERE student_id = ?');
                $stmt->bind_param('ssssi', $name, $email, $mobile, $course, $id);
                $stmt->execute();
                $stmt->close();
                $flash = ['type' => 'success', 'msg' => "Student record #$id updated successfully."];
            }
        }
    }
}

// Search and Filter logic
$search = trim((string) ($_GET['search'] ?? ''));
$filterCourse = (string) ($_GET['course'] ?? 'all');

$sql = 'SELECT student_id, full_name, username, email, mobile, course, year_level, role, created_at FROM students WHERE 1=1';
$params = [];
$types = '';

if ($search !== '') {
    $sql .= ' AND (full_name LIKE ? OR email LIKE ? OR username LIKE ?)';
    $like = '%' . $search . '%';
    $params[] = &$like;
    $params[] = &$like;
    $params[] = &$like;
    $types .= 'sss';
}

if ($filterCourse !== 'all') {
    $sql .= ' AND course = ?';
    $params[] = &$filterCourse;
    $types .= 's';
}

$sql .= ' ORDER BY student_id DESC';

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$students = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>StudentHub | Manage Students</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <header class="site-header">
    <div class="topbar">ADMINISTRATIVE MODULE • CHARUSAT FTE</div>
    <div class="header-content">
      <h1>StudentHub</h1>
      <p>Student Records Management System</p>
    </div>
  </header>

  <nav class="navbar" aria-label="Role-based navigation">
    <a href="index.html">Home</a>
    <a href="admin_dashboard.php">Admin Dashboard</a>
    <a class="active" href="student_management.php">Manage Students</a>
    <a href="event_management.php">Manage Events</a>
    <a href="events.html">Events Portal</a>
  </nav>

  <div style="background: var(--bg-alt); padding: 0.6rem 1.25rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border);">
    <span>Logged in as: <strong><?= htmlspecialchars((string) $_SESSION['full_name'], ENT_QUOTES, 'UTF-8') ?></strong> (Administrator)</span>
    <form action="logout.php" method="POST" style="margin: 0;">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>" />
      <button class="btn btn-secondary" type="submit" style="padding: 0.35rem 0.85rem; font-size: 0.85rem;">Sign Out</button>
    </form>
  </div>

  <main class="section">
    <div class="container">
      <?php if ($flash !== null): ?>
        <div class="site-banner" style="margin-top: 1.5rem; border-radius: var(--radius-sm); background: <?= $flash['type'] === 'success' ? '#dcfce7' : '#fee2e2' ?>; color: <?= $flash['type'] === 'success' ? '#166534' : '#991b1b' ?>;">
          <?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php endif; ?>

      <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-top: 1.5rem;">
        <h2>Enrolled Student Directory</h2>
        <div style="display: flex; gap: 0.75rem;">
          <a class="btn btn-secondary" href="student_management.php?action=export_csv">Export CSV</a>
          <button class="btn btn-primary" onclick="openModal('addModal')">+ Add Student Record</button>
        </div>
      </div>

      <!-- Search and Filter Bar -->
      <form action="student_management.php" method="GET" class="card" style="margin-top: 1.25rem; padding: 1rem; display: flex; flex-wrap: wrap; gap: 1rem; align-items: center;">
        <input type="text" name="search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Search by name, email, or username..." style="flex: 1; min-width: 200px; padding: 0.6rem 0.8rem; border-radius: var(--radius-sm); border: 1px solid var(--border);" />
        <select name="course" style="padding: 0.6rem 0.8rem; border-radius: var(--radius-sm); border: 1px solid var(--border);">
          <option value="all">All Academic Programs</option>
          <option value="B.Tech CSE" <?= $filterCourse === 'B.Tech CSE' ? 'selected' : '' ?>>B.Tech CSE</option>
          <option value="B.Tech IT" <?= $filterCourse === 'B.Tech IT' ? 'selected' : '' ?>>B.Tech IT</option>
          <option value="B.Tech CE" <?= $filterCourse === 'B.Tech CE' ? 'selected' : '' ?>>B.Tech CE</option>
        </select>
        <button class="btn btn-primary" type="submit" style="padding: 0.6rem 1.2rem;">Filter</button>
        <?php if ($search !== '' || $filterCourse !== 'all'): ?>
          <a href="student_management.php" class="btn btn-secondary" style="padding: 0.6rem 1.2rem;">Reset</a>
        <?php endif; ?>
      </form>

      <!-- Records Table -->
      <section class="panel" style="margin-top: 1.5rem; overflow-x: auto;">
        <table class="table-responsive">
          <thead>
            <tr>
              <th>ID</th>
              <th>Full Name</th>
              <th>Email</th>
              <th>Mobile</th>
              <th>Course</th>
              <th>Year</th>
              <th>Role</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($students->num_rows === 0): ?>
              <tr><td colspan="8" style="text-align: center; color: var(--muted); padding: 2rem;">No student records match your query.</td></tr>
            <?php else: ?>
              <?php while ($row = $students->fetch_assoc()): ?>
                <tr>
                  <td>#<?= (int) $row['student_id'] ?></td>
                  <td><strong><?= htmlspecialchars($row['full_name'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                  <td><?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($row['mobile'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><span class="badge badge-primary"><?= htmlspecialchars($row['course'], ENT_QUOTES, 'UTF-8') ?></span></td>
                  <td><?= htmlspecialchars($row['year_level'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><span class="badge <?= $row['role'] === 'admin' ? 'badge-danger' : 'badge-secondary' ?>"><?= htmlspecialchars($row['role'], ENT_QUOTES, 'UTF-8') ?></span></td>
                  <td>
                    <div class="management-actions">
                      <form action="student_management.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this student record?');" style="margin: 0;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>" />
                        <input type="hidden" name="action" value="delete" />
                        <input type="hidden" name="student_id" value="<?= (int) $row['student_id'] ?>" />
                        <button type="submit" class="btn btn-danger" style="padding: 0.35rem 0.65rem; font-size: 0.8rem;">Delete</button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </section>
    </div>
  </main>

  <!-- Add Student Modal -->
  <div class="modal-overlay" id="addModal">
    <div class="modal-content">
      <button class="modal-close" onclick="closeModal('addModal')">×</button>
      <h3>Add New Student Record</h3>
      <form action="student_management.php" method="POST" style="margin-top: 1rem;">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>" />
        <input type="hidden" name="action" value="add" />

        <div class="form-group">
          <label>Full Name:</label>
          <input type="text" name="full_name" placeholder="e.g. Priyanshu Patel" required />
        </div>
        <div class="form-group">
          <label>Institutional Email:</label>
          <input type="email" name="email" placeholder="e.g. priyanshu@studenthub.edu" required />
        </div>
        <div class="form-group">
          <label>Mobile Number:</label>
          <input type="tel" name="mobile" placeholder="10-digit number" required />
        </div>
        <div class="form-group">
          <label>Program:</label>
          <select name="course">
            <option value="B.Tech CSE">B.Tech CSE</option>
            <option value="B.Tech IT">B.Tech IT</option>
            <option value="B.Tech CE">B.Tech CE</option>
          </select>
        </div>
        <div class="form-group">
          <label>Year Level:</label>
          <select name="year">
            <option value="1st Year">1st Year</option>
            <option value="2nd Year" selected>2nd Year</option>
            <option value="3rd Year">3rd Year</option>
            <option value="4th Year">4th Year</option>
          </select>
        </div>
        <button class="btn btn-primary" type="submit" style="width: 100%; margin-top: 0.5rem;">Save Student</button>
      </form>
    </div>
  </div>

  <footer class="footer">
    <div class="container">
      <p>&copy; 2026 StudentHub Portal • Malkesh Patel (25DCS089)</p>
    </div>
  </footer>

  <script src="script.js"></script>
</body>
</html>
