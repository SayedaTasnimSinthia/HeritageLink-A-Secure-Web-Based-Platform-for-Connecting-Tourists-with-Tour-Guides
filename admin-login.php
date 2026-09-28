<?php
session_start();
require_once 'db.php';

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if ($username === 'admin' && $password === '123456789') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = 'admin';
    
        header("Location: admin-dashboard.php");
        exit;
    } else {
        $error_message = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Login - HeritageLink</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
 
  <link rel="cdnjs" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <link rel="stylesheet" href="index.css">
  <link rel="stylesheet" href="admin-login.css">
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
  <main class="login-wrapper">
    <div class="login-card">
      <div class="login-header">
        <h1 class="login-title">LOGIN </h1>
        
      </div>
      <?php if (!empty($error_message)): ?>
        <div class="login-error"><?php echo htmlspecialchars($error_message); ?></div>
      <?php endif; ?>
      <form action="admin-login.php" method="POST" class="auth-form">
        <div class="form-group">
          <label for="username">Username</label>
          <div class="input-container">
            <input 
              type="text" 
              id="username" 
              name="username" 
              placeholder="Enter your username..." 
              required 
            />
            <i class="fa-solid fa-envelope input-icon"></i>
          </div>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <div class="input-container">
            <input 
              type="password" 
              id="password" 
              name="password" 
              placeholder="Enter your password..." 
              required 
            />
            <i class="fa-solid fa-lock input-icon lock-icon"></i>
          </div>
        </div>
        <button type="submit" class="btn-submit">Login</button>
      </form>

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
            <li><a href="terms.php">Terms & condition</a></li>
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