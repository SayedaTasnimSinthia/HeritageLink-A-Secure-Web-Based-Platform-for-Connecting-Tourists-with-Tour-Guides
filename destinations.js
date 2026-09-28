document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("dest-live-search");
  const catalogGrid = document.getElementById("dest-catalog-grid");
  const noMatchBox = document.getElementById("no-search-match");
  const cards = catalogGrid ? catalogGrid.querySelectorAll(".dest-item-card") : [];

  if (!searchInput) return;

  searchInput.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
    }
  });

  searchInput.addEventListener("input", function () {
    const query = this.value.toLowerCase().trim().replace(/\s+/g, " ");
    let visibleCount = 0;

    cards.forEach((card) => {
      const siteName = (card.dataset.siteName || "").toLowerCase();
      const district = (card.dataset.district || "").toLowerCase();

      const searchTarget = `${siteName} ${district}`;

      if (query === "" || searchTarget.includes(query)) {
        card.style.display = "flex";
        visibleCount++;
      } else {
        card.style.display = "none";
      }
    });

    if (noMatchBox) {
      if (visibleCount === 0 && cards.length > 0) {
        noMatchBox.style.display = "block";
      } else {
        noMatchBox.style.display = "none";
      }
    }
  });
});