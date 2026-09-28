<?php 
require_once 'db.php'; 
$page_title = "Explore Bangladesh's Rich Heritage";
require_once 'header.php'; 
?>

  <section class="hero-section">
    <div class="page-container">
      <h1>Explore Bangladesh's Rich Heritage</h1>
      <p>Book trusted tour guides, discover historical landmarks and request translator assistance for a seamless cultural experience</p>
    </div>
  </section>

  <div class="page-container info-section">
    <div class="grid-two-columns">
      <img class="rounded-image" src="images/Hussaini_Dalan_(27193260074).jpg" alt="Archaeological Exploration" />
      <div class="info-text">
        <h2>Plan Your Trip With Us</h2>
        <ul class="feature-list">
          <li>- Certified &amp; Verified Tour Guides</li>
          <li>- Multilingual Translator Requests</li>
          <li>- Seamless Online Booking Calendar</li>
        </ul>
      </div>
    </div>

    <div class="grid-two-columns">
      <div class="info-text">
        <h2>Uncover Bengal’s Deep History</h2>
        <p class="muted-text">From the terracotta temples of Kantajew to the ancient monasteries of Somapura Mahavihara, experience history guided by certified local experts</p>
        <div class="stats-row">
          <div class="stat-box">
            <h3>14+</h3>
            <p>Districts Covered</p>
          </div>
          <div class="stat-box">
            <h3>320+</h3>
            <p>Verified Guides</p>
          </div>
          <div class="stat-box">
            <h3>67+</h3>
            <p>Heritage Sites</p>
          </div>
        </div>
      </div>
      <img class="rounded-image" src="images/ahsan-monjil.jpg" alt="Travelers exploring" />
    </div>

    <div class="features-strip">
      <div class="feature-item"><i class="fa-solid fa-user-check"></i> Verified Guides</div>
      <div class="feature-item"><i class="fa-solid fa-lock"></i> Secure Booking</div>
      <div class="feature-item"><i class="fa-solid fa-language"></i> Translator Support</div>
     
    </div>

    <div class="section-header">
      <h2>Popular Heritage Destinations</h2>
      <a href="destinations.php" class="view-all-link">View All <i class="fa-solid fa-arrow-right"></i></a>
    </div>

    <div class="destinations-grid">
      <a href="destination-details.php?name=lalbagh-fort" class="dest-card">
        <img src="images/the-tomb-of-bibi-pari.jpg" alt="Lalbagh Fort">
        <div class="dest-card-body">
          <h4>Lalbagh Fort</h4>
          <p>Dhaka</p>
        </div>
      </a>

      <a href="destination-details.php?name=somapura-mahavihara" class="dest-card">
        <img src="images//Paharpur_Buddhist_Bihar.jpg" alt="Somapura Mahavihara">
        <div class="dest-card-body">
          <h4>Somapura Mahavihara</h4>
          <p>Naogaon</p>
        </div>
      </a>

      <a href="destination-details.php?name=sixty-dome-mosque" class="dest-card">
        <img src="images/8513198.webp" alt="Sixty Dome Mosque">
        <div class="dest-card-body">
          <h4>Sixty Dome Mosque</h4>
          <p>Bagerhat</p>
        </div>
      </a>

      <a href="destination-details.php?name=mahasthangarh" class="dest-card">
        <img src="images/mahasthangarh.jpg" alt="Mahasthangarh">
        <div class="dest-card-body">
          <h4>Mahasthangarh</h4>
          <p>Bogra</p>
        </div>
      </a>
    </div>

    <div class="section-header">
      <h2>Verified Tour Guides</h2>
      <a href="guides.php" class="view-all-link">View All Guides <i class="fa-solid fa-arrow-right"></i></a>
    </div>

    <div class="guides-grid">
      <div class="guide-card">
        <img class="guide-avatar" src="images/2 (1).jpg" alt="Kamal Ahmed" />
        <div class="guide-name">Kamal Ahmed <i class="fa-solid fa-circle-check verified-icon"></i></div>
        <div class="guide-role">Dhaka & Sonargaon Specialist</div>
        <div class="guide-tags">
          <span class="tag">Bengali</span>
          <span class="tag">English</span>
        </div>
        <a href="guide-profile.php?id=1" class="btn-brass">View Profile</a>
      </div>


      <div class="guide-card">
        <img class="guide-avatar" src="images/3.jpg" alt="Alina Rahman" />
        <div class="guide-name">Alina Rahman <i class="fa-solid fa-circle-check verified-icon"></i></div>
        <div class="guide-role">Archaeology Expert</div>
        <div class="guide-tags">
          <span class="tag">Bengali</span>
          <span class="tag">Japanese</span>
        </div>
        <a href="guide-profile.php?id=2" class="btn-brass">View Profile</a>
      </div>

      <div class="guide-card">
        <img class="guide-avatar" src="images/5.jpg" alt="Allan Marma" />
        <div class="guide-name">Allan Marma <i class="fa-solid fa-circle-check verified-icon"></i></div>
        <div class="guide-role">Chittagong Hill Tracts</div>
        <div class="guide-tags">
          <span class="tag">Bengali</span>
          <span class="tag">Burmese</span>
        </div>
        <a href="guide-profile.php?id=3" class="btn-brass">View Profile</a>
      </div>
    </div>
  </div>

  <section class="how-it-works-section">
    <div class="page-container">
      <h2 class="how-it-works-title">How HeritageLink Works</h2>
      <div class="steps-grid">
        <div class="step-card">
          <div class="step-icon"><i class="fa-solid fa-map-location-dot"></i></div>
          <h3>Choose a Destination</h3>
          <p>Select from Bangladesh's famous heritage sites</p>
        </div>
        <div class="step-card">
          <div class="step-icon"><i class="fa-solid fa-user-check"></i></div>
          <h3>Pick a Verified Guide</h3>
          <p>Browse ratings, languages & availability.</p>
        </div>
        <div class="step-card">
          <div class="step-icon"><i class="fa-solid fa-calendar-check"></i></div>
          <h3>Book Securely</h3>
          <p>Confirm your booking & request a translator</p>
        </div>
        <div class="step-card">
          <div class="step-icon"><i class="fa-solid fa-language"></i></div>
          <h3>Enjoy Your Tour</h3>
          <p>Experience Bangladesh with trusted local experts</p>
        </div>
      </div>
    </div>
  </section>

  <section class="cta-banner" id="booking-section">
    <div class="page-container">
      <h2>Ready to explore Bangladesh's heritage?</h2>
      <p>Join thousands of travelers discovering history with verified local guides</p>
      <a href="guides.php" class="btn-terracotta">Book Your First Tour</a>
    </div>
  </section>

  <section class="newsletter-section">
    <div class="page-container">
      <div class="newsletter-card">
        <div class="newsletter-content">
          <h3>Subscribe to Our Newsletter</h3>
          <p>Get exclusive travel tips, heritage updates & destination guides delivered straight to your inbox.</p>
          <form id="newsletter-form" class="newsletter-form">
            <input type="email" id="newsletter-email" name="email" class="newsletter-input" placeholder="Enter your email" required />
            <button type="submit" class="btn-subscribe">Subscribe</button>
          </form>
          <div id="newsletter-message" class="newsletter-msg"></div>
        </div>
        <img class="newsletter-image" src="images/e56uyu5oox4ydmrpw1cf.jpg" alt="Bangladeshi Heritage Architecture" />
      </div>
    </div>
  </section>

<?php require_once 'footer.php'; ?>