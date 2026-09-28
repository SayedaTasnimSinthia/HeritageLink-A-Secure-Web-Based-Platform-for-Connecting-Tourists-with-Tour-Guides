
function confirmDeleteSlot(availId, guideName, dateStr) {
  return confirm(`Are you sure you want to delete this availability slot?\n\nSlot ID: ${availId}\nGuide: ${guideName}\nDate: ${dateStr}\n\nThis action cannot be undone.`);
}


document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("live-delete-search");
  const tableBody = document.getElementById("shifts-table-body");
  const noMatchRow = document.getElementById("no-match-row");
  const rows = tableBody ? tableBody.querySelectorAll(".shift-data-row") : [];

  if (!searchInput) return;

 
  searchInput.addEventListener("keydown", (e) => {
    if (e.key === "Enter") e.preventDefault();
  });

  searchInput.addEventListener("input", function () {
    const query = this.value.toLowerCase().trim().replace(/\s+/g, " ");
    let visibleCount = 0;

    rows.forEach((row) => {
   
      const dateRaw = (row.dataset.dateRaw || "").toLowerCase();
      const dateParts = (row.dataset.dateParts || "").toLowerCase();


      const guideId = (row.dataset.guideId || "").toLowerCase();
      const guideName = (row.dataset.guideName || "").toLowerCase();
     const time = (row.dataset.time || "").toLowerCase();
      const district = (row.dataset.district || "").toLowerCase();
      const sites = (row.dataset.sites || "").toLowerCase();
      const status = (row.dataset.status || "").toLowerCase();
      const availId = (row.dataset.id || "").toLowerCase();
      const rowVisibleText = row.innerText.toLowerCase();

      const searchTarget = `${dateRaw} ${dateParts} ${time} ${guideId} ${guideName} ${district} ${sites} ${status} ${availId} ${rowVisibleText}`;

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