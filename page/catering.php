<?php
define('SECURE_ACCESS', true);
ini_set('display_errors', 1);
error_reporting(E_ALL);
include_once '../dasboard-admin/database/koneksi.php';

$imageQuery = "
    SELECT catering_id, file_name, file_path 
    FROM catering_images 
    WHERE image_type = 'catering'
";
$imagekit_base_url = "https://ik.imagekit.io/bkx7wk6gv";
$imageResult = $conn->query($imageQuery);

$villaImages = [];
if ($imageResult->num_rows > 0) {
    while ($imageRow = $imageResult->fetch_assoc()) {
        $villaId = $imageRow['catering_id'];
        $filePath = rtrim($imageRow['file_path'], '/');
        $fileName = ltrim($imageRow['file_name'], '/');


        $villaImages[$villaId][] = $imagekit_base_url . $filePath . '/' . $fileName;
    }
}

$query = "SELECT o.id, o.nama_catering, o.deskripsi, o.harga, o.category_catering
FROM catering o
INNER JOIN (
    SELECT MIN(id) as id, category_catering
    FROM catering
    GROUP BY category_catering
) AS unique_categories ON o.id = unique_categories.id";
$resultDataCatering = $conn->query($query);


$pageTitle = 'Calydore | Catering Puncak – Menu Lezat untuk Semua Acara';
$description = "Penyedia jasa catering di Puncak Cisarua, Bogor untuk berbagai acara Anda. Sajikan hidangan lezat dengan layanan catering profesional untuk pernikahan, rapat, atau acara lainnya di kawasan Puncak yang sejuk.";
$pageType = "catering";
$pageURL = BASE_URL .
    '/page/catering';
include '../includes/header.php';
?>
<link rel="stylesheet" href="../assets/style/outbound-katalog.css">

<div class="grid-container">
    <div class="grid-item item1">
        <div class="title-category">
            Catering
        </div>
        <p>
            Kami hadir untuk menyajikan hidangan lezat dengan kualitas terbaik untuk berbagai acara di Puncak Cisarua
            Bogor. Dengan bahan segar, rasa autentik, dan pelayanan profesional, kami siap untuk memastikan setiap
            hidangan tidak hanya mengenyangkan, tetapi juga memiliki meninggalkan kesan
            istimewa.</p>
    </div>

    <div class="grid-item item2">

        <div class="slideshow">
            <div class="slides">
                <?php

                foreach ($villaImages as $cateringId => $images) {
                    foreach ($images as $index => $image) {
                        echo '<div class="slide">
                                <img src="' . htmlspecialchars($image) . '?tr=w-640,h-360,q-80" alt="Slide ' . ($index + 1) . '" loading="lazy">
                              </div>';
                    }
                }
                ?>
            </div>
        </div>
    </div>
</div>

<div class=" divider-outbound">
    <span>Berbagai Pilihan Catering</span>
</div>



<section class="row row-cols-2 row-cols-md-4 g-3 mb-2 justify-content-center">
    <?php
    if ($resultDataCatering->num_rows > 0) {

        while ($villa = $resultDataCatering->fetch_assoc()) {
            $villaId = $villa['id'];
            $images = isset($villaImages[$villaId]) ? $villaImages[$villaId] : [];
            $cardLink = "catering_details.php?category=" . $villa['category_catering'];

            echo '
        <div class="col">
            <div class="custom-card">
                <img src="' . htmlspecialchars($images[0] ?? '') . '?tr=w-640,h-640,q-80" class="card-img" alt="' . htmlspecialchars($villa['nama_catering']) . '" loading="lazy">

                </div>
<a href="' . BASE_URL . '/daftar-catering/' . rawurlencode(str_replace(' ', '-', strtolower($villa['category_catering']))) . '" class="booking-btn">' . htmlspecialchars($villa['category_catering']) . '</a>
        </div>';
        }
    }
    ?>
</section>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FoodEstablishment",
  "name": "Calydore | Catering di Puncak – Menu Lezat untuk Semua Acara",
  "url": "https://calydore.com/page/catering",
  "logo": "<?php echo BASE_URL; ?>/assets/favico.ico",
  "description": "Layanan catering terbaik di Puncak Cisarua dengan menu lezat dan harga terjangkau untuk berbagai acara.",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "Jl. Raya Puncak KM. 79 No. 62, Kopo, Cisarua",
    "addressLocality": "Cisarua",
    "addressRegion": "Jawa Barat",
    "postalCode": "16750",
    "addressCountry": "ID"
  },
  "telephone": "+62 877 7891 1805",
  "email": "calydoreofficial@gmail.com",
  "servesCuisine": "Indonesian, Western, Asian",
  "sameAs": [
    "https://www.instagram.com/calydore.official",
    "https://www.tiktok.com/@calydore.official",
    "https://www.facebook.com/CalydoreOfficial"
  ]
}
        </script>


<?php
$ChatWa = "Permisi Kak? Mau tanya tentang Catering di Calydore...";
$encodedText = urlencode($ChatWa);
$phoneNumber = "6287778911805";
$waLink = "https://api.whatsapp.com/send?phone={$phoneNumber}&text={$encodedText}";

include_once '../includes/wa-kanan.php';
require_once '../includes/footer.php';
?>