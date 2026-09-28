document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("add-guide-form");
  const alertBox = document.getElementById("alert-box");

  if (!form) return;

  form.addEventListener("submit", (e) => {

    const langCheckboxes = form.querySelectorAll('input[name="languages[]"]:checked');
    if (langCheckboxes.length === 0) {
      e.preventDefault();
      showAlert("Please select at least one spoken language.");
      return;
    }

   
    const siteCheckboxes = form.querySelectorAll('input[name="heritage_sites[]"]:checked');
    if (siteCheckboxes.length === 0) {
      e.preventDefault();
      showAlert("Please select at least one heritage site covered.");
      return;
    }

  
    const phoneInput = document.getElementById("phone");
    if (phoneInput && phoneInput.value.trim().length < 8) {
      e.preventDefault();
      showAlert("Please enter a valid primary phone number.");
      return;
    }

   
    const rateInput = document.getElementById("rate_amount");
    if (rateInput && parseFloat(rateInput.value) <= 0) {
      e.preventDefault();
      showAlert("Please provide a valid rate amount greater than zero.");
      return;
    }
  });

  function showAlert(message) {
    alertBox.textContent = message;
    alertBox.className = "alert-box error";
    alertBox.style.display = "block";
    window.scrollTo({ top: 180, behavior: "smooth" });
  }
});