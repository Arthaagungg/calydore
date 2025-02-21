// Ambil base URL secara otomatis
const BASE_URL =
  window.location.origin +
  window.location.pathname.split("/").slice(0, 2).join("/").replace(/\/$/, "");

// Fungsi untuk generate konten berdasarkan data yang diambil menggunakan AJAX
let cacheData = {};

document.addEventListener("DOMContentLoaded", function () {
  let menus = [
    "villa",
    "villa-kamar",
    "glamping",
    "outbound",
    "catering",
    "hotel",
  ];
  menus.forEach((menu) => {
    fetch("includes/database-user/get_" + menu + ".php")
      .then((response) => response.json())
      .then((data) => {
        cacheData[menu] = data; // Simpan data ke cache
      })
      .catch((error) => console.error(`Error loading ${menu}:`, error));
  });
});

// Fungsi untuk menampilkan data dari cache jika sudah ada
function generateContent(menu) {
  let contentHtml = "";
  if (cacheData[menu]) {
    renderContent(menu, cacheData[menu]); // Gunakan data dari cache
  } else {
    fetch("includes/database-user/get_" + menu + ".php")
      .then((response) => response.json())
      .then((data) => {
        cacheData[menu] = data;
        renderContent(menu, data);
      })
      .catch((error) => console.error("Error fetching data:", error));
  }
}

// Fungsi untuk merender data ke halaman
function renderContent(menu, data) {
  let contentHtml = "";
  data.forEach((item) => {
    contentHtml += generateItemHtml(item, menu); // Gunakan fungsi dinamis untuk item
  });
  contentHtml += `
<a class="btn-layanan" href="${BASE_URL}/page/${menu}">
  Jelajahi pilihan ${menu.replace("-", " ")} lainnya.
  <i class="uil uil-arrow-right"></i>
</a>

  `;
  document.getElementById(`${menu}-content`).innerHTML = contentHtml;
  initializeSlickSlider();
}

// Fungsi untuk membuat HTML dinamis berdasarkan jenis menu dan item
function generateItemHtml(item, menu) {
  // Validasi item.images agar tidak ada error jika undefined/null
  if (!item.images || !Array.isArray(item.images)) {
    console.error("Item missing images:", item);
    return ""; // Skip jika tidak ada gambar
  }

  // Generate HTML untuk gambar
  let imagesHtml = item.images
    .map(
      (src) =>
        `<img src="${src}?tr=w-640,h-360,q-80" alt="${item.nama}" loading="lazy">`
    )
    .join("");

  // Generate price info yang berbeda untuk outbound dan catering
  let priceInfo = "";
  let rating = "";

  if (menu === "outbound" || menu === "catering") {
    let harga = Number(item.harga);
    let formattedHarga = harga.toLocaleString("id-ID");
    priceInfo = `
        <div class="price-info">
            <p class="desc1">Mulai dari</p>
            <div class="price-row">
                <p class="price">Rp ${formattedHarga}</p>
                <p class="desc2">Per orang</p>
            </div>
        </div>
    `;
  } else {
    let harga_weekday = Number(item.harga_weekday);
    let formattedHarga_weekday = harga_weekday.toLocaleString("id-ID");
    rating = `
     
               <div class="location-rating">
                   <div class="location">
                       <i class="fa fa-map-marker" aria-hidden="true"></i> ${item.lokasi}
                   </div>
                   <div class="rating">
                       <i class="fa fa-star icon-small" aria-hidden="true"></i> ${item.rating} / 5
                   </div>
               </div>`;
    priceInfo = `
         <div class="price-info">
             <p class="desc1">Mulai dari</p>
             <div class="price-row">
                 <p class="price">Rp ${formattedHarga_weekday}</p>
                 <p class="desc2">Per Malam</p>
             </div>
         </div>
     `;
  }
  const maxLength = 200;
  let shortDesc = item.deskripsi;

  // Jika panjang deskripsi lebih dari maxLength, potong di akhir kata
  if (shortDesc.length > maxLength) {
    let trimmedText = shortDesc.slice(0, maxLength);
    shortDesc = trimmedText.replace(/\s+\S*$/, "..."); // Hapus kata terakhir yang kepotong
  }
  // Generate main HTML
  return `
  <div class="sub-box">
    <div class="slider slick-slider">
      ${imagesHtml}
    </div>
    <div class="sub-box-content">
      ${rating}
      <p class="title">${item.nama}</p>
      <p class="descTitle">${shortDesc}</p>
      ${priceInfo}
    </div>
<a class="btn-selengkapnya" href="${BASE_URL}/${menu}/${item.slug}">
  Selengkapnya
</a>


  </div>
`;
}

