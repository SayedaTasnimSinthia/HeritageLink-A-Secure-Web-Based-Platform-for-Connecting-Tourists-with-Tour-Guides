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
$success_msg = "";
$error_msg   = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $phone           = trim($_POST['phone'] ?? '');
    $nid_number      = trim($_POST['nid_number'] ?? '');
    $gender          = trim($_POST['gender'] ?? '');
    $dob             = trim($_POST['dob'] ?? '');
    $blood_group     = trim($_POST['blood_group'] ?? '');
    $nationality     = trim($_POST['nationality'] ?? 'Bangladeshi');
    $emergency_name  = trim($_POST['emergency_name'] ?? '');
    $emergency_phone = trim($_POST['emergency_phone'] ?? '');
    $new_password    = trim($_POST['new_password'] ?? '');

    $photo_path = null;
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $file_tmp  = $_FILES['profile_photo']['tmp_name'];
        $file_name = $_FILES['profile_photo']['name'];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed   = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($file_ext, $allowed)) {
            $upload_dir = 'uploads/customers/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $target_filename = 'customer_' . $customer_id . '_' . time() . '.' . $file_ext;
            $target_file     = $upload_dir . $target_filename;

            if (move_uploaded_file($file_tmp, $target_file)) {
                $photo_path = $target_file;
            } else {
                $error_msg = "Failed to upload profile picture.";
            }
        } else {
            $error_msg = "Only JPG, JPEG, PNG, and WEBP images are allowed.";
        }
    }

    if (empty($error_msg)) {
        $dob_val = !empty($dob) ? $dob : null;
        $gender_val = in_array($gender, ['Male', 'Female', 'Other']) ? $gender : null;

        if ($photo_path) {
            $sql = "UPDATE customers SET 
                    phone = ?, nid_number = ?, gender = ?, dob = ?, 
                    blood_group = ?, nationality = ?, emergency_name = ?, emergency_phone = ?, profile_photo = ?
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssssi", $phone, $nid_number, $gender_val, $dob_val, $blood_group, $nationality, $emergency_name, $emergency_phone, $photo_path, $customer_id);
        } else {
            $sql = "UPDATE customers SET 
                    phone = ?, nid_number = ?, gender = ?, dob = ?, 
                    blood_group = ?, nationality = ?, emergency_name = ?, emergency_phone = ?
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssssi", $phone, $nid_number, $gender_val, $dob_val, $blood_group, $nationality, $emergency_name, $emergency_phone, $customer_id);
        }

        if ($stmt->execute()) {
            if (!empty($new_password)) {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $pwd_stmt = $conn->prepare("UPDATE customers SET password = ? WHERE id = ?");
                $pwd_stmt->bind_param("si", $hashed, $customer_id);
                $pwd_stmt->execute();
            }
            $success_msg = "Profile updated successfully!";
        } else {
            $error_msg = "Error updating profile. Please try again.";
        }
    }
}

$fetch_stmt = $conn->prepare("SELECT * FROM customers WHERE id = ? LIMIT 1");
$fetch_stmt->bind_param("i", $customer_id);
$fetch_stmt->execute();
$cust = $fetch_stmt->get_result()->fetch_assoc();

$display_name   = !empty($cust['name']) ? $cust['name'] : 'Tourist';
$display_avatar = !empty($cust['profile_photo']) && file_exists($cust['profile_photo']) 
                  ? $cust['profile_photo'] 
                  : 'images/default-avatar.png';
$member_since   = !empty($cust['created_at']) ? date("F d, Y", strtotime($cust['created_at'])) : 'N/A';

