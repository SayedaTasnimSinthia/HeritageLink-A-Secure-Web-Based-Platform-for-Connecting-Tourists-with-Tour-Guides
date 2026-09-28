<?php
session_start();
require_once 'db.php';


if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin-login.php");
    exit;
}

$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_availability_id'])) {
    $target_avail_id = trim($_POST['delete_availability_id']);

    if (!empty($target_avail_id)) {
        $clean_target_id = $conn->real_escape_string($target_avail_id);

        $delete_sql = "DELETE FROM guide_availability WHERE availability_id = '$clean_target_id' LIMIT 1";
        if ($conn->query($delete_sql)) {
            $success_message = "Availability slot (" . htmlspecialchars($target_avail_id) . ") has been removed successfully.";
        } else {
            $error_message = "Database error: Unable to delete availability slot. " . $conn->error;
        }
    }
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
            g.full_name,
            g.profile_photo
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
  <title>Delete Shift / Slot - HeritageLink Admin</title>

 
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">


  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

 
  <link rel="stylesheet" href="index.css">
  <link rel="stylesheet" href="admin-dashboard.css">
  <link rel="stylesheet" href="admin-guides-availability-delete.css">
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


  <main class="delete-shifts-wrapper">
    <div class="page-container-wide">

      <div class="delete-header-box">
        <h1 class="delete-page-title">Delete Availability Slots</h1>
      
      </div>


      <?php if (!empty($error_message)): ?>
        <div class="alert-box error"><?php echo htmlspecialchars($error_message); ?></div>
      <?php elseif (!empty($success_message)): ?>
        <div class="alert-box success"><?php echo htmlspecialchars($success_message); ?></div>
      <?php endif; ?>

     
      <div class="search-filter-card">
        <div class="search-input-wrap">
          <i class="fa-solid fa-magnifying-glass search-icon"></i>
          <input 
            type="text" 
            id="live-delete-search" 
            placeholder="Search schedules by date (YYYY-MM-DD), time, guide, location, district..." 
            autocomplete="off"
          />
        </div>
      </div>


      <div class="table-container-card">
        <div class="table-responsive">
          <table class="shifts-table" id="shifts-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Time Slot</th>
                <th>Guide (Name & ID)</th>
                <th>Location / District</th>
                <th>Sites Covered</th>
                <th>Slot Status</th>
                <th>Slot ID</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody id="shifts-table-body">
              <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): 
                  $formatted_date = date("M d, Y", strtotime($row['available_date']));
                  $day_of_week   = date("l", strtotime($row['available_date']));
                  $short_year    = date("y", strtotime($row['available_date']));
                  $full_year     = date("Y", strtotime($row['available_date']));
                  $month_num     = date("m", strtotime($row['available_date']));
                  $day_num       = date("d", strtotime($row['available_date']));
                  $formatted_start = date("h:i A", strtotime($row['start_time']));
                  $formatted_end   = date("h:i A", strtotime($row['end_time']));
                  $guide_name    = !empty($row['full_name']) ? $row['full_name'] : 'Unassigned / Deleted';
                  $photo_path    = !empty($row['profile_photo']) ? $row['profile_photo'] : 'images/default-avatar.png';

                  $status_class = 'status-available';
                  $status_lower = strtolower($row['slot_status']);
                  if ($status_lower === 'booked') $status_class = 'status-booked';
                  elseif ($status_lower === 'on hold') $status_class = 'status-on-hold';
                  elseif ($status_lower === 'unavailable') $status_class = 'status-unavailable';
                ?>
                  <tr 
                    class="shift-data-row"
                    data-id="<?php echo htmlspecialchars($row['availability_id']); ?>"
                    data-guide-id="<?php echo htmlspecialchars($row['guide_id']); ?>"
                    data-guide-name="<?php echo htmlspecialchars($guide_name); ?>"
                    data-date-raw="<?php echo htmlspecialchars($row['available_date']); ?>"
                    data-date-parts="<?php echo htmlspecialchars("$full_year $short_year $month_num $day_num $formatted_date $day_of_week"); ?>"
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
                      <div class="time-box">
                        <i class="fa-regular fa-clock time-icon"></i>
                        <span><?php echo htmlspecialchars($formatted_start . " - " . $formatted_end); ?></span>
                      </div>
                    </td>

           
                    <td>
                      <div class="guide-profile-cell">
                        <img src="<?php echo htmlspecialchars($photo_path); ?>" alt="<?php echo htmlspecialchars($guide_name); ?>" class="guide-thumb" />
                        <div class="guide-text-wrap">
                          <strong><?php echo htmlspecialchars($guide_name); ?></strong>
                          <span class="guide-id-tag"><?php echo htmlspecialchars($row['guide_id']); ?></span>
                        </div>
                      </div>
                    </td>

          
                    <td>
                      <i class="fa-solid fa-location-dot location-icon"></i>
                      <strong><?php echo htmlspecialchars($row['working_district']); ?></strong>
                    </td>

                    
                    <td>
                      <div class="badges-wrap">
                        <?php 
                          $sites = array_map('trim', explode(',', $row['working_sites']));
                          foreach ($sites as $s):
                            if (!empty($s)):
                        ?>
                          <span class="badge site-badge"><?php echo htmlspecialchars($s); ?></span>
                        <?php 
                            endif;
                          endforeach; 
                        ?>
                      </div>
                    </td>

                  
                    <td>
                      <span class="status-pill <?php echo $status_class; ?>">
                        <?php echo htmlspecialchars($row['slot_status']); ?>
                      </span>
                    </td>

                
                    <td>
                      <span class="avail-id-code"><?php echo htmlspecialchars($row['availability_id']); ?></span>
                    </td>

           
                    <td class="text-center">
                      <form method="POST" action="admin-guides-availability-delete.php" class="delete-form" onsubmit="return confirmDeleteSlot('<?php echo htmlspecialchars($row['availability_id']); ?>', '<?php echo htmlspecialchars(addslashes($guide_name)); ?>', '<?php echo htmlspecialchars($formatted_date); ?>');">
                        <input type="hidden" name="delete_availability_id" value="<?php echo htmlspecialchars($row['availability_id']); ?>">
                        <button type="submit" class="btn-delete" title="Delete Availability Slot">
                          <i class="fa-solid fa-trash-can"></i> Delete
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr id="empty-db-row">
                  <td colspan="8" class="no-data-cell">
                    <i class="fa-solid fa-calendar-xmark empty-icon"></i>
                    <p>No availability schedules found in the database.</p>
                  </td>
                </tr>
              <?php endif; ?>
\
              <tr id="no-match-row" style="display: none;">
                <td colspan="8" class="no-data-cell">
                  <i class="fa-solid fa-magnifying-glass empty-icon"></i>
                  <p>No shifts match your search criteria.</p>
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

  <script src="admin-guides-availability-delete.js"></script>
</body>
</html>