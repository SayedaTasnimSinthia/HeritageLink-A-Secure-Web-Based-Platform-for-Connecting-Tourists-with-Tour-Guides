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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_rating') {
    $booking_id  = trim($_POST['booking_id'] ?? '');
    $guide_id    = trim($_POST['guide_id'] ?? '');
    $rating      = (int)($_POST['rating'] ?? 0);
    $review_text = trim($_POST['review_text'] ?? '');

    $check_stmt = $conn->prepare("SELECT id FROM bookings WHERE booking_id = ? AND customer_id = ? AND status = 'Completed' LIMIT 1");
    $check_stmt->bind_param("si", $booking_id, $customer_id);
    $check_stmt->execute();
    $valid_booking = $check_stmt->get_result()->fetch_assoc();

    if (!$valid_booking) {
        $error_msg = "You can only rate tours that have been marked as Completed.";
    } elseif ($rating < 1 || $rating > 5) {
        $error_msg = "Please select a valid star rating from 1 to 5.";
    } else {
      
        $rev_check = $conn->prepare("SELECT id FROM reviews WHERE booking_id = ? LIMIT 1");
        $rev_check->bind_param("s", $booking_id);
        $rev_check->execute();

        if ($rev_check->get_result()->num_rows > 0) {
            $error_msg = "Reviews are permanent and cannot be modified once submitted.";
        } else {
            // Insert review for the first and only time
            $ins = $conn->prepare("INSERT INTO reviews (booking_id, customer_id, guide_id, rating, review_text) VALUES (?, ?, ?, ?, ?)");
            $ins->bind_param("sisis", $booking_id, $customer_id, $guide_id, $rating, $review_text);
            if ($ins->execute()) {
                $success_msg = "Thank you! Your rating and feedback have been recorded.";
            } else {
                $error_msg = "Error submitting rating. Please try again.";
            }
        }
    }
}

$sql = "SELECT 
            b.booking_id, b.site_name, b.booking_date, b.time_slot, b.status AS booking_status,
            g.guide_id, g.full_name AS guide_name, g.profile_photo AS guide_photo,
            ga.working_district,
            r.rating AS existing_rating, r.review_text AS existing_review, r.created_at AS review_date
        FROM bookings b
        INNER JOIN guides g ON b.guide_id = g.guide_id
        LEFT JOIN guide_availability ga ON b.availability_id = ga.availability_id
        LEFT JOIN reviews r ON b.booking_id = r.booking_id
        WHERE b.customer_id = ? AND b.status = 'Completed'
        ORDER BY b.booking_date DESC, b.id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$res = $stmt->get_result();

$completed_trips = [];
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $completed_trips[] = $row;
    }
}

