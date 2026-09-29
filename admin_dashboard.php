<?php
/**
 * StudentHub Portal - Administrative Dashboard & Audit Logging (Practical 14)
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

$userId = (int) $_SESSION['user_id'];
$userName = (string) $_SESSION['full_name'];
$ip = filter_var($_SERVER['REMOTE_ADDR'] ?? null, FILTER_VALIDATE_IP) ?: '127.0.0.1';

// Record admin access in audit log
$auditStmt = $conn->prepare('INSERT INTO dashboard_audit_log (user_id, user_name, action, details, ip_address) VALUES (?, ?, "Viewed Admin Dashboard", "Admin opened portal oversight console", ?)');
$auditStmt->bind_param('iss', $userId, $userName, $ip);
$auditStmt->execute();
$auditStmt->close();

// Metrics aggregation
$totalStudents = (int) $conn->query('SELECT COUNT(*) AS c FROM students')->fetch_assoc()['c'];
$totalEvents = (int) $conn->query('SELECT COUNT(*) AS c FROM events')->fetch_assoc()['c'];
$totalRegistrations = (int) $conn->query('SELECT COUNT(*) AS c FROM registrations')->fetch_assoc()['c'];

// Recent registrations
$recentRegistrations = $conn->query(
    'SELECT s.full_name, s.email, e.title, r.registration_date, r.status '
    . 'FROM registrations AS r '
    . 'JOIN students AS s ON s.student_id = r.student_id '
    . 'JOIN events AS e ON e.event_id = r.event_id '
    . 'ORDER BY r.registration_date DESC LIMIT 5'
);

// Recent audit logs
$auditLogs = $conn->query('SELECT user_name, action, details, ip_address, created_at FROM dashboard_audit_log ORDER BY log_id DESC LIMIT 5');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>StudentHub | Administrator Dashboard</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <div class="top-tools">
    <button class="theme-btn" type="button" aria-label="Switch theme" onclick="toggleTheme()">🌙</button>
    <button class="menu-btn" type="button" aria-label="Toggle menu" onclick="toggleMenu()">☰</button>
  </div>

  <header class="site-header">
    <div class="topbar">ADMINISTRATIVE CONSOLE • CHARUSAT FTE</div>
    <div class="header-content">
      <h1>StudentHub</h1>
      <p>Institutional Oversight & Analytics</p>
    </div>
  </header>

  <nav class="navbar" id="menu" aria-label="Role-based navigation">
    <a href="index.html">Home</a>
    <a class="active" href="admin_dashboard.php">Dashboard</a>
    <a href="student_management.php">Manage Students</a>
    <a href="event_management.php">Manage Events</a>
    <a href="events.html">Events Portal</a>
  </nav>

  <div style="background: var(--bg-alt); padding: 0.6rem 1.25rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border);">
    <span>Admin Session: <strong><?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></strong></span>
    <form action="logout.php" method="POST" style="margin: 0;">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>" />
      <button class="btn btn-secondary" type="submit" style="padding: 0.35rem 0.85rem; font-size: 0.85rem;">Sign Out</button>
    </form>
  </div>

  <main class="section">
    <div class="container">
      <section class="panel" style="margin-top: 1.5rem;">
        <h2>Campus Operational Metrics</h2>
        <p class="muted">Live system counters and student participation summary.</p>
      </section>

      <section class="metrics-grid" style="margin-top: 1.5rem;">
        <div class="metric-card card">
          <div class="metric-num"><?= $totalStudents ?></div>
          <div class="metric-label">Enrolled Students</div>
          <small class="text-primary"><a href="student_management.php">Manage Accounts →</a></small>
        </div>
        <div class="metric-card card">
          <div class="metric-num"><?= $totalEvents ?></div>
          <div class="metric-label">Published Events</div>
          <small class="text-primary"><a href="event_management.php">Manage Events →</a></small>
        </div>
        <div class="metric-card card">
          <div class="metric-num"><?= $totalRegistrations ?></div>
          <div class="metric-label">Event Participations</div>
          <small class="text-success">Active Enlistments</small>
        </div>
        <div class="metric-card card">
          <div class="metric-num">100%</div>
          <div class="metric-label">System Uptime</div>
          <small class="text-success">MySQL & Apache Active</small>
        </div>
      </section>

      <div class="dashboard-columns" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
        <!-- Recent Registrations -->
        <section class="panel">
          <h3>Recent Event Registrations</h3>
          <table class="table-responsive" style="margin-top: 1rem;">
            <thead>
              <tr>
                <th>Student</th>
                <th>Event Title</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($r = $recentRegistrations->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($r['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><strong><?= htmlspecialchars($r['title'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                  <td><span class="status-tag status-eligible"><?= htmlspecialchars($r['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </section>

        <!-- Security Audit Log -->
        <section class="panel">
          <h3>Security Audit Trail (Latest 5)</h3>
          <table class="table-responsive" style="margin-top: 1rem;">
            <thead>
              <tr>
                <th>User</th>
                <th>Action</th>
                <th>Timestamp</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($log = $auditLogs->fetch_assoc()): ?>
                <tr>
                  <td><strong><?= htmlspecialchars($log['user_name'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                  <td><?= htmlspecialchars($log['action'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($log['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
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
