document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("user-search-input");
  const touristsCards = document.querySelectorAll(".tourist-profile-card");
  const totalCountVal = document.getElementById("total-count-val");
  const noSearchResults = document.getElementById("no-search-results");

  if (!searchInput) return;

  searchInput.addEventListener("input", function () {
    const filter = this.value.trim().toLowerCase();
    let visibleCount = 0;

    touristsCards.forEach((card) => {
      const searchData = card.getAttribute("data-search") || "";
      if (searchData.includes(filter)) {
        card.style.display = "block";
        visibleCount++;
      } else {
        card.style.display = "none";
      }
    });

    if (totalCountVal) {
      totalCountVal.textContent = visibleCount;
    }

    if (noSearchResults) {
      noSearchResults.style.display = visibleCount === 0 ? "block" : "none";
    }
  });
});