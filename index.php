<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
define('SECURE_ACCESS', true);
include_once 'dasboard-admin/database/koneksi.php';
$pageType = "website";
$pageURL = BASE_URL;
$pageTitle = "Sewa Villa, Glamping, Hotel, Outbound & Catering di Puncak Cisarua, Hanya di Calydore";
require_once __DIR__ . '/includes/header.php';


$logo = "SELECT * FROM assets_images WHERE image_type = 'logo'";
$result_logo = $conn->query($logo);

$imagekit_base_url = "https://ik.imagekit.io/bkx7wk6gv";

$logoImages = [];
if ($result_logo->num_rows > 0) {
  while ($imageRow = $result_logo->fetch_assoc()) {
    $filePath = rtrim($imageRow['file_path'], '/');
    $fileName = ltrim($imageRow['file_name'], '/');

    $logoImages[] = $imagekit_base_url . $filePath . '/' . $fileName;
  }
}


?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/style/category.css">


<div class="container">
  <div class="dropdown">
    <button class="btn dropdown-toggle w-100" type="button" aria-expanded="false" onclick="buttonProduk()">
      Jelajahi Layanan Kami
    </button>
  </div>
</div>

<div class="main-content" id="content-calydore">
  <img src="<?php echo htmlspecialchars($logoImages[0]); ?>" alt="Villa" class="main-image">

  <div class="text-content">
    <h1>CALYDORE</h1>
    <p>
      Temukan berbagai pengalaman terbaik di <span style='font-weight: bold;'>calydore</span>, platform yang
      menghadirkan solusi lengkap dalam satu tempat.<br>
      Kami bukan sekedar penghubung—<span style='font-weight: bold;'>calydore adalah wadah yang aman, mudah, dan
        terpercaya </span>untuk memastikan setiap pemesanan Anda berjalan lancar. Semua proses booking dan pembayaran
      dilakukan langsung melalui Admin terpercaya kami, sehingga Anda bisa menikmati layanan tanpa khawatir.<br>
      Jelajahi berbagai pilihan terbaik dan rancang pengalaman sesuai keinginan Anda. <span
        style='font-weight: bold;'>calydore hadir untuk memberikan kenyamanan dan kemudahan bagi setiap perjalanan
        Anda!</span></p>
    <button class="button" onclick="window.location.href=`page/tentang-kami.php`">Tentang Kami</button>

  </div>
</div>


<div class="menu-container" id="produk">
  <!-- Menu Villa Start -->
  <div class="menu-item" onclick="toggleContent('villa', this)">
    <span class="tittle-menu">Villa</span> <span class="menu-description">
      "Rasakan kenyamanan premium di villa eksklusif di puncak cisarua , lengkap dengan pemandangan indah untuk momen
      tak terlupakan."
    </span>
    <i class="bi bi-arrow-down-circle"></i>
  </div>
  <div id="villa-content" class="menu-content" style="display: none;">
  </div>
  <!-- Menu Villa End -->

  <!--Menu Hotel Start-->
  <div class="menu-item" onclick="toggleContent('hotel', this)">
    <span class="tittle-menu">Hotel</span>
    <span class="menu-description">
      "Nikmati layanan terbaik dan fasilitas mewah di hotel pilihan kawasan Puncak untuk pengalaman liburan yang
      sempurna."
    </span>
    <i class="bi bi-arrow-down-circle"></i>
  </div>
  <div id="hotel-content" class="menu-content" style="display: none;">
  </div>
  <!--End menu hotel end-->

  <!-- Menu Villa kamar Start -->
  <div class="menu-item" onclick="toggleContent('villa-kamar', this)">
    <span class="tittle-menu">Villa Kamar</span>
    <span class="menu-description">
      "Pilih kamar villa nyaman di puncak cisarua dengan fasilitas unggulan untuk pengalaman staycation ideal."
    </span>
    <i class="bi bi-arrow-down-circle"></i>
  </div>
  <div id="villa-kamar-content" class="menu-content" style="display: none;">
  </div>
  <!-- Menu Villa kamar end -->

  <!-- Menu glamping Start -->
  <div class="menu-item" onclick="toggleContent('glamping', this)">
    <span class="tittle-menu">Glamping</span>
    <span class="menu-description">
      "Pengalaman alam yang mewah dalam tenda glamping di kawasan alam cisarua puncak, cocok untuk liburan romantis atau
      keluarga."
    </span>
    <i class="bi bi-arrow-down-circle"></i>
  </div>
  <div id="glamping-content" class="menu-content" style="display: none;">
  </div>
  <!-- Menu glamping end -->

  <!-- Menu outbound Start -->
  <div class="menu-item" onclick="toggleContent('outbound', this)">
    <span class="tittle-menu">Outbound</span>
    <span class="menu-description">
      "Nikmati serunya kegiatan outbound berbagai kawasan alam di cisarua puncak, sempurna untuk kebersamaan dan
      tantangan seru di alam
      terbuka."
    </span>
    <i class="bi bi-arrow-down-circle"></i>
  </div>
  <div id="outbound-content" class="menu-content" style="display: none;">
  </div>
  <!-- Menu outbound end -->

  <!-- Menu catering Start -->
  <div class="menu-item fade-in" onclick="toggleContent('catering', this)">
    <span class="tittle-menu">Catering</span>
    <span class="menu-description">
      "Hidangan berkualitas untuk berbagai acara, dengan cita rasa istimewa."
    </span>
    <i class="bi bi-arrow-down-circle"></i>
  </div>
  <div id="catering-content" class="menu-content" style="display: none;">
  </div>
  <!-- Menu catering end -->
