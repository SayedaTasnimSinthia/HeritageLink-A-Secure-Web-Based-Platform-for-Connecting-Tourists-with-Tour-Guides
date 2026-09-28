document.addEventListener("DOMContentLoaded", () => {
  const forms = document.querySelectorAll(".star-rating-form");

  forms.forEach((form) => {
    const starsGroup = form.querySelector(".stars-group");
    const starBtns = starsGroup.querySelectorAll(".star-btn");
    const hiddenInput = form.querySelector(".rating-value-input");
    const feedbackText = form.querySelector(".rating-feedback-text");

    let currentRating = parseInt(hiddenInput.value, 10) || 0;

    function renderStars(rating) {
      starBtns.forEach((star) => {
        const val = parseInt(star.getAttribute("data-val"), 10);
        if (val <= rating) {
          star.classList.remove("fa-regular");
          star.classList.add("fa-solid", "active");
        } else {
          star.classList.remove("fa-solid", "active");
          star.classList.add("fa-regular");
        }
      });
    }

    starBtns.forEach((star) => {
      star.addEventListener("mouseenter", function () {
        const hoverVal = parseInt(this.getAttribute("data-val"), 10);
        renderStars(hoverVal);
        if (feedbackText) {
          feedbackText.textContent = `${hoverVal} / 5 Stars`;
        }
      });

  
      star.addEventListener("click", function () {
        currentRating = parseInt(this.getAttribute("data-val"), 10);
        hiddenInput.value = currentRating;
        renderStars(currentRating);
        if (feedbackText) {
          feedbackText.textContent = `${currentRating} / 5 Stars`;
        }
      });
    });

    starsGroup.addEventListener("mouseleave", () => {
      renderStars(currentRating);
      if (feedbackText) {
        feedbackText.textContent = currentRating > 0 ? `${currentRating} / 5 Stars` : "Click a star to rate";
      }
    });


    form.addEventListener("submit", (e) => {
      if (!currentRating || currentRating < 1 || currentRating > 5) {
        e.preventDefault();
        alert("Please select a rating out of 5 stars before submitting.");
      }
    });
  });
});