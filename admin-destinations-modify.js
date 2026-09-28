document.addEventListener("DOMContentLoaded", () => {
  const quickSelector = document.getElementById("quick_site_selector");
  const searchInput = document.getElementById("search_name");
  const searchForm = document.getElementById("search-dest-form");
  const editCard = document.getElementById("edit-destination-card");

 
  if (editCard) {
    editCard.scrollIntoView({ behavior: "smooth", block: "start" });
  }


  if (quickSelector && searchInput && searchForm) {
    quickSelector.addEventListener("change", function () {
      if (this.value) {
        searchInput.value = this.value;
        searchForm.submit();
      }
    });
  }
});