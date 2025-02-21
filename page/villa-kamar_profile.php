<?php
define('SECURE_ACCESS', true);

include_once '../dasboard-admin/database/koneksi.php';


$villaSlug = isset($_GET['slug']) ? $_GET['slug'] : '';
$imagekit_base_url = "https://ik.imagekit.io/bkx7wk6gv";


$query = "
    SELECT v.*, GROUP_CONCAT(CONCAT(vi.file_path, vi.file_name)) AS images
    FROM villa_kamar v
    LEFT JOIN villa_kamar_images vi ON v.id = vi.villa_kamar_id
    WHERE v.slug_villa_kamar = ?
    GROUP BY v.id
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Gagal menyiapkan query: " . $conn->error);
}
$stmt->bind_param('s', $villaSlug);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $villa = $result->fetch_assoc();
} else {
    echo '<script>
    alert("villa_kamar Tidak Ditemukan!");
    window.location.href = "villa_kamar.php";
    </script>';
    exit;
}


$query_features = "
    SELECT feature_name, feature_value, icons_link 
    FROM villa_kamar_features 
    WHERE villa_kamar_id = ?
";
$stmt_features = $conn->prepare($query_features);
if (!$stmt_features) {
    die("Gagal menyiapkan query fitur: " . $conn->error);
}
$stmt_features->bind_param('i', $villa['id']);
$stmt_features->execute();
$result_features = $stmt_features->get_result();

$features = [];
if ($result_features->num_rows > 0) {
    while ($row = $result_features->fetch_assoc()) {
        $features[] = [
            'feature_name' => $row['feature_name'],
            'feature_value' => $row['feature_value'],
            'icon_link' => $row['icons_link'],
        ];
    }
}


$imageArray = isset($villa['images']) ? explode(',', $villa['images']) : [];
$imageArray = array_map(function ($image) use ($imagekit_base_url) {
    return $imagekit_base_url . $image;
}, $imageArray);
$pageTitle = 'Calydore | ' . $villa['nama_villa_kamar'] . ' di Puncak – Nyaman & Private';
$description = htmlspecialchars($villa['deskripsi'] ?? 'Sewa kamar villa eksklusif di kawasan area Puncak Cisarua, Bogor, dengan pemandangan alam yang menakjubkan dan fasilitas premium. Nikmati kenyamanan dan ketenangan selama menginap di kamar villa mewah dengan layanan terbaik. Booking kamar villa Anda sekarang untuk liburan yang sempurna!', ENT_QUOTES, 'UTF-8');
$pageType = "villa kamar";
$pageURL = BASE_URL .
    '/villa-kamar/' . $villaSlug;


