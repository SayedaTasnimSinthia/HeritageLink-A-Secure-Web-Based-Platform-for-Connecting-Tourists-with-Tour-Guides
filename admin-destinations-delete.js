
function confirmDeleteDest(destId, siteName) {
  return confirm(`Are you sure you want to delete this destination landmark?\n\nSite Name: ${siteName}\nDestination ID: ${destId}\n\nThis action cannot be undone.`);
}


document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("live-delete-search");
  const tableBody = document.getElementById("destinations-table-body");
  const noMatchRow = document.getElementById("no-match-row");
  const rows = tableBody ? tableBody.querySelectorAll(".destination-data-row") : [];

  if (!searchInput) return;

  searchInput.addEventListener("keydown", (e) => {
    if (e.key === "Enter") e.preventDefault();
  });

  searchInput.addEventListener("input", function () {
    const query = this.value.toLowerCase().trim().replace(/\s+/g, " ");
    let visibleCount = 0;

    rows.forEach((row) => {
      // Strictly restrict search target to the site name attribute
      const siteName = (row.dataset.siteName || "").toLowerCase();

      if (query === "" || siteName.includes(query)) {
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