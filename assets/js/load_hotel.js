let arrowState = "default";

function toggleArrow() {
  const arrowIcon = document.getElementById("arrowIcon");
  const tabUp = document.getElementById("harga-tab-up");
  const tabDown = document.getElementById("harga-tab-down");

  if (arrowState === "default") {
    arrowIcon.classList.remove("fa-arrows-alt-v");
    arrowIcon.classList.add("fa-arrow-up");
    tabUp.classList.add("show", "active");
    tabDown.classList.remove("show", "active");
    arrowState = "up";
  } else if (arrowState === "up") {
    arrowIcon.classList.remove("fa-arrow-up");
    arrowIcon.classList.add("fa-arrow-down");
    tabUp.classList.remove("show", "active");
    tabDown.classList.add("show", "active");
    arrowState = "down";
  } else if (arrowState === "down") {
    arrowIcon.classList.remove("fa-arrow-down");
    arrowIcon.classList.add("fa-arrow-up");
    tabDown.classList.remove("show", "active");
    tabUp.classList.add("show", "active");
    arrowState = "up";
  }
}

document.querySelectorAll(".nav-link").forEach((item) => {
  item.addEventListener("click", (event) => {
    if (!event.target.id.includes("harga")) {
      const arrowIcon = document.getElementById("arrowIcon");
      arrowIcon.classList.remove("fa-arrow-up", "fa-arrow-down");
      arrowIcon.classList.add("fa-arrows-alt-v");
      document
        .getElementById("harga-tab-up")
        .classList.remove("show", "active");
      document
        .getElementById("harga-tab-down")
        .classList.remove("show", "active");

      arrowState = "default";
    }
  });
});

const categoryState = {
  villa: {
    currentPage: 1,
    isLastPage: false,
  },
  rekomendasi: {
    currentPage: 1,
    isLastPage: false,
  },
  terlaris: {
    currentPage: 1,
    isLastPage: false,
  },
  hargaup: {
    currentPage: 1,
    isLastPage: false,
  },
  hargadown: {
    currentPage: 1,
    isLastPage: false,
  },
};

function loadMore(categoryName) {
  const state = categoryState[categoryName];

  if (!state || state.isLastPage) {
    console.log(`Semua data untuk kategori ${categoryName} telah dimuat.`);
    return;
  }
  state.currentPage++;

  fetch(
    `../includes/database-user/load_hotel.php?page=${
      state.currentPage
    }&tab=${encodeURIComponent(categoryName)}`
  )
    .then((response) => {
      if (!response.ok) {
        console.error(
          `Ser  er error: ${response.status} ${response.statusText}`
        );
        throw new Error("Gagal memuat data dari server");
      }
      return response.json();
    })
    .then((data) => {
      if (
        !data ||
        !data.data.html ||
        typeof data.data.isLastPage === "undefined"
      ) {
        console.error("Format respons server tidak sesuai:", data);
        return;
      }

      const categoryList = document.getElementById(categoryName);
      if (!categoryList) {
        console.error(`Elemen kategori ${categoryName} tidak ditemukan.`);
        return;
      }

      const newContent = document.createElement("div");
      newContent.innerHTML = data.data.html;

      const firstNewContent = newContent.children[0];
      categoryList.append(...newContent.children);
      if (firstNewContent) {
        firstNewContent.scrollIntoView({
          behavior: "smooth",
        });
      }

      state.isLastPage = data.data.isLastPage;

      if (state.isLastPage) {
        const loadMoreButton = document.getElementById(
          `loadMore${capitalizeFirstLetter(categoryName)}`
        );

        if (loadMoreButton) loadMoreButton.style.display = "none";
      }
    })
    .catch((error) => {
      console.error(
        "Terjadi kesalahan saat memuat data:",
        error.message || error
      );
      if (error.stack) console.error(error.stack);
    });
}

function capitalizeFirstLetter(string) {
  return string.charAt(0).toUpperCase() + string.slice(1);
}
