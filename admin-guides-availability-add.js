document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("add-availability-form");
  const alertBox = document.getElementById("alert-box");
  const guideSearchInput = document.getElementById("guide_search_input");
  const guideSelect = document.getElementById("guide_id");
  const sitesContainer = document.getElementById("working-sites-container");
  const startTimeInput = document.getElementById("start_time");
  const endTimeInput = document.getElementById("end_time");

  if (!form) return;


  function updateWorkingSites() {
    if (!guideSelect || !sitesContainer) return;
    
    const selectedOption = guideSelect.options[guideSelect.selectedIndex];
    sitesContainer.innerHTML = "";

    if (!guideSelect.value || !selectedOption) {
      sitesContainer.innerHTML = `<p class="site-placeholder-text">Please select a guide above to view their assigned heritage sites.</p>`;
      return;
    }

    const sitesAttr = selectedOption.getAttribute("data-heritage-sites") || "";
    if (!sitesAttr.trim()) {
      sitesContainer.innerHTML = `<p class="site-placeholder-text" style="color: #DC2626;">No heritage sites assigned to this guide profile.</p>`;
      return;
    }


    const sitesList = sitesAttr.split(",").map(s => s.trim()).filter(s => s.length > 0);

    sitesList.forEach((site) => {
      const label = document.createElement("label");
      label.className = "vertical-checkbox-item";

      const checkbox = document.createElement("input");
      checkbox.type = "checkbox";
      checkbox.name = "working_sites[]";
      checkbox.value = site;
      checkbox.checked = true; // Automatically checked for ease of use

      label.appendChild(checkbox);
      label.appendChild(document.createTextNode(" " + site));
      sitesContainer.appendChild(label);
    });
  }


  if (guideSelect) {
    guideSelect.addEventListener("change", updateWorkingSites);
  }


  if (guideSearchInput && guideSelect) {
    guideSearchInput.addEventListener("input", function () {
      const term = this.value.trim().toLowerCase();
      let matchedFirst = false;

      Array.from(guideSelect.options).forEach((opt) => {
        if (!opt.value) return;

        const gId = opt.getAttribute("data-guide-id") || "";
        const gName = opt.getAttribute("data-guide-name") || "";

        if (gId.includes(term) || gName.includes(term)) {
          opt.hidden = false;
          if (!matchedFirst && term !== "") {
            guideSelect.value = opt.value;
            updateWorkingSites();
            matchedFirst = true;
          }
        } else {
          opt.hidden = true;
        }
      });

      if (term === "") {
        guideSelect.value = "";
        updateWorkingSites();
      }
    });
  }


  if (guideSelect && guideSelect.value) {
    updateWorkingSites();
  }


  form.addEventListener("submit", (e) => {
    const sites = form.querySelectorAll('input[name="working_sites[]"]:checked');
    if (sites.length === 0) {
      e.preventDefault();
      showAlert("Please select at least one working site for that day.");
      return;
    }

    if (startTimeInput && endTimeInput && startTimeInput.value >= endTimeInput.value) {
      e.preventDefault();
      showAlert("End time must be later than start time.");
      return;
    }
  });

  function showAlert(msg) {
    if (!alertBox) return;
    alertBox.textContent = msg;
    alertBox.className = "alert-box error";
    alertBox.style.display = "block";
    window.scrollTo({ top: 150, behavior: "smooth" });
  }
});