$page_title = "Rate My Guides";
$extra_css  = "customer-rate-guide.css";
require_once 'header.php';
?>

  <main class="rate-guide-wrapper">
    <div class="page-container">

      <div class="rate-header-box">
        <h1 class="rate-main-title">Rate Your Tour Guides</h1>
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

      <div class="rate-cards-stack">
        <?php if (!empty($completed_trips)): ?>
          <?php foreach ($completed_trips as $trip): 
            $guide_avatar = (!empty($trip['guide_photo']) && file_exists($trip['guide_photo'])) ? $trip['guide_photo'] : 'images/default-avatar.png';
            $tour_date = date("D, d M Y", strtotime($trip['booking_date']));
            $has_review = !is_null($trip['existing_rating']);
            $saved_rating = (int)$trip['existing_rating'];
            $submitted_date = !empty($trip['review_date']) ? date("d M Y, h:i A", strtotime($trip['review_date'])) : '';
          ?>
            <article class="rate-guide-card">
              
              <div class="rate-card-head">
                <div class="trip-ident">
                  <span class="booking-ref-badge"><i class="fa-solid fa-hashtag"></i> <?php echo htmlspecialchars($trip['booking_id']); ?></span>
                </div>
                <div class="tour-date-text">
                  <span>Tour Date: <strong><?php echo htmlspecialchars($tour_date); ?></strong> (<?php echo htmlspecialchars($trip['time_slot']); ?>)</span>
                </div>
              </div>

              <div class="rate-card-body">
        
                <div class="guide-summary-box">
                  <img src="<?php echo htmlspecialchars($guide_avatar); ?>" alt="<?php echo htmlspecialchars($trip['guide_name']); ?>" class="guide-avatar-img">
                  <div class="guide-summary-details">
                    <h3 class="guide-title"><?php echo htmlspecialchars($trip['guide_name']); ?> <i class="fa-solid fa-circle-check verified-icon" title="Certified Guide"></i></h3>
                    <span class="guide-code-tag"><?php echo htmlspecialchars($trip['guide_id']); ?></span>
                    
                    <div class="site-info-block">
                      <span class="site-tag"><i class="fa-solid fa-landmark-dome"></i> <?php echo htmlspecialchars($trip['site_name']); ?></span>
                    </div>
                  </div>
                </div>

                <div class="rating-display-container">
                  <?php if ($has_review): ?>
                    <div class="submitted-review-view">
                      <div class="submitted-header">
                        <span class="rated-badge"><i class="fa-solid fa-check-circle"></i> Feedback Submitted</span>
                        <?php if (!empty($submitted_date)): ?>
                          <span class="submitted-date">Rated on <?php echo htmlspecialchars($submitted_date); ?></span>
                        <?php endif; ?>
                      </div>

                      <div class="static-stars-row">
                        <div class="stars-display">
                          <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fa-star <?php echo ($i <= $saved_rating) ? 'fa-solid filled-star' : 'fa-regular empty-star'; ?>"></i>
                          <?php endfor; ?>
                        </div>
                        <span class="static-rating-score"><?php echo $saved_rating; ?> / 5 Stars</span>
                      </div>

                      <div class="static-comment-box">
                        <span class="comment-label">Your Review:</span>
                        <p class="comment-text">
                          <?php echo !empty($trip['existing_review']) ? nl2br(htmlspecialchars($trip['existing_review'])) : 'No written comments provided.'; ?>
                        </p>
                      </div>
                    </div>

                  <?php else: ?>

                    <form action="customer-rate-guide.php" method="POST" class="star-rating-form">
                      <input type="hidden" name="action" value="submit_rating">
                      <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($trip['booking_id']); ?>">
                      <input type="hidden" name="guide_id" value="<?php echo htmlspecialchars($trip['guide_id']); ?>">
                      <input type="hidden" name="rating" class="rating-value-input" value="0" required>

                      <div class="star-picker-wrap">
                        <span class="star-picker-label">Rate your experience:</span>
                        <div class="stars-group" data-current-rating="0">
                          <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fa-star star-btn fa-regular" data-val="<?php echo $i; ?>" title="<?php echo $i; ?> star<?php echo ($i > 1) ? 's' : ''; ?>"></i>
                          <?php endfor; ?>
                        </div>
                        <span class="rating-feedback-text">Click a star to rate</span>
                      </div>

                      <div class="review-input-wrap">
                        <label for="review_text_<?php echo htmlspecialchars($trip['booking_id']); ?>">Your Feedback &amp; Review (Optional):</label>
                        <textarea 
                          name="review_text" 
                          id="review_text_<?php echo htmlspecialchars($trip['booking_id']); ?>" 
                          rows="3" 
                          placeholder="Share highlights of your excursion, guide knowledge, communication..."
                        ></textarea>
                      </div>

                      <div class="rate-action-row">
                        <button type="submit" class="btn-submit-rating">
                          <i class="fa-solid fa-star"></i> Submit Rating
                        </button>
                      </div>
                    </form>
                  <?php endif; ?>
                </div>

              </div>

            </article>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="no-trips-card">
         
            <h2>No Completed Tours Yet</h2>
            <p>You can rate and share reviews for tour guides once your scheduled excursion has taken place and been marked as <strong>Completed</strong>.</p>
            
          </div>
        <?php endif; ?>
      </div>

    </div>
  </main>

  <script src="customer-rate-guide.js"></script>

<?php require_once 'footer.php'; ?>