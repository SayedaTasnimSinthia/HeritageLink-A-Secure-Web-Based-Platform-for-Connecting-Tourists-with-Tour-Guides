document.addEventListener("DOMContentLoaded", () => {
  const photoInput = document.getElementById("profile_photo");
  const avatarPreview = document.getElementById("avatar-preview");

  if (photoInput && avatarPreview) {
    photoInput.addEventListener("change", function (e) {
      const file = e.target.files[0];
      if (file) {

        if (!file.type.startsWith("image/")) {
          alert("Please upload a valid image file.");
          photoInput.value = "";
          return;
        }

        const reader = new FileReader();
        reader.onload = function (event) {
          avatarPreview.setAttribute("src", event.target.result);
        };
        reader.readAsDataURL(file);
      }
    });
  }
});