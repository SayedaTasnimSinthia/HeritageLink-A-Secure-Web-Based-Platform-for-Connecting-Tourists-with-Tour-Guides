<?php
session_start();
require_once 'db.php';

if (empty($_SESSION['admin_logged_in'])) {
    header("Location: admin-login.php");
    exit;
}

$alert_message = "";
$alert_type = "";


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_action'])) {
    $booking_id = trim($_POST['booking_id'] ?? '');
    $availability_id = trim($_POST['availability_id'] ?? '');
    $action = trim($_POST['admin_action']);

    if (!empty($booking_id) && !empty($availability_id)) {
        if ($action === 'confirm') {
            $u1 = $conn->prepare("UPDATE bookings SET status = 'Confirmed' WHERE booking_id = ?");
            $u1->bind_param("s", $booking_id);
            $u1->execute();

            $u2 = $conn->prepare("UPDATE guide_availability SET slot_status = 'Booked' WHERE availability_id = ?");
            $u2->bind_param("s", $availability_id);
            $u2->execute();

            $alert_message = "Booking #$booking_id confirmed successfully! Guide schedule status updated to Booked.";
            $alert_type = "success";
        } elseif ($action === 'cancel') {
            $u1 = $conn->prepare("UPDATE bookings SET status = 'Cancelled' WHERE booking_id = ?");
            $u1->bind_param("s", $booking_id);
            $u1->execute();

            $u2 = $conn->prepare("UPDATE guide_availability SET slot_status = 'Available' WHERE availability_id = ?");
            $u2->bind_param("s", $availability_id);
            $u2->execute();

            $alert_message = "Booking #$booking_id cancelled. The guide slot has been released and is now Available again on the website.";
            $alert_type = "success";
        } elseif ($action === 'complete') {
            $u1 = $conn->prepare("UPDATE bookings SET status = 'Completed' WHERE booking_id = ?");
            $u1->bind_param("s", $booking_id);
            $u1->execute();

            $alert_message = "Booking #$booking_id successfully marked as Completed.";
            $alert_type = "success";
        }
    }
}

$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';

$query = "SELECT 
            b.booking_id, b.customer_id, b.guide_id, b.availability_id, b.site_name, b.booking_date, b.time_slot, b.status AS booking_status, b.created_at AS booking_created,
            c.name AS customer_name, c.email AS customer_email, c.phone AS customer_phone, c.nid_number AS customer_nid, c.gender AS customer_gender, c.dob AS customer_dob, c.blood_group AS customer_blood, c.nationality AS customer_nationality, c.emergency_name, c.emergency_phone,
            g.full_name AS guide_name, g.phone AS guide_phone, g.rate_amount, g.rate_type, g.profile_photo AS guide_photo,
            ga.working_district, ga.slot_status
          FROM bookings b
          INNER JOIN customers c ON b.customer_id = c.id
          LEFT JOIN guides g ON b.guide_id = g.guide_id
          LEFT JOIN guide_availability ga ON b.availability_id = ga.availability_id ";

if (!empty($filter_status) && in_array($filter_status, ['Pending', 'Confirmed', 'Cancelled', 'Completed'])) {
    $query .= " WHERE b.status = '" . $conn->real_escape_string($filter_status) . "' ";
}

$query .= " ORDER BY b.id DESC";
$res = $conn->query($query);

