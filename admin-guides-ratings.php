<?php
session_start();
require_once 'db.php';


if (empty($_SESSION['admin_logged_in'])) {
    header("Location: admin-login.php");
    exit;
}


$platform_stats_sql = "SELECT COUNT(id) AS total_reviews, AVG(rating) AS overall_avg FROM reviews";
$platform_stats_res = $conn->query($platform_stats_sql);
$platform_total_reviews = 0;
$platform_overall_avg = 0.0;
if ($platform_stats_res && $row = $platform_stats_res->fetch_assoc()) {
    $platform_total_reviews = (int)$row['total_reviews'];
    $platform_overall_avg = !empty($row['overall_avg']) ? round((float)$row['overall_avg'], 1) : 0.0;
}


$guides_sql = "SELECT 
                g.guide_id, g.full_name, g.profile_photo, g.working_division,
                COUNT(r.id) AS review_count,
                AVG(r.rating) AS avg_rating
               FROM guides g
               LEFT JOIN reviews r ON g.guide_id = r.guide_id
               GROUP BY g.guide_id
               ORDER BY avg_rating DESC, review_count DESC, g.full_name ASC";
$guides_res = $conn->query($guides_sql);


$reviews_sql = "SELECT 
                    r.id, r.booking_id, r.guide_id, r.rating, r.review_text, r.created_at AS review_date,
                    c.name AS customer_name, c.email AS customer_email,
                    b.site_name, b.booking_date
                FROM reviews r
                LEFT JOIN customers c ON r.customer_id = c.id
                LEFT JOIN bookings b ON r.booking_id = b.booking_id
                ORDER BY r.created_at DESC";
$reviews_res = $conn->query($reviews_sql);