$page_title = "My Profile & Settings";
$extra_css  = "customer-profile-view.css";
require_once 'header.php';
?>

  <main class="cust-profile-wrapper">
    <div class="page-container">

      <div class="profile-header-box">
        <h1 class="profile-main-title">My Profile &amp; Settings</h1>
     
      </div>

      <?php if (!empty($success_msg)): ?>
        <div class="alert-box alert-success">
          <i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($success_msg); ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($error_msg)): ?>
        <div class="alert-box alert-danger">
          <i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($error_msg); ?>
        </div>
      <?php endif; ?>

      <form action="customer-profile-view.php" method="POST" enctype="multipart/form-data" class="profile-form-layout">
        <input type="hidden" name="action" value="update_profile">

        <div class="profile-sidebar-col">
          <div class="profile-card avatar-overview-card">
            <div class="avatar-preview-container">
              <img id="avatar-preview" src="<?php echo htmlspecialchars($display_avatar); ?>" alt="Profile Photo" />
              <label for="profile_photo" class="avatar-upload-label" title="Upload New Photo">
                <i class="fa-solid fa-camera"></i>
                <input type="file" id="profile_photo" name="profile_photo" accept="image/*" />
              </label>
            </div>
            <h2 class="sidebar-user-name"><?php echo htmlspecialchars($display_name); ?></h2>
            <span class="user-role-tag"><i class="fa-solid fa-passport"></i> Registered Tourist</span>

            <div class="sidebar-quick-specs">
              <div class="spec-row">
                <span class="spec-lbl"><i class="fa-solid fa-id-card-clip"></i> Customer ID</span>
                <span class="spec-val">#CUST-<?php echo str_pad($cust['id'], 4, '0', STR_PAD_LEFT); ?></span>
              </div>
              <div class="spec-row">
                <span class="spec-lbl"><i class="fa-regular fa-calendar-check"></i> Member Since</span>
                <span class="spec-val"><?php echo htmlspecialchars($member_since); ?></span>
              </div>
              <div class="spec-row">
                <span class="spec-lbl"><i class="fa-solid fa-envelope"></i> Email</span>
                <span class="spec-val spec-email" title="<?php echo htmlspecialchars($cust['email']); ?>">
                  <?php echo htmlspecialchars($cust['email']); ?>
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="profile-main-col">

          <div class="profile-card">
            <div class="card-section-title">
              <i class="fa-solid fa-user-shield"></i>
              <h2>Personal Information</h2>
            </div>

            <div class="form-fields-grid">

              <div class="form-field-group">
                <label for="name">Full Name <span class="badge-locked"><i class="fa-solid fa-lock"></i> Locked</span></label>
                <input type="text" id="name" value="<?php echo htmlspecialchars($cust['name'] ?? ''); ?>" disabled class="input-disabled" />
              </div>

              <div class="form-field-group">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" placeholder="+880 1700-000000" value="<?php echo htmlspecialchars($cust['phone'] ?? ''); ?>" />
              </div>

              <div class="form-field-group">
                <label for="nid_number">NID / Passport Number</label>
                <input type="text" id="nid_number" name="nid_number" placeholder="Enter NID or Passport..." value="<?php echo htmlspecialchars($cust['nid_number'] ?? ''); ?>" />
              </div>

              <div class="form-field-group">
                <label for="nationality">Nationality</label>
                <input type="text" id="nationality" name="nationality" placeholder="e.g., Bangladeshi, Foreigner" value="<?php echo htmlspecialchars($cust['nationality'] ?? 'Bangladeshi'); ?>" />
              </div>

              <div class="form-field-group">
                <label for="gender">Gender</label>
                <select id="gender" name="gender">
                  <option value="">Select Gender</option>
                  <option value="Male" <?php echo ($cust['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                  <option value="Female" <?php echo ($cust['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                  <option value="Other" <?php echo ($cust['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                </select>
              </div>
              <div class="form-field-group">
                <label for="dob">Date of Birth</label>
                <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($cust['dob'] ?? ''); ?>" />
              </div>

              <div class="form-field-group">
                <label for="blood_group">Blood Group</label>
                <select id="blood_group" name="blood_group">
                  <option value="">Select Blood Group</option>
                  <?php 
                  $b_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                  foreach ($b_groups as $bg) {
                      $sel = ($cust['blood_group'] === $bg) ? 'selected' : '';
                      echo "<option value=\"$bg\" $sel>$bg</option>";
                  }
                  ?>
                </select>
              </div>
            </div>
          </div>

          <div class="profile-card">
            <div class="card-section-title">
              <i class="fa-solid fa-truck-medical"></i>
              <h2>Emergency Contact Details</h2>
            </div>
            <p class="section-hint-text">Provided for tourist safety during on-site excursions & guided historical tours</p>

            <div class="form-fields-grid">
              <div class="form-field-group">
                <label for="emergency_name">Contact Person Name</label>
                <input type="text" id="emergency_name" name="emergency_name" value="<?php echo htmlspecialchars($cust['emergency_name'] ?? ''); ?>" />
              </div>

              <div class="form-field-group">
                <label for="emergency_phone">Contact Phone Number</label>
                <input type="text" id="emergency_phone" name="emergency_phone" placeholder="+880 1800-000000" value="<?php echo htmlspecialchars($cust['emergency_phone'] ?? ''); ?>" />
              </div>
            </div>
          </div>


          <div class="profile-card">
            <div class="card-section-title">
              <i class="fa-solid fa-key"></i>
              <h2>Security &amp; Password</h2>
            </div>
            <div class="form-fields-grid single-col">
              <div class="form-field-group">
                <label for="new_password">New Password (leave blank if unchanged)</label>
                <input type="password" id="new_password" name="new_password" placeholder="Enter a secure new password..." autocomplete="new-password" />
              </div>
            </div>
          </div>

     
          <div class="profile-actions-bar">
            <a href="customer-dashboard.php" class="btn-cancel-link">Cancel</a>
            <button type="submit" class="btn-save-profile">
              <i class="fa-solid fa-floppy-disk"></i> Save Profile Changes
            </button>
          </div>

        </div>
      </form>

    </div>
  </main>

  <script src="customer-profile-view.js"></script>

<?php require_once 'footer.php'; ?>