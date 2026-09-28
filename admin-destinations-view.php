<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin-login.php");
    exit;
}

$sql = "SELECT * FROM destinations ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>View Destinations - HeritageLink Admin</title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

 
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


  <link rel="stylesheet" href="index.css">
  <link rel="stylesheet" href="admin-dashboard.css">
  <link rel="stylesheet" href="admin-destinations-view.css">
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


  <main class="view-destinations-wrapper">
    <div class="page-container-full">

   
      <div class="view-header-box">
        <h1 class="view-page-title">Heritage Destinations Directory</h1>
    
      </div>

      <div class="search-filter-card">
        <div class="search-input-wrap">
          <i class="fa-solid fa-magnifying-glass search-icon"></i>
          <input 
            type="text" 
            id="live-destination-search" 
            placeholder="Type to search by Site Name (e.g. Lalbagh Fort, Somapura Mahavihara)..." 
            autocomplete="off"
          />
        </div>
      </div>

    
      <div class="table-container-card">
        <div class="table-responsive">
          <table class="destinations-table" id="destinations-table">
            <thead>
              <tr>
                <th>Image</th>
                <th>Destination ID</th>
                <th>Site Name</th>
                <th>Division</th>
                <th>District</th>
                <th>Location</th>
                <th>Heritage Type</th>
                <th>Historical Period</th>
                <th>Built Year</th>
                <th>Description</th>
                <th>Opening Hours</th>
                <th>Entry Fee</th>
                <th>Guide Available</th>
                <th>Status</th>
                <th>Registered At</th>
              </tr>
            </thead>
            <tbody id="destinations-table-body">
              <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): 
                  $status_class = 'status-active';
                  $status_lower = strtolower($row['status']);
                  if ($status_lower === 'under maintenance') {
                      $status_class = 'status-maintenance';
                  } elseif ($status_lower === 'closed') {
                      $status_class = 'status-closed';
                  }

                  $guide_avail_class = strtolower($row['guide_available']) === 'yes' ? 'guide-yes' : 'guide-no';
                  $formatted_created = date("M d, Y", strtotime($row['created_at']));
                ?>
                  <tr 
                    class="destination-data-row"
                    data-site-name="<?php echo htmlspecialchars(strtolower($row['site_name'])); ?>"
                    data-dest-id="<?php echo htmlspecialchars(strtolower($row['destination_id'])); ?>"
                    data-district="<?php echo htmlspecialchars(strtolower($row['district'])); ?>"
                    data-division="<?php echo htmlspecialchars(strtolower($row['division'])); ?>"
                    data-location="<?php echo htmlspecialchars(strtolower($row['location'])); ?>"
                    data-type="<?php echo htmlspecialchars(strtolower($row['heritage_type'])); ?>"
                    data-period="<?php echo htmlspecialchars(strtolower($row['historical_period'])); ?>"
                    data-status="<?php echo htmlspecialchars(strtolower($row['status'])); ?>"
                  >
                 
                    <td>
                      <img 
                        src="<?php echo htmlspecialchars(!empty($row['destination_image']) ? $row['destination_image'] : 'images/default-destination.jpg'); ?>" 
                        alt="<?php echo htmlspecialchars($row['site_name']); ?>" 
                        class="dest-thumb" 
                      />
                    </td>

                   
                    <td>
                      <span class="dest-id-tag"><?php echo htmlspecialchars($row['destination_id']); ?></span>
                    </td>

                    <td>
                      <strong class="site-name-text"><?php echo htmlspecialchars($row['site_name']); ?></strong>
                    </td>

                 
                    <td>
                      <span class="table-text"><?php echo htmlspecialchars($row['division']); ?></span>
                    </td>

                    <td>
                      <span class="table-text"><?php echo htmlspecialchars($row['district']); ?></span>
                    </td>

                    <td>
                      <div class="loc-text">
                        <i class="fa-solid fa-location-dot location-icon"></i>
                        <span><?php echo htmlspecialchars($row['location']); ?></span>
                      </div>
                    </td>

                
                    <td>
                      <span class="badge type-badge"><?php echo htmlspecialchars($row['heritage_type']); ?></span>
                    </td>

                    <td>
                      <span class="period-text"><i class="fa-solid fa-landmark"></i> <?php echo htmlspecialchars($row['historical_period']); ?></span>
                    </td>

                
                    <td>
                      <span class="built-year-text"><?php echo htmlspecialchars(!empty($row['built_year']) ? $row['built_year'] : 'N/A'); ?></span>
                    </td>

                    <td>
                      <div class="desc-cell" title="<?php echo htmlspecialchars($row['description']); ?>">
                        <?php echo htmlspecialchars($row['description']); ?>
                      </div>
                    </td>

          
                    <td>
                      <div class="hours-box">
                        <i class="fa-regular fa-clock text-icon"></i>
                        <span><?php echo htmlspecialchars($row['opening_hours']); ?></span>
                      </div>
                    </td>

               
                    <td>
                      <div class="fee-badge" title="<?php echo htmlspecialchars($row['entry_fee']); ?>">
                        <?php echo htmlspecialchars($row['entry_fee']); ?>
                      </div>
                    </td>


                    <td>
                      <span class="guide-pill <?php echo $guide_avail_class; ?>">
                        <i class="fa-solid <?php echo strtolower($row['guide_available']) === 'yes' ? 'fa-circle-check' : 'fa-circle-xmark'; ?>"></i>
                        <?php echo htmlspecialchars($row['guide_available']); ?>
                      </span>
                    </td>

      
                    <td>
                      <span class="status-pill <?php echo $status_class; ?>">
                        <?php echo htmlspecialchars($row['status']); ?>
                      </span>
                    </td>

                    <td>
                      <span class="created-at-text"><?php echo htmlspecialchars($formatted_created); ?></span>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr id="empty-db-row">
                  <td colspan="15" class="no-data-cell">
                    <i class="fa-solid fa-folder-open empty-icon"></i>
                    <p>No heritage destinations found in the database. Add new sites to populate this directory.</p>
                  </td>
                </tr>
              <?php endif; ?>

         
              <tr id="no-match-row" style="display: none;">
                <td colspan="15" class="no-data-cell">
                  <i class="fa-solid fa-magnifying-glass empty-icon"></i>
                  <p>No destinations match your search query.</p>
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

  <script src="admin-destinations-view.js"></script>
</body>
</html>