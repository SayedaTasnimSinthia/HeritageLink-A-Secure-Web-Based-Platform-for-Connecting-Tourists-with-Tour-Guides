const form = document.getElementById("newsletter-form");
const emailInput = document.getElementById("newsletter-email");
const msgBox = document.getElementById("newsletter-message");

form.onsubmit = async function (e) {
  e.preventDefault();

  let response = await fetch("subscribe.php", {
    method: "POST",
    body: new FormData(form),
  });

  let data = await response.json();


  msgBox.textContent = data.message;
  msgBox.className = "newsletter-msg " + data.status;


  if (data.status === "success") {
    emailInput.value = "";
  }
};