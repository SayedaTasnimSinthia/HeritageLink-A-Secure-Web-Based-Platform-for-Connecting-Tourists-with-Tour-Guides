<?php
session_start();
require_once 'db.php';

$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name             = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email            = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password         = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match!";
    } else {
        $clean_name  = $conn->real_escape_string($name);
        $clean_email = $conn->real_escape_string($email);

        $check_query = "SELECT id FROM customers WHERE email = '$clean_email' LIMIT 1";
        $check_result = $conn->query($check_query);

        if ($check_result && $check_result->num_rows > 0) {
            $error_message = "This email is already registered! Please log in.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $insert_query = "INSERT INTO customers (name, email, password) VALUES ('$clean_name', '$clean_email', '$hashed_password')";
            if ($conn->query($insert_query)) {
                $success_message = "Account created successfully! Redirecting to login...";
                header("refresh:2;url=customer-login.php");
            } else {
                $error_message = "Something went wrong. Please try again later.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Register - HeritageLink</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <link rel="stylesheet" href="index.css">
  <link rel="stylesheet" href="customer-signup.css">
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

  <main class="signup-wrapper">
    <div class="signup-card">

      <div class="signup-header">
        <h1 class="signup-title">REGISTER</h1>
      </div>

      <?php if (!empty($error_message)): ?>
        <div class="alert-box error" id="alert-box"><?php echo htmlspecialchars($error_message); ?></div>
      <?php elseif (!empty($success_message)): ?>
        <div class="alert-box success" id="alert-box"><?php echo htmlspecialchars($success_message); ?></div>
      <?php else: ?>
        <div class="alert-box error" id="alert-box" style="display: none;"></div>
      <?php endif; ?>

  
      <form id="signup-form" action="customer-signup.php" method="POST" class="signup-form">
        
        <div class="field-box">
          <label for="name">Full Name</label>
          <div class="input-icon-wrap">
            <input 
              type="text" 
              id="name" 
              name="name" 
              placeholder="Enter your full name..." 
              required 
            />
            <i class="fa-solid fa-user user-icon"></i>
          </div>
        </div>

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

        <!-- Password Field -->
        <div class="field-box">
          <label for="password">Password</label>
          <div class="input-icon-wrap">
            <input 
              type="password" 
              id="password" 
              name="password" 
              placeholder="Create a strong password..." 
              required 
            />
            <i class="fa-solid fa-lock lock-icon"></i>
          </div>
        </div>

        <div class="field-box">
          <label for="confirm_password">Re-enter Your Password</label>
          <div class="input-icon-wrap">
            <input 
              type="password" 
              id="confirm_password" 
              name="confirm_password" 
              placeholder="Repeat your password to verify..." 
              required 
            />
            <i class="fa-solid fa-lock lock-icon"></i>
          </div>
        </div>

        <button type="submit" class="btn-register">Register</button>
      </form>

      <div class="login-redirect-container">
        <a href="customer-login.php" class="login-redirect-link">
          Already Have An Account? Click Here To Login
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

  <script src="customer-signup.js"></script>
</body>
</html>