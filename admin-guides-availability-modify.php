<?php
session_start();
require_once 'db.php';


if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin-login.php");
    exit;
}

$error_message = "";
$success_message = "";
$edit_data = null;
$selected_id = isset($_GET['edit_id']) ? trim($_GET['edit_id']) : '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_availability'])) {
    $availability_id  = isset($_POST['availability_id']) ? trim($_POST['availability_id']) : '';
    $available_date   = isset($_POST['available_date']) ? trim($_POST['available_date']) : '';
    $start_time       = isset($_POST['start_time']) ? trim($_POST['start_time']) : '';
    $end_time         = isset($_POST['end_time']) ? trim($_POST['end_time']) : '';
    $working_district = isset($_POST['working_district']) ? trim($_POST['working_district']) : '';
    $max_tourists     = isset($_POST['max_tourists']) ? (int)$_POST['max_tourists'] : 10;
    $slot_status      = isset($_POST['slot_status']) ? trim($_POST['slot_status']) : 'Available';
    $notes            = isset($_POST['notes']) ? trim($_POST['notes']) : '';

    $sites_array      = isset($_POST['working_sites']) && is_array($_POST['working_sites']) ? $_POST['working_sites'] : [];
    $working_sites    = implode(", ", $sites_array);

    if (empty($availability_id) || empty($available_date) || empty($start_time) || empty($end_time) || empty($working_district) || empty($working_sites)) {
        $error_message = "Please fill in all required fields and select at least one working site.";
    } elseif ($start_time >= $end_time) {
        $error_message = "End time must be later than start time.";
    } else {
        $clean_avail_id  = $conn->real_escape_string($availability_id);
        $clean_date      = $conn->real_escape_string($available_date);
        $clean_start     = $conn->real_escape_string($start_time);
        $clean_end       = $conn->real_escape_string($end_time);
        $clean_district  = $conn->real_escape_string($working_district);
        $clean_sites     = $conn->real_escape_string($working_sites);
        $clean_status    = $conn->real_escape_string($slot_status);
        $clean_notes     = $conn->real_escape_string($notes);

       
        $g_query = "SELECT guide_id FROM guide_availability WHERE availability_id = '$clean_avail_id' LIMIT 1";
        $g_res = $conn->query($g_query);
        $g_row = $g_res ? $g_res->fetch_assoc() : null;
        $guide_id = $g_row ? $g_row['guide_id'] : '';

  
        $overlap_check = "SELECT id FROM guide_availability 
                          WHERE guide_id = '$guide_id' 
                          AND available_date = '$clean_date' 
                          AND availability_id != '$clean_avail_id'
                          AND (start_time < '$clean_end' AND end_time > '$clean_start') LIMIT 1";
        $overlap_res = $conn->query($overlap_check);

        if ($overlap_res && $overlap_res->num_rows > 0) {
            $error_message = "Conflict: This guide already has another shift overlapping this date and time range.";
        } else {
            $update_sql = "UPDATE guide_availability SET 
                available_date = '$clean_date',
                start_time = '$clean_start',
                end_time = '$clean_end',
                working_district = '$clean_district',
                working_sites = '$clean_sites',
                max_tourists = $max_tourists,
                slot_status = '$clean_status',
                notes = '$clean_notes'
                WHERE availability_id = '$clean_avail_id'";

            if ($conn->query($update_sql)) {
                $success_message = "Shift schedule ($clean_avail_id) updated successfully!";
                $selected_id = $clean_avail_id;
            } else {
                $error_message = "Database error: Could not update schedule. " . $conn->error;
            }
        }
    }
}


if (!empty($selected_id)) {
    $clean_edit_id = $conn->real_escape_string($selected_id);
    $edit_query = "SELECT ga.*, g.full_name 
                   FROM guide_availability ga 
                   LEFT JOIN guides g ON ga.guide_id = g.guide_id 
                   WHERE ga.availability_id = '$clean_edit_id' LIMIT 1";
    $edit_res = $conn->query($edit_query);
    if ($edit_res && $edit_res->num_rows > 0) {
        $edit_data = $edit_res->fetch_assoc();
    }
}

