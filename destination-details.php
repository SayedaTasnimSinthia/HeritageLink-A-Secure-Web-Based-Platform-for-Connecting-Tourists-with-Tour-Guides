<?php
require_once 'db.php';

$dest        = null;
$error_title = "";
$error_desc  = "";
$has_guides  = false;
$is_active   = false;
$photo       = 'images/default-destination.jpg';

$dest_id   = isset($_GET['id']) ? trim($_GET['id']) : '';
$dest_name = isset($_GET['name']) ? trim($_GET['name']) : '';

if (!empty($dest_id)) {
    $clean_id = $conn->real_escape_string($dest_id);
    $query    = "SELECT * FROM destinations WHERE destination_id = '$clean_id' LIMIT 1";
    $res      = $conn->query($query);
    if ($res && $res->num_rows > 0) {
        $dest = $res->fetch_assoc();
    }
} elseif (!empty($dest_name)) {
    $normalized_name = str_replace('-', ' ', $dest_name);
    $clean_name      = $conn->real_escape_string($normalized_name);
    $query           = "SELECT * FROM destinations WHERE site_name LIKE '%$clean_name%' LIMIT 1";
    $res             = $conn->query($query);
    if ($res && $res->num_rows > 0) {
        $dest = $res->fetch_assoc();
    }
}

if (!$dest) {
    $error_title = "Destination Not Found";
    $error_desc  = "The historical landmark you are trying to view does not exist or may have been removed.";
} else {
    $is_active = (($dest['status'] ?? '') === 'Active');
    $photo     = !empty($dest['destination_image']) ? $dest['destination_image'] : 'images/default-destination.jpg';

    $clean_site_name = $conn->real_escape_string($dest['site_name']);
    $guide_check_sql = "SELECT 1 FROM guides WHERE account_status = 'Active' AND heritage_sites LIKE '%$clean_site_name%' LIMIT 1";
    $guide_check_res = $conn->query($guide_check_sql);
    $has_guides      = ($guide_check_res && $guide_check_res->num_rows > 0);
}

