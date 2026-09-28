document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("cust-booking-search");
  const filterBtns = document.querySelectorAll(".history-filter-btn");
  const bookingCards = document.querySelectorAll(".user-booking-card");
  const noFilterResults = document.getElementById("no-filter-results");

  let activeStatus = "all";
  let searchQuery = "";

  function filterCards() {
    let visibleCount = 0;

    bookingCards.forEach((card) => {
      const cardStatus = card.getAttribute("data-status") || "";
      const searchBlob = card.getAttribute("data-search") || "";

      const matchesStatus = (activeStatus === "all" || cardStatus === activeStatus);
      const matchesSearch = (searchQuery === "" || searchBlob.includes(searchQuery));

      if (matchesStatus && matchesSearch) {
        card.style.display = "block";
        visibleCount++;
      } else {
        card.style.display = "none";
      }
    });

    if (noFilterResults) {
      noFilterResults.style.display = (visibleCount === 0 && bookingCards.length > 0) ? "block" : "none";
    }
  }

  filterBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      filterBtns.forEach((b) => b.classList.remove("active"));
      this.classList.add("active");
      activeStatus = this.getAttribute("data-status") || "all";
      filterCards();
    });
  });

  if (searchInput) {
    searchInput.addEventListener("input", function () {
      searchQuery = this.value.trim().toLowerCase().replace(/^#/, "");
      filterCards();
    });
  }
});