<?php
session_start();
require_once 'db.php';
if (empty($_SESSION['admin_logged_in'])) {
    header("Location: admin-login.php");
    exit;
}

$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$selected_cust_id = isset($_GET['cust_id']) ? (int)$_GET['cust_id'] : 0;

$target_user = null;
$user_bookings = [];

if ($selected_cust_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM customers WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $selected_cust_id);
    $stmt->execute();
    $target_user = $stmt->get_result()->fetch_assoc();
} elseif (!empty($search_query)) {
    $clean_q = preg_replace('/[^0-9]/', '', $search_query);
    $escaped_q = $conn->real_escape_string($search_query);

    if (!empty($clean_q) && (strpos(strtoupper($search_query), 'CUST') !== false || is_numeric($search_query))) {
        $user_sql = "SELECT * FROM customers WHERE id = " . (int)$clean_q . " LIMIT 1";
    } else {
        $user_sql = "SELECT * FROM customers WHERE email LIKE '%$escaped_q%' OR name LIKE '%$escaped_q%' LIMIT 1";
    }
    $res = $conn->query($user_sql);
    if ($res && $res->num_rows > 0) {
        $target_user = $res->fetch_assoc();
    }
}

if ($target_user) {
    $cid = (int)$target_user['id'];
    $b_sql = "SELECT b.*, g.full_name AS guide_name, g.phone AS guide_phone, g.rate_amount, g.rate_type, ga.working_district 
              FROM bookings b
              LEFT JOIN guides g ON b.guide_id = g.guide_id
              LEFT JOIN guide_availability ga ON b.availability_id = ga.availability_id
              WHERE b.customer_id = $cid
              ORDER BY b.booking_date DESC, b.id DESC";
    $b_res = $conn->query($b_sql);
    if ($b_res && $b_res->num_rows > 0) {
        while ($b_row = $b_res->fetch_assoc()) {
            $user_bookings[] = $b_row;
        }
    }
}

$all_cust_sql = "SELECT c.*, COUNT(b.id) AS total_user_bookings 
                 FROM customers c 
                 LEFT JOIN bookings b ON c.id = b.customer_id 
                 GROUP BY c.id 
                 ORDER BY c.name ASC";
