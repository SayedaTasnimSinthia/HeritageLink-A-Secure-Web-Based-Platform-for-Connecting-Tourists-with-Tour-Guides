<?php
session_start();
require_once 'db.php';


if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin-login.php");
    exit;
}

$error_message = "";
$success_message = "";
$dest_data = null;
$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';


if (!empty($search_name)) {
    $clean_search = $conn->real_escape_string($search_name);
    $query = "SELECT * FROM destinations WHERE site_name LIKE '%$clean_search%' OR destination_id = '$clean_search' LIMIT 1";
    $res = $conn->query($query);
    if ($res && $res->num_rows > 0) {
        $dest_data = $res->fetch_assoc();
    } else {
        $error_message = "No destination found matching: " . htmlspecialchars($search_name);
    }
}


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_destination'])) {
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
    $existing_image    = isset($_POST['existing_image']) ? trim($_POST['existing_image']) : 'images/default-destination.jpg';

    if (empty($destination_id) || empty($site_name) || empty($division) || empty($district) || empty($location) || empty($heritage_type) || empty($historical_period) || empty($opening_hours) || empty($entry_fee) || empty($description)) {
        $error_message = "Please fill in all required fields.";
    } else {
        $clean_dest_id   = $conn->real_escape_string($destination_id);
        $clean_site_name = $conn->real_escape_string($site_name);

        
        $check_sql = "SELECT id FROM destinations WHERE site_name = '$clean_site_name' AND destination_id != '$clean_dest_id' LIMIT 1";
        $check_res = $conn->query($check_sql);

        if ($check_res && $check_res->num_rows > 0) {
            $error_message = "Another destination with this site name already exists.";
        } else {
 
            $clean_district  = $conn->real_escape_string($district);
            $clean_loc       = $conn->real_escape_string($location);

            $guide_check_sql = "SELECT id FROM guide_availability 
                                WHERE slot_status = 'Available' 
                                AND (working_sites LIKE '%$clean_site_name%' OR working_district LIKE '%$clean_district%' OR working_district LIKE '%$clean_loc%') 
                                LIMIT 1";
            $guide_check_res = $conn->query($guide_check_sql);
            $guide_available = ($guide_check_res && $guide_check_res->num_rows > 0) ? 'Yes' : 'No';

            $image_path = $existing_image;
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

            $clean_division   = $conn->real_escape_string($division);
            $clean_heritage   = $conn->real_escape_string($heritage_type);
            $clean_period     = $conn->real_escape_string($historical_period);
            $clean_built_year = $conn->real_escape_string($built_year);
            $clean_hours      = $conn->real_escape_string($opening_hours);
            $clean_fee        = $conn->real_escape_string($entry_fee);
            $clean_status     = $conn->real_escape_string($status);
            $clean_desc       = $conn->real_escape_string($description);
            $clean_image      = $conn->real_escape_string($image_path);

            $update_sql = "UPDATE destinations SET 
                site_name = '$clean_site_name',
                division = '$clean_division',
                district = '$clean_district',
                location = '$clean_loc',
                heritage_type = '$clean_heritage',
                historical_period = '$clean_period',
                built_year = '$clean_built_year',
                opening_hours = '$clean_hours',
                entry_fee = '$clean_fee',
                guide_available = '$guide_available',
                destination_image = '$clean_image',
                description = '$clean_desc',
                status = '$clean_status'
                WHERE destination_id = '$clean_dest_id'";

            if ($conn->query($update_sql)) {
                $success_message = "Destination details updated successfully! (Guide Available: $guide_available)";
                // Refresh data array
                $refetch = $conn->query("SELECT * FROM destinations WHERE destination_id = '$clean_dest_id' LIMIT 1");
                if ($refetch) {
                    $dest_data = $refetch->fetch_assoc();
                }
            } else {
                $error_message = "Database error: Could not update destination. " . $conn->error;
            }
        }
    }
}

