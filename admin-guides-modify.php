<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin-login.php");
    exit;
}

$error_message = "";
$success_message = "";
$guide_data = null;
$search_id = isset($_GET['search_id']) ? trim($_GET['search_id']) : '';

$dest_query = "SELECT site_name, district FROM destinations WHERE status = 'Active' ORDER BY site_name ASC";
$dest_res = $conn->query($dest_query);

$valid_sites = [];
$destination_options = [];

if ($dest_res && $dest_res->num_rows > 0) {
    while ($row = $dest_res->fetch_assoc()) {
        $valid_sites[] = $row['site_name'];
        $destination_options[] = $row;
    }
}

if (!empty($search_id)) {
    $search_stmt = $conn->prepare("SELECT * FROM guides WHERE guide_id = ? LIMIT 1");
    $search_stmt->bind_param("s", $search_id);
    $search_stmt->execute();
    $result = $search_stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $guide_data = $result->fetch_assoc();
    } else {
        $error_message = "No guide found with Guide ID: " . htmlspecialchars($search_id);
    }
    $search_stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_guide'])) {
    $guide_id         = isset($_POST['guide_id']) ? trim($_POST['guide_id']) : '';
    $full_name        = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $email            = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone            = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $emergency_phone  = isset($_POST['emergency_phone']) ? trim($_POST['emergency_phone']) : '';
    $gender           = isset($_POST['gender']) ? trim($_POST['gender']) : '';
    $dob              = isset($_POST['dob']) ? trim($_POST['dob']) : '';
    $nid_number       = isset($_POST['nid_number']) ? trim($_POST['nid_number']) : '';
    $license_no       = isset($_POST['license_no']) ? trim($_POST['license_no']) : '';
    $experience       = isset($_POST['experience_years']) ? (int)$_POST['experience_years'] : 0;
    $specialization   = isset($_POST['specialization']) ? trim($_POST['specialization']) : '';
    $working_division = isset($_POST['working_division']) ? trim($_POST['working_division']) : 'Dhaka';
    $rate_type        = isset($_POST['rate_type']) ? trim($_POST['rate_type']) : 'Daily';
    $rate_amount      = isset($_POST['rate_amount']) ? (float)$_POST['rate_amount'] : 0.00;
    $short_bio        = isset($_POST['short_bio']) ? trim($_POST['short_bio']) : '';
    $account_status   = isset($_POST['account_status']) ? trim($_POST['account_status']) : 'Active';
    $existing_photo   = isset($_POST['existing_photo']) ? trim($_POST['existing_photo']) : 'images/default-avatar.png';

    $languages_array  = isset($_POST['languages']) && is_array($_POST['languages']) ? $_POST['languages'] : [];
    $languages_str    = implode(", ", array_map('trim', $languages_array));

    $raw_sites_array  = isset($_POST['heritage_sites']) && is_array($_POST['heritage_sites']) ? $_POST['heritage_sites'] : [];
    $sites_array      = array_values(array_intersect($raw_sites_array, $valid_sites));
    $heritage_sites   = implode(", ", $sites_array);

    if (empty($full_name) || empty($email) || empty($phone) || empty($gender) || empty($dob) || empty($nid_number) || empty($specialization) || empty($languages_str)) {
        $error_message = "Please fill in all required fields and select at least one language.";
    } elseif (empty($sites_array)) {
        $error_message = "Please select at least one valid heritage site listed under Destinations.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } else {
        $check_stmt = $conn->prepare("SELECT guide_id FROM guides WHERE (email = ? OR nid_number = ?) AND guide_id != ? LIMIT 1");
        $check_stmt->bind_param("sss", $email, $nid_number, $guide_id);
        $check_stmt->execute();
        $check_res = $check_stmt->get_result();

        if ($check_res && $check_res->num_rows > 0) {
            $error_message = "The email or National ID (NID) is already registered to another guide.";
        } else {
            $photo_path = $existing_photo;
            if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
                $file_tmp   = $_FILES['profile_photo']['tmp_name'];
                $file_name  = basename($_FILES['profile_photo']['name']);
                $file_ext   = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed    = ['jpg', 'jpeg', 'png', 'webp'];

                if (in_array($file_ext, $allowed)) {
                    if (!is_dir('images')) {
                        mkdir('images', 0777, true);
                    }
                    $new_file_name = "guide_" . time() . "_" . mt_rand(100, 999) . "." . $file_ext;
                    $target_path   = "images/" . $new_file_name;
                    if (move_uploaded_file($file_tmp, $target_path)) {
                        $photo_path = $target_path;
                    }
                }
            }

            $update_stmt = $conn->prepare("UPDATE guides SET 
                full_name = ?,
                email = ?,
                phone = ?,
                emergency_phone = ?,
                gender = ?,
                dob = ?,
                nid_number = ?,
                license_no = ?,
                experience_years = ?,
                specialization = ?,
                languages = ?,
                heritage_sites = ?,
                working_division = ?,
                rate_type = ?,
                rate_amount = ?,
                profile_photo = ?,
                short_bio = ?,
                account_status = ?
                WHERE guide_id = ?");

            $update_stmt->bind_param(
                "ssssssssisssssdssss",
                $full_name,
                $email,
                $phone,
                $emergency_phone,
                $gender,
                $dob,
                $nid_number,
                $license_no,
                $experience,
                $specialization,
                $languages_str,
                $heritage_sites,
                $working_division,
                $rate_type,
                $rate_amount,
                $photo_path,
                $short_bio,
                $account_status,
                $guide_id
            );

            if ($update_stmt->execute()) {
                $success_message = "Guide information updated successfully!";
                $refetch_stmt = $conn->prepare("SELECT * FROM guides WHERE guide_id = ? LIMIT 1");
                $refetch_stmt->bind_param("s", $guide_id);
                $refetch_stmt->execute();
                $refetch_res = $refetch_stmt->get_result();
                if ($refetch_res) {
                    $guide_data = $refetch_res->fetch_assoc();
                }
                $refetch_stmt->close();
            } else {
                $error_message = "Database error: Could not update guide info. " . $conn->error;
            }
            $update_stmt->close();
        }
        $check_stmt->close();
    }
}