function initializeSlickSlider() {
  // Hapus inisialisasi Slick jika sudah ada
  $(".slick-slider").each(function () {
    if ($(this).hasClass("slick-initialized")) {
      $(this).slick("unslick"); // Hapus slider sebelumnya
    }
  });

  // Inisialisasi slider jika elemen memiliki konten
  $(".slick-slider").each(function () {
    if ($(this).children().length === 0) {
      console.error("Slider is empty:", this);
      return; // Skip slider jika kosong
    }

    $(this).slick({
      dots: false,
      infinite: true,
      slidesToShow: 1,
      slidesToScroll: 1,
      autoplay: true,
      autoplaySpeed: 2000,
      arrows: true,
      prevArrow:
        '<button type="button" class="slick-prev-custom"><i class="uil uil-angle-left"></i></button>',
      nextArrow:
        '<button type="button" class="slick-next-custom"><i class="uil uil-angle-right"></i></button>',
    });
  });
}

function toggleContent(menuId, menuItem) {
  const content = document.getElementById(menuId + "-content");
  const arrow = menuItem.querySelector("i"); // Menemukan ikon panah dalam menu item

  // Cek jika ikon panah sudah memiliki class 'rotate'
  if (!arrow.classList.contains("rotate")) {
    // Jika tidak memiliki class 'rotate', aktifkan scroll
    if (menuItem.children.length > 0) {
      const firstChild = menuItem.children[0]; // Mengambil anak pertama
      setTimeout(() => {
        const position =
          firstChild.getBoundingClientRect().top + window.pageYOffset;
        const offset = window.innerHeight * 0.2; // 20% dari tinggi layar

        window.scrollTo({
          top: position - offset, // Pastikan scroll setelah tampilan sudah stabil
          behavior: "smooth",
        });
      }, 150);
    }
  }
  const contents = {
    villa: document.getElementById("villa-content"),
    "villa-kamar": document.getElementById("villa-kamar-content"),
    glamping: document.getElementById("glamping-content"),
    outbound: document.getElementById("outbound-content"),
    catering: document.getElementById("catering-content"),
    hotel: document.getElementById("hotel-content"),
  };

  // Menyembunyikan semua konten kecuali yang sedang diklik
  Object.keys(contents).forEach((key) => {
    if (key !== menuId && contents[key]) {
      contents[key].style.display = "none";
      contents[key].classList.remove("active"); // Hapus animasi jika ada
    }
  });

  const contentBox = contents[menuId];

  if (!contentBox) {
    console.error("Konten dengan ID " + menuId + " tidak ditemukan!");
    return;
  }
  const allArrows = document.querySelectorAll(".menu-item i");
  allArrows.forEach((arrowItem) => {
    arrowItem.classList.remove("rotate");
  });

  // Toggle tampilan konten
  if (contentBox.style.display === "flex") {
    setTimeout(() => {
      contentBox.style.display = "none";
      contentBox.classList.remove("active");
      arrow.classList.remove("rotate");
    }, 500);
  } else {
    setTimeout(() => {
      contentBox.style.display = "flex";
      contentBox.classList.add("active");
      arrow.classList.add("rotate");

      generateContent(menuId); // Generate konten setelah delay
    }, 300);
  }
  // Delay 700ms sebelum konten muncul
}

// Event listener untuk menu
document.querySelectorAll(".menu-item").forEach((item) => {
  item.addEventListener("click", (event) => {
    const menu = event.target.getAttribute("data-menu");
    if (menu) {
      toggleContent(menu, event.currentTarget); // Kirim elemen menu sebagai argumen kedua
    }
  });
});
