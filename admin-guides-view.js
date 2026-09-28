document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("live-guide-search");
  const tableBody = document.getElementById("guides-table-body");
  const noMatchRow = document.getElementById("no-match-row");
  const rows = tableBody ? tableBody.querySelectorAll(".guide-data-row") : [];

  if (!searchInput) return;

  searchInput.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
    }
  });

  function performSearch() {
    // Normalize query: lowercase, trim, and collapse extra spaces
    const query = searchInput.value.toLowerCase().trim().replace(/\s+/g, " ");
    let visibleCount = 0;

    rows.forEach((row) => {
      // 1. Gather all searchable data attributes
      const guideId = (row.dataset.id || "").toLowerCase();
      const name = (row.dataset.name || "").toLowerCase();
      const spec = (row.dataset.spec || "").toLowerCase();
      const status = (row.dataset.status || "").toLowerCase();
      const division = (row.dataset.division || "").toLowerCase();
      const languages = (row.dataset.languages || "").toLowerCase();
      const sites = (row.dataset.sites || "").toLowerCase();
      const email = (row.dataset.email || "").toLowerCase();
      const phone = (row.dataset.phone || "").toLowerCase();
      const rowVisibleText = row.innerText.toLowerCase();
      const searchTarget = `${guideId} ${name} ${spec} ${status} ${division} ${languages} ${sites} ${email} ${phone} ${rowVisibleText}`;

      if (query === "" || searchTarget.includes(query)) {
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
  }

  searchInput.addEventListener("input", performSearch);
  searchInput.addEventListener("keyup", performSearch);

  if (searchInput.value.trim() !== "") {
    performSearch();
  }
});