$all_sql = "SELECT ga.*, g.full_name 
            FROM guide_availability ga 
            LEFT JOIN guides g ON ga.guide_id = g.guide_id 
            ORDER BY ga.available_date ASC, ga.start_time ASC";
$all_res = $conn->query($all_sql);

$current_sites = !empty($edit_data['working_sites']) ? array_map('trim', explode(',', $edit_data['working_sites'])) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Modify Shifts & Schedules - HeritageLink Admin</title>


  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">


  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


  <link rel="stylesheet" href="index.css">
  <link rel="stylesheet" href="admin-dashboard.css">
  <link rel="stylesheet" href="admin-guides-availability-modify.css">
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

  
  <main class="modify-shifts-wrapper">
    <div class="page-container-wide">

      
      <div class="view-header-box">
        <h1 class="view-page-title">Modify Shift Schedules</h1>
      </div>


      <?php if (!empty($error_message)): ?>
        <div class="alert-box error"><?php echo htmlspecialchars($error_message); ?></div>
      <?php elseif (!empty($success_message)): ?>
        <div class="alert-box success"><?php echo htmlspecialchars($success_message); ?></div>
      <?php endif; ?>

    
      <?php if ($edit_data): ?>
        <div class="form-main-card" id="edit-shift-container">
          <div class="edit-card-header">
            <h2>Edit Shift: <span class="highlight-id"><?php echo htmlspecialchars($edit_data['availability_id']); ?></span></h2>
            <a href="admin-guides-availability-modify.php" class="btn-close-edit"><i class="fa-solid fa-xmark"></i> Close Edit Form</a>
          </div>

          <form action="admin-guides-availability-modify.php?edit_id=<?php echo urlencode($edit_data['availability_id']); ?>" method="POST" class="guide-vertical-form">
            <input type="hidden" name="update_availability" value="1" />
            <input type="hidden" name="availability_id" value="<?php echo htmlspecialchars($edit_data['availability_id']); ?>" />

         
            <div class="form-field">
              <label>Assigned Guide <span class="badge-locked"><i class="fa-solid fa-lock"></i> Fixed</span></label>
              <input type="text" value="<?php echo htmlspecialchars(($edit_data['full_name'] ? $edit_data['full_name'] : 'Deleted Guide') . ' (' . $edit_data['guide_id'] . ')'); ?>" readonly class="readonly-input" />
            </div>

          
            <div class="form-field">
              <label for="available_date">Available Date <span class="req">*</span></label>
              <input type="date" id="available_date" name="available_date" value="<?php echo htmlspecialchars($edit_data['available_date']); ?>" required />
            </div>

     
            <div class="form-field">
              <label for="start_time">Start Time <span class="req">*</span></label>
              <input type="time" id="start_time" name="start_time" value="<?php echo htmlspecialchars($edit_data['start_time']); ?>" required />
            </div>

          
            <div class="form-field">
              <label for="end_time">End Time <span class="req">*</span></label>
              <input type="time" id="end_time" name="end_time" value="<?php echo htmlspecialchars($edit_data['end_time']); ?>" required />
            </div>

            <div class="form-field">
              <label for="working_district">Working District <span class="req">*</span></label>
              <input type="text" id="working_district" name="working_district" value="<?php echo htmlspecialchars($edit_data['working_district']); ?>" required />
            </div>

            <div class="form-field">
              <label>Working Sites On That Day <span class="req">*</span></label>
              <div class="vertical-checkbox-container">
                <?php
                  $all_sites = [
                    'Lalbagh Fort',
                    'Ahsan Manzil',
                    'Panam City',
                    'Somapura Mahavihara (Paharpur)',
                    'Mahasthangarh',
                    'Sixty Dome Mosque',
                    'Foy\'s Lake',
                    'Jaflong',
                    'Shah Jalal\'s Shrine',
                    'Dhakeshwari National Temple',
                    'Puthia Temple Complex, Rajshahi'
                  ];
                  foreach ($all_sites as $site):
                    $checked = in_array($site, $current_sites) ? 'checked' : '';
                ?>
                  <label class="vertical-checkbox-item">
                    <input type="checkbox" name="working_sites[]" value="<?php echo htmlspecialchars($site); ?>" <?php echo $checked; ?>> <?php echo htmlspecialchars($site); ?>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>

         
            <div class="form-field">
              <label for="max_tourists">Maximum Tourists Capacity <span class="req">*</span></label>
              <input type="number" id="max_tourists" name="max_tourists" min="1" max="100" value="<?php echo (int)$edit_data['max_tourists']; ?>" required />
            </div>

   
            <div class="form-field">
              <label for="slot_status">Slot Status <span class="req">*</span></label>
              <select id="slot_status" name="slot_status" required>
                <option value="Available" <?php echo ($edit_data['slot_status'] === 'Available') ? 'selected' : ''; ?>>Available</option>
                <option value="Booked" <?php echo ($edit_data['slot_status'] === 'Booked') ? 'selected' : ''; ?>>Booked</option>
                <option value="On Hold" <?php echo ($edit_data['slot_status'] === 'On Hold') ? 'selected' : ''; ?>>On Hold</option>
                <option value="Unavailable" <?php echo ($edit_data['slot_status'] === 'Unavailable') ? 'selected' : ''; ?>>Unavailable</option>
              </select>
            </div>


            <div class="form-field">
              <label for="notes">Notes / Special Instructions (Optional)</label>
              <textarea id="notes" name="notes" rows="4"><?php echo htmlspecialchars($edit_data['notes']); ?></textarea>
            </div>


            <div class="form-actions-row">
              <a href="admin-guides-availability-modify.php" class="btn-cancel">Cancel</a>
              <button type="submit" class="btn-save-shift"><i class="fa-solid fa-floppy-disk"></i> Save Shift Changes</button>
            </div>
          </form>
        </div>
      <?php endif; ?>

  
      <div class="search-filter-card">
        <div class="search-input-wrap">
          <i class="fa-solid fa-magnifying-glass search-icon"></i>
          <input 
            type="text" 
            id="live-modify-search" 
            placeholder="Search schedules by date (YYYY-MM-DD), time, guide, site location, district..." 
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
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody id="shifts-table-body">
              <?php if ($all_res && $all_res->num_rows > 0): ?>
                <?php while ($row = $all_res->fetch_assoc()): 
                  $formatted_date = date("M d, Y", strtotime($row['available_date']));
                  $day_of_week   = date("l", strtotime($row['available_date']));
                  $short_year    = date("y", strtotime($row['available_date'])); // e.g. 26
                  $full_year     = date("Y", strtotime($row['available_date'])); // e.g. 2026
                  $month_num     = date("m", strtotime($row['available_date'])); // e.g. 09
                  $day_num       = date("d", strtotime($row['available_date'])); // e.g. 11
                  $formatted_start = date("h:i A", strtotime($row['start_time']));
                  $formatted_end   = date("h:i A", strtotime($row['end_time']));
                  $guide_name = !empty($row['full_name']) ? $row['full_name'] : 'Deleted Guide';

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
                      <strong><?php echo htmlspecialchars($guide_name); ?></strong><br>
                      <span class="guide-id-tag"><?php echo htmlspecialchars($row['guide_id']); ?></span>
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

                  
                    <td class="text-center">
                      <a href="admin-guides-availability-modify.php?edit_id=<?php echo urlencode($row['availability_id']); ?>" class="btn-edit-action">
                        <i class="fa-solid fa-pen-to-square"></i> Edit Shift
                      </a>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr id="empty-db-row">
                  <td colspan="7" class="no-data-cell">
                    <i class="fa-solid fa-calendar-xmark empty-icon"></i>
                    <p>No availability entries found in the database.</p>
                  </td>
                </tr>
              <?php endif; ?>

          
              <tr id="no-match-row" style="display: none;">
                <td colspan="7" class="no-data-cell">
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

  <script src="admin-guides-availability-modify.js"></script>
</body>
</html>