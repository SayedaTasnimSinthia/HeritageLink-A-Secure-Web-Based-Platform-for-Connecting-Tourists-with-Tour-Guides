document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("live-availability-search");
  const tableBody = document.getElementById("availability-table-body");
  const noMatchRow = document.getElementById("no-match-row");
  const rows = tableBody ? tableBody.querySelectorAll(".availability-data-row") : [];

  if (!searchInput) return;

  searchInput.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
    }
  });

  function performSearch() {
    const query = searchInput.value.toLowerCase().trim().replace(/\s+/g, " ");
    let visibleCount = 0;

    rows.forEach((row) => {
   
      const guideId = (row.dataset.guideId || "").toLowerCase();
      const guideName = (row.dataset.guideName || "").toLowerCase();
      const dateRaw = (row.dataset.dateRaw || "").toLowerCase();
      const dateFormatted = (row.dataset.dateFormatted || "").toLowerCase();
      const time = (row.dataset.time || "").toLowerCase();
      const district = (row.dataset.district || "").toLowerCase();
      const sites = (row.dataset.sites || "").toLowerCase();
      const status = (row.dataset.status || "").toLowerCase();
      const availId = (row.dataset.id || "").toLowerCase();

      const rowVisibleText = row.innerText.toLowerCase();

      const searchTarget = `${guideId} ${guideName} ${dateRaw} ${dateFormatted} ${time} ${district} ${sites} ${status} ${availId} ${rowVisibleText}`;

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