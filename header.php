<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_logged_in = isset($_SESSION['customer_logged_in']) && $_SESSION['customer_logged_in'] === true;
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . " - HeritageLink" : "HeritageLink - Explore Bangladesh's Rich Heritage"; ?></title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <link rel="stylesheet" href="index.css">
  <?php if (isset($extra_css)): ?>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($extra_css); ?>">
  <?php endif; ?>
</head>
<body>

  <header>
    <div class="nav-bar">
      <a href="index.php" class="logo">Heritage<span>Link</span></a>
      <ul class="nav-links">
        <li><a href="index.php" class="<?php echo ($current_page === 'index.php') ? 'active-nav' : ''; ?>">Home</a></li>
        
        <?php if ($is_logged_in): ?>
          <li>
            <a href="customer-dashboard.php" class="<?php echo ($current_page === 'customer-dashboard.php') ? 'active-nav' : ''; ?>">Dashboard</a>
          </li>
        <?php endif; ?>

        <li><a href="destinations.php" class="<?php echo ($current_page === 'destinations.php') ? 'active-nav' : ''; ?>">Destinations</a></li>
        <li><a href="guides.php" class="<?php echo ($current_page === 'guides.php') ? 'active-nav' : ''; ?>">Guides</a></li>
        <li><a href="contact.php" class="<?php echo ($current_page === 'contact.php') ? 'active-nav' : ''; ?>">Contact Us</a></li>
        
        <?php if ($is_logged_in): ?>
          <li>
            <a href="customer-logout.php" class="btn-user-logout-nav">
              <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
          </li>
        <?php else: ?>
            <li><a href="login-choice.php" class="btn-login">Login / Sign Up</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </header>