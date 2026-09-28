<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin-login.php");
    exit;
}

$action = isset($_GET['action']) ? trim($_GET['action']) : 'profiles';

$query = "SELECT id, name, email, phone, nid_number, profile_photo, gender, dob, blood_group, nationality, emergency_name, emergency_phone, created_at 
          FROM customers 
          ORDER BY id DESC";
$res = $conn->query($query);

$customers = [];
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $customers[] = $row;
    }
}
$total_customers = count($customers);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Tourist Profiles - HeritageLink Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <link rel="stylesheet" href="index.css">
  <link rel="stylesheet" href="admin-view-profiles.css">
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
  <main class="manage-users-wrapper">
    <div class="page-container">

      <div class="users-header-area">
        <h1 class="users-main-title">Registered Tourist Profiles</h1>
        
      </div>

      <div class="users-toolbar-card">
        <div class="toolbar-left">
          <div class="search-input-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input 
              type="text" 
              id="user-search-input" 
              placeholder="Search by Customer ID, name, email or phone number..." 
              autocomplete="off"
            />
          </div>
        </div>
        <div class="toolbar-right">
          <span class="count-pill">
            <i class="fa-solid fa-users"></i> Total Registered: <strong id="total-count-val"><?php echo $total_customers; ?></strong>
          </span>
        </div>
      </div>

      <div class="tourists-cards-stack" id="tourists-container">
        <?php if (!empty($customers)): ?>
          <?php foreach ($customers as $c): 
            $photo = (!empty($c['profile_photo']) && file_exists($c['profile_photo'])) ? $c['profile_photo'] : 'images/default-avatar.png';
            $cust_id_raw = (string)$c['id'];
            $cust_code = "#CUST-" . str_pad($c['id'], 4, '0', STR_PAD_LEFT);
            $registered_date = !empty($c['created_at']) ? date("d M Y, h:i A", strtotime($c['created_at'])) : 'N/A';
            $dob_display = !empty($c['dob']) ? date("d M Y", strtotime($c['dob'])) : 'Not Provided';
            $full_name = !empty($c['name']) ? $c['name'] : 'Name Not Set';
            $phone_val = !empty($c['phone']) ? $c['phone'] : '';

       
            $search_blob = strtolower($cust_id_raw . ' ' . $cust_code . ' ' . $full_name . ' ' . $c['email'] . ' ' . $phone_val);
          ?>
            <article class="tourist-profile-card" data-search="<?php echo htmlspecialchars($search_blob); ?>">
          
              <div class="card-head-row">
                <div class="tourist-ident">
                  <img src="<?php echo htmlspecialchars($photo); ?>" alt="<?php echo htmlspecialchars($full_name); ?>" class="tourist-avatar-img">
                  <div class="tourist-meta">
                    <div class="name-id-row">
                      <h2><?php echo htmlspecialchars($full_name); ?></h2>
                      <span class="cust-code-badge"><?php echo htmlspecialchars($cust_code); ?></span>
                    </div>
                    <span class="tourist-email"><i class="fa-solid fa-envelope"></i> <?php echo htmlspecialchars($c['email']); ?></span>
                  </div>
                </div>

                <div class="reg-date-box">
                  <span class="meta-label">Member Since</span>
                  <span class="meta-val"><i class="fa-regular fa-calendar-check"></i> <?php echo htmlspecialchars($registered_date); ?></span>
                </div>
              </div>

              <div class="card-details-grid">

                <div class="details-section-box">
                  <h3 class="section-box-title"><i class="fa-solid fa-id-card"></i> Identification &amp; Contact</h3>
                  
                  <div class="info-row">
                    <span class="info-label">Customer ID:</span>
                    <span class="info-value font-highlight"><?php echo htmlspecialchars($cust_code); ?></span>
                  </div>

                  <div class="info-row">
                    <span class="info-label">Phone:</span>
                    <span class="info-value"><?php echo !empty($c['phone']) ? htmlspecialchars($c['phone']) : '<em class="text-unfilled">Not specified</em>'; ?></span>
                  </div>

                  <div class="info-row">
                    <span class="info-label">NID / Passport:</span>
                    <span class="info-value"><?php echo !empty($c['nid_number']) ? htmlspecialchars($c['nid_number']) : '<em class="text-unfilled">Not specified</em>'; ?></span>
                  </div>

                  <div class="info-row">
                    <span class="info-label">Nationality:</span>
                    <span class="info-value"><?php echo !empty($c['nationality']) ? htmlspecialchars($c['nationality']) : '<em class="text-unfilled">Not specified</em>'; ?></span>
                  </div>
                </div>

        
                <div class="details-section-box">
                  <h3 class="section-box-title"><i class="fa-solid fa-user-tag"></i> Demographics &amp; Health</h3>
                  
                  <div class="info-row">
                    <span class="info-label">Gender:</span>
                    <span class="info-value"><?php echo !empty($c['gender']) ? htmlspecialchars($c['gender']) : '<em class="text-unfilled">Not specified</em>'; ?></span>
                  </div>

                  <div class="info-row">
                    <span class="info-label">Date of Birth:</span>
                    <span class="info-value"><?php echo htmlspecialchars($dob_display); ?></span>
                  </div>

                  <div class="info-row">
                    <span class="info-label">Blood Group:</span>
                    <span class="info-value">
                      <?php if (!empty($c['blood_group'])): ?>
                        <span class="badge-blood-group"><i class="fa-solid fa-droplet"></i> <?php echo htmlspecialchars($c['blood_group']); ?></span>
                      <?php else: ?>
                        <em class="text-unfilled">Not specified</em>
                      <?php endif; ?>
                    </span>
                  </div>
                </div>

          
                <div class="details-section-box emergency-box">
                  <h3 class="section-box-title"><i class="fa-solid fa-truck-medical"></i> Emergency Safety Contact</h3>
                  
                  <div class="info-row">
                    <span class="info-label">Contact Person:</span>
                    <span class="info-value"><?php echo !empty($c['emergency_name']) ? htmlspecialchars($c['emergency_name']) : '<em class="text-unfilled">None listed</em>'; ?></span>
                  </div>

                  <div class="info-row">
                    <span class="info-label">Emergency Phone:</span>
                    <span class="info-value">
                      <?php if (!empty($c['emergency_phone'])): ?>
                        <strong><a href="tel:<?php echo htmlspecialchars($c['emergency_phone']); ?>" class="emergency-tel-link"><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($c['emergency_phone']); ?></a></strong>
                      <?php else: ?>
                        <em class="text-unfilled">None listed</em>
                      <?php endif; ?>
                    </span>
                  </div>
                </div>

              </div>

            </article>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="no-records-card">
            <i class="fa-solid fa-user-slash"></i>
            <h2>No Tourist Profiles Found</h2>
            <p>There are currently no registered customer accounts stored in the platform database.</p>
          </div>
        <?php endif; ?>
      </div>

      <div id="no-search-results" class="no-records-card" style="display: none;">
        <i class="fa-solid fa-magnifying-glass"></i>
        <h2>No Matching Tourists Found</h2>
        <p>No user profile matches your search keyword. Please try a different customer ID, name, email, or phone number.</p>
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

  <script src="admin-view-profiles.js"></script>
</body>
</html>