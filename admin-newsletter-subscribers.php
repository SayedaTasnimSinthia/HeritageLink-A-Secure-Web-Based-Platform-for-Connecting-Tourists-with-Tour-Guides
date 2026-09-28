<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin-login.php");
    exit;
}

$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_subscriber_id'])) {
    $target_id = (int)$_POST['delete_subscriber_id'];

    if ($target_id > 0) {
        $delete_sql = "DELETE FROM subscribers WHERE id = $target_id LIMIT 1";
        if ($conn->query($delete_sql)) {
            $success_message = "Subscriber record removed successfully.";
        } else {
            $error_message = "Database error: Could not remove subscriber. " . $conn->error;
        }
    }
}

$sql = "SELECT id, email, subscribed_at FROM subscribers ORDER BY subscribed_at DESC";
$result = $conn->query($sql);
$total_subscribers = ($result) ? $result->num_rows : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Newsletter Subscribers - HeritageLink Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="index.css">
  <link rel="stylesheet" href="admin-dashboard.css">
  <link rel="stylesheet" href="admin-newsletter-subscribers.css">
</head>
<body>
  <header>
    <div class="nav-bar">
      <a href="admin-dashboard.php" class="logo">Heritage<span>Link</span></a>
      <div class="admin-nav-actions">
        <a href="admin-dashboard.php" class="btn-nav-dashboard">Dashboard</a>
        <span class="admin-user-badge"><i class="fa-solid fa-user-shield"></i> Admin</span>
        <a href="admin-logout.php" class="btn-admin-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
      </div>
    </div>
  </header>
  <main class="subscribers-wrapper">
    <div class="page-container">
      <div class="subscribers-header-box">
        <h1 class="subscribers-page-title">Newsletter Subscribers</h1>
       
      </div>
      <?php if (!empty($error_message)): ?>
        <div class="alert-box error"><?php echo htmlspecialchars($error_message); ?></div>
      <?php elseif (!empty($success_message)): ?>
        <div class="alert-box success"><?php echo htmlspecialchars($success_message); ?></div>
      <?php endif; ?>
      <div class="search-filter-card">
        <div class="filter-top-bar">
          <div class="counter-badge">
            <i class="fa-solid fa-envelope-circle-check"></i>
            <span>Total Subscribers: <strong><?php echo $total_subscribers; ?></strong></span>
          </div>
        </div>

        <div class="search-input-wrap">
          <i class="fa-solid fa-magnifying-glass search-icon"></i>
          <input 
            type="text" 
            id="live-subscriber-search" 
            placeholder="Search by email address or subscription date (YYYY-MM-DD)..." 
            autocomplete="off"
          />
        </div>
      </div>
      <div class="table-container-card">
        <div class="table-responsive">
          <table class="subscribers-table" id="subscribers-table">
            <thead>
              <tr>
                <th style="width: 80px;">#</th>
                <th>Subscriber Email Address</th>
                <th>Subscribed Date &amp; Time</th>
                <th>Status</th>
                <th class="text-center" style="width: 140px;">Action</th>
              </tr>
            </thead>
            <tbody id="subscribers-table-body">
              <?php if ($result && $result->num_rows > 0): 
                $serial = 1;
                while ($row = $result->fetch_assoc()): 
                  $formatted_date = date("M d, Y", strtotime($row['subscribed_at']));
                  $formatted_time = date("h:i A", strtotime($row['subscribed_at']));
              ?>
                <tr 
                  class="subscriber-data-row"
                  data-email="<?php echo htmlspecialchars(strtolower($row['email'])); ?>"
                  data-date="<?php echo htmlspecialchars(strtolower($row['subscribed_at'])); ?>"
                >
          
                  <td>
                    <span class="serial-tag"><?php echo $serial++; ?></span>
                  </td>

                  <td>
                    <div class="email-cell">
                      <i class="fa-regular fa-envelope email-icon"></i>
                      <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>" class="email-link">
                        <?php echo htmlspecialchars($row['email']); ?>
                      </a>
                    </div>
                  </td>
                  <td>
                    <div class="datetime-cell">
                      <span class="date-text"><?php echo htmlspecialchars($formatted_date); ?></span>
                      <small class="time-text"><i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($formatted_time); ?></small>
                    </div>
                  </td>

                  <td>
                    <span class="status-pill status-active">
                      <i class="fa-solid fa-circle-check"></i> Subscribed
                    </span>
                  </td>

                  <td class="text-center">
                    <form method="POST" action="admin-newsletter-subscribers.php" class="delete-form" onsubmit="return confirmDeleteSubscriber('<?php echo htmlspecialchars(addslashes($row['email'])); ?>');">
                      <input type="hidden" name="delete_subscriber_id" value="<?php echo (int)$row['id']; ?>" />
                      <button type="submit" class="btn-delete" title="Remove subscriber from mailing list">
                        <i class="fa-solid fa-trash-can"></i> Remove
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr id="empty-db-row">
                <td colspan="5" class="no-data-cell">
                  <i class="fa-solid fa-envelope-open-text empty-icon"></i>
                  <p>No newsletter subscribers found in the database yet.</p>
                </td>
              </tr>
            <?php endif; ?>
            <tr id="no-match-row" style="display: none;">
              <td colspan="5" class="no-data-cell">
                <i class="fa-solid fa-magnifying-glass empty-icon"></i>
                <p>No subscribers match your search query.</p>
              </td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </main>
  <footer>
    <div class="page-container">
      <div class="footer-row">
        <div class="footer-left">
          <h4>Heritage<span>Link</span></h4>
          <p>A secure centralized web platform connecting domestic and international tourists<br>with verified heritage tour guides across Bangladesh's historical landmarks.</p>
          <div class="footer-socials">
            <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
            <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
            <a href="https://youtube.com" target="_blank" rel="noopener noreferrer" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
          </div>
        </div>
<div class="footer-right">
  <h4>Legal & Support</h4>
  <ul>
    <li><a href="javascript:void(0);" class="disabled-link">FAQ</a></li>
    <li><a href="javascript:void(0);" class="disabled-link">About</a></li>
    <li><a href="javascript:void(0);" class="disabled-link">Cookie Policy</a></li>
    <li><a href="javascript:void(0);" class="disabled-link">Privacy Policy</a></li>
    <li><a href="javascript:void(0);" class="disabled-link">Terms &amp; condition</a></li>
    <li><a href="javascript:void(0);" class="disabled-link">Guide Verification Policy</a></li>
  </ul>
</div>
      </div>
    </div>

    <div class="footer-copyright">
      <span>&copy; 2026 HeritageLink. All Rights Reserved.</span>
    </div>
  </footer>

  <script src="admin-newsletter-subscribers.js"></script>
</body>
</html>