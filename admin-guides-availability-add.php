<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin-login.php");
    exit;
}

$error_message = "";
$success_message = "";


$auto_avail_id = "AVL-" . date("Y") . "-" . rand(1000, 9999);


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $availability_id  = isset($_POST['availability_id']) ? trim($_POST['availability_id']) : '';
    $guide_id         = isset($_POST['guide_id']) ? trim($_POST['guide_id']) : '';
    $available_date   = isset($_POST['available_date']) ? trim($_POST['available_date']) : '';
    $start_time       = isset($_POST['start_time']) ? trim($_POST['start_time']) : '';
    $end_time         = isset($_POST['end_time']) ? trim($_POST['end_time']) : '';
    $working_district = isset($_POST['working_district']) ? trim($_POST['working_district']) : '';
    $max_tourists     = isset($_POST['max_tourists']) ? (int)$_POST['max_tourists'] : 10;
    $slot_status      = isset($_POST['slot_status']) ? trim($_POST['slot_status']) : 'Available';
    $notes            = isset($_POST['notes']) ? trim($_POST['notes']) : '';

    $sites_array      = isset($_POST['working_sites']) && is_array($_POST['working_sites']) ? $_POST['working_sites'] : [];
    $working_sites    = implode(", ", $sites_array);

    if (empty($availability_id) || empty($guide_id) || empty($available_date) || empty($start_time) || empty($end_time) || empty($working_district) || empty($working_sites)) {
        $error_message = "Please fill in all required fields and select at least one working site.";
    } elseif ($start_time >= $end_time) {
        $error_message = "End time must be later than start time.";
    } else {
        $clean_avail_id  = $conn->real_escape_string($availability_id);
        $clean_guide_id  = $conn->real_escape_string($guide_id);
        $clean_date      = $conn->real_escape_string($available_date);
        $clean_start     = $conn->real_escape_string($start_time);
        $clean_end       = $conn->real_escape_string($end_time);
        $clean_district  = $conn->real_escape_string($working_district);
        $clean_sites     = $conn->real_escape_string($working_sites);
        $clean_status    = $conn->real_escape_string($slot_status);
        $clean_notes     = $conn->real_escape_string($notes);

        $overlap_check = "SELECT id FROM guide_availability WHERE guide_id = '$clean_guide_id' AND available_date = '$clean_date' AND (start_time < '$clean_end' AND end_time > '$clean_start') LIMIT 1";
        $overlap_res = $conn->query($overlap_check);

        if ($overlap_res && $overlap_res->num_rows > 0) {
            $error_message = "This guide already has an assigned or overlapping availability slot on this date.";
        } else {
            $insert_sql = "INSERT INTO guide_availability 
                (availability_id, guide_id, available_date, start_time, end_time, working_district, working_sites, max_tourists, slot_status, notes) 
                VALUES 
                ('$clean_avail_id', '$clean_guide_id', '$clean_date', '$clean_start', '$clean_end', '$clean_district', '$clean_sites', $max_tourists, '$clean_status', '$clean_notes')";

            if ($conn->query($insert_sql)) {
                $success_message = "Guide availability slot successfully registered!";
                $auto_avail_id = "AVL-" . date("Y") . "-" . rand(1000, 9999);
            } else {
                $error_message = "Database error: Could not save availability. " . $conn->error;
            }
        }
    }
}

