<?php
require_once 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isset($_SESSION['customer_logged_in']) || $_SESSION['customer_logged_in'] !== true) {
    header("Location: customer-login.php");
    exit;
}

$customer_id = (int)$_SESSION['customer_id'];

$cust_stmt = $conn->prepare("SELECT name, email FROM customers WHERE id = ? LIMIT 1");
$cust_stmt->bind_param("i", $customer_id);
$cust_stmt->execute();
$customer_data = $cust_stmt->get_result()->fetch_assoc();
$display_name = !empty($customer_data['name']) ? $customer_data['name'] : 'Tourist';

$sql = "SELECT 
            b.booking_id, b.site_name, b.booking_date, b.time_slot, b.status AS booking_status, b.created_at AS booking_created,
            g.guide_id, g.full_name AS guide_name, g.phone AS guide_phone, g.rate_amount, g.rate_type, g.profile_photo AS guide_photo,
            ga.working_district, ga.notes AS slot_notes
        FROM bookings b
        LEFT JOIN guides g ON b.guide_id = g.guide_id
        LEFT JOIN guide_availability ga ON b.availability_id = ga.availability_id
        WHERE b.customer_id = ?
        ORDER BY b.booking_date DESC, b.id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

$bookings = [];
$counts = [
    'total' => 0,
    'confirmed' => 0,
    'completed' => 0,
    'pending' => 0,
    'cancelled' => 0
];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
        $counts['total']++;
        $status_key = strtolower($row['booking_status']);
        if (isset($counts[$status_key])) {
            $counts[$status_key]++;
        }
    }
}

