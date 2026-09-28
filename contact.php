<?php
require_once 'db.php';
$page_title = "Contact Us";
$extra_css = "contact.css";
require_once 'header.php';
?>

  <main class="contact-section">
    <div class="page-container">

      <div class="contact-header">
        <h1 class="contact-title">Contact Us</h1>
        
      </div>

      <div class="contact-cards-grid">

        <div class="contact-card">
          <div class="card-heading">
            <i class="fa-solid fa-house-chimney card-icon address-icon"></i>
            <h3>Office Address</h3>
          </div>
          <div class="card-body">
            <p><strong>HeritageLink Bangladesh HQ</strong></p>
            <p>Level 7, Parjatan Bhaban</p>
            <p>Plot E-5 C/1, Agargaon, Sher-e-Bangla Nagar</p>
            <p>Dhaka - 1207, Bangladesh</p>
          </div>
        </div>

        <div class="contact-card">
          <div class="card-heading">
            <i class="fa-solid fa-mobile-screen-button card-icon phone-icon"></i>
            <h3>Phone</h3>
          </div>
          <div class="card-body">
            <p><a href="tel:+88028181234">+880 (2) 818-1234</a> (Hotline)</p>
            <p><a href="tel:+8801712345678">+880 1712-345678</a> (Support)</p>
            <p><a href="tel:+8801912345678">+880 1912-345678</a> (WhatsApp)</p>
          </div>
        </div>

        <div class="contact-card">
          <div class="card-heading">
            <i class="fa-solid fa-envelope card-icon email-icon"></i>
            <h3>Email</h3>
          </div>
          <div class="card-body">
            <p><a href="mailto:support@heritagelink.com.bd">support@heritagelink.com.bd</a></p>
            <p><a href="mailto:info@heritagelink.com.bd">info@heritagelink.com.bd</a></p>
            <p><a href="mailto:tours@heritagelink.com.bd">tours@heritagelink.com.bd</a></p>
          </div>
        </div>

        <div class="contact-card">
          <div class="card-heading">
            <i class="fa-solid fa-clock card-icon clock-icon"></i>
            <h3>Working Hours</h3>
          </div>
          <div class="card-body">
            <p><strong>Sunday – Thursday:</strong></p>
            <p>9:00 AM – 6:00 PM (BST)</p>
            <p><strong>Friday – Saturday:</strong></p>
            <p>Emergency Guide Support Only (24/7)</p>
          </div>
        </div>

      </div>

    </div>
  </main>

<?php require_once 'footer.php'; ?>