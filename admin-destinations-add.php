<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin-login.php");
    exit;
}

$error_message = "";
$success_message = "";


$auto_dest_id = "DST-" . date("Y") . "-" . rand(1000, 9999);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $destination_id    = isset($_POST['destination_id']) ? trim($_POST['destination_id']) : '';
    $site_name         = isset($_POST['site_name']) ? trim($_POST['site_name']) : '';
    $division          = isset($_POST['division']) ? trim($_POST['division']) : '';
    $district          = isset($_POST['district']) ? trim($_POST['district']) : '';
    $location          = isset($_POST['location']) ? trim($_POST['location']) : '';
    $heritage_type     = isset($_POST['heritage_type']) ? trim($_POST['heritage_type']) : '';
    $historical_period = isset($_POST['historical_period']) ? trim($_POST['historical_period']) : '';
    $built_year        = isset($_POST['built_year']) ? trim($_POST['built_year']) : '';
    $opening_hours     = isset($_POST['opening_hours']) ? trim($_POST['opening_hours']) : '';
    $entry_fee         = isset($_POST['entry_fee']) ? trim($_POST['entry_fee']) : '';
    $status            = isset($_POST['status']) ? trim($_POST['status']) : 'Active';
    $description       = isset($_POST['description']) ? trim($_POST['description']) : '';

    if (empty($destination_id) || empty($site_name) || empty($division) || empty($district) || empty($location) || empty($heritage_type) || empty($historical_period) || empty($opening_hours) || empty($entry_fee) || empty($description)) {
        $error_message = "Please fill in all required fields.";
    } else {
        $clean_dest_id   = $conn->real_escape_string($destination_id);
        $clean_site_name = $conn->real_escape_string($site_name);


        $check_sql = "SELECT destination_id FROM destinations WHERE destination_id = '$clean_dest_id' OR site_name = '$clean_site_name' LIMIT 1";
        $check_res = $conn->query($check_sql);

        if ($check_res && $check_res->num_rows > 0) {
            $error_message = "A destination with this ID or Site Name already exists.";
        } else {
         
            $clean_district = $conn->real_escape_string($district);
            $clean_loc      = $conn->real_escape_string($location);
            
            $guide_check_sql = "SELECT id FROM guide_availability 
                                WHERE slot_status = 'Available' 
                                AND (working_sites LIKE '%$clean_site_name%' OR working_district LIKE '%$clean_district%' OR working_district LIKE '%$clean_loc%') 
                                LIMIT 1";
            $guide_check_res = $conn->query($guide_check_sql);
            $guide_available = ($guide_check_res && $guide_check_res->num_rows > 0) ? 'Yes' : 'No';

       
            $image_path = "images/default-destination.jpg";
            if (isset($_FILES['destination_image']) && $_FILES['destination_image']['error'] === UPLOAD_ERR_OK) {
                $file_tmp   = $_FILES['destination_image']['tmp_name'];
                $file_name  = basename($_FILES['destination_image']['name']);
                $file_ext   = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed    = ['jpg', 'jpeg', 'png', 'webp'];

                if (in_array($file_ext, $allowed)) {
                    if (!is_dir('images')) {
                        mkdir('images', 0777, true);
                    }
                    $new_file_name = "dest_" . time() . "_" . rand(100, 999) . "." . $file_ext;
                    $target_path   = "images/" . $new_file_name;
                    if (move_uploaded_file($file_tmp, $target_path)) {
                        $image_path = $target_path;
                    }
                }
            }

    
            $clean_division    = $conn->real_escape_string($division);
            $clean_heritage    = $conn->real_escape_string($heritage_type);
            $clean_period      = $conn->real_escape_string($historical_period);
            $clean_built_year  = $conn->real_escape_string($built_year);
            $clean_hours       = $conn->real_escape_string($opening_hours);
            $clean_fee         = $conn->real_escape_string($entry_fee);
            $clean_status      = $conn->real_escape_string($status);
            $clean_desc        = $conn->real_escape_string($description);
            $clean_image       = $conn->real_escape_string($image_path);

            $insert_sql = "INSERT INTO destinations 
                (destination_id, site_name, division, district, location, heritage_type, historical_period, built_year, opening_hours, entry_fee, guide_available, destination_image, description, status) 
                VALUES 
                ('$clean_dest_id', '$clean_site_name', '$clean_division', '$clean_district', '$clean_loc', '$clean_heritage', '$clean_period', '$clean_built_year', '$clean_hours', '$clean_fee', '$guide_available', '$clean_image', '$clean_desc', '$clean_status')";

            if ($conn->query($insert_sql)) {
                $success_message = "Destination landmark registered successfully! (Guide Available Status: $guide_available)";
                $auto_dest_id = "DST-" . date("Y") . "-" . rand(1000, 9999);
            } else {
                $error_message = "Database error: Could not save destination. " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Add Destination - HeritageLink Admin</title>


  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">


  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <link rel="stylesheet" href="index.css">
  <link rel="stylesheet" href="admin-dashboard.css">
  <link rel="stylesheet" href="admin-destinations-add.css">
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


  <main class="destination-add-wrapper">
    <div class="page-container">

      <div class="form-header-box">
        <h1 class="form-page-title">Add New Heritage Destination</h1>
      </div>

   
      <div class="form-main-card">
        
    
        <?php if (!empty($error_message)): ?>
          <div class="alert-box error" id="alert-box"><?php echo htmlspecialchars($error_message); ?></div>
        <?php elseif (!empty($success_message)): ?>
          <div class="alert-box success" id="alert-box"><?php echo htmlspecialchars($success_message); ?></div>
        <?php else: ?>
          <div class="alert-box error" id="alert-box" style="display: none;"></div>
        <?php endif; ?>

        <form id="add-destination-form" action="admin-destinations-add.php" method="POST" enctype="multipart/form-data" class="guide-vertical-form">
          
  
          <div class="form-field">
            <label for="destination_id">
              <span>Destination ID</span>
              <span class="badge-autogen">Auto-Generated</span>
            </label>
            <input type="text" id="destination_id" name="destination_id" value="<?php echo htmlspecialchars($auto_dest_id); ?>" readonly class="readonly-input" />
          </div>

      
          <div class="form-field">
            <label for="site_name"><span>Site Name</span> <span class="req">*</span></label>
            <input type="text" id="site_name" name="site_name" placeholder="e.g. Lalbagh Fort, Somapura Mahavihara, Ahsan Manzil" required />
          </div>


          <div class="form-field">
            <label for="division"><span>Division</span> <span class="req">*</span></label>
            <select id="division" name="division" required>
              <option value="">-- Select Division --</option>
              <option value="Dhaka">Dhaka Division</option>
              <option value="Chittagong">Chittagong Division</option>
              <option value="Rajshahi">Rajshahi Division</option>
              <option value="Khulna">Khulna Division</option>
              <option value="Sylhet">Sylhet Division</option>
              <option value="Barisal">Barisal Division</option>
              <option value="Rangpur">Rangpur Division</option>
              <option value="Mymensingh">Mymensingh Division</option>
            </select>
          </div>


          <div class="form-field">
            <label for="district"><span>District</span> <span class="req">*</span></label>
            <input type="text" id="district" name="district" placeholder="e.g. Dhaka, Bogra, Bagerhat, Naogaon" required />
          </div>

   
          <div class="form-field">
            <label for="location"><span>Location / Landmark Address</span> <span class="req">*</span></label>
            <input type="text" id="location" name="location" placeholder="e.g. Lalbagh, Old Dhaka 1211" required />
          </div>

         
          <div class="form-field">
            <label for="heritage_type"><span>Heritage Type</span> <span class="req">*</span></label>
            <input type="text" id="heritage_type" name="heritage_type" placeholder="e.g. Archaeological Site, Fortress / Palace, Ancient Temple Complex" required />
          </div>

          <div class="form-field">
            <label for="historical_period"><span>Historical Period</span> <span class="req">*</span></label>
            <input type="text" id="historical_period" name="historical_period" placeholder="e.g. Mughal Era, Pala Dynasty, Sultanate Period" required />
          </div>

          <div class="form-field">
            <label for="built_year"><span>Built Year / Era</span></label>
            <input type="text" id="built_year" name="built_year" placeholder="e.g. 1678 AD, 8th Century, 1872 AD" />
          </div>

        
          <div class="form-field">
            <label for="opening_hours"><span>Opening Hours</span> <span class="req">*</span></label>
            <input type="text" id="opening_hours" name="opening_hours" placeholder="e.g. 10:00 AM - 05:00 PM (Closed Sundays)" required />
          </div>

       
          <div class="form-field">
            <label for="entry_fee"><span>Entry Fee Details</span> <span class="req">*</span></label>
            <input type="text" id="entry_fee" name="entry_fee" placeholder="e.g. Local: 20 BDT, SAARC: 100 BDT, Foreigner: 500 BDT, Students: Free" required />
          </div>

         
          <div class="form-field">
            <label>
              <span>Guide Availability</span>
              <span class="badge-system-calc"><i class="fa-solid fa-arrows-rotate"></i> Auto-Calculated</span>
            </label>
            <div class="info-callout-box">
              <i class="fa-solid fa-circle-info"></i>
              <span>The system will automatically detect and mark guide availability based on matching shifts in the "Guide Availability" schedule for this landmark.</span>
            </div>
          </div>

   
          <div class="form-field">
            <label for="destination_image"><span>Destination Cover Photo</span></label>
            <input type="file" id="destination_image" name="destination_image" accept="image/png, image/jpeg, image/webp" />
          </div>

       
          <div class="form-field">
            <label for="status"><span>Site Operational Status</span> <span class="req">*</span></label>
            <select id="status" name="status" required>
              <option value="Active">Active</option>
              <option value="Under Maintenance">Under Maintenance</option>
              <option value="Closed">Temporarily Closed</option>
            </select>
          </div>

          <div class="form-field">
            <label for="description"><span>Historical Overview &a Description</span> <span class="req">*</span></label>
            <textarea id="description" name="description" rows="5" placeholder="Provide background history, architectural significance and visitor highlights..." required></textarea>
          </div>


          <div class="form-actions-row">
            <a href="admin-dashboard.php" class="btn-cancel">Cancel</a>
            <button type="submit" class="btn-save-destination"><i class="fa-solid fa-plus"></i> Add Destination</button>
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

        <<div class="footer-right">
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

  <script src="admin-destinations-add.js"></script>
</body>
</html>