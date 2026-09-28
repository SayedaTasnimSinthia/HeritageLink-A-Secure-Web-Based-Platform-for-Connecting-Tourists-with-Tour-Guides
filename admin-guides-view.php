<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin-login.php");
    exit;
}

$sql = "SELECT * FROM guides ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>View Guides - HeritageLink Admin</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="index.css">
  <link rel="stylesheet" href="admin-dashboard.css">
  <link rel="stylesheet" href="admin-guides-view.css">
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

  <main class="view-guides-wrapper">
    <div class="page-container-wide">

      <div class="view-header-box">
        <h1 class="view-page-title">Registered Tour Guides</h1>
      </div>

      <div class="search-filter-card">
        <div class="search-input-wrap">
          <i class="fa-solid fa-magnifying-glass search-icon"></i>
          <input 
            type="text" 
            id="live-guide-search" 
            placeholder="Search by Guide ID (e.g. GD-101), Name, Status, Division, Sites, Languages..." 
            autocomplete="off"
          />
        </div>
      </div>

      <div class="table-container-card">
        <div class="table-responsive">
          <table class="guides-table" id="guides-table">
            <thead>
              <tr>
                <th>Photo</th>
                <th>Guide ID</th>
                <th>Full Name</th>
                <th>Contact Info</th>
                <th>Division</th>
                <th>Rate (BDT)</th>
                <th>Languages</th>
                <th>Heritage Sites</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody id="guides-table-body">
              <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                  <tr 
                    class="guide-data-row"
                    data-id="<?php echo htmlspecialchars($row['guide_id']); ?>"
                    data-name="<?php echo htmlspecialchars($row['full_name']); ?>"
                    data-spec="<?php echo htmlspecialchars($row['specialization']); ?>"
                    data-email="<?php echo htmlspecialchars($row['email']); ?>"
                    data-phone="<?php echo htmlspecialchars($row['phone']); ?>"
                    data-division="<?php echo htmlspecialchars($row['working_division']); ?>"
                    data-status="<?php echo htmlspecialchars($row['account_status']); ?>"
                    data-languages="<?php echo htmlspecialchars($row['languages']); ?>"
                    data-sites="<?php echo htmlspecialchars($row['heritage_sites']); ?>"
                  >
                    <td>
                      <img 
                        src="<?php echo htmlspecialchars($row['profile_photo'] ? $row['profile_photo'] : 'images/default-avatar.png'); ?>" 
                        alt="<?php echo htmlspecialchars($row['full_name']); ?>" 
                        class="guide-thumb" 
                      />
                    </td>
                    <td><span class="guide-id-tag"><?php echo htmlspecialchars($row['guide_id']); ?></span></td>
                    <td>
                      <strong><?php echo htmlspecialchars($row['full_name']); ?></strong><br>
                      <small class="text-muted"><?php echo htmlspecialchars($row['specialization']); ?></small>
                    </td>
                    <td>
                      <div><i class="fa-solid fa-envelope text-icon"></i> <?php echo htmlspecialchars($row['email']); ?></div>
                      <div><i class="fa-solid fa-phone text-icon"></i> <?php echo htmlspecialchars($row['phone']); ?></div>
                    </td>
                    <td><?php echo htmlspecialchars($row['working_division']); ?></td>
                    <td>
                      <strong><?php echo number_format($row['rate_amount'], 2); ?></strong> 
                      <small>/ <?php echo htmlspecialchars($row['rate_type']); ?></small>
                    </td>
                    <td>
                      <div class="badges-wrap">
                        <?php 
                          $langs = array_map('trim', explode(',', $row['languages']));
                          foreach ($langs as $lang):
                            if (!empty($lang)):
                        ?>
                          <span class="badge lang-badge"><?php echo htmlspecialchars($lang); ?></span>
                        <?php 
                            endif;
                          endforeach; 
                        ?>
                      </div>
                    </td>
                    <td>
                      <div class="badges-wrap">
                        <?php 
                          $sites = array_map('trim', explode(',', $row['heritage_sites']));
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
                      <span class="status-pill <?php echo strtolower($row['account_status']); ?>">
                        <?php echo htmlspecialchars($row['account_status']); ?>
                      </span>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr id="empty-db-row">
                  <td colspan="9" class="no-data-cell">
                    <i class="fa-solid fa-folder-open empty-icon"></i>
                    <p>No guides found in the database.</p>
                  </td>
                </tr>
              <?php endif; ?>
      
              <tr id="no-match-row" style="display: none;">
                <td colspan="9" class="no-data-cell">
                  <i class="fa-solid fa-magnifying-glass empty-icon"></i>
                  <p>No matching guides found for your search.</p>
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

  <script src="admin-guides-view.js"></script>
</body>
</html>