$bookings = [];
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $bookings[] = $row;
    }
}
$total_count = count($bookings);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Manage Bookings - HeritageLink Admin</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


  <link rel="stylesheet" href="index.css">
  <link rel="stylesheet" href="admin-dashboard.css">
  <link rel="stylesheet" href="admin-manage-bookings.css">
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

  <main class="manage-bookings-wrapper">
    <div class="page-container-wide">

      <div class="view-header-box">
        <h1 class="view-page-title">Manage Customer Bookings</h1>
      
      </div>

      <?php if (!empty($alert_message)): ?>
        <div class="alert-box <?php echo $alert_type; ?>" id="action-alert">
          <i class="fa-solid <?php echo ($alert_type === 'success') ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>"></i>
          <span><?php echo htmlspecialchars($alert_message); ?></span>
        </div>
      <?php endif; ?>

      <div class="booking-filter-card">
        <div class="filter-status-buttons">
          <a href="admin-manage-bookings.php?status=Pending" class="filter-btn status-pending-btn <?php echo ($filter_status === 'Pending') ? 'active' : ''; ?>">Pending</a>
          <a href="admin-manage-bookings.php?status=Confirmed" class="filter-btn status-confirmed-btn <?php echo ($filter_status === 'Confirmed') ? 'active' : ''; ?>">Confirmed</a>
          <a href="admin-manage-bookings.php?status=Cancelled" class="filter-btn status-cancelled-btn <?php echo ($filter_status === 'Cancelled') ? 'active' : ''; ?>">Cancelled</a>
          <a href="admin-manage-bookings.php?status=Completed" class="filter-btn status-completed-btn <?php echo ($filter_status === 'Completed') ? 'active' : ''; ?>">Completed</a>
        </div>

        <div class="search-input-wrap">
          <i class="fa-solid fa-magnifying-glass search-icon"></i>
          <input 
            type="text" 
            id="booking-live-search" 
            placeholder="Search by Guide Name, Site Name, Tourist Name or Booking ID..." 
            autocomplete="off"
          />
        </div>
      </div>

      <div class="bookings-cards-stack" id="bookings-container">
        <?php if (!empty($bookings)): ?>
          <?php foreach ($bookings as $b): 
            $status = $b['booking_status'];
            $formatted_tour_date = date("D, d M Y", strtotime($b['booking_date']));
            $created_date = date("d M Y, h:i A", strtotime($b['booking_created']));

            $search_corpus = strtolower(
                trim($b['booking_id']) . ' ' . 
                trim($b['customer_name'] ?? '') . ' ' . 
                trim($b['guide_name'] ?? '') . ' ' . 
                trim($b['site_name'] ?? '')
            );
          ?>
            <article class="booking-admin-card" data-search="<?php echo htmlspecialchars($search_corpus); ?>">
              
              <div class="booking-card-top">
                <div class="top-ident">
                  <span class="booking-ref-badge"><i class="fa-solid fa-hashtag"></i> <?php echo htmlspecialchars($b['booking_id']); ?></span>
                  <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $status)); ?>">
                    <i class="fa-solid fa-circle-dot"></i> <?php echo htmlspecialchars($status); ?>
                  </span>
                  <span class="slot-status-note">
                    Guide Schedule Status: <strong><?php echo htmlspecialchars($b['slot_status'] ?? 'N/A'); ?></strong>
                  </span>
                </div>
                <div class="top-date-created">
                  <span>Reserved on: <strong><?php echo htmlspecialchars($created_date); ?></strong></span>
                </div>
              </div>

              <div class="booking-details-grid">
                
                <div class="detail-box">
                  <h3 class="box-title"><i class="fa-solid fa-user-tag"></i> Customer Information</h3>
                  
                  <div class="info-line">
                    <span class="lbl">Name:</span>
                    <strong class="val"><?php echo htmlspecialchars($b['customer_name'] ?? 'Not Provided'); ?></strong>
                  </div>
                  <div class="info-line">
                    <span class="lbl">Email:</span>
                    <span class="val"><a href="mailto:<?php echo htmlspecialchars($b['customer_email']); ?>"><?php echo htmlspecialchars($b['customer_email']); ?></a></span>
                  </div>
                  <div class="info-line">
                    <span class="lbl">Phone:</span>
                    <strong class="val"><?php echo !empty($b['customer_phone']) ? htmlspecialchars($b['customer_phone']) : '<em class="empty-txt">None</em>'; ?></strong>
                  </div>
                  <div class="info-line">
                    <span class="lbl">NID / Passport:</span>
                    <span class="val"><?php echo !empty($b['customer_nid']) ? htmlspecialchars($b['customer_nid']) : '<em class="empty-txt">None</em>'; ?></span>
                  </div>
                  <div class="info-line">
                    <span class="lbl">Nationality:</span>
                    <span class="val"><?php echo !empty($b['customer_nationality']) ? htmlspecialchars($b['customer_nationality']) : 'Bangladeshi'; ?></span>
                  </div>
                  <div class="info-line">
                    <span class="lbl">Blood Group:</span>
                    <span class="val">
                      <?php if (!empty($b['customer_blood'])): ?>
                        <span class="badge-blood"><i class="fa-solid fa-droplet"></i> <?php echo htmlspecialchars($b['customer_blood']); ?></span>
                      <?php else: ?>
                        <em class="empty-txt">N/A</em>
                      <?php endif; ?>
                    </span>
                  </div>
                  
                  <div class="emergency-subcard">
                    <span class="em-title"><i class="fa-solid fa-truck-medical"></i> Emergency Contact:</span>
                    <span class="em-data"><?php echo !empty($b['emergency_name']) ? htmlspecialchars($b['emergency_name']) : 'Not listed'; ?></span>
                    <?php if (!empty($b['emergency_phone'])): ?>
                      <a href="tel:<?php echo htmlspecialchars($b['emergency_phone']); ?>" class="em-phone"><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($b['emergency_phone']); ?></a>
                    <?php endif; ?>
                  </div>
                </div>

                <div class="detail-box">
                  <h3 class="box-title"><i class="fa-solid fa-id-badge"></i> Assigned Tour Guide</h3>
                  
                  <div class="info-line">
                    <span class="lbl">Guide Name:</span>
                    <strong class="val"><?php echo htmlspecialchars($b['guide_name'] ?? 'Unassigned'); ?></strong>
                  </div>
                  <div class="info-line">
                    <span class="lbl">Guide ID:</span>
                    <span class="val font-mono"><?php echo htmlspecialchars($b['guide_id']); ?></span>
                  </div>
                  <div class="info-line">
                    <span class="lbl">Contact:</span>
                    <span class="val"><?php echo !empty($b['guide_phone']) ? htmlspecialchars($b['guide_phone']) : '<em class="empty-txt">None</em>'; ?></span>
                  </div>
                  <div class="info-line">
                    <span class="lbl">Guide Fee:</span>
                    <strong class="val fee-highlight">৳<?php echo number_format($b['rate_amount'] ?? 0); ?> / <?php echo htmlspecialchars($b['rate_type'] ?? 'Daily'); ?></strong>
                  </div>
                </div>
                <div class="detail-box">
                  <h3 class="box-title"><i class="fa-solid fa-landmark"></i> Schedule &amp; Landmark</h3>
                  
                  <div class="info-line">
                    <span class="lbl">Heritage Landmark:</span>
                    <strong class="val site-title"><?php echo htmlspecialchars($b['site_name']); ?></strong>
                  </div>
                  <div class="info-line">
                    <span class="lbl">District:</span>
                    <span class="val"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($b['working_district'] ?? 'N/A'); ?></span>
                  </div>
                  <div class="info-line">
                    <span class="lbl">Tour Date:</span>
                    <strong class="val date-highlight"><i class="fa-regular fa-calendar"></i> <?php echo htmlspecialchars($formatted_tour_date); ?></strong>
                  </div>
                  <div class="info-line">
                    <span class="lbl">Time Slot:</span>
                    <span class="val"><i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($b['time_slot']); ?></span>
                  </div>
                  <div class="info-line">
                    <span class="lbl">Slot Ref ID:</span>
                    <span class="val font-mono"><?php echo htmlspecialchars($b['availability_id']); ?></span>
                  </div>
                </div>

              </div>

              <?php if ($status === 'Pending' || $status === 'Confirmed'): ?>
                <div class="card-action-bar">
                  <form method="POST" action="admin-manage-bookings.php<?php echo !empty($filter_status) ? '?status=' . urlencode($filter_status) : ''; ?>" class="action-form">
                    <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($b['booking_id']); ?>">
                    <input type="hidden" name="availability_id" value="<?php echo htmlspecialchars($b['availability_id']); ?>">

                    <?php if ($status === 'Pending'): ?>
                      <button type="submit" name="admin_action" value="confirm" class="btn-action btn-confirm" onclick="return confirm('Confirm this booking and lock the guide schedule as Booked?');">
                        <i class="fa-solid fa-circle-check"></i> Confirm Booking
                      </button>
                      <button type="submit" name="admin_action" value="cancel" class="btn-action btn-cancel-booking" onclick="return confirm('Cancel this booking? The slot will immediately become Available on guides.php again.');">
                        <i class="fa-solid fa-ban"></i> Cancel Booking
                      </button>
                    <?php elseif ($status === 'Confirmed'): ?>
                      <button type="submit" name="admin_action" value="complete" class="btn-action btn-complete" onclick="return confirm('Mark this tour excursion as completed?');">
                        <i class="fa-solid fa-flag-checkered"></i> Mark as Completed
                      </button>
                      <button type="submit" name="admin_action" value="cancel" class="btn-action btn-cancel-booking" onclick="return confirm('Cancel this confirmed reservation and release slot back to Available on guides.php?');">
                        <i class="fa-solid fa-ban"></i> Cancel Booking
                      </button>
                    <?php endif; ?>
                  </form>
                </div>
              <?php endif; ?>

            </article>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="no-data-card">
          
            <h2>No Bookings Found</h2>
            <p>No customer reservations match the filter</p>
          </div>
        <?php endif; ?>
      </div>

      <div id="no-search-results" class="no-data-card" style="display: none;">
        <i class="fa-solid fa-magnifying-glass"></i>
        <h2>No Matching Bookings</h2>
        <p>No customer bookings match your search. Please search using a guide name, site name, tourist name or booking ID</p>
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

  <script src="admin-manage-bookings.js"></script>
</body>
</html>