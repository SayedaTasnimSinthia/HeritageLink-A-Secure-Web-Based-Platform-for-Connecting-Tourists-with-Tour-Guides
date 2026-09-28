<?php
require_once 'db.php';

$page_title = "FAQ";
$extra_css = "faq.css";
require_once 'header.php';
?>

  <main class="faq-page-wrapper">
    <div class="page-container">

      <div class="faq-title-area">
        <h1 class="faq-main-title">FAQ</h1>
      </div>

      <div class="faq-info-card">
        <div class="faq-list">

          <div class="faq-item">
            <h3 class="faq-question">1. What is HeritageLink &amp; what services do you offer?</h3>
            <p class="faq-answer">
              HeritageLink is an online tourism platform connecting travelers with certified, local tour guides across historical landmarks in Bangladesh. We provide heritage site exploration, personalized itineraries &amp; multilingual translator booking assistance.
            </p>
          </div>

          <div class="faq-item">
            <h3 class="faq-question">2. Are the tour guides verified &amp; trustworthy?</h3>
            <p class="faq-answer">
              Yes. All guides on our platform undergo a rigorous verification process, including government-issued identity checks (NID), heritage knowledge evaluations &amp; background safety screenings before they receive a verified badge.
            </p>
          </div>

          <div class="faq-item">
            <h3 class="faq-question">3. Can I request a translator for foreign languages?</h3>
            <p class="faq-answer">
              Yes. Many of our registered tour guides are multilingual, offering tour services in English, Japanese, Arabic &amp; other foreign languages. You can select language requirements directly from a guide's profile before booking.
            </p>
          </div>

          <div class="faq-item">
            <h3 class="faq-question">4. How do I book a tour guide & receive confirmation?</h3>
            <p class="faq-answer">
              Choose your desired heritage destination, select an available verified guide, pick your schedule &amp; complete the reservation form. Once confirmed, you will receive an instant digital booking voucher with guide contact details.
            </p>
          </div>

          <div class="faq-item">
            <h3 class="faq-question">5. What are your working hours & how can I contact support?</h3>
            <p class="faq-answer">
              Our head office customer support is open Sunday through Thursday, 9:00 AM – 6:00 PM (BST). Emergency guide assistance is available 24/7 via phone or our online contact form, with regular inquiries answered within one business day.
            </p>
          </div>

        </div>
      </div>

    </div>
  </main>

<?php require_once 'footer.php'; ?>