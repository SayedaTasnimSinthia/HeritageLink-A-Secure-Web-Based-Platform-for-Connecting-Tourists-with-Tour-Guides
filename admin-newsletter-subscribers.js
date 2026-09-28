document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("user-search-input");
  const cards = document.querySelectorAll(".tourist-profile-card");
  const totalCountVal = document.getElementById("total-count-val");
  const noSearchResults = document.getElementById("no-search-results");

  if (!searchInput) return;

  searchInput.addEventListener("input", function () {
    const query = this.value.trim().toLowerCase().replace(/^#/, "");
    let matchCount = 0;

    cards.forEach((card) => {
      const searchBlob = card.getAttribute("data-search") || "";
      if (query === "" || searchBlob.includes(query)) {
        card.style.display = "block";
        matchCount++;
      } else {
        card.style.display = "none";
      }
    });

    if (totalCountVal) {
      totalCountVal.textContent = matchCount;
    }

    if (noSearchResults) {
      noSearchResults.style.display = matchCount === 0 ? "block" : "none";
    }
  });
});