$all_sites_res = $conn->query("SELECT destination_id, site_name, district FROM destinations ORDER BY site_name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Modify Destination - HeritageLink Admin</title>

 
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">


  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


  <link rel="stylesheet" href="index.css">
  <link rel="stylesheet" href="admin-dashboard.css">
  <link rel="stylesheet" href="admin-destinations-modify.css">
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

 
  <main class="destination-modify-wrapper">
    <div class="page-container">

  
      <div class="form-header-box">
        <h1 class="form-page-title">Modify Destination Info</h1>
       
      </div>


      <div class="search-dest-card">
        <form method="GET" action="admin-destinations-modify.php" class="search-dest-form" id="search-dest-form">
          <label for="search_name">Search Destination by Site Name</label>
          <div class="search-input-group">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input 
              type="text" 
              id="search_name" 
              name="search_name" 
              value="<?php echo htmlspecialchars($search_name); ?>" 
              placeholder="e.g. Lalbagh Fort, Somapura Mahavihara..." 
              autocomplete="off" 
              required 
            />
            <button type="submit" class="btn-fetch-dest">Fetch Details</button>
          </div>
        </form>

       
        <?php if ($all_sites_res && $all_sites_res->num_rows > 0): ?>
          <div class="quick-pick-row">
            <span>Or select directly:</span>
            <select id="quick_site_selector">
              <option value="">-- Choose Existing Destination --</option>
              <?php while ($s = $all_sites_res->fetch_assoc()): ?>
                <option value="<?php echo htmlspecialchars($s['site_name']); ?>">
                  <?php echo htmlspecialchars($s['site_name']) . " (" . htmlspecialchars($s['district']) . ")"; ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
        <?php endif; ?>
      </div>

 
      <?php if (!empty($error_message)): ?>
        <div class="alert-box error"><?php echo htmlspecialchars($error_message); ?></div>
      <?php elseif (!empty($success_message)): ?>
        <div class="alert-box success"><?php echo htmlspecialchars($success_message); ?></div>
      <?php endif; ?>

      
      <?php if ($dest_data): ?>
        <div class="form-main-card" id="edit-destination-card">
          <form action="admin-destinations-modify.php?search_name=<?php echo urlencode($dest_data['site_name']); ?>" method="POST" enctype="multipart/form-data" class="guide-vertical-form">
            <input type="hidden" name="update_destination" value="1" />
            <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($dest_data['destination_image']); ?>" />

       
            <div class="form-field">
              <label for="destination_id">
                <span>Destination ID</span>
                <span class="badge-locked"><i class="fa-solid fa-lock"></i> Locked</span>
              </label>
              <input type="text" id="destination_id" name="destination_id" value="<?php echo htmlspecialchars($dest_data['destination_id']); ?>" readonly class="readonly-input" />
            </div>

          
            <div class="form-field">
              <label for="site_name"><span>Site Name</span> <span class="req">*</span></label>
              <input type="text" id="site_name" name="site_name" value="<?php echo htmlspecialchars($dest_data['site_name']); ?>" required />
            </div>

           
            <div class="form-field">
              <label for="division"><span>Division</span> <span class="req">*</span></label>
              <select id="division" name="division" required>
                <?php
                  $divisions = ['Dhaka', 'Chittagong', 'Rajshahi', 'Khulna', 'Sylhet', 'Barisal', 'Rangpur', 'Mymensingh'];
                  foreach ($divisions as $div):
                ?>
                  <option value="<?php echo $div; ?>" <?php echo ($dest_data['division'] === $div) ? 'selected' : ''; ?>>
                    <?php echo $div; ?> Division
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            
            <div class="form-field">
              <label for="district"><span>District</span> <span class="req">*</span></label>
              <input type="text" id="district" name="district" value="<?php echo htmlspecialchars($dest_data['district']); ?>" required />
            </div>

    
            <div class="form-field">
              <label for="location"><span>Location / Landmark Address</span> <span class="req">*</span></label>
              <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($dest_data['location']); ?>" required />
            </div>

            
            <div class="form-field">
              <label for="heritage_type"><span>Heritage Type</span> <span class="req">*</span></label>
              <input type="text" id="heritage_type" name="heritage_type" value="<?php echo htmlspecialchars($dest_data['heritage_type']); ?>" placeholder="e.g. Archaeological Site, Fortress / Palace, Ancient Temple Complex" required />
            </div>

            <div class="form-field">
              <label for="historical_period"><span>Historical Period</span> <span class="req">*</span></label>
              <input type="text" id="historical_period" name="historical_period" value="<?php echo htmlspecialchars($dest_data['historical_period']); ?>" required />
            </div>

          
            <div class="form-field">
              <label for="built_year"><span>Built Year / Era</span></label>
              <input type="text" id="built_year" name="built_year" value="<?php echo htmlspecialchars($dest_data['built_year']); ?>" />
            </div>

          
            <div class="form-field">
              <label for="opening_hours"><span>Opening Hours</span> <span class="req">*</span></label>
              <input type="text" id="opening_hours" name="opening_hours" value="<?php echo htmlspecialchars($dest_data['opening_hours']); ?>" required />
            </div>

      
            <div class="form-field">
              <label for="entry_fee"><span>Entry Fee Details</span> <span class="req">*</span></label>
              <input type="text" id="entry_fee" name="entry_fee" value="<?php echo htmlspecialchars($dest_data['entry_fee']); ?>" placeholder="e.g. Local: 20 BDT, SAARC: 100 BDT, Foreigner: 500 BDT, Students: Free" required />
            </div>

   
            <div class="form-field">
              <label>
                <span>Guide Availability</span>
                <span class="guide-status-tag <?php echo strtolower($dest_data['guide_available']) === 'yes' ? 'tag-yes' : 'tag-no'; ?>">
                  Currently: <?php echo htmlspecialchars($dest_data['guide_available']); ?>
                </span>
              </label>
              <div class="info-callout-box">
                <i class="fa-solid fa-arrows-rotate"></i>
                <span>This status will re-evaluate automatically when you save changes, based on active slots in <strong>guide_availability</strong> for this landmark or district.</span>
              </div>
            </div>

           
            <div class="form-field">
              <label><span>Destination Cover Photo</span></label>
              <div class="current-photo-wrap">
                <img src="<?php echo htmlspecialchars(!empty($dest_data['destination_image']) ? $dest_data['destination_image'] : 'images/default-destination.jpg'); ?>" alt="Cover Photo Preview" class="current-dest-preview" />
                <span class="photo-hint">Upload below only if you wish to change the existing landmark photo.</span>
              </div>
              <input type="file" id="destination_image" name="destination_image" accept="image/png, image/jpeg, image/webp" />
            </div>

     
            <div class="form-field">
              <label for="status"><span>Site Operational Status</span> <span class="req">*</span></label>
              <select id="status" name="status" required>
                <option value="Active" <?php echo ($dest_data['status'] === 'Active') ? 'selected' : ''; ?>>Active (Open to Public)</option>
                <option value="Under Maintenance" <?php echo ($dest_data['status'] === 'Under Maintenance') ? 'selected' : ''; ?>>Under Maintenance</option>
                <option value="Closed" <?php echo ($dest_data['status'] === 'Closed') ? 'selected' : ''; ?>>Temporarily Closed</option>
              </select>
            </div>

       
            <div class="form-field">
              <label for="description"><span>Historical Overview &amp; Description</span> <span class="req">*</span></label>
              <textarea id="description" name="description" rows="5" required><?php echo htmlspecialchars($dest_data['description']); ?></textarea>
            </div>

       
            <div class="form-actions-row">
              <a href="admin-dashboard.php" class="btn-cancel">Cancel</a>
              <button type="submit" class="btn-save-destination"><i class="fa-solid fa-floppy-disk"></i> Update Destination Info</button>
            </div>

          </form>
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

  <script src="admin-destinations-modify.js"></script>
</body>
</html>