// Group reviews by guide_id
$guide_reviews = [];
if ($reviews_res && $reviews_res->num_rows > 0) {
    while ($rev = $reviews_res->fetch_assoc()) {
        $guide_reviews[$rev['guide_id']][] = $rev;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Tour Guide Ratings &amp; Reviews - HeritageLink Admin</title>


  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">


  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <link rel="stylesheet" href="index.css">
  <link rel="stylesheet" href="admin-dashboard.css">
  <link rel="stylesheet" href="admin-guides-ratings.css">
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

  <main class="ratings-page-wrapper">
    <div class="page-container-wide">

      <div class="ratings-header-box">
        <h1 class="ratings-page-title">Tour Guides Ratings &amp; Reviews</h1>
      </div>


      <div class="ratings-metrics-row">
        <div class="metrics-card">
          <span class="metrics-value"><i class="fa-solid fa-id-badge"></i> <?php echo ($guides_res) ? $guides_res->num_rows : 0; ?></span>
          <span class="metrics-label">Total Tour Guides</span>
        </div>
        <div class="metrics-card">
          <span class="metrics-value text-gold"><i class="fa-solid fa-star"></i> <?php echo number_format($platform_overall_avg, 1); ?> / 5.0</span>
          <span class="metrics-label">Platform Average Rating</span>
        </div>
        <div class="metrics-card">
          <span class="metrics-value text-navy"><i class="fa-solid fa-comments"></i> <?php echo $platform_total_reviews; ?></span>
          <span class="metrics-label">Customer Reviews Submitted</span>
        </div>
      </div>

      <div class="search-filter-card">
        <div class="search-input-wrap">
          <i class="fa-solid fa-magnifying-glass search-icon"></i>
          <input 
            type="text" 
            id="guide-ratings-search" 
            placeholder="Search by Guide Name, Guide ID, Landmark Site, or Reviewer Name..." 
            autocomplete="off"
          />
        </div>
      </div>


      <div class="guides-ratings-stack" id="guides-ratings-container">
        <?php if ($guides_res && $guides_res->num_rows > 0): ?>
          <?php while ($guide = $guides_res->fetch_assoc()): 
            $gid = $guide['guide_id'];
            $guide_photo = (!empty($guide['profile_photo']) && file_exists($guide['profile_photo'])) ? $guide['profile_photo'] : 'images/default-avatar.png';
            $rev_count = (int)$guide['review_count'];
            $avg_val = !empty($guide['avg_rating']) ? round((float)$guide['avg_rating'], 1) : 0.0;
            $reviews_list = $guide_reviews[$gid] ?? [];


            $comments_text = "";
            foreach ($reviews_list as $r) {
                $comments_text .= " " . ($r['customer_name'] ?? '') . " " . ($r['site_name'] ?? '') . " " . ($r['review_text'] ?? '');
            }
            $search_blob = strtolower(trim($guide['full_name'] . " " . $gid . " " . $comments_text));
          ?>
            <article class="guide-rating-card" data-search="<?php echo htmlspecialchars($search_blob); ?>">
              
      
              <div class="guide-rating-header">
                <div class="guide-ident-block">
                  <img src="<?php echo htmlspecialchars($guide_photo); ?>" alt="<?php echo htmlspecialchars($guide['full_name']); ?>" class="guide-avatar" />
                  <div class="guide-meta-box">
                    <div class="guide-name-row">
                      <h2><?php echo htmlspecialchars($guide['full_name']); ?></h2>
                      <span class="guide-code-tag"><?php echo htmlspecialchars($gid); ?></span>
                    </div>
                  </div>
                </div>

          
                <div class="guide-score-box">
                  <div class="score-stars-row">
                    <span class="score-number"><?php echo ($rev_count > 0) ? number_format($avg_val, 1) : 'N/A'; ?></span>
                    <div class="stars-icons">
                      <?php 
                        $rounded_star = round($avg_val);
                        for ($s = 1; $s <= 5; $s++) {
                            if ($s <= $rounded_star && $rev_count > 0) {
                                echo '<i class="fa-solid fa-star filled-star"></i>';
                            } else {
                                echo '<i class="fa-regular fa-star empty-star"></i>';
                            }
                        }
                      ?>
                    </div>
                  </div>
                  <span class="review-counter-label">
                    <i class="fa-regular fa-comment-dots"></i> <?php echo $rev_count; ?> customer review<?php echo ($rev_count === 1) ? '' : 's'; ?>
                  </span>
                </div>
              </div>


              <div class="reviews-list-section">
                <h3 class="reviews-section-title">
                  <i class="fa-solid fa-comments"></i> Customer Feedback &amp; Reviews
                </h3>

                <?php if (!empty($reviews_list)): ?>
                  <div class="comments-grid">
                    <?php foreach ($reviews_list as $rev): 
                      $rating_num = (int)$rev['rating'];
                      $rev_date = date("M d, Y", strtotime($rev['review_date']));
                      $site_booked = !empty($rev['site_name']) ? $rev['site_name'] : 'Heritage Tour';
                    ?>
                      <div class="customer-comment-card">
                        <div class="comment-card-top">
                          <div class="reviewer-ident">
                            <i class="fa-solid fa-circle-user reviewer-icon"></i>
                            <div>
                              <strong><?php echo htmlspecialchars($rev['customer_name'] ?? 'Traveler'); ?></strong>
                              <span class="comment-site"><i class="fa-solid fa-landmark-dome"></i> <?php echo htmlspecialchars($site_booked); ?></span>
                            </div>
                          </div>
                          <div class="review-score-date">
                            <div class="individual-stars">
                              <?php for ($st = 1; $st <= 5; $st++): ?>
                                <i class="fa-star <?php echo ($st <= $rating_num) ? 'fa-solid filled-star' : 'fa-regular empty-star'; ?>"></i>
                              <?php endfor; ?>
                            </div>
                            <span class="comment-date"><?php echo htmlspecialchars($rev_date); ?></span>
                          </div>
                        </div>

                        <div class="comment-body">
                          <p><?php echo !empty($rev['review_text']) ? nl2br(htmlspecialchars($rev['review_text'])) : 'No written comments provided.'; ?></p>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php else: ?>
                  <div class="no-reviews-box">
                    <i class="fa-regular fa-star"></i>
                    <span>No customer reviews have been submitted for this tour guide yet.</span>
                  </div>
                <?php endif; ?>
              </div>

            </article>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="no-guides-found-card">
            <i class="fa-solid fa-user-slash"></i>
            <h2>No Guides Found</h2>
            <p>There are currently no tour guides registered in the system.</p>
          </div>
        <?php endif; ?>
      </div>


      <div id="no-search-results" class="no-guides-found-card" style="display: none;">
        <i class="fa-solid fa-magnifying-glass"></i>
        <h2>No Matching Guide Reviews</h2>
        <p>No guides or customer reviews match your search query. Please try searching by another guide name, site, or reviewer name.</p>
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

  <script src="admin-guides-ratings.js"></script>
</body>
</html>