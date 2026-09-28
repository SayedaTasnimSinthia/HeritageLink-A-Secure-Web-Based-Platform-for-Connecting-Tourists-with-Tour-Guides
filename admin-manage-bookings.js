document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("booking-live-search");
  const cards = document.querySelectorAll(".booking-admin-card");
  const noMatchCard = document.getElementById("no-search-results");

  if (!searchInput) return;

  searchInput.addEventListener("input", function () {
    const query = this.value.trim().toLowerCase().replace(/^#/, "");
    let visibleCount = 0;

    cards.forEach((card) => {
      const searchData = card.getAttribute("data-search") || "";
      if (query === "" || searchData.includes(query)) {
        card.style.display = "block";
        visibleCount++;
      } else {
        card.style.display = "none";
      }
    });

    if (noMatchCard) {
      noMatchCard.style.display = (visibleCount === 0 && cards.length > 0) ? "block" : "none";
    }
  });
});