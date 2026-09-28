document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("guide-ratings-search");
  const cards = document.querySelectorAll(".guide-rating-card");
  const noMatchNotice = document.getElementById("no-search-results");

  if (!searchInput) return;

  searchInput.addEventListener("input", function () {
    const query = this.value.trim().toLowerCase().replace(/^#/, "");
    let matchCount = 0;

    cards.forEach((card) => {
      const searchData = card.getAttribute("data-search") || "";
      if (query === "" || searchData.includes(query)) {
        card.style.display = "";
        matchCount++;
      } else {
        card.style.display = "none";
      }
    });

    if (noMatchNotice) {
      noMatchNotice.style.display = (matchCount === 0 && cards.length > 0) ? "block" : "none";
    }
  });
});