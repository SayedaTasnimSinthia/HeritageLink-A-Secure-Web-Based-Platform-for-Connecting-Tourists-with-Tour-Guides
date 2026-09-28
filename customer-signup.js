document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("signup-form");
  const nameInput = document.getElementById("name");
  const emailInput = document.getElementById("email");
  const passwordInput = document.getElementById("password");
  const confirmPasswordInput = document.getElementById("confirm_password");
  const alertBox = document.getElementById("alert-box");

  if (!form) return;

  form.addEventListener("submit", (e) => {
    const name = nameInput.value.trim();
    const email = emailInput.value.trim();
    const password = passwordInput.value.trim();
    const confirmPassword = confirmPasswordInput.value.trim();

    if (!name || !email || !password || !confirmPassword) {
      e.preventDefault();
      showAlert("Please fill in all fields.");
      return;
    }

    const emailPattern = /^.+@.+\..+$/;
    if (!emailPattern.test(email)) {
      e.preventDefault();
      showAlert("Please enter a valid email address.");
      return;
    }

    if (password.length < 6) {
      e.preventDefault();
      showAlert("Password must be at least 6 characters long.");
      return;
    }

    if (password !== confirmPassword) {
      e.preventDefault();
      showAlert("Passwords do not match!");
      return;
    }
  });

  function showAlert(msg) {
    alertBox.textContent = msg;
    alertBox.className = "alert-box error";
    alertBox.style.display = "block";
  }
});