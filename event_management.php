<?php
/**
 * StudentHub Portal - Event Management with Poster Upload & File Validation (Practical 12)
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

const EVENT_POSTER_MAX_BYTES = 2 * 1024 * 1024; // 2 MB limit
$flash = null;

function saveEventPoster(array $file): ?string
{
    $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
    if ($error === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($error !== UPLOAD_ERR_OK) {
        throw new RuntimeException('File upload failed. Please try again.');
    }

    $tmp = (string) ($file['tmp_name'] ?? '');
    $origName = (string) ($file['name'] ?? '');
    $size = is_file($tmp) ? filesize($tmp) : false;

    if (!$tmp || !is_uploaded_file($tmp) || $size === false || $size < 1) {
        throw new RuntimeException('Invalid uploaded file.');
    }

    if ($size > EVENT_POSTER_MAX_BYTES) {
        throw new RuntimeException('Poster image must be 2 MB or smaller.');
    }

    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    $allowedMimes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp'
    ];

    if (!isset($allowedMimes[$ext])) {
        throw new RuntimeException('Poster must be a valid JPG, PNG, or WEBP image.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp);
    if ($mime !== $allowedMimes[$ext]) {
        throw new RuntimeException('Uploaded file content does not match image extension.');
    }

    $uploadDir = __DIR__ . '/uploads/event-posters';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $storedName = bin2hex(random_bytes(16)) . '.' . $ext;
    if (!move_uploaded_file($tmp, $uploadDir . '/' . $storedName)) {
        throw new RuntimeException('Could not save uploaded poster image.');
    }

    return $storedName;
}

// POST Handling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validCsrfToken($_POST['csrf_token'] ?? null)) {
        $flash = ['type' => 'danger', 'msg' => 'Request expired. Please refresh and try again.'];
    } else {
        $action = (string) ($_POST['action'] ?? '');

        if ($action === 'add') {
            $title = trim((string) ($_POST['title'] ?? ''));
            $desc = trim((string) ($_POST['description'] ?? ''));
            $date = (string) ($_POST['event_date'] ?? '');
            $location = trim((string) ($_POST['location'] ?? ''));
            $category = (string) ($_POST['category'] ?? 'Technical');

            if ($title === '' || $desc === '' || $date === '' || $location === '') {
                $flash = ['type' => 'danger', 'msg' => 'Please fill in all required event fields.'];
            } else {
                try {
                    $poster = isset($_FILES['poster']) ? saveEventPoster($_FILES['poster']) : null;
                    $stmt = $conn->prepare('INSERT INTO events (title, description, event_date, location, category, poster_image) VALUES (?, ?, ?, ?, ?, ?)');
                    $stmt->bind_param('ssssss', $title, $desc, $date, $location, $category, $poster);
                    $stmt->execute();
                    $stmt->close();
                    $flash = ['type' => 'success', 'msg' => 'New campus event published successfully!'];
                } catch (RuntimeException $e) {
                    $flash = ['type' => 'danger', 'msg' => $e->getMessage()];
                }
            }
        } elseif ($action === 'delete') {
            $id = (int) ($_POST['event_id'] ?? 0);
            if ($id > 0) {
                $stmt = $conn->prepare('DELETE FROM events WHERE event_id = ?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $stmt->close();
                $flash = ['type' => 'success', 'msg' => "Event #$id deleted."];
            }
        }
    }
}

$events = $conn->query('SELECT event_id, title, description, event_date, location, category, poster_image, created_at FROM events ORDER BY event_date ASC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>StudentHub | Manage Events</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <header class="site-header">
    <div class="topbar">ADMINISTRATIVE MODULE • CHARUSAT FTE</div>
    <div class="header-content">
      <h1>StudentHub</h1>
      <p>Event Management & Poster Uploads</p>
    </div>
  </header>

  <nav class="navbar" aria-label="Role-based navigation">
    <a href="index.html">Home</a>
    <a href="admin_dashboard.php">Admin Dashboard</a>
    <a href="student_management.php">Manage Students</a>
    <a class="active" href="event_management.php">Manage Events</a>
    <a href="events.html">Events Portal</a>
  </nav>

  <main class="section">
    <div class="container">
      <?php if ($flash !== null): ?>
        <div class="site-banner" style="margin-top: 1.5rem; border-radius: var(--radius-sm); background: <?= $flash['type'] === 'success' ? '#dcfce7' : '#fee2e2' ?>; color: <?= $flash['type'] === 'success' ? '#166534' : '#991b1b' ?>;">
          <?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php endif; ?>

      <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-top: 1.5rem;">
        <h2>Event Management Directory</h2>
        <button class="btn btn-primary" onclick="openModal('addEventModal')">+ Create New Event</button>
      </div>

      <section class="panel" style="margin-top: 1.5rem; overflow-x: auto;">
        <table class="table-responsive">
          <thead>
            <tr>
              <th>ID</th>
              <th>Event Title</th>
              <th>Category</th>
              <th>Date</th>
              <th>Venue</th>
              <th>Poster</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($ev = $events->fetch_assoc()): ?>
              <tr>
                <td>#<?= (int) $ev['event_id'] ?></td>
                <td><strong><?= htmlspecialchars($ev['title'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                <td><span class="badge badge-primary"><?= htmlspecialchars($ev['category'], ENT_QUOTES, 'UTF-8') ?></span></td>
                <td><?= htmlspecialchars($ev['event_date'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($ev['location'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <?php if (!empty($ev['poster_image'])): ?>
                    <span class="badge badge-success">Uploaded</span>
                  <?php else: ?>
                    <span class="badge badge-secondary">None</span>
                  <?php endif; ?>
                </td>
                <td>
                  <form action="event_management.php" method="POST" onsubmit="return confirm('Delete this event?');" style="margin: 0;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>" />
                    <input type="hidden" name="action" value="delete" />
                    <input type="hidden" name="event_id" value="<?= (int) $ev['event_id'] ?>" />
                    <button type="submit" class="btn btn-danger" style="padding: 0.35rem 0.65rem; font-size: 0.8rem;">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </section>
    </div>
  </main>

  <!-- Add Event Modal -->
  <div class="modal-overlay" id="addEventModal">
    <div class="modal-content">
      <button class="modal-close" onclick="closeModal('addEventModal')">×</button>
      <h3>Create Campus Event</h3>
      <form action="event_management.php" method="POST" enctype="multipart/form-data" style="margin-top: 1rem;">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>" />
        <input type="hidden" name="action" value="add" />

        <div class="form-group">
          <label>Event Title:</label>
          <input type="text" name="title" placeholder="e.g. Annual Hackathon 2026" required />
        </div>
        <div class="form-group">
          <label>Description:</label>
          <textarea name="description" rows="3" placeholder="Brief event summary..." required></textarea>
        </div>
        <div class="form-group">
          <label>Event Date:</label>
          <input type="date" name="event_date" required />
        </div>
        <div class="form-group">
          <label>Location / Venue:</label>
          <input type="text" name="location" placeholder="e.g. Auditorium / Lab 3" required />
        </div>
        <div class="form-group">
          <label>Category:</label>
          <select name="category">
            <option value="Technical">Technical</option>
            <option value="Cultural">Cultural</option>
            <option value="Sports">Sports</option>
            <option value="Workshop">Workshop</option>
          </select>
        </div>
        <div class="form-group">
          <label>Poster Image (Max 2MB - JPG/PNG/WEBP):</label>
          <input type="file" name="poster" accept="image/png, image/jpeg, image/webp" />
        </div>
        <button class="btn btn-primary" type="submit" style="width: 100%; margin-top: 0.5rem;">Publish Event</button>
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