</div>

</div>

<?php
$ChatWa = "Permisi Kak! Mau tanya tentang Calydore...";
$encodedText = urlencode($ChatWa);
$phoneNumber = "6287778911805";
$waLink = "https://api.whatsapp.com/send?phone={$phoneNumber}&text={$encodedText}";

include_once 'includes/wa-kanan.php';
include_once 'includes/testimoni.php';
?>



<?php
include_once 'includes/footer.php';

?>
<script src="assets/js/category.js"></script>
<script>
  function buttonProduk() {
    const produk = document.getElementById('produk');

    if (produk && produk.children.length > 0) {
      const content = produk.children[0];

      const position = content.getBoundingClientRect().top + window.pageYOffset;

      const offset = window.innerHeight * 0.2;

      window.scrollTo({
        top: position - offset,
        behavior: 'smooth'
      });
    }
  }
  if ('scrollRestoration' in history) {
    history.scrollRestoration = "manual";
  }
  document.addEventListener("DOMContentLoaded", function () {
    const menuItems = document.querySelectorAll(".menu-item");

    const checkVisibility = () => {
      menuItems.forEach((item) => {
        const itemTop = item.getBoundingClientRect().top;
        const itemBottom = item.getBoundingClientRect().bottom;

        // Jika elemen masuk ke viewport
        if (itemTop < window.innerHeight && itemBottom > 0) {
          item.classList.add("fade-in");
          item.classList.remove("fade-out");
        } else {
          // Jika elemen keluar dari viewport
          item.classList.add("fade-out");
          item.classList.remove("fade-in");
        }
      });
    };

    // Panggil fungsi saat halaman di-scroll
    window.addEventListener("scroll", checkVisibility);

    // Panggil fungsi saat halaman pertama kali dimuat
    checkVisibility();
  });
</script>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "Calydore",
  "url": "https://calydore.com",
  "logo": "<?php echo BASE_URL; ?>/assets/favico.ico",
  "description": "Calydore adalah platform terbaik untuk sewa villa, hotel, glamping, outbound, dan catering di Puncak Cisarua.",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "Jl. Raya Puncak KM. 79 No. 62, Kopo, Cisarua",
    "addressLocality": "Cisarua",
    "addressRegion": "Jawa Barat",
    "postalCode": "16750",
    "addressCountry": "ID"
  },
  "geo": {
    "@type": "GeoCoordinates",
    "latitude": "-6.671199665215391",
    "longitude": "106.92411747356142"
  },
  "telephone": "+62 877 7891 1805",
  "email": "calydoreofficial@gmail.com",
  "sameAs": [
    "https://www.instagram.com/calydore.official",
    "https://www.tiktok.com/@calydore.official",
    "https://www.facebook.com/CalydoreOfficial"
  ]
}

</script>

</body>

</html>