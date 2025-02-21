<?php
define('SECURE_ACCESS', true);

include_once '../dasboard-admin/database/koneksi.php';
$imagekit_base_url = "https://ik.imagekit.io/bkx7wk6gv";

$imageQuery = "SELECT outbound_id, file_name, file_path FROM outbound_images";
$imageResult = $conn->query($imageQuery);

$villaImages = [];
if ($imageResult->num_rows > 0) {
    while ($imageRow = $imageResult->fetch_assoc()) {
        $villaId = $imageRow['outbound_id'];
        $filePath = rtrim($imageRow['file_path'], '/');
        $fileName = ltrim($imageRow['file_name'], '/');


        $villaImages[$villaId][] = $imagekit_base_url . $filePath . '/' . $fileName;
    }
}

$query = "SELECT o.id, o.nama_outbound, o.deskripsi, o.kapasitas, o.harga, o.lokasi, o.category_outbound, o.slug_outbound
FROM outbound o
INNER JOIN (
    SELECT MIN(id) as id, category_outbound
    FROM outbound
    GROUP BY category_outbound
) AS unique_categories ON o.id = unique_categories.id";
$resultDataOutbound = $conn->query($query);

$pageTitle = 'Calydore | Paket Outbound Puncak – Team Building & Fun Games';
$description = "Ikuti kegiatan outbound seru di Puncak Cisarua, Bogor. Aktivitas luar ruangan untuk tim atau keluarga, cocok untuk membangun kerjasama dan kekompakan di kawasan alam yang asri. Hubungi kami untuk paket outbound terbaik.";
$pageType = "outbound";
$pageURL = BASE_URL .
    '/page/outbound';
include '../includes/header.php';

?>
<link rel="stylesheet" href="../assets/style/outbound-katalog.css">

<div class="grid-container">
    <div class="grid-item item1">
        <div class="title-category">
            Outbound
        </div>
        <p>
            Rasakan pengalaman outbound yang seru dan penuh tantangan!
            Nikmati berbagai aktivitas yang memperkuat teamwork, keberanian,
            dan kreativitas dalam suasana alam yang asri.dan kreativitas dalam suasana alam yang asri.dan kreativitas
        </p>
    </div>

    <div class="grid-item item2">
        <div class="slideshow">
            <div class="slides">
                <?php
                foreach ($villaImages as $outboundId => $images) {
                    foreach ($images as $index => $image) {
                        echo '<div class="slide">
                                <img src="' . htmlspecialchars($image) . '?tr=w-640,h-360,q-80" alt="Slide ' . ($index + 1) . 'loading="lazy"">
                              </div>';
                    }
                }
                ?>
            </div>
        </div>
    </div>
</div>

<div class="divider-outbound">
    <span>Berbagai Pilihan Outbound</span>
</div>

<section class="row row-cols-2 row-cols-md-4 g-3 mb-2 justify-content-center">
    <?php
    while ($villa = $resultDataOutbound->fetch_assoc()) {
        $villaId = $villa['id'];
        $images = isset($villaImages[$villaId]) ? $villaImages[$villaId] : [];
        echo '
        <div class="col">
            <div class="custom-card">
                <img src="' . htmlspecialchars($images[0] ?? '') . '?tr=w-640,h-640,q-80" class="card-img" alt="' . htmlspecialchars($villa['nama_outbound']) . '" loading="lazy">
            </div>
<a href="' . BASE_URL . '/daftar-outbound/' . rawurlencode(str_replace(' ', '-', strtolower($villa['category_outbound']))) . '" class="booking-btn">' . htmlspecialchars($villa['category_outbound']) . '</a>
        </div>';
    }
    ?>
</section>
<script type="application/ld+json">
    {
    "@context": "https://schema.org",
    "@type": "EventVenue",
    "name": "Calydore | Paket Outbound Puncak – Team Building & Fun Games",
    "url": "https://calydore.com/page/outbound",
    "logo": "
    <?php echo BASE_URL; ?>/assets/favico.ico",
    "description": "Paket outbound di Puncak Cisarua untuk team building, fun games, dan kegiatan seru lainnya.",
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
    "sameAs": [
    "https://www.instagram.com/calydore.official",
    "https://www.tiktok.com/@calydore.official",
    "https://www.facebook.com/CalydoreOfficial"
    ]
    }
    </script>



<?php
$ChatWa = "Permisi Kak? Mau tanya tentang Outbound di Calydore...";
$encodedText = urlencode($ChatWa);
$phoneNumber = "6287778911805";
$waLink = "https://api.whatsapp.com/send?phone={$phoneNumber}&text={$encodedText}";

include_once '../includes/wa-kanan.php';
require_once '../includes/footer.php';
?>