$all_cust_res = $conn->query($all_cust_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>User Booking History - HeritageLink Admin</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <link rel="stylesheet" href="index.css">
  <link rel="stylesheet" href="admin-dashboard.css">
  <link rel="stylesheet" href="admin-user-booking-history.css">
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


  <main class="history-page-wrapper">
    <div class="page-container-wide">

      <div class="history-header-box">
        <h1 class="history-page-title">User Tour History</h1>
      </div>

      <div class="search-filter-card">
        <form method="GET" action="admin-user-booking-history.php" class="search-user-form">
          <div class="search-input-wrap">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input 
              type="text" 
              name="q" 
              id="user-history-search" 
              placeholder="Type Customer ID (e.g. 1 or #CUST-0001), Name or Email..." 
              value="<?php echo htmlspecialchars($search_query); ?>" 
              autocomplete="off" 
              required
            />
          </div>

          <button type="submit" class="btn-search-user">
            Search User
          </button>

          <?php if ($target_user || !empty($search_query)): ?>
            <a href="admin-user-booking-history.php" class="btn-reset-search">
              View All Users
            </a>
          <?php endif; ?>
        </form>
      </div>

      <?php if (!empty($search_query) && !$target_user): ?>
        <div class="alert-box error">
          <i class="fa-solid fa-circle-exclamation"></i>
          <span>No user found matching "<strong><?php echo htmlspecialchars($search_query); ?></strong>". Please verify the customer ID, name or email address.</span>
        </div>
      <?php endif; ?>

      <?php if ($target_user): 
        $user_avatar = (!empty($target_user['profile_photo']) && file_exists($target_user['profile_photo'])) ? $target_user['profile_photo'] : 'images/default-avatar.png';
        $cust_code = "#CUST-" . str_pad($target_user['id'], 4, '0', STR_PAD_LEFT);
        $total_res = count($user_bookings);
        $confirmed_cnt = 0;
        $completed_cnt = 0;
        $cancelled_cnt = 0;
        $pending_cnt = 0;

        foreach ($user_bookings as $ub) {
            if ($ub['status'] === 'Confirmed') $confirmed_cnt++;
            elseif ($ub['status'] === 'Completed') $completed_cnt++;
            elseif ($ub['status'] === 'Cancelled') $cancelled_cnt++;
            elseif ($ub['status'] === 'Pending') $pending_cnt++;
        }
      ?>
        <div class="user-dossier-card">
          <div class="user-dossier-top">
            <div class="dossier-avatar-wrap">
              <img src="<?php echo htmlspecialchars($user_avatar); ?>" alt="<?php echo htmlspecialchars($target_user['name']); ?>" class="dossier-avatar" />
              <span class="dossier-id-pill"><?php echo htmlspecialchars($cust_code); ?></span>
            </div>

            <div class="dossier-details-wrap">
              <div class="dossier-title-row">
                <h2><?php echo htmlspecialchars($target_user['name'] ?? 'Anonymous Tourist'); ?></h2>
                <span class="nationality-tag"><i class="fa-solid fa-passport"></i> <?php echo htmlspecialchars($target_user['nationality'] ?? 'Bangladeshi'); ?></span>
              </div>

              <div class="dossier-meta-grid">
                <div class="dossier-meta-item">
                  <span class="meta-label"><i class="fa-solid fa-envelope"></i> Email:</span>
                  <a href="mailto:<?php echo htmlspecialchars($target_user['email']); ?>" class="meta-val"><?php echo htmlspecialchars($target_user['email']); ?></a>
                </div>

                <div class="dossier-meta-item">
                  <span class="meta-label"><i class="fa-solid fa-phone"></i> Phone:</span>
                  <strong class="meta-val"><?php echo !empty($target_user['phone']) ? htmlspecialchars($target_user['phone']) : '<em>Not Set</em>'; ?></strong>
                </div>

                <div class="dossier-meta-item">
                  <span class="meta-label"><i class="fa-solid fa-id-card"></i> NID:</span>
                  <span class="meta-val"><?php echo !empty($target_user['nid_number']) ? htmlspecialchars($target_user['nid_number']) : '<em>Not Set</em>'; ?></span>
                </div>

                <div class="dossier-meta-item">
                  <span class="meta-label"><i class="fa-solid fa-droplet"></i> Blood Group:</span>
                  <span class="meta-val badge-blood"><?php echo !empty($target_user['blood_group']) ? htmlspecialchars($target_user['blood_group']) : 'N/A'; ?></span>
                </div>
              </div>
            </div>
          </div>

          <div class="dossier-metrics-strip">
            <div class="d-metric"><span class="d-count"><?php echo $total_res; ?></span><span class="d-lbl">Total Reservations</span></div>
            <div class="d-metric"><span class="d-count status-confirmed-text"><?php echo $confirmed_cnt; ?></span><span class="d-lbl">Confirmed</span></div>
            <div class="d-metric"><span class="d-count status-completed-text"><?php echo $completed_cnt; ?></span><span class="d-lbl">Completed</span></div>
            <div class="d-metric"><span class="d-count status-pending-text"><?php echo $pending_cnt; ?></span><span class="d-lbl">Pending</span></div>
            <div class="d-metric"><span class="d-count status-cancelled-text"><?php echo $cancelled_cnt; ?></span><span class="d-lbl">Cancelled</span></div>
          </div>
        </div>
        <div class="table-container-card">
          <div class="table-card-header">
            <h3><i class="fa-solid fa-clock-rotate-left"></i> Itinerary &amp; Excursion Records</h3>
            <span class="counter-badge"><strong><?php echo $total_res; ?></strong> Record(s) Found</span>
          </div>

          <div class="table-responsive">
            <table class="history-table">
              <thead>
                <tr>
                  <th>Booking ID</th>
                  <th>Heritage Landmark</th>
                  <th>Assigned Guide</th>
                  <th>Tour Date &amp; Shift</th>
                  <th>Guide Rate</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($user_bookings)): ?>
                  <?php foreach ($user_bookings as $b): 
                    $tour_date = date("M d, Y", strtotime($b['booking_date']));
                    $b_status = strtolower($b['status']);
                  ?>
                    <tr>
                      <td>
                        <span class="booking-ref-tag"><i class="fa-solid fa-hashtag"></i> <?php echo htmlspecialchars($b['booking_id']); ?></span>
                      </td>
                      <td>
                        <strong class="site-name-text"><?php echo htmlspecialchars($b['site_name']); ?></strong><br>
                        <small class="district-text"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($b['working_district'] ?? 'Bangladesh'); ?></small>
                      </td>
                      <td>
                        <strong><?php echo htmlspecialchars($b['guide_name'] ?? 'Unassigned'); ?></strong><br>
                        <span class="guide-tag"><?php echo htmlspecialchars($b['guide_id']); ?></span>
                      </td>
                      <td>
                        <div class="datetime-cell">
                          <span class="date-text"><i class="fa-regular fa-calendar"></i> <?php echo htmlspecialchars($tour_date); ?></span>
                          <small class="time-text"><i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($b['time_slot']); ?></small>
                        </div>
                      </td>
                      <td>
                        <strong class="fee-text">৳<?php echo number_format($b['rate_amount'] ?? 0); ?></strong>
                        <small class="rate-type-sub">/ <?php echo htmlspecialchars($b['rate_type'] ?? 'Daily'); ?></small>
                      </td>
                      <td>
                        <span class="status-pill status-<?php echo $b_status; ?>">
                          <i class="fa-solid fa-circle-dot"></i> <?php echo htmlspecialchars($b['status']); ?>
                        </span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="6" class="no-data-cell">
                      <i class="fa-solid fa-calendar-xmark empty-icon"></i>
                      <p>This user has not placed any tour bookings yet.</p>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      <?php else: ?>
        <div class="table-container-card">
          <div class="table-card-header">
            <h3><i class="fa-solid fa-users"></i> All Registered Travelers Directory</h3>
            <span class="counter-badge">Total Travelers: <strong><?php echo ($all_cust_res) ? $all_cust_res->num_rows : 0; ?></strong></span>
          </div>

          <div class="table-responsive">
            <table class="history-table" id="all-users-table">
              <thead>
                <tr>
                  <th>Customer ID</th>
                  <th>Traveler Name</th>
                  <th>Email Address</th>
                  <th>Phone Number</th>
                  <th class="text-center">Total Bookings</th>
                  <th class="text-center">Action</th>
                </tr>
              </thead>
              <tbody id="all-users-tbody">
                <?php if ($all_cust_res && $all_cust_res->num_rows > 0): ?>
                  <?php while ($c = $all_cust_res->fetch_assoc()): 
                    $cid_code = "#CUST-" . str_pad($c['id'], 4, '0', STR_PAD_LEFT);
                  ?>
                    <tr class="user-row" data-search="<?php echo htmlspecialchars(strtolower($cid_code . ' ' . $c['id'] . ' ' . ($c['name'] ?? '') . ' ' . $c['email'])); ?>">
                      <td><span class="serial-tag"><?php echo htmlspecialchars($cid_code); ?></span></td>
                      <td><strong><?php echo htmlspecialchars($c['name'] ?? 'Not Set'); ?></strong></td>
                      <td><a href="mailto:<?php echo htmlspecialchars($c['email']); ?>" class="email-link"><?php echo htmlspecialchars($c['email']); ?></a></td>
                      <td><?php echo !empty($c['phone']) ? htmlspecialchars($c['phone']) : '<em class="text-muted">Not Set</em>'; ?></td>
                      <td class="text-center">
                        <span class="badge-count-pill"><?php echo (int)$c['total_user_bookings']; ?> tour(s)</span>
                      </td>
                      <td class="text-center">
                        <a href="admin-user-booking-history.php?cust_id=<?php echo $c['id']; ?>" class="btn-inspect-history">
                          <i class="fa-solid fa-clock-rotate-left"></i> View History
                        </a>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="6" class="no-data-cell">
                      <i class="fa-solid fa-user-slash empty-icon"></i>
                      <p>No registered travelers found in database.</p>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endif; ?>

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

  <script src="admin-user-booking-history.js"></script>
</body>
</html>