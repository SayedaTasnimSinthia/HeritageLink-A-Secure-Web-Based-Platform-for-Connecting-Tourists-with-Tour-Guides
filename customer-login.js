document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("customer-login-form");
  const emailInput = document.getElementById("email");
  const passwordInput = document.getElementById("password");
  const errorBox = document.getElementById("error-box");

  if (!form) return;

  form.addEventListener("submit", (e) => {
    const email = emailInput.value.trim();
    const password = passwordInput.value.trim();

    if (!email || !password) {
      e.preventDefault();
      errorBox.textContent = "Please fill in all fields.";
      errorBox.style.display = "block";
      return;
    }

    const emailPattern = /^.+@.+\..+$/;
    if (!emailPattern.test(email)) {
      e.preventDefault();
      errorBox.textContent = "Please enter a valid email address.";
      errorBox.style.display = "block";
      return;
    }
  });
});