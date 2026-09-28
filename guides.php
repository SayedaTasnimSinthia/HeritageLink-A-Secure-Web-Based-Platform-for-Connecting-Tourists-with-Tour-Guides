<?php
require_once 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$booking_alert = null;
$selected_site = isset($_GET['site']) ? trim($_GET['site']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book_slot') {
    if (!isset($_SESSION['customer_logged_in']) || $_SESSION['customer_logged_in'] !== true) {
        header("Location: customer-login.php");
        exit;
    }

    $customer_id     = (int)$_SESSION['customer_id'];
    $guide_id        = trim($_POST['guide_id'] ?? '');
    $availability_id = trim($_POST['availability_id'] ?? '');

    $profile_stmt = $conn->prepare("SELECT phone, nid_number, gender, dob, blood_group, nationality, emergency_name, emergency_phone FROM customers WHERE id = ? LIMIT 1");
    $profile_stmt->bind_param("i", $customer_id);
    $profile_stmt->execute();
    $user_prof = $profile_stmt->get_result()->fetch_assoc();

    $is_profile_complete = (
        !empty($user_prof['phone']) &&
        !empty($user_prof['nid_number']) &&
        !empty($user_prof['gender']) &&
        !empty($user_prof['dob']) &&
        !empty($user_prof['blood_group']) &&
        !empty($user_prof['nationality']) &&
        !empty($user_prof['emergency_name']) &&
        !empty($user_prof['emergency_phone'])
    );

    if (!$is_profile_complete) {
        $booking_alert = [
            'status'       => 'warning',
            'title'        => 'Profile Incomplete!',
            'message'      => 'You must complete your profile information (Phone, NID, Date of Birth, Blood Group, and Emergency Contacts) before reserving a heritage tour slot.',
            'redirect_url' => 'customer-profile-view.php'
        ];
    } else {
        $slot_stmt = $conn->prepare("SELECT * FROM guide_availability WHERE availability_id = ? AND slot_status = 'Available' LIMIT 1");
        $slot_stmt->bind_param("s", $availability_id);
        $slot_stmt->execute();
        $slot_data = $slot_stmt->get_result()->fetch_assoc();

        if ($slot_data) {
            $booking_id   = "BK-" . date('Y') . "-" . strtoupper(substr(uniqid(), -6));
            $booking_date = $slot_data['available_date'];
            $time_slot    = date("h:i A", strtotime($slot_data['start_time'])) . " - " . date("h:i A", strtotime($slot_data['end_time']));
            $site_name    = !empty($selected_site) ? $selected_site : trim(explode(',', $slot_data['working_sites'])[0]);

            $ins_booking = $conn->prepare("INSERT INTO bookings (booking_id, customer_id, guide_id, availability_id, site_name, booking_date, time_slot, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')");
            $ins_booking->bind_param("sisssss", $booking_id, $customer_id, $guide_id, $availability_id, $site_name, $booking_date, $time_slot);

            if ($ins_booking->execute()) {
                $upd_slot = $conn->prepare("UPDATE guide_availability SET slot_status = 'On Hold' WHERE availability_id = ?");
                $upd_slot->bind_param("s", $availability_id);
                $upd_slot->execute();

                $booking_alert = [
                    'status'       => 'success',
                    'title'        => 'Booking Request Placed!',
                    'message'      => "Your reservation request for " . htmlspecialchars($site_name) . " with Guide #" . htmlspecialchars($guide_id) . " on " . date('D, d M Y', strtotime($booking_date)) . " ($time_slot) has been placed under status <strong>Pending</strong>. Slot is currently on hold awaiting admin confirmation. Reference ID: <strong>" . htmlspecialchars($booking_id) . "</strong>.",
                    'redirect_url' => 'customer-dashboard.php'
                ];
            } else {
                $booking_alert = [
                    'status'       => 'error',
                    'title'        => 'Booking Failed',
                    'message'      => 'An error occurred while creating your reservation. Please try again.',
                    'redirect_url' => null
                ];
            }
        } else {
            $booking_alert = [
                'status'       => 'error',
                'title'        => 'Slot Unavailable',
                'message'      => 'Sorry, this guide schedule is already on hold or booked by another traveler.',
                'redirect_url' => null
            ];
        }
    }
}

$site_escaped = $conn->real_escape_string($selected_site);
$guides_sql = "SELECT g.*, 
                      COALESCE(AVG(r.rating), 0) AS avg_rating, 
                      COUNT(r.id) AS review_count
               FROM guides g
               LEFT JOIN reviews r ON g.guide_id = r.guide_id
               WHERE g.account_status = 'Active' ";

if (!empty($selected_site)) {
    $guides_sql .= " AND g.heritage_sites LIKE '%$site_escaped%' ";
}

$guides_sql .= " GROUP BY g.guide_id ORDER BY g.full_name ASC";
$guides_res = $conn->query($guides_sql);

