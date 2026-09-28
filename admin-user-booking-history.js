document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("user-history-search");
  const tableRows = document.querySelectorAll("#all-users-tbody .user-row");

  if (!searchInput || tableRows.length === 0) return;

  searchInput.addEventListener("input", function () {
    const filter = this.value.trim().toLowerCase().replace(/^#/, "");

    tableRows.forEach((row) => {
      const rowSearchData = row.getAttribute("data-search") || "";
      if (filter === "" || rowSearchData.includes(filter)) {
        row.style.display = "";
      } else {
        row.style.display = "none";
      }
    });
  });
});