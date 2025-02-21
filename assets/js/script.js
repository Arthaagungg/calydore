document.addEventListener("DOMContentLoaded", () => {
  // Scroll Effect for Search Input
  const searchContainer = document.querySelector(".search-container");
  const searchInput = document.querySelector(".search-input");

  if (searchContainer && searchInput) {
    window.addEventListener("scroll", () => {
      const rect = searchInput.getBoundingClientRect();

      if (rect.top <= 22) {
        searchContainer.classList.add("small");
        searchInput.placeholder = "Ketik Untuk Mencari...";
      } else {
        searchInput.placeholder = "Cari Semua Di Calydore";
        searchContainer.classList.remove("small");
      }
    });
  }
});
