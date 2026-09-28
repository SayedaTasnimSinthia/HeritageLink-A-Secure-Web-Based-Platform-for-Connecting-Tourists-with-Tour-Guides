document.addEventListener("DOMContentLoaded", () => {
  const bookingForms = document.querySelectorAll(".book-form");
  bookingForms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      const confirmBooking = confirm("Are you sure you want to reserve this tour slot with this verified guide?");
      if (!confirmBooking) {
        e.preventDefault();
      }
    });
  });

  const searchInput = document.getElementById("guide-text-filter");
  const guideCards = document.querySelectorAll(".guide-profile-card");
  const noMatchNotice = document.getElementById("no-guides-filter-match");

  if (searchInput) {
    searchInput.addEventListener("keydown", (e) => {
      if (e.key === "Enter") {
        e.preventDefault();
      }
    });

    searchInput.addEventListener("input", function () {
      const query = this.value.trim().toLowerCase().replace(/\s+/g, " ");
      let visibleCount = 0;

      guideCards.forEach((card) => {
        const sites = card.getAttribute("data-sites") || "";
        const langs = card.getAttribute("data-langs") || "";
        const name  = card.getAttribute("data-name") || "";

        if (query === "" || sites.includes(query) || langs.includes(query) || name.includes(query)) {
          card.style.display = "";
          visibleCount++;
        } else {
          card.style.display = "none";
        }
      });

      if (noMatchNotice) {
        noMatchNotice.style.display = (visibleCount === 0 && guideCards.length > 0) ? "block" : "none";
      }
    });
  }
});