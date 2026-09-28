document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("live-destination-search");
  const tableBody = document.getElementById("destinations-table-body");
  const noMatchRow = document.getElementById("no-match-row");
  const rows = tableBody ? tableBody.querySelectorAll(".destination-data-row") : [];

  if (!searchInput) return;

  searchInput.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
    }
  });

  searchInput.addEventListener("input", function () {
    const query = this.value.toLowerCase().trim().replace(/\s+/g, " ");
    let visibleCount = 0;

    rows.forEach((row) => {
   
      const siteName = (row.dataset.siteName || "").toLowerCase();
      const destId   = (row.dataset.destId || "").toLowerCase();
      const location = (row.dataset.location || "").toLowerCase();
      const district = (row.dataset.district || "").toLowerCase();
      const division = (row.dataset.division || "").toLowerCase();
      const type     = (row.dataset.type || "").toLowerCase();
      const period   = (row.dataset.period || "").toLowerCase();
      const allText  = row.innerText.toLowerCase();

      const searchTarget = `${siteName} ${destId} ${location} ${district} ${division} ${type} ${period} ${allText}`;


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
  });
});