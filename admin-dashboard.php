<?php
session_start();
require_once 'db.php';

if (empty($_SESSION['admin_logged_in'])) {
    header("Location: admin-login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard - HeritageLink</title>


  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


  <link rel="stylesheet" href="index.css">
  <link rel="stylesheet" href="admin-dashboard.css">

</head>
<body>

  <header>
    <div class="nav-bar">
      <a href="admin-dashboard.php" class="logo">Heritage<span>Link</span></a>
      <div class="admin-nav-actions">
        <span class="admin-user-badge"><i class="fa-solid fa-user-shield"></i> Admin</span>
        <a href="admin-logout.php" class="btn-admin-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
      </div>
    </div>
  </header>


  <main class="dashboard-wrapper">
    <div class="page-container">


      <div class="dashboard-header-box">
        <h1 class="dashboard-title">Admin Dashboard</h1>
      </div>


      <div class="dashboard-sections-stack">

        <section class="admin-section-card">
          <div class="section-card-header">
            <div class="icon-title-wrap">
              <div class="section-icon-box"><i class="fa-solid fa-id-badge"></i></div>
              <h2>Manage Guides</h2>
            </div>
          </div>
          <div class="subcards-grid five-cols">
            <a href="admin-guides-view.php" class="admin-subcard">
              <i class="fa-solid fa-eye"></i>
              <h3>View Guides Info</h3>
              <p>Browse all tour guide profiles</p>
            </a>
            <a href="admin-guides-add.php" class="admin-subcard">
              <i class="fa-solid fa-user-plus"></i>
              <h3>Add New Guide</h3>
              <p>Register new heritage tour experts</p>
            </a>
            <a href="admin-guides-modify.php" class="admin-subcard">
              <i class="fa-solid fa-pen-to-square"></i>
              <h3>Modify Guide Info</h3>
              <p>Update guide details</p>
            </a>
            <a href="admin-guides-delete.php" class="admin-subcard delete-card">
              <i class="fa-solid fa-trash-can"></i>
              <h3>Delete Guide</h3>
              <p>Remove guide profiles</p>
            </a>
            <a href="admin-guides-ratings.php" class="admin-subcard">
              <i class="fa-solid fa-star"></i>
              <h3>Guides Rating</h3>
              <p>Inspect customer star reviews & ratings</p>
            </a>
          </div>
        </section>

 
        <section class="admin-section-card">
          <div class="section-card-header">
            <div class="icon-title-wrap">
              <div class="section-icon-box"><i class="fa-solid fa-calendar-days"></i></div>
              <h2>Manage Guide Availability</h2>
            </div>
          </div>
          <div class="subcards-grid">
            <a href="admin-guides-availability-view.php" class="admin-subcard">
              <i class="fa-solid fa-calendar-check"></i>
              <h3>View Availability</h3>
              <p>Check guide schedules</p>
            </a>
            <a href="admin-guides-availability-add.php" class="admin-subcard">
              <i class="fa-solid fa-calendar-plus"></i>
              <h3>Add Available Dates</h3>
              <p>Assign open dates & working districts</p>
            </a>
            <a href="admin-guides-availability-modify.php" class="admin-subcard">
              <i class="fa-solid fa-calendar-week"></i>
              <h3>Modify Shifts</h3>
              <p>Reschedule or edit shift timings for guides</p>
            </a>
            <a href="admin-guides-availability-delete.php" class="admin-subcard delete-card">
              <i class="fa-solid fa-calendar-xmark"></i>
              <h3>Delete Dates</h3>
              <p>Remove unavailable slots & mark leaves</p>
            </a>
          </div>
        </section>

       
        <section class="admin-section-card">
          <div class="section-card-header">
            <div class="icon-title-wrap">
              <div class="section-icon-box"><i class="fa-solid fa-landmark-dome"></i></div>
              <h2>Manage Destinations Info</h2>
            </div>
          </div>
          <div class="subcards-grid">
            <a href="admin-destinations-view.php" class="admin-subcard">
              <i class="fa-solid fa-monument"></i>
              <h3>View Destinations</h3>
              <p>See current landmarks, ticket info & districts</p>
            </a>
            <a href="admin-destinations-add.php" class="admin-subcard">
              <i class="fa-solid fa-map-pin"></i>
              <h3>Add New Site</h3>
              <p>Upload new historical landmarks & its descriptions</p>
            </a>
            <a href="admin-destinations-modify.php" class="admin-subcard">
              <i class="fa-solid fa-file-pen"></i>
              <h3>Modify Site Info</h3>
              <p>Edit images, historical details & visitor timings</p>
            </a>
            <a href="admin-destinations-delete.php" class="admin-subcard delete-card">
              <i class="fa-solid fa-trash"></i>
              <h3>Delete Site Info</h3>
              <p>Remove inactive or inaccessible heritage destinations</p>
            </a>
          </div>
        </section>


        <section class="admin-section-card">
          <div class="section-card-header">
            <div class="icon-title-wrap">
              <div class="section-icon-box"><i class="fa-solid fa-users"></i></div>
              <h2>Manage Users</h2>
            </div>
          </div>
          <div class="subcards-grid three-cols">
            <a href="admin-view-profiles.php" class="admin-subcard">
              <i class="fa-solid fa-address-card"></i>
              <h3>View Tourist Profiles</h3>
              <p>Inspect registered user accounts, contact details & verification status</p>
            </a>
            <a href="admin-user-booking-history.php" class="admin-subcard">
              <i class="fa-solid fa-clock-rotate-left"></i>
              <h3>View Booking History of a User</h3>
              <p>Review individual travel records, booked landmarks & payment transactions</p>
            </a>
            <a href="admin-newsletter-subscribers.php" class="admin-subcard">
              <i class="fa-solid fa-envelope-open-text"></i>
              <h3>View Newsletter Subscriber List</h3>
              <p>Browse email addresses subscribed to promotional updates & bulletins</p>
            </a>
          </div>
        </section>

  
        <section class="admin-section-card">
          <div class="section-card-header">
            <div class="icon-title-wrap">
              <div class="section-icon-box"><i class="fa-solid fa-clipboard-list"></i></div>
              <h2>Manage Bookings</h2>
            </div>
          </div>
          <div class="subcards-grid one-col">
            <a href="admin-manage-bookings.php" class="admin-subcard">
              <i class="fa-solid fa-tasks"></i>
              <h3>Manage All Bookings</h3>
              <p>View reservations, approve tour assignments, handle cancellations & mark completed excursions</p>
            </a>
          </div>
        </section>

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

</body>
</html>