$guides_sql = "SELECT guide_id, full_name, heritage_sites FROM guides ORDER BY full_name ASC";
$guides_res = $conn->query($guides_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Add Guide Availability - HeritageLink Admin</title>

 
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">


  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


  <link rel="stylesheet" href="index.css">
  <link rel="stylesheet" href="admin-dashboard.css">
  <link rel="stylesheet" href="admin-guides-availability-add.css">
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


  <main class="availability-add-wrapper">
    <div class="page-container">

  
      <div class="form-header-box">
        <h1 class="form-page-title">Add Available Dates</h1>
      </div>

   
      <div class="form-main-card">
        
 
        <?php if (!empty($error_message)): ?>
          <div class="alert-box error" id="alert-box"><?php echo htmlspecialchars($error_message); ?></div>
        <?php elseif (!empty($success_message)): ?>
          <div class="alert-box success" id="alert-box"><?php echo htmlspecialchars($success_message); ?></div>
        <?php else: ?>
          <div class="alert-box error" id="alert-box" style="display: none;"></div>
        <?php endif; ?>

        <form id="add-availability-form" action="admin-guides-availability-add.php" method="POST" class="guide-vertical-form">
          

          <div class="form-field">
            <label for="availability_id">
              <span>Availability ID</span>
              <span class="badge-autogen">Auto-Generated</span>
            </label>
            <input type="text" id="availability_id" name="availability_id" value="<?php echo htmlspecialchars($auto_avail_id); ?>" readonly class="readonly-input" />
          </div>

          <div class="form-field">
            <label for="guide_search_input">
              <span>Quick Search Guide</span>
            </label>
            <div class="search-guide-box">
              <i class="fa-solid fa-magnifying-glass search-guide-icon"></i>
              <input type="text" id="guide_search_input" placeholder="Type Guide ID or Name..." autocomplete="off" />
            </div>
          </div>

  
          <div class="form-field">
            <label for="guide_id">
              <span>Guide (Name / ID)</span>
              <span class="req">*</span>
            </label>
            <select id="guide_id" name="guide_id" required>
              <option value="">-- Select Registered Guide --</option>
              <?php if ($guides_res && $guides_res->num_rows > 0): ?>
                <?php while ($g = $guides_res->fetch_assoc()): ?>
                  <option 
                    value="<?php echo htmlspecialchars($g['guide_id']); ?>" 
                    data-guide-id="<?php echo htmlspecialchars(strtolower($g['guide_id'])); ?>" 
                    data-guide-name="<?php echo htmlspecialchars(strtolower($g['full_name'])); ?>"
                    data-heritage-sites="<?php echo htmlspecialchars($g['heritage_sites']); ?>"
                  >
                    <?php echo htmlspecialchars($g['full_name']) . " (" . htmlspecialchars($g['guide_id']) . ")"; ?>
                  </option>
                <?php endwhile; ?>
              <?php else: ?>
                <option value="" disabled>No guides available. Please add guides first.</option>
              <?php endif; ?>
            </select>
          </div>


          <div class="form-field">
            <label for="available_date">
              <span>Available Date</span>
              <span class="req">*</span>
            </label>
            <input type="date" id="available_date" name="available_date" min="<?php echo date('Y-m-d'); ?>" required />
          </div>

          <div class="form-field">
            <label for="start_time">
              <span>Start Time</span>
              <span class="req">*</span>
            </label>
            <input type="time" id="start_time" name="start_time" value="09:00" required />
          </div>

      
          <div class="form-field">
            <label for="end_time">
              <span>End Time</span>
              <span class="req">*</span>
            </label>
            <input type="time" id="end_time" name="end_time" value="17:00" required />
          </div>

         
          <div class="form-field">
            <label for="working_district">
              <span>Working District</span>
              <span class="req">*</span>
            </label>
            <input type="text" id="working_district" name="working_district" placeholder="e.g. Dhaka, Bogra, Bagerhat, Sylhet, Naogaon" required />
          </div>

          
          <div class="form-field">
            <label>
              <span>Working Sites On That Day</span>
              <span class="req">*</span>
            </label>
            <div class="vertical-checkbox-container" id="working-sites-container">
              <p class="site-placeholder-text">Please select a guide above to view their assigned heritage sites.</p>
            </div>
          </div>

      
          <div class="form-field">
            <label for="max_tourists">
              <span>Maximum Tourists Capacity</span>
              <span class="req">*</span>
            </label>
            <input type="number" id="max_tourists" name="max_tourists" min="1" max="100" value="10" placeholder="e.g. 10" required />
          </div>

    
          <div class="form-field">
            <label for="slot_status">
              <span>Slot Status</span>
              <span class="req">*</span>
            </label>
            <select id="slot_status" name="slot_status" required>
              <option value="Available">Available</option>
              <option value="Booked">Booked</option>
              <option value="On Hold">On Hold</option>
              <option value="Unavailable">Unavailable</option>
            </select>
          </div>

     
          <div class="form-field">
            <label for="notes">
              <span>Notes / Special Instructions (Optional)</span>
            </label>
            <textarea id="notes" name="notes" rows="4"></textarea>
          </div>

      
          <div class="form-actions-row">
            <a href="admin-dashboard.php" class="btn-cancel">Cancel</a>
            <button type="submit" class="btn-save-availability"><i class="fa-solid fa-calendar-plus"></i> Save Availability</button>
          </div>

        </form>
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

  <script src="admin-guides-availability-add.js"></script>
</body>
</html>