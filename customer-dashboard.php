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

$cust_stmt = $conn->prepare("SELECT name, email, created_at FROM customers WHERE id = ? LIMIT 1");
$cust_stmt->bind_param("i", $customer_id);
$cust_stmt->execute();
$cust_res = $cust_stmt->get_result();
$customer = $cust_res->fetch_assoc();

$display_name = !empty($customer['name']) ? $customer['name'] : (!empty($_SESSION['customer_name']) ? $_SESSION['customer_name'] : 'Tourist');

$page_title = "Tourist Dashboard";
$extra_css  = "customer-dashboard.css";
require_once 'header.php';
?>

  <main class="cust-dashboard-wrapper">
    <div class="page-container">

      <div class="cust-header-box">
        <h1 class="cust-dashboard-title">Tourist Dashboard</h1>
        <p class="cust-greeting-text">Hello, <span><?php echo htmlspecialchars($display_name); ?></span>!</p>
        
      </div>

      <div class="dashboard-sections-stack">

        <section class="dashboard-category-card">
          <div class="category-card-header">
            <div class="icon-title-wrap">
              <div class="section-icon-box"><i class="fa-solid fa-id-card"></i></div>
              <h2>My Profile &amp; Settings</h2>
            </div>
          </div>
          <div class="subcards-grid one-col">
            <a href="customer-profile-view.php" class="dashboard-subcard">
              <i class="fa-solid fa-user-gear"></i>
              <h3>View &amp; Edit Profile Info</h3>
              <p>Update your full name, registered email address, phone, NID & personal emergency contacts</p>
            </a>
          </div>
        </section>


        <section class="dashboard-category-card">
          <div class="category-card-header">
            <div class="icon-title-wrap">
              <div class="section-icon-box"><i class="fa-solid fa-calendar-check"></i></div>
              <h2>My Bookings & Schedules</h2>
            </div>
          </div>
          <div class="subcards-grid one-col">
            <a href="customer-booking-history.php" class="dashboard-subcard">
              <i class="fa-solid fa-clock-rotate-left"></i>
              <h3>View Booking History</h3>
              <p>Browse past, current & upcoming heritage excursion records</p>
            </a>
           
            </a>
          </div>
        </section>

  
        <section class="dashboard-category-card">
          <div class="category-card-header">
            <div class="icon-title-wrap">
              <div class="section-icon-box"><i class="fa-solid fa-star"></i></div>
              <h2>Tour Guides & Feedback</h2>
            </div>
          </div>
          <div class="subcards-grid one-col">
            <a href="customer-rate-guide.php" class="dashboard-subcard">
              <i class="fa-solid fa-award"></i>
              <h3>Rate My Guides</h3>
              <p>Leave star ratings & share authentic feedback on your tour guide experience</p>
            </a>
          </div>
        </section>

      </div>

    </div>
  </main>

<?php require_once 'footer.php'; ?>