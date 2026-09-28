document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("add-destination-form");
  const alertBox = document.getElementById("alert-box");

  if (!form) return;

  form.addEventListener("submit", (e) => {
    const siteName = document.getElementById("site_name").value.trim();
    const heritageType = document.getElementById("heritage_type").value.trim();
    const district = document.getElementById("district").value.trim();
    const location = document.getElementById("location").value.trim();
    const entryFee = document.getElementById("entry_fee").value.trim();

    if (siteName.length < 3) {
      e.preventDefault();
      showAlert("Please enter a valid destination site name (minimum 3 characters).");
      return;
    }

    if (heritageType.length < 2) {
      e.preventDefault();
      showAlert("Please enter a valid heritage type.");
      return;
    }

    if (district.length < 2 || location.length < 2) {
      e.preventDefault();
      showAlert("Please provide specific district and location details.");
      return;
    }

    if (entryFee.length === 0) {
      e.preventDefault();
      showAlert("Please provide entry fee details for visitors.");
      return;
    }
  });

  function showAlert(msg) {
    alertBox.textContent = msg;
    alertBox.className = "alert-box error";
    alertBox.style.display = "block";
    window.scrollTo({ top: 150, behavior: "smooth" });
  }
});