$page_title = $dest ? $dest['site_name'] : "Destination Details";
$extra_css  = "destination-details.css";
require_once 'header.php';
?>

  <main class="destination-details-wrapper">
    <div class="page-container">

      <?php if ($dest): ?>
        <div class="details-header-box">
          <span class="details-category-tag"><?php echo htmlspecialchars($dest['heritage_type']); ?></span>
          <h1 class="details-page-title"><?php echo htmlspecialchars($dest['site_name']); ?></h1>
          <p class="details-location-sub">
            <i class="fa-solid fa-location-dot"></i> 
            <?php echo htmlspecialchars($dest['location'] . ', ' . $dest['district'] . ', ' . $dest['division'] . ' Division'); ?>
          </p>
        </div>

        <div class="details-showcase-grid">

          <div class="details-media-column">
            <div class="spotlight-image-card">
              <img src="<?php echo htmlspecialchars($photo); ?>" alt="<?php echo htmlspecialchars($dest['site_name']); ?>" class="spotlight-img" />
              
              <div class="spotlight-badges">
                <span class="spotlight-district-badge">
                  <i class="fa-solid fa-map-pin"></i> <?php echo htmlspecialchars($dest['district']); ?>
                </span>
                
                <?php if ($dest['status'] === 'Active'): ?>
                  <span class="spotlight-status-badge status-open"><i class="fa-solid fa-circle-check"></i> Open to Public</span>
                <?php elseif ($dest['status'] === 'Under Maintenance'): ?>
                  <span class="spotlight-status-badge status-maintenance"><i class="fa-solid fa-triangle-exclamation"></i> Under Maintenance</span>
                <?php else: ?>
                  <span class="spotlight-status-badge status-closed"><i class="fa-solid fa-circle-xmark"></i> Temporarily Closed</span>
                <?php endif; ?>
              </div>
            </div>

            <div class="guide-action-box">
              <div class="guide-status-row <?php echo $has_guides ? 'status-has-guides' : 'status-no-guides'; ?>">
                <i class="fa-solid <?php echo $has_guides ? 'fa-circle-check' : 'fa-circle-info'; ?>"></i>
                <div>
                  <strong><?php echo $has_guides ? 'Tour Guides Available' : 'No Guides Available'; ?></strong>
                  <p><?php echo $has_guides ? 'Verified heritage guides are scheduled for this site.' : 'No certified guides are currently assigned to this location.'; ?></p>
                </div>
              </div>

              <div class="action-btn-container">
                <?php if ($has_guides): ?>
                  <?php $guides_url = 'guides.php?site=' . urlencode($dest['site_name']); ?>
                  <a href="<?php echo htmlspecialchars($guides_url); ?>" class="btn-action-guides">
                    <i class="fa-solid fa-id-badge"></i> View Available Guides
                  </a>
                <?php else: ?>
                  <button type="button" class="btn-action-guides btn-disabled" disabled title="No guides available for this destination">
                    <i class="fa-solid fa-ban"></i> No Guides Available
                  </button>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="details-content-column">
            
            <div class="meta-spec-card">
              <h2 class="spec-card-title">Key Landmark Information</h2>
              <div class="spec-grid">
                
                <div class="spec-item">
                  <div class="spec-icon-box"><i class="fa-solid fa-landmark"></i></div>
                  <div class="spec-text">
                    <span class="spec-label">Historical Period</span>
                    <strong class="spec-value"><?php echo htmlspecialchars($dest['historical_period']); ?></strong>
                  </div>
                </div>

                <div class="spec-item">
                  <div class="spec-icon-box"><i class="fa-regular fa-calendar-days"></i></div>
                  <div class="spec-text">
                    <span class="spec-label">Built Year / Era</span>
                    <strong class="spec-value"><?php echo htmlspecialchars(!empty($dest['built_year']) ? $dest['built_year'] : 'Historical Era'); ?></strong>
                  </div>
                </div>

                <div class="spec-item">
                  <div class="spec-icon-box"><i class="fa-solid fa-layer-group"></i></div>
                  <div class="spec-text">
                    <span class="spec-label">Heritage Type</span>
                    <strong class="spec-value"><?php echo htmlspecialchars($dest['heritage_type']); ?></strong>
                  </div>
                </div>

                <div class="spec-item">
                  <div class="spec-icon-box"><i class="fa-regular fa-clock"></i></div>
                  <div class="spec-text">
                    <span class="spec-label">Visiting Hours</span>
                    <strong class="spec-value"><?php echo htmlspecialchars($dest['opening_hours']); ?></strong>
                  </div>
                </div>

                <div class="spec-item full-width">
                  <div class="spec-icon-box"><i class="fa-solid fa-ticket"></i></div>
                  <div class="spec-text">
                    <span class="spec-label">Entry Fee Details</span>
                    <strong class="spec-value"><?php echo htmlspecialchars($dest['entry_fee']); ?></strong>
                  </div>
                </div>

              </div>
            </div>

            <div class="history-card">
              <h2>Historical Significance &amp; Overview</h2>
              <div class="history-narrative">
                <?php 
                  $paragraphs = explode("\n", str_replace("\r", "", $dest['description']));
                  foreach ($paragraphs as $p) {
                      $trimmed = trim($p);
                      if (!empty($trimmed)) {
                          echo "<p>" . htmlspecialchars($trimmed) . "</p>";
                      }
                  }
                ?>
              </div>
            </div>

          </div>

        </div>

      <?php else: ?>
        <div class="not-found-card">
          <i class="fa-solid fa-landmark-flag not-found-icon"></i>
          <h2><?php echo htmlspecialchars($error_title); ?></h2>
          <p><?php echo htmlspecialchars($error_desc); ?></p>
          <a href="destinations.php" class="btn-go-catalog">Browse All Destinations</a>
        </div>
      <?php endif; ?>

    </div>
  </main>

<?php require_once 'footer.php'; ?>