$selected_langs = !empty($guide_data['languages']) ? array_map('trim', explode(',', $guide_data['languages'])) : [];
$selected_sites = !empty($guide_data['heritage_sites']) ? array_map('trim', explode(',', $guide_data['heritage_sites'])) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Modify Tour Guide - HeritageLink Admin</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <link rel="stylesheet" href="index.css">
  <link rel="stylesheet" href="admin-dashboard.css">
  <link rel="stylesheet" href="admin-guides-modify.css">
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

  <main class="modify-guide-wrapper">
    <div class="page-container">

      <div class="form-header-box">
        <h1 class="form-page-title">Modify Tour Guide Info</h1>
      </div>

      <div class="search-guide-card">
        <form method="GET" action="admin-guides-modify.php" class="search-id-form">
          <label for="search_id">Enter Guide ID to Edit</label>
          <div class="search-input-group">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input 
              type="text" 
              id="search_id" 
              name="search_id" 
              value="<?php echo htmlspecialchars($search_id); ?>" 
              placeholder="e.g. GD-101" 
              required 
            />
            <button type="submit" class="btn-find-guide">Fetch Details</button>
          </div>
        </form>
      </div>

      <?php if (!empty($error_message)): ?>
        <div class="alert-box error"><?php echo htmlspecialchars($error_message); ?></div>
      <?php elseif (!empty($success_message)): ?>
        <div class="alert-box success"><?php echo htmlspecialchars($success_message); ?></div>
      <?php endif; ?>

      <?php if ($guide_data): ?>
        <div class="form-main-card">
          <form action="admin-guides-modify.php?search_id=<?php echo urlencode($guide_data['guide_id']); ?>" method="POST" enctype="multipart/form-data" class="guide-vertical-form">
            <input type="hidden" name="update_guide" value="1" />
            <input type="hidden" name="existing_photo" value="<?php echo htmlspecialchars($guide_data['profile_photo']); ?>" />

            <div class="form-field">
              <label for="guide_id">Guide ID <span class="badge-locked"><i class="fa-solid fa-lock"></i> Cannot be modified</span></label>
              <input type="text" id="guide_id" name="guide_id" value="<?php echo htmlspecialchars($guide_data['guide_id']); ?>" readonly class="readonly-input" />
            </div>

            <div class="form-field">
              <label for="full_name">Full Name <span class="req">*</span></label>
              <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($guide_data['full_name']); ?>" required />
            </div>

            <div class="form-field">
              <label for="email">Email Address <span class="req">*</span></label>
              <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($guide_data['email']); ?>" required />
            </div>

            <div class="form-field">
              <label for="phone">Phone Number <span class="req">*</span></label>
              <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($guide_data['phone']); ?>" required />
            </div>

            <div class="form-field">
              <label for="emergency_phone">Emergency Contact</label>
              <input type="text" id="emergency_phone" name="emergency_phone" value="<?php echo htmlspecialchars($guide_data['emergency_phone']); ?>" />
            </div>

            <div class="form-field">
              <label for="gender">Gender <span class="req">*</span></label>
              <select id="gender" name="gender" required>
                <option value="Male" <?php echo ($guide_data['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                <option value="Female" <?php echo ($guide_data['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                <option value="Other" <?php echo ($guide_data['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
              </select>
            </div>

            <div class="form-field">
              <label for="dob">Date of Birth <span class="req">*</span></label>
              <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($guide_data['dob']); ?>" required />
            </div>

            <div class="form-field">
              <label for="nid_number">National ID (NID) <span class="req">*</span></label>
              <input type="text" id="nid_number" name="nid_number" value="<?php echo htmlspecialchars($guide_data['nid_number']); ?>" required />
            </div>

            <div class="form-field">
              <label for="license_no">Accreditation / License No.</label>
              <input type="text" id="license_no" name="license_no" value="<?php echo htmlspecialchars($guide_data['license_no']); ?>" placeholder="e.g. BPC-TG-2024-089" />
            </div>

            <div class="form-field">
              <label for="experience_years">Years of Experience <span class="req">*</span></label>
              <input type="number" id="experience_years" name="experience_years" min="0" max="60" value="<?php echo htmlspecialchars($guide_data['experience_years']); ?>" required />
            </div>

            <div class="form-field">
              <label for="specialization">Specialization <span class="req">*</span></label>
              <input type="text" id="specialization" name="specialization" value="<?php echo htmlspecialchars($guide_data['specialization']); ?>" required />
            </div>

            <div class="form-field">
              <label for="working_division">Primary Operating Division <span class="req">*</span></label>
              <select id="working_division" name="working_division" required>
                <?php 
                  $divisions = ['Dhaka', 'Chittagong', 'Rajshahi', 'Khulna', 'Sylhet', 'Barisal', 'Rangpur', 'Mymensingh'];
                  foreach ($divisions as $div):
                ?>
                  <option value="<?php echo $div; ?>" <?php echo ($guide_data['working_division'] === $div) ? 'selected' : ''; ?>>
                    <?php echo $div; ?> Division
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-field">
              <label for="account_status">Account Status <span class="req">*</span></label>
              <select id="account_status" name="account_status">
                <option value="Active" <?php echo ($guide_data['account_status'] === 'Active') ? 'selected' : ''; ?>>Active (Available for booking)</option>
                <option value="Inactive" <?php echo ($guide_data['account_status'] === 'Inactive') ? 'selected' : ''; ?>>Inactive (Suspended / Under Review)</option>
              </select>
            </div>

            <div class="form-field">
              <label>Languages Spoken <span class="req">*</span></label>
              <div class="vertical-checkbox-container">
                <?php
                  $all_languages = ['Bengali', 'English', 'Urdu', 'Chinese (Mandarin)', 'Korean', 'Italian', 'Vietnamese', 'Thai', 'Arabic', 'Japanese', 'French', 'German', 'Spanish', 'Hindi', 'Burmese', 'Russian'];
                  foreach ($all_languages as $lang):
                    $checked = in_array($lang, $selected_langs) ? 'checked' : '';
                ?>
                  <label class="vertical-checkbox-item">
                    <input type="checkbox" name="languages[]" value="<?php echo $lang; ?>" <?php echo $checked; ?>> <?php echo $lang; ?>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>

            <div class="form-field">
              <label>Heritage Sites Covered <span class="req">*</span></label>
              <div class="vertical-checkbox-container">
                <?php if (!empty($destination_options)): ?>
                  <?php foreach ($destination_options as $d): ?>
                    <?php $checked = in_array($d['site_name'], $selected_sites) ? 'checked' : ''; ?>
                    <label class="vertical-checkbox-item">
                      <input 
                        type="checkbox" 
                        name="heritage_sites[]" 
                        value="<?php echo htmlspecialchars($d['site_name']); ?>" 
                        <?php echo $checked; ?>
                      > 
                      <?php echo htmlspecialchars($d['site_name']) . " (" . htmlspecialchars($d['district']) . ")"; ?>
                    </label>
                  <?php endforeach; ?>
                <?php else: ?>
                  <p style="padding: 10px; color: var(--text-muted, #5A6A80); font-size: 0.9rem; margin: 0;">
                    No active destinations found. Please add destination sites first under <strong>Manage Destinations Info &gt; Add New Site</strong>.
                  </p>
                <?php endif; ?>
              </div>
            </div>

            <div class="form-field">
              <label for="rate_type">Rate Type <span class="req">*</span></label>
              <select id="rate_type" name="rate_type">
                <option value="Daily" <?php echo ($guide_data['rate_type'] === 'Daily') ? 'selected' : ''; ?>>Daily Rate (BDT)</option>
                <option value="Hourly" <?php echo ($guide_data['rate_type'] === 'Hourly') ? 'selected' : ''; ?>>Hourly Rate (BDT)</option>
              </select>
            </div>

            <div class="form-field">
              <label for="rate_amount">Rate Amount (BDT) <span class="req">*</span></label>
              <input type="number" id="rate_amount" name="rate_amount" step="50" min="0" value="<?php echo htmlspecialchars($guide_data['rate_amount']); ?>" required />
            </div>

            <div class="form-field">
              <label>Current Profile Photo</label>
              <div class="current-photo-wrap">
                <img src="<?php echo htmlspecialchars($guide_data['profile_photo'] ? $guide_data['profile_photo'] : 'images/default-avatar.png'); ?>" alt="Current Avatar" class="current-avatar-preview" />
              </div>
              <input type="file" id="profile_photo" name="profile_photo" accept="image/png, image/jpeg, image/webp" />
            </div>

            <div class="form-field">
              <label for="short_bio">Short Bio (Optional)</label>
              <textarea id="short_bio" name="short_bio" rows="4"><?php echo htmlspecialchars($guide_data['short_bio']); ?></textarea>
            </div>

            <div class="form-actions-row">
              <a href="admin-dashboard.php" class="btn-cancel">Cancel</a>
              <button type="submit" class="btn-save-guide"><i class="fa-solid fa-floppy-disk"></i> Update Guide Info</button>
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

</body>
</html>