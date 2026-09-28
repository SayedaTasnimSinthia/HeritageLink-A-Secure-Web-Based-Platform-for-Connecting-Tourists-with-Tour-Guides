function confirmDelete(guideId, guideName) {
  return confirm(`Are you sure you want to delete guide "${guideName}" (ID: ${guideId})?\n\nThis action cannot be undone.`);
}

document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("live-guide-search");
  const rows = document.querySelectorAll(".guide-data-row");
  const noMatchRow = document.getElementById("no-match-row");

  if (!searchInput) return;

  searchInput.addEventListener("input", function () {
    const query = this.value.trim().toLowerCase();
    let visibleCount = 0;

    rows.forEach((row) => {
      const text = row.textContent.toLowerCase();

      if (query === "" || text.includes(query)) {
        row.style.display = "";
        visibleCount++;
      } else {
        row.style.display = "none";
      }
    });

    if (noMatchRow) {
      if (visibleCount === 0 && rows.length > 0) {
        noMatchRow.style.display = "";
      } else {
        noMatchRow.style.display = "none";
      }
    }
  });
});