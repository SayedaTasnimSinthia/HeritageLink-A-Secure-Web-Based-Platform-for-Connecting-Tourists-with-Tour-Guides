<?php
session_start();
require_once 'db.php';

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (!empty($email) && !empty($password)) {
        $clean_email = $conn->real_escape_string($email);
        $query = "SELECT * FROM customers WHERE email = '$clean_email' LIMIT 1";
        $result = $conn->query($query);

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify password (supports both password_hash and plain text for easy testing)
            if (password_verify($password, $user['password']) || $password === $user['password']) {
                $_SESSION['customer_logged_in'] = true;
                $_SESSION['customer_id'] = $user['id'];
                $_SESSION['customer_name'] = $user['name'];
                $_SESSION['customer_email'] = $user['email'];

                header("Location: customer-dashboard.php");
                exit;
            } else {
                $error_message = "Invalid email or password!";
            }
        } else {
            $error_message = "Invalid email or password!";
        }
    } else {
        $error_message = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Customer Login - HeritageLink</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <link rel="stylesheet" href="index.css">
  <link rel="stylesheet" href="customer-login.css">
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

  <main class="customer-login-wrapper">
    <div class="customer-login-card">
      <div class="login-header">
        <h1 class="login-title">LOGIN</h1>
        <p class="welcome-text">Welcome Back!</p>
      </div>
      <?php if (!empty($error_message)): ?>
        <div class="login-error-box" id="error-box"><?php echo htmlspecialchars($error_message); ?></div>
      <?php else: ?>
        <div class="login-error-box" id="error-box" style="display: none;"></div>
      <?php endif; ?>
      <form id="customer-login-form" action="customer-login.php" method="POST" class="customer-form">
        <div class="field-box">
          <label for="email">Email</label>
          <div class="input-icon-wrap">
            <input 
              type="email" 
              id="email" 
              name="email" 
              placeholder="Enter your email address..." 
              required 
            />
            <i class="fa-solid fa-envelope envelope-icon"></i>
          </div>
        </div>

    
        <div class="field-box">
          <label for="password">Password</label>
          <div class="input-icon-wrap">
            <input 
              type="password" 
              id="password" 
              name="password" 
              placeholder="Enter your password..." 
              required 
            />
            <i class="fa-solid fa-lock lock-icon"></i>
          </div>
        </div>
        <button type="submit" class="btn-customer-login">Login</button>
      </form>
      <div class="signup-link-container">
        <a href="customer-signup.php" class="signup-link">
          Don’t Have An Account? Click Here To Sign Up
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

  <script src="customer-login.js"></script>
</body>
</html>