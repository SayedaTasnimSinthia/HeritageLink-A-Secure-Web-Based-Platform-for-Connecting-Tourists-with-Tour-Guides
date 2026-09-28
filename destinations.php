<?php
require_once 'db.php';

$page_title = "Heritage Destinations";
$extra_css = "destinations.css";
require_once 'header.php';

$sql = "SELECT d.*, 
        EXISTS(
            SELECT 1 FROM guides g 
            WHERE g.account_status = 'Active' 
            AND g.heritage_sites LIKE CONCAT('%', d.site_name, '%')
        ) AS has_active_guides
        FROM destinations d 
        ORDER BY CASE WHEN d.status = 'Active' THEN 1 ELSE 2 END, d.site_name ASC";

$result = $conn->query($sql);
?>

  <section class="destinations-hero">
    <div class="page-container">
      <h1>Historical Landmarks &amp; Heritage Sites</h1>
    </div>
  </section>

  <main class="destinations-main">
    <div class="page-container">

      <div class="dest-search-bar-wrap">
        <div class="dest-search-input-box">
          <i class="fa-solid fa-magnifying-glass search-icon"></i>
          <input 
            type="text" 
            id="dest-live-search" 
            placeholder="Search landmarks by Site Name or District..." 
            autocomplete="off"
          />
        </div>
      </div>

      <div class="dest-catalog-grid" id="dest-catalog-grid">
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($dest = $result->fetch_assoc()): 
            $photo = !empty($dest['destination_image']) ? $dest['destination_image'] : 'images/default-destination.jpg';
            $is_active = ($dest['status'] === 'Active');
            $has_guides = ((int)$dest['has_active_guides'] === 1);
          ?>
            <article 
              class="dest-item-card"
              data-site-name="<?php echo htmlspecialchars(strtolower($dest['site_name'])); ?>"
              data-district="<?php echo htmlspecialchars(strtolower($dest['district'])); ?>"
            >
              <div class="dest-card-media">
                <img src="<?php echo htmlspecialchars($photo); ?>" alt="<?php echo htmlspecialchars($dest['site_name']); ?>" loading="lazy" />
                <span class="dest-badge-district"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($dest['district']); ?></span>
                
                <?php if (!$is_active): ?>
                  <span class="dest-badge-maintenance"><?php echo htmlspecialchars($dest['status']); ?></span>
                <?php endif; ?>
              </div>

              <div class="dest-card-content">
                <div class="dest-card-top">
                  <div class="dest-category-tag"><?php echo htmlspecialchars($dest['heritage_type']); ?></div>
                  <h3 class="dest-title" title="<?php echo htmlspecialchars($dest['site_name']); ?>">
                    <?php echo htmlspecialchars($dest['site_name']); ?>
                  </h3>
                  
                  <p class="dest-summary" title="<?php echo htmlspecialchars($dest['description']); ?>">
                    <?php echo htmlspecialchars($dest['description']); ?>
                  </p>
                </div>

                <div class="dest-card-bottom">
                 
                  <div class="dest-meta-list">
                    <div class="meta-item">
                      <i class="fa-regular fa-calendar-check meta-icon"></i>
                      <span>Period: <strong><?php echo htmlspecialchars($dest['historical_period']); ?></strong></span>
                    </div>
                    <div class="meta-item">
                      <i class="fa-regular fa-clock meta-icon"></i>
                      <span>Hours: <?php echo htmlspecialchars($dest['opening_hours']); ?></span>
                    </div>
                    <div class="meta-item">
                      <i class="fa-solid fa-ticket meta-icon"></i>
                      <span>Entry: <?php echo htmlspecialchars($dest['entry_fee']); ?></span>
                    </div>
                  </div>

                  <div class="dest-guide-indicator <?php echo $has_guides ? 'guide-yes' : 'guide-no'; ?>">
                    <i class="fa-solid <?php echo $has_guides ? 'fa-user-check' : 'fa-user-slash'; ?>"></i>
                    <span><?php echo $has_guides ? 'Tour Guides Available On-Site' : 'Self-Guided Exploration'; ?></span>
                  </div>

                  <div class="dest-card-actions">
                    <a href="destination-details.php?id=<?php echo urlencode($dest['destination_id']); ?>" class="btn-dest-details">
                      <i class="fa-solid fa-circle-info"></i> View Details
                    </a>

                    <?php if ($has_guides): ?>
                      <a href="guides.php?site=<?php echo urlencode($dest['site_name']); ?>" class="btn-dest-guides">
                        <i class="fa-solid fa-id-badge"></i> View Guides
                      </a>
                    <?php else: ?>
                      <button type="button" class="btn-dest-guides btn-disabled" disabled title="No guides are currently available for this destination">
                        <i class="fa-solid fa-ban"></i> No Guides
                      </button>
                    <?php endif; ?>
                  </div>
                </div>

              </div>
            </article>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="no-dest-found">
            <i class="fa-solid fa-landmark-dome"></i>
            <h3>No Destinations Found</h3>
            <p>Destinations will appear here once registered by the administrator.</p>
          </div>
        <?php endif; ?>
      </div>

      <div id="no-search-match" class="no-dest-found" style="display: none;">
        <i class="fa-solid fa-magnifying-glass"></i>
        <h3>No Matching Landmarks</h3>
        <p>No heritage destinations found matching that Site Name or District.</p>
      </div>

    </div>
  </main>

  <script src="destinations.js"></script>

<?php require_once 'footer.php'; ?>