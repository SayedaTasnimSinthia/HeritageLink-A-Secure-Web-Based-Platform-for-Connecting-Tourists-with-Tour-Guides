<?php require_once 'db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login Selection - HeritageLink</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  

  <link rel="stylesheet" href="index.css">
  <link rel="stylesheet" href="login-choice.css">
</head>
<body>

  <header>
    <div class="nav-bar">
      <a href="index.php" class="logo">Heritage<span>Link</span></a>
      <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="destinations.php">Destinations</a></li>
        <li><a href="guides.php">Guides</a></li>
   
        <li><a href="contact.php">Contact Us</a></li>
        <li><a href="login-choice.php" class="btn-login">Login / Sign Up</a></li>
      </ul>
    </div>
  </header>


  <main class="choice-main-container">
    <div class="page-container">
      <div class="choice-title-box">
        <h1 class="choice-heading">Who Are You <span>?</span></h1>
      </div>

      <div class="roles-wrapper">

<a href="customer-login.php" class="role-card">
  <div class="role-icon-box">
    <img src="images/4862440.png" alt="Customer" class="role-img">
  </div>
  <h2 class="role-name">CUSTOMER</h2>
</a>


<a href="admin-login.php" class="role-card">
  <div class="role-icon-box">
    <img src="images/12574690.png" alt="Admin" class="role-img">
  </div>
  <h2 class="role-name">ADMIN</h2>
</a>
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
          <h4>Legal &amp; Support</h4>
          <ul>
            <li><a href="faq.php">FAQ</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="cookie-policy.php">Cookie Policy</a></li>
            <li><a href="privacy-policy.php">Privacy Policy</a></li>
            <li><a href="terms.php">Terms &amp; condition</a></li>
            <li><a href="guide-policy.php">Guide Verification Policy</a></li>
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