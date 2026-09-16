<?php
/**
 * StudentHub Portal - Dynamic Student Dashboard (Practical 10)
 * Developed by Malkesh Patel (25DCS089)
 */

declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db_connect.php';

startAppSession();

if (!isset($_SESSION['user_id'])) {
    restoreRememberedUser($conn);
}

requireRole('student');

$userId = (int) $_SESSION['user_id'];

// Fetch student personal details
$stmt = $conn->prepare('SELECT full_name, email, course, year_level, mobile FROM students WHERE student_id = ? LIMIT 1');
$stmt->bind_param('i', $userId);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch student's registered events
$eventsStmt = $conn->prepare(
    'SELECT e.title, e.event_date, e.location, e.category, r.status '
    . 'FROM registrations AS r '
    . 'JOIN events AS e ON e.event_id = r.event_id '
    . 'WHERE r.student_id = ? ORDER BY e.event_date ASC'
);
$eventsStmt->bind_param('i', $userId);
$eventsStmt->execute();
$registeredEvents = $eventsStmt->get_result();
$eventsStmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>StudentHub | Student Workspace</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <div class="top-tools">
    <button class="theme-btn" type="button" aria-label="Switch theme" onclick="toggleTheme()">🌙</button>
    <button class="menu-btn" type="button" aria-label="Toggle menu" onclick="toggleMenu()">☰</button>
  </div>

  <header class="site-header">
    <div class="topbar">AUTHENTICATED WORKSPACE • CHARUSAT FTE</div>
    <div class="header-content">
      <h1>StudentHub</h1>
      <p>Student Academic Dashboard</p>
    </div>
  </header>

  <nav class="navbar" id="menu" aria-label="Main navigation">
    <a href="index.html">Home</a>
    <a href="about.html">About</a>
    <a class="active" href="Dashboard.php">Dashboard</a>
    <a href="attendence.html">Attendance</a>
    <a href="timetable.html">Timetable</a>
    <a href="results.html">Results</a>
    <a href="fees.html">Fees</a>
    <a href="events.html">Events</a>
    <a href="contact.html">Contact</a>
  </nav>

  <div style="background: var(--bg-alt); padding: 0.6rem 1.25rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border);">
    <span>Logged in as: <strong><?= htmlspecialchars((string) $_SESSION['full_name'], ENT_QUOTES, 'UTF-8') ?></strong> (Student)</span>
    <form action="logout.php" method="POST" style="margin: 0;">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>" />
      <button class="btn btn-secondary" type="submit" style="padding: 0.35rem 0.85rem; font-size: 0.85rem;">Sign Out</button>
    </form>
  </div>

  <main class="section">
    <div class="container">
      <section class="panel" style="margin-top: 1.5rem;">
        <h2>Welcome, <?= htmlspecialchars($student['full_name'] ?? 'Student', ENT_QUOTES, 'UTF-8') ?>!</h2>
        <p class="muted">Program: <strong><?= htmlspecialchars($student['course'] ?? 'B.Tech CSE', ENT_QUOTES, 'UTF-8') ?></strong> • Year: <strong><?= htmlspecialchars($student['year_level'] ?? '2nd Year', ENT_QUOTES, 'UTF-8') ?></strong> • Email: <?= htmlspecialchars($student['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
      </section>

      <section class="metrics-grid" style="margin-top: 1.5rem;">
        <div class="metric-card card">
          <div class="metric-num">91%</div>
          <div class="metric-label">Subject Attendance</div>
          <small class="text-success">✔ Exam Eligible</small>
        </div>
        <div class="metric-card card">
          <div class="metric-num">8.92</div>
          <div class="metric-label">Current CGPA</div>
          <small class="text-info">3rd Semester</small>
        </div>
        <div class="metric-card card">
          <div class="metric-num"><?= $registeredEvents->num_rows ?></div>
          <div class="metric-label">Registered Events</div>
          <small class="text-primary">Participations</small>
        </div>
        <div class="metric-card card">
          <div class="metric-num">₹0</div>
          <div class="metric-label">Outstanding Dues</div>
          <small class="text-success">✔ Cleared</small>
        </div>
      </section>

      <div class="dashboard-columns" style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
        <section class="panel">
          <h3>My Registered Campus Events</h3>
          <?php if ($registeredEvents->num_rows === 0): ?>
            <p class="muted">You have not registered for any events yet. <a href="events.html">Browse available events</a>.</p>
          <?php else: ?>
            <table class="table-responsive" style="margin-top: 1rem;">
              <thead>
                <tr>
                  <th>Event Title</th>
                  <th>Category</th>
                  <th>Date</th>
                  <th>Location</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($ev = $registeredEvents->fetch_assoc()): ?>
                  <tr>
                    <td><strong><?= htmlspecialchars($ev['title'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                    <td><span class="badge badge-primary"><?= htmlspecialchars($ev['category'], ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td><?= htmlspecialchars($ev['event_date'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($ev['location'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="status-tag status-eligible"><?= htmlspecialchars($ev['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </section>

        <section class="panel">
          <h3>Quick Academic Links</h3>
          <ul class="quick-links">
            <li><a href="attendence.html" class="link-arrow">View Detailed Attendance Record →</a></li>
            <li><a href="timetable.html" class="link-arrow">Check Weekly Class Schedule →</a></li>
            <li><a href="results.html" class="link-arrow">View Semester Grade Sheet →</a></li>
            <li><a href="fees.html" class="link-arrow">Download Fee Payment Receipts →</a></li>
            <li><a href="faculty.html" class="link-arrow">Contact Department Faculty →</a></li>
          </ul>
        </section>
      </div>
    </div>
  </main>

  <footer class="footer">
    <div class="container">
      <p>&copy; 2026 StudentHub Portal • Malkesh Patel (25DCS089)</p>
    </div>
  </footer>

  <script src="script.js"></script>
</body>
</html>
