// Ambil jumlah slide yang ada di dalam Swiper
var totalSlides = document.querySelectorAll(".mySwiper .swiper-slide").length;

// Konfigurasi Swiper
var swiperConfig = {
  effect: "coverflow",
  grabCursor: true,
  centeredSlides: true,
  slidesPerView: "auto",
  coverflowEffect: {
    rotate: 0,
    stretch: 0,
    depth: 100,
    modifier: 2,
    slideShadows: true,
  },
  spaceBetween: 1,
  initialSlide: 0, // Pastikan mulai dari slide pertama

  navigation: {
    nextEl: ".swiper-button-next",
    prevEl: ".swiper-button-prev",
  },

  breakpoints: {
    320: { slidesPerView: 1.5, spaceBetween: 10 }, // HP kecil
    480: { slidesPerView: 2, spaceBetween: 15 }, // Tablet kecil
    768: { slidesPerView: 2.5, spaceBetween: 15 }, // Tablet besar
    1024: { slidesPerView: 3, spaceBetween: 20 }, // Laptop/PC
  },
};

// **Cek jumlah gambar**
// **Jika gambar lebih dari 2, gunakan loop**
if (totalSlides > 2) {
  swiperConfig.loop = true;
  swiperConfig.loopedSlides = totalSlides;
  swiperConfig.loopAdditionalSlides = totalSlides;
}

var swiper = new Swiper(".mySwiper", swiperConfig);

// === MODAL FUNCTION ===
function openModal(imageSrc) {
  document.getElementById("modalImage").src = imageSrc;
  document.getElementById("imageModal").style.display = "flex";
}

function closeModal() {
  document.getElementById("imageModal").style.display = "none";
}

// Tutup modal jika klik di luar gambar
window.onclick = function (event) {
  var modal = document.getElementById("imageModal");
  if (event.target === modal) {
    closeModal();
  }
};
document.addEventListener("click", function (event) {
  let clickedSlide = event.target.closest(".swiper-slide"); // Mendeteksi slide yang diklik
  let clickedImage = event.target.closest(".album-image"); // Mendeteksi gambar yang diklik

  if (clickedImage) {
    let imgSrc = clickedImage.src; // Ambil langsung sumber gambar
    openModal(imgSrc);
    return; // Hentikan eksekusi lebih lanjut agar tidak masuk ke kondisi berikutnya
  }

  if (clickedSlide) {
    if (clickedSlide.classList.contains("swiper-slide-active")) {
      // Jika slide yang diklik sudah aktif, buka modal
      let img = clickedSlide.querySelector("img");
      if (img) {
        openModal(img.src);
      }
    } else {
      // Jika slide belum aktif, jadikan slide yang diklik menjadi aktif
      let slides = Array.from(document.querySelectorAll(".swiper-slide"));
      let index = slides.indexOf(clickedSlide);
      swiper.slideTo(index);
    }
  }
});