$page_title = "My Booking History";
$extra_css  = "customer-booking-history.css";
require_once 'header.php';
?>

  <main class="cust-history-wrapper">
    <div class="page-container">

      <div class="history-header-box">
        <h1 class="history-main-title">My Booking History</h1>
      </div>

      <div class="history-metrics-grid">
        <div class="metric-card">
          <span class="metric-num"><?php echo $counts['total']; ?></span>
          <span class="metric-label"><i class="fa-solid fa-suitcase"></i> Total Trips</span>
        </div>
        <div class="metric-card">
          <span class="metric-num text-confirmed"><?php echo $counts['confirmed']; ?></span>
          <span class="metric-label"><i class="fa-solid fa-circle-check"></i> Confirmed</span>
        </div>
        <div class="metric-card">
          <span class="metric-num text-completed"><?php echo $counts['completed']; ?></span>
          <span class="metric-label"><i class="fa-solid fa-flag-checkered"></i> Completed</span>
        </div>
        <div class="metric-card">
          <span class="metric-num text-pending"><?php echo $counts['pending']; ?></span>
          <span class="metric-label"><i class="fa-solid fa-clock"></i> Pending</span>
        </div>
        <div class="metric-card">
          <span class="metric-num text-cancelled"><?php echo $counts['cancelled']; ?></span>
          <span class="metric-label"><i class="fa-solid fa-ban"></i> Cancelled</span>
        </div>
      </div>


      <div class="history-toolbar-card">
        <div class="filter-pills-wrap">
          <button type="button" class="history-filter-btn active" data-status="all">All Trips</button>
          <button type="button" class="history-filter-btn" data-status="confirmed">Confirmed</button>
          <button type="button" class="history-filter-btn" data-status="pending">Pending</button>
          <button type="button" class="history-filter-btn" data-status="completed">Completed</button>
          <button type="button" class="history-filter-btn" data-status="cancelled">Cancelled</button>
        </div>

        <div class="search-input-box">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input 
            type="text" 
            id="cust-booking-search" 
            placeholder="Search by Site Name, Guide Name or Booking ID..." 
            autocomplete="off"
          />
        </div>
      </div>

      <div class="bookings-history-stack" id="bookings-history-stack">
        <?php if (!empty($bookings)): ?>
          <?php foreach ($bookings as $b): 
            $status = $b['booking_status'];
            $status_class = strtolower($status);
            $formatted_tour_date = date("D, d M Y", strtotime($b['booking_date']));
            $created_date = date("d M Y, h:i A", strtotime($b['booking_created']));
            $guide_avatar = (!empty($b['guide_photo']) && file_exists($b['guide_photo'])) ? $b['guide_photo'] : 'images/default-avatar.png';
            $district_display = !empty($b['working_district']) ? $b['working_district'] : 'Historical Zone';

            $search_blob = strtolower(
                $b['booking_id'] . ' ' . 
                $b['site_name'] . ' ' . 
                ($b['guide_name'] ?? '') . ' ' . 
                ($b['guide_id'] ?? '') . ' ' . 
                $status
            );
          ?>
            <article class="user-booking-card" data-status="<?php echo htmlspecialchars($status_class); ?>" data-search="<?php echo htmlspecialchars($search_blob); ?>">
              
              <div class="card-head-row">
                <div class="ref-status-wrap">
                  <span class="booking-code"><i class="fa-solid fa-hashtag"></i> <?php echo htmlspecialchars($b['booking_id']); ?></span>
                  <span class="cust-status-pill status-<?php echo htmlspecialchars($status_class); ?>">
                    <i class="fa-solid fa-circle-dot"></i> <?php echo htmlspecialchars($status); ?>
                  </span>
                </div>
                <div class="reserved-on-text">
                  <span>Reserved: <strong><?php echo htmlspecialchars($created_date); ?></strong></span>
                </div>
              </div>

   
              <div class="card-body-grid">
  
                <div class="info-section landmark-section">
                  <span class="sec-label"><i class="fa-solid fa-landmark-dome"></i> Heritage Destination</span>
                  <h2 class="landmark-name"><?php echo htmlspecialchars($b['site_name']); ?></h2>
                  <span class="district-badge"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($district_display); ?></span>
                </div>

                <div class="info-section schedule-section">
                  <span class="sec-label"><i class="fa-regular fa-calendar-check"></i> Schedule &amp; Timings</span>
                  <div class="schedule-detail-row">
                    <span class="detail-label">Tour Date:</span>
                    <strong class="detail-value text-navy"><?php echo htmlspecialchars($formatted_tour_date); ?></strong>
                  </div>
                  <div class="schedule-detail-row">
                    <span class="detail-label">Time Slot:</span>
                    <span class="detail-value time-badge"><i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($b['time_slot']); ?></span>
                  </div>
                </div>

                <div class="info-section guide-section">
                  <span class="sec-label"><i class="fa-solid fa-id-badge"></i> Assigned Tour Guide</span>
                  <div class="guide-profile-snippet">
                    <img src="<?php echo htmlspecialchars($guide_avatar); ?>" alt="<?php echo htmlspecialchars($b['guide_name'] ?? 'Guide'); ?>" class="guide-thumb" />
                    <div class="guide-meta-data">
                      <div class="guide-name-title">
                        <strong><?php echo htmlspecialchars($b['guide_name'] ?? 'Assigned Expert'); ?></strong>
                        <i class="fa-solid fa-circle-check verified-icon" title="Certified Guide"></i>
                      </div>
                      <span class="guide-code"><?php echo htmlspecialchars($b['guide_id']); ?></span>
                      <?php if (!empty($b['guide_phone'])): ?>
                        <a href="tel:<?php echo htmlspecialchars($b['guide_phone']); ?>" class="guide-call-link">
                          <i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($b['guide_phone']); ?>
                        </a>
                      <?php endif; ?>
                    </div>
                  </div>

                  <div class="fee-row">
                    <span>Guide Fee:</span>
                    <strong>৳<?php echo number_format($b['rate_amount'] ?? 0); ?> / <?php echo htmlspecialchars($b['rate_type'] ?? 'Daily'); ?></strong>
                  </div>
                </div>

              </div>

            </article>
          <?php endforeach; ?>
      <?php else: ?>
  <div class="no-history-card">
  
    <h2>No Heritage Excursions Yet</h2>
    <p>You have not made any tour reservations. Explore certified heritage tour guides & available schedules to reserve your next excursion.</p>
  </div>
<?php endif; ?>
      </div>

      <div id="no-filter-results" class="no-history-card" style="display: none;">
        <i class="fa-solid fa-magnifying-glass"></i>
        <h2>No Matching Reservations Found</h2>
        <p>No bookings match your search query or selected filter. Please try a different landmark name, guide name or booking reference</p>
      </div>

    </div>
  </main>

  <script src="customer-booking-history.js"></script>

<?php require_once 'footer.php'; ?>