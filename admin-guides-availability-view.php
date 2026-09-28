<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin-login.php");
    exit;
}


$sql = "SELECT 
            ga.id,
            ga.availability_id,
            ga.guide_id,
            ga.available_date,
            ga.start_time,
            ga.end_time,
            ga.working_district,
            ga.working_sites,
            ga.max_tourists,
            ga.slot_status,
            ga.notes,
            g.full_name,
            g.profile_photo,
            g.phone
        FROM guide_availability ga
        LEFT JOIN guides g ON ga.guide_id = g.guide_id
        ORDER BY ga.available_date ASC, ga.start_time ASC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>View Guide Availability - HeritageLink Admin</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <link rel="stylesheet" href="index.css">
  <link rel="stylesheet" href="admin-dashboard.css">
  <link rel="stylesheet" href="admin-guides-availability-view.css">
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


  <main class="view-availability-wrapper">
    <div class="page-container-wide">

      <div class="view-header-box">
        <h1 class="view-page-title">Guide Availability Schedule</h1>
       
      </div>


      <div class="search-filter-card">
        <div class="search-input-wrap">
          <i class="fa-solid fa-magnifying-glass search-icon"></i>
          <input 
            type="text" 
            id="live-availability-search" 
            placeholder="Search by Guide Name, ID, Location, District, Date (YYYY-MM-DD)..." 
            autocomplete="off"
          />
        </div>
      </div>

   
      <div class="table-container-card">
        <div class="table-responsive">
          <table class="availability-table" id="availability-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Time Slot</th>
                <th>Guide Info</th>
                <th>Working Location</th>
                <th>Working Sites</th>
                <th>Capacity</th>
                <th>Slot Status</th>
                <th>Availability ID</th>
              </tr>
            </thead>
            <tbody id="availability-table-body">
              <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): 
                  $formatted_date = date("M d, Y", strtotime($row['available_date']));
                  $day_of_week   = date("l", strtotime($row['available_date']));
                  $formatted_start = date("h:i A", strtotime($row['start_time']));
                  $formatted_end   = date("h:i A", strtotime($row['end_time']));
                  $guide_display_name = !empty($row['full_name']) ? $row['full_name'] : 'Unassigned / Deleted';
                  $photo_path = !empty($row['profile_photo']) ? $row['profile_photo'] : 'images/default-avatar.png';
                ?>
                  <tr 
                    class="availability-data-row"
                    data-id="<?php echo htmlspecialchars($row['availability_id']); ?>"
                    data-guide-id="<?php echo htmlspecialchars($row['guide_id']); ?>"
                    data-guide-name="<?php echo htmlspecialchars($guide_display_name); ?>"
                    data-date-raw="<?php echo htmlspecialchars($row['available_date']); ?>"
                    data-date-formatted="<?php echo htmlspecialchars($formatted_date . " " . $day_of_week); ?>"
                    data-time="<?php echo htmlspecialchars($formatted_start . " " . $formatted_end . " " . $row['start_time'] . " " . $row['end_time']); ?>"
                    data-district="<?php echo htmlspecialchars($row['working_district']); ?>"
                    data-sites="<?php echo htmlspecialchars($row['working_sites']); ?>"
                    data-status="<?php echo htmlspecialchars($row['slot_status']); ?>"
                  >
                 
                    <td>
                      <div class="date-badge">
                        <span class="date-main"><?php echo htmlspecialchars($formatted_date); ?></span>
                        <span class="date-sub"><?php echo htmlspecialchars($day_of_week); ?></span>
                      </div>
                    </td>

               
                    <td>
                      <div class="time-slot-box">
                        <i class="fa-regular fa-clock time-icon"></i>
                        <span><?php echo htmlspecialchars($formatted_start . " - " . $formatted_end); ?></span>
                      </div>
                    </td>

             
                    <td>
                      <div class="guide-profile-cell">
                        <img 
                          src="<?php echo htmlspecialchars($photo_path); ?>" 
                          alt="<?php echo htmlspecialchars($guide_display_name); ?>" 
                          class="guide-thumb" 
                        />
                        <div class="guide-text-wrap">
                          <strong><?php echo htmlspecialchars($guide_display_name); ?></strong>
                          <span class="guide-id-tag"><?php echo htmlspecialchars($row['guide_id']); ?></span>
                        </div>
                      </div>
                    </td>

           
                    <td>
                      <div class="location-box">
                        <i class="fa-solid fa-location-dot location-icon"></i>
                        <strong><?php echo htmlspecialchars($row['working_district']); ?></strong>
                      </div>
                    </td>

         
                    <td>
                      <div class="badges-wrap">
                        <?php 
                          $sites = array_map('trim', explode(',', $row['working_sites']));
                          foreach ($sites as $site):
                            if (!empty($site)):
                        ?>
                          <span class="badge site-badge"><?php echo htmlspecialchars($site); ?></span>
                        <?php 
                            endif;
                          endforeach; 
                        ?>
                      </div>
                    </td>

                
                    <td>
                      <span class="capacity-pill">
                        <i class="fa-solid fa-users"></i> <?php echo (int)$row['max_tourists']; ?> Max
                      </span>
                    </td>

               
                    <td>
                      <?php
                        $status_class = 'status-available';
                        $status_lower = strtolower($row['slot_status']);
                        if ($status_lower === 'booked') {
                            $status_class = 'status-booked';
                        } elseif ($status_lower === 'on hold') {
                            $status_class = 'status-on-hold';
                        } elseif ($status_lower === 'unavailable') {
                            $status_class = 'status-unavailable';
                        }
                      ?>
                      <span class="status-pill <?php echo $status_class; ?>">
                        <?php echo htmlspecialchars($row['slot_status']); ?>
                      </span>
                    </td>

                    
                    <td>
                      <span class="avail-id-code"><?php echo htmlspecialchars($row['availability_id']); ?></span>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr id="empty-db-row">
                  <td colspan="8" class="no-data-cell">
                    <i class="fa-solid fa-calendar-xmark empty-icon"></i>
                    <p>No availability schedules found in the database. Add available dates to get started.</p>
                  </td>
                </tr>
              <?php endif; ?>

              <tr id="no-match-row" style="display: none;">
                <td colspan="8" class="no-data-cell">
                  <i class="fa-solid fa-magnifying-glass empty-icon"></i>
                  <p>No availability entries match your search query.</p>
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

  <script src="admin-guides-availability-view.js"></script>
</body>
</html>