require_once '../includes/header.php';
?>
<link rel='stylesheet' href='../assets/src/swiper/swiper-bundle.min.css'>
<link rel="stylesheet" href="../assets/style/album_profile.css">
<link rel="stylesheet" href="../assets/style/profile.css">
<div class="profile-container">

    <div class="profile-image">
        <?php
        $isActive = true;
        $imageHTML = ''; // Simpan HTML gambar di sini agar bisa dipakai di 2 tempat
        foreach ($imageArray as $image) {
            $imageTag = '<div class="carousel-item ' . ($isActive ? 'active' : '') . '">
                    <img loading="lazy" src="' . htmlspecialchars($image) . '?tr=q-80,c-at_max,w-800,dpr-2" class="d-block w-100" alt="Villa" >
                </div>';
            $imageHTML .= $imageTag;
            $isActive = false;
        }
        ?>
        <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php echo $imageHTML; ?>
            </div>
        </div>


        <div class="profile-detail">
            <div class="villa-details">
                <h2>Villa Kamar <?php echo htmlspecialchars($villa['nama_villa_kamar']); ?></h2>
                <p class="location"><i class="fa fa-map-marker" aria-hidden="true"></i>
                    <?php echo htmlspecialchars($villa['lokasi']); ?>
                </p>
                <p class="rating"><i class="fa fa-star icon-small" aria-hidden="true"></i>
                    <?php echo htmlspecialchars($villa['rating']); ?> / 5
                </p>
                <p class="description"><?php echo nl2br(htmlspecialchars($villa['deskripsi'])); ?></p>
                <div class="price-info">
                    <p class="desc1">Mulai dari</p>
                    <p class="price">Rp <?php echo number_format($villa['harga_weekday'], 0, ',', '.'); ?> / malam</p>
                    <p class="kapasitas">Kapasitas : <?php echo htmlspecialchars($villa['kapasitas']); ?></p>
                </div>
            </div>


            <div class="fasilitas">
                <h3>Fasilitas</h3>
                <ul>
                    <?php
                    foreach ($features as $feature) {
                        if (!empty($feature['icon_link'])) {
                            echo '<li>
                                ' . ($feature['icon_link']) . '
                                &nbsp;&nbsp;' . htmlspecialchars(ucwords(str_replace('_', ' ', $feature['feature_name']))) .
                                ' : <span>' . htmlspecialchars(ucwords($feature['feature_value'])) . '
                            </span></li>';
                        }
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="profile-album">
        <h3>Album Gambar</h3>
        <div class="swiper-container">
            <div class="swiper mySwiper">
                <div class="swiper-wrapper">
                    <?php echo str_replace('carousel-item', 'swiper-slide', $imageHTML); ?>
                </div>
            </div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
    </div>

    <!-- Modal -->
    <div id="imageModal" class="modal">
        <span class="close" onclick="closeModal()">&times;</span>
        <img class="modal-content" id="modalImage">
    </div>


    <div class="profile-rekomendasi">
        <h3>Villa Kamar Rekomendasi</h3>
        <ul>
            <?php
            $query_recommendations = "
            SELECT v.id, v.nama_villa_kamar, v.lokasi, v.rating, v.slug_villa_kamar,
                (SELECT CONCAT(vi.file_path, vi.file_name) 
                    FROM villa_kamar_images vi 
                    WHERE vi.villa_kamar_id = v.id 
                    LIMIT 1) AS first_image
            FROM villa_kamar v
            WHERE v.id <> " . intval($villa['id']) . "
            ORDER BY v.rating DESC 
            LIMIT 8
            ";

            $result_recommendations = $conn->query($query_recommendations);

            if ($result_recommendations->num_rows > 0) {
                while ($recommended_villa = $result_recommendations->fetch_assoc()) {
                    $imageSrc = $recommended_villa['first_image'] ? $imagekit_base_url . $recommended_villa['first_image'] : 'path/to/default-image.jpg';

                    echo '
                        <li>
                            <a href="' . BASE_URL . '/villa-kamar/' . htmlspecialchars($recommended_villa['slug_villa_kamar']) . '">
                                <img src="' . htmlspecialchars($imageSrc) . '?tr=w-640,h-360,q-80" alt="Villa Image" class="recommended-image" loading="lazy">
                                <div class="recommended-details">
                                    <h4>Villa Kamar ' . htmlspecialchars($recommended_villa['nama_villa_kamar']) . '</h4>
                                    <p><i class="fa fa-map-marker" aria-hidden="true"></i> ' . htmlspecialchars($recommended_villa['lokasi']) . '</p>
                                    <p><i class="fa fa-star icon-small" aria-hidden="true"></i> ' . htmlspecialchars($recommended_villa['rating']) . ' / 5</p>
                                </div>
                            </a>
                        </li>';
                }
            }
            ?>
        </ul>
    </div>
</div>


<script src='../assets/src/swiper/swiper-bundle.min.js'></script>
<script src="../assets/js/album_profile.js"></script>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Resort",
  "name": "Villa Kamar <?php echo htmlspecialchars($villa['nama_villa_kamar']); ?>",
  "url": "https://calydore.com/page/villa-kamar/<?php echo urlencode($villa['slug_villa_kamar']); ?>",
  "logo": "<?php echo BASE_URL; ?>/assets/favico.ico",
  "image": "<?php echo htmlspecialchars($imageArray[0]); ?>",
  "description": "<?php echo htmlspecialchars($villa['deskripsi']); ?>",
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
  "priceRange": "Rp.<?php echo htmlspecialchars($villa['harga_weekday']); ?> - Rp.<?php echo htmlspecialchars($villa['harga_weekend']); ?> per malam",
  "starRating": {
    "@type": "Rating",
    "ratingValue": "<?php echo htmlspecialchars($villa['rating']); ?>",
    "bestRating": "5"
  },
  "amenityFeature": [
    <?php
    $featureList = [];
    foreach ($features as $feature) {
        $featureList[] = '{
            "@type": "LocationFeatureSpecification",
            "name": "' . htmlspecialchars($feature['feature_name']) . '",
            "value": "' . ($feature['feature_value'] ? "true" : "false") . '"
        }';
    }
    echo implode(',', $featureList);
    ?>
  ],
  "sameAs": [
    "https://www.instagram.com/calydore.official",
    "https://www.tiktok.com/@calydore.official",
    "https://www.facebook.com/CalydoreOfficial"
  ]
}
</script>


<?php
$ChatWa = "Permisi Kak? untuk Villa kamar " . htmlspecialchars($villa['nama_villa_kamar']) . ", tanggal ... Ready ga ?";
$encodedText = urlencode($ChatWa);
$phoneNumber = "6287778911805";
$waLink = "https://api.whatsapp.com/send?phone={$phoneNumber}&text={$encodedText}";

include_once '../includes/wa-tengah.php';
require_once '../includes/footer.php';
?>