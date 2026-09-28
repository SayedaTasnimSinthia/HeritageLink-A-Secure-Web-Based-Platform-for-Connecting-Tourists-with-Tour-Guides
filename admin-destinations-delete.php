<?php
session_start();
require_once 'db.php';


if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin-login.php");
    exit;
}

$error_message = "";
$success_message = "";


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_destination_id'])) {
    $target_dest_id = trim($_POST['delete_destination_id']);

    if (!empty($target_dest_id)) {
        $clean_target_id = $conn->real_escape_string($target_dest_id);


        $img_query = "SELECT destination_image, site_name FROM destinations WHERE destination_id = '$clean_target_id' LIMIT 1";
        $img_res = $conn->query($img_query);

        if ($img_res && $img_res->num_rows > 0) {
            $dest_row = $img_res->fetch_assoc();
            $image_file = $dest_row['destination_image'];
            $site_name_display = $dest_row['site_name'];

  
            $delete_sql = "DELETE FROM destinations WHERE destination_id = '$clean_target_id' LIMIT 1";
            if ($conn->query($delete_sql)) {
                // Delete cover photo file if it's not the default image
                if (!empty($image_file) && $image_file !== 'images/default-destination.jpg' && file_exists($image_file)) {
                    @unlink($image_file);
                }
                $success_message = "Destination landmark \"" . htmlspecialchars($site_name_display) . "\" (ID: " . htmlspecialchars($target_dest_id) . ") has been deleted successfully.";
            } else {
                $error_message = "Database error: Unable to delete destination. " . $conn->error;
            }
        } else {
            $error_message = "Destination ID not found or already deleted.";
        }
    }
}


$sql = "SELECT * FROM destinations ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Delete Destinations - HeritageLink Admin</title>

 
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


  <link rel="stylesheet" href="index.css">
  <link rel="stylesheet" href="admin-dashboard.css">
  <link rel="stylesheet" href="admin-destinations-delete.css">
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


  <main class="delete-destinations-wrapper">
    <div class="page-container-wide">

    
      <div class="delete-header-box">
        <h1 class="delete-page-title">Delete Heritage Destinations</h1>
    
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
            placeholder="Search destinations by site name..." 
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
                <th>Site Name & ID</th>
                <th>Location / District</th>
                <th>Heritage Type</th>
                <th>Historical Period</th>
                <th>Status</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody id="destinations-table-body">
              <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): 
                  $status_class = 'status-active';
                  $status_lower = strtolower($row['status']);
                  if ($status_lower === 'under maintenance') $status_class = 'status-maintenance';
                  elseif ($status_lower === 'closed') $status_class = 'status-closed';

                  $image_path = !empty($row['destination_image']) ? $row['destination_image'] : 'images/default-destination.jpg';
                ?>
                  <tr 
                    class="destination-data-row"
                    data-id="<?php echo htmlspecialchars($row['destination_id']); ?>"
                    data-site-name="<?php echo htmlspecialchars($row['site_name']); ?>"
                    data-district="<?php echo htmlspecialchars($row['district']); ?>"
                    data-division="<?php echo htmlspecialchars($row['division']); ?>"
                    data-type="<?php echo htmlspecialchars($row['heritage_type']); ?>"
                    data-period="<?php echo htmlspecialchars($row['historical_period']); ?>"
                    data-status="<?php echo htmlspecialchars($row['status']); ?>"
                  >
            
                    <td>
                      <img src="<?php echo htmlspecialchars($image_path); ?>" alt="<?php echo htmlspecialchars($row['site_name']); ?>" class="dest-thumb" />
                    </td>

            
                    <td>
                      <div class="dest-info-cell">
                        <strong class="site-name-text"><?php echo htmlspecialchars($row['site_name']); ?></strong>
                        <span class="dest-id-tag"><?php echo htmlspecialchars($row['destination_id']); ?></span>
                      </div>
                    </td>

                 
                    <td>
                      <div class="location-box">
                        <i class="fa-solid fa-location-dot location-icon"></i>
                        <span><?php echo htmlspecialchars($row['district'] . ', ' . $row['division']); ?></span>
                      </div>
                    </td>

                  
                    <td>
                      <span class="badge type-badge"><?php echo htmlspecialchars($row['heritage_type']); ?></span>
                    </td>

                
                    <td>
                      <span class="period-text"><i class="fa-solid fa-landmark"></i> <?php echo htmlspecialchars($row['historical_period']); ?></span>
                    </td>

                    <td>
                      <span class="status-pill <?php echo $status_class; ?>">
                        <?php echo htmlspecialchars($row['status']); ?>
                      </span>
                    </td>

                   
                    <td class="text-center">
                      <form method="POST" action="admin-destinations-delete.php" class="delete-form" onsubmit="return confirmDeleteDest('<?php echo htmlspecialchars($row['destination_id']); ?>', '<?php echo htmlspecialchars(addslashes($row['site_name'])); ?>');">
                        <input type="hidden" name="delete_destination_id" value="<?php echo htmlspecialchars($row['destination_id']); ?>">
                        <button type="submit" class="btn-delete" title="Permanently Delete Destination">
                          <i class="fa-solid fa-trash-can"></i> Delete
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr id="empty-db-row">
                  <td colspan="7" class="no-data-cell">
                    <i class="fa-solid fa-folder-open empty-icon"></i>
                    <p>No destinations found in the database.</p>
                  </td>
                </tr>
              <?php endif; ?>

         
              <tr id="no-match-row" style="display: none;">
                <td colspan="7" class="no-data-cell">
                  <i class="fa-solid fa-magnifying-glass empty-icon"></i>
                  <p>No destinations match your search criteria.</p>
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

  <script src="admin-destinations-delete.js"></script>
</body>
</html>