$guides = [];
if ($guides_res && $guides_res->num_rows > 0) {
    while ($row = $guides_res->fetch_assoc()) {
        $gid   = $row['guide_id'];
        $today = date('Y-m-d');
        
        $avail_sql = "SELECT * FROM guide_availability 
                      WHERE guide_id = '$gid' 
                      AND slot_status = 'Available' 
                      AND available_date >= '$today' "
                      . (!empty($selected_site) ? "AND working_sites LIKE '%$site_escaped%' " : "")
                      . "ORDER BY available_date ASC, start_time ASC";

        $avail_res = $conn->query($avail_sql);
        $slots = [];
        if ($avail_res && $avail_res->num_rows > 0) {
            while ($s = $avail_res->fetch_assoc()) {
                $slots[] = $s;
            }
        }
        $row['slots'] = $slots;
        $guides[] = $row;
    }
}

if (!empty($selected_site) && empty($guides)) {
    header("Location: destination-details.php?name=" . urlencode($selected_site));
    exit;
}

$page_title = !empty($selected_site) ? htmlspecialchars($selected_site) . " Guides" : "Verified Guides";
$extra_css  = "guides.css";
require_once 'header.php';
?>

  <section class="guides-hero">
    <div class="page-container">
      <?php if (!empty($selected_site)): ?>
        <h1>Tour Guides for <?php echo htmlspecialchars($selected_site); ?></h1>
      <?php else: ?>
        <h1>Verified Heritage Tour Guides</h1>
      <?php endif; ?>
    </div>
  </section>

  <main class="guides-wrapper">
    <div class="page-container">
      <?php if ($booking_alert): ?>
        <div class="alert-box <?php echo htmlspecialchars($booking_alert['status']); ?>">
          <div class="alert-icon-col">
            <?php if ($booking_alert['status'] === 'success'): ?>
              <i class="fa-solid fa-circle-check"></i>
            <?php elseif ($booking_alert['status'] === 'warning'): ?>
              <i class="fa-solid fa-triangle-exclamation"></i>
            <?php else: ?>
              <i class="fa-solid fa-circle-xmark"></i>
            <?php endif; ?>
          </div>

          <div class="alert-body-col">
            <h3 class="alert-title"><?php echo htmlspecialchars($booking_alert['title']); ?></h3>
            <p class="alert-text"><?php echo $booking_alert['message']; ?></p>

            <?php if (!empty($booking_alert['redirect_url'])): ?>
              <div class="alert-actions">
                <?php if ($booking_alert['status'] === 'warning'): ?>
                  <a href="<?php echo htmlspecialchars($booking_alert['redirect_url']); ?>" class="btn-alert-action btn-warning-action">
                    <i class="fa-solid fa-user-pen"></i> Complete Profile Now
                  </a>
                <?php elseif ($booking_alert['status'] === 'success'): ?>
                  <a href="<?php echo htmlspecialchars($booking_alert['redirect_url']); ?>" class="btn-alert-action btn-success-action">
                    <i class="fa-solid fa-gauge-high"></i> Go to Dashboard
                  </a>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>

      <div class="guides-search-bar-wrap">
        <div class="guides-search-input-box">
          <i class="fa-solid fa-magnifying-glass search-icon"></i>
          <input 
            type="text" 
            id="guide-text-filter" 
            placeholder="Search guides by Site Name, Language or Guide Name..." 
            autocomplete="off"
          />
        </div>
      </div>

      <?php if (!empty($guides)): ?>
        <div class="guides-stack" id="guides-stack-container">
          <?php foreach ($guides as $guide): 
            $photo        = !empty($guide['profile_photo']) ? $guide['profile_photo'] : 'images/default-avatar.png';
            $languages    = array_filter(array_map('trim', explode(',', $guide['languages'])));
            $sites        = array_filter(array_map('trim', explode(',', $guide['heritage_sites'])));
            $review_count = (int)$guide['review_count'];
            $avg_rating   = round((float)$guide['avg_rating'], 1);

            $sites_blob = strtolower(implode(' ', $sites));
            $langs_blob = strtolower(implode(' ', $languages));
            $name_blob  = strtolower($guide['full_name'] . ' ' . $guide['guide_id']);
          ?>
            <article 
              class="guide-profile-card" 
              data-sites="<?php echo htmlspecialchars($sites_blob); ?>" 
              data-langs="<?php echo htmlspecialchars($langs_blob); ?>" 
              data-name="<?php echo htmlspecialchars($name_blob); ?>"
            >
              <div class="guide-card-top-grid">
                
                <div class="guide-avatar-col">
                  <img src="<?php echo htmlspecialchars($photo); ?>" alt="<?php echo htmlspecialchars($guide['full_name']); ?>" class="guide-avatar-img">
                  <span class="guide-id-pill"><i class="fa-solid fa-id-badge"></i> <?php echo htmlspecialchars($guide['guide_id']); ?></span>
                </div>

                <div class="guide-details-col">
                  <div class="guide-name-row">
                    <div class="guide-name-rating-wrap">
                      <h2>
                        <?php echo htmlspecialchars($guide['full_name']); ?> 
                        <i class="fa-solid fa-circle-check verified-icon" title="Certified Heritage Guide"></i>
                      </h2>

                      <?php if ($review_count > 0): ?>
                        <span class="guide-rating-pill" title="<?php echo $review_count; ?> customer review(s)">
                          <i class="fa-solid fa-star"></i>
                          <strong><?php echo number_format($avg_rating, 1); ?></strong>
                          <span class="rating-count-parens">(<?php echo $review_count; ?>)</span>
                        </span>
                      <?php else: ?>
                        <span class="guide-rating-pill no-rating">
                          <i class="fa-regular fa-star"></i> No rating
                        </span>
                      <?php endif; ?>
                    </div>

                    <div class="rate-badge">
                      <span class="rate-amount">৳<?php echo number_format($guide['rate_amount']); ?></span>
                      <span class="rate-period">/ <?php echo htmlspecialchars($guide['rate_type']); ?></span>
                    </div>
                  </div>

                  <p class="guide-spec-line">
                    <i class="fa-solid fa-graduation-cap"></i> <strong>Specialization:</strong> <?php echo htmlspecialchars($guide['specialization']); ?> 
                    <span class="exp-bullet">&bull;</span> <?php echo (int)$guide['experience_years']; ?> Years Exp.
                  </p>

                  <?php if (!empty($guide['short_bio'])): ?>
                    <p class="guide-bio-text"><?php echo htmlspecialchars($guide['short_bio']); ?></p>
                  <?php endif; ?>

                  <div class="chips-group">
                    <span class="chips-title"><i class="fa-solid fa-language"></i> Languages:</span>
                    <div class="chips-wrap">
                      <?php foreach ($languages as $lang): ?>
                        <span class="chip chip-lang"><?php echo htmlspecialchars($lang); ?></span>
                      <?php endforeach; ?>
                    </div>
                  </div>

                  <div class="chips-group">
                    <span class="chips-title"><i class="fa-solid fa-landmark"></i> Covers:</span>
                    <div class="chips-wrap">
                      <?php foreach ($sites as $site): ?>
                        <span class="chip chip-site <?php echo ($site === $selected_site) ? 'chip-site-highlight' : ''; ?>">
                          <?php echo htmlspecialchars($site); ?>
                        </span>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>

              </div>
            
              <div class="guide-slots-section">
                <div class="slots-header">
                  <h3><i class="fa-regular fa-calendar-check"></i> Available Schedules &amp; Shifts</h3>
                  <span class="slots-counter"><?php echo count($guide['slots']); ?> available date(s)</span>
                </div>

                <?php if (!empty($guide['slots'])): ?>
                  <div class="slots-grid">
                    <?php foreach ($guide['slots'] as $slot): 
                      $formatted_date = date("D, d M Y", strtotime($slot['available_date']));
                      $formatted_time = date("h:i A", strtotime($slot['start_time'])) . " - " . date("h:i A", strtotime($slot['end_time']));
                    ?>
                      <div class="slot-item-box">
                        <div class="slot-info">
                          <span class="slot-date"><i class="fa-regular fa-calendar"></i> <?php echo $formatted_date; ?></span>
                          <span class="slot-time"><i class="fa-regular fa-clock"></i> <?php echo $formatted_time; ?></span>
                          <span class="slot-site"><i class="fa-solid fa-landmark"></i> <?php echo htmlspecialchars($slot['working_sites']); ?></span>
                        </div>

                        <form action="guides.php<?php echo !empty($selected_site) ? '?site=' . urlencode($selected_site) : ''; ?>" method="POST" class="book-form">
                          <input type="hidden" name="action" value="book_slot">
                          <input type="hidden" name="guide_id" value="<?php echo htmlspecialchars($guide['guide_id']); ?>">
                          <input type="hidden" name="availability_id" value="<?php echo htmlspecialchars($slot['availability_id']); ?>">

                          <button type="submit" class="btn-make-booking">
                            <i class="fa-solid fa-calendar-plus"></i> Make Booking
                          </button>
                        </form>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php else: ?>
                  <div class="no-slots-box">
                    
                    <p>No open availability slots currently listed for this guide. Please check back soon or review other experts.</p>
                  </div>
                <?php endif; ?>
              </div>

            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div id="no-guides-filter-match" class="no-guides-found" style="display: none;">
    
        <h2>No Matching Tour Guides Found</h2>
        <p>No verified guides match your selected search keyword. Please try another heritage site, language, or guide name.</p>
      </div>

    </div>
  </main>

  <script src="guides.js"></script>

<?php require_once 'footer.php'; ?>