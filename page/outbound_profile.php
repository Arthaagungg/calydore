<?php
define('SECURE_ACCESS', true);

include_once '../dasboard-admin/database/koneksi.php';

$villaSlug = isset($_GET['slug']) ? $_GET['slug'] : '';
$imagekit_base_url = "https://ik.imagekit.io/bkx7wk6gv";

$query = "
    SELECT v.*, GROUP_CONCAT(CONCAT(vi.file_path, vi.file_name)) AS images
    FROM outbound v
    LEFT JOIN outbound_images vi ON v.id = vi.outbound_id
    WHERE v.slug_outbound = ?
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
    alert("Outbound Tidak Ditemukan!");
    window.location.href = "outbound.php";
    </script>';
    exit;
}

$query_features = "
    SELECT feature_name, feature_value, icons_link 
    FROM outbound_features 
    WHERE outbound_id = ?
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

$pageTitle = 'Calydore | ' . $villa['category_outbound'] . ' terbaik dan terlengkap di Cisarua Puncak';
$description = htmlspecialchars($villa['deskripsi'] ?? 'Ikuti kegiatan outbound seru di Puncak Cisarua, Bogor, yang ideal untuk mempererat hubungan tim atau keluarga. Aktivitas luar ruangan yang menyenangkan dan mendidik, cocok untuk acara perusahaan atau kegiatan liburan bersama. Hubungi kami untuk paket outbound terbaik.', ENT_QUOTES, 'UTF-8');

$pageType = "outbound";
$pageURL = BASE_URL . '/outbound/' . $villaSlug;

require_once '../includes/header.php';
?>
<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/Swiper/11.0.5/swiper-bundle.css'>
<link rel="stylesheet" href="../assets/style/album_profile.css">
<link rel="stylesheet" href="../assets/style/profile.css">
<div class="profile-container">
    <?php
    $isActive = true;
    $imageHTML = ''; // Simpan HTML gambar di sini agar bisa dipakai di 2 tempat
    foreach ($imageArray as $image) {
        $imageTag = '<div class="carousel-item ' . ($isActive ? 'active' : '') . '">
                    <img loading="lazy" src="' . htmlspecialchars($image) . '?tr=q-80,c-at_max,w-800,dpr-2" class="d-block w-100" alt="Outbound">
                </div>';
        $imageHTML .= $imageTag;
        $isActive = false;
    }
    ?>
    <div class="profile-image">
        <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php echo $imageHTML; ?>
            </div>
        </div>

        <div class="profile-detail">
            <h2><?php echo htmlspecialchars($villa['nama_outbound']); ?></h2>
            <p class="location"><i class="fa fa-map-marker" aria-hidden="true"></i>
                <?php echo htmlspecialchars($villa['lokasi']); ?></p>
            <p class="description"><?php echo nl2br(htmlspecialchars($villa['deskripsi'])); ?></p>
            <div class="price-info">
                <p class="desc1">Mulai dari</p>
                <p class="price">Rp <?php echo number_format($villa['harga'], 0, ',', '.'); ?> / Orang</p>
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
                                '
                            </span></li>';
                        }
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>

    <?php
    $sql_game_kompetisi = "SELECT * FROM assets_images WHERE image_type = 'game kompetisi'";
    $sql_game_rotasi = "SELECT * FROM assets_images WHERE image_type = 'game rotasi'";

    $result_kompetisi = $conn->query($sql_game_kompetisi);
    $result_rotasi = $conn->query($sql_game_rotasi);

    if ($villa['category_outbound'] == 'family gathering') {
        if ($villa['game_kompetisi'] == 'ada') {
            ?>
            <div class="menu-utama">
                <div class="divider">
                    <h4>Game Kompetisi</h4>
                </div>
                <div class="album-container">
                    <?php
                    if ($result_kompetisi->num_rows > 0) {
                        while ($row_kompetisi = $result_kompetisi->fetch_assoc()) {
                            $name_assets = $row_kompetisi['name_assets'];
                            $image_path = $row_kompetisi['file_path'];
                            $image_name = $row_kompetisi['file_name'];
                            $image_url = $imagekit_base_url . $image_path . $image_name;
                            ?>
                            <div class="image-with-text" onclick="openModal('<?php echo $image_url; ?>')">
                                <img src="<?php echo $image_url; ?>?tr=w-650,h-650,q-80" alt="<?php echo $name_assets; ?>"
                                    loading="lazy">
                                <div class="text-overlay">
                                    <span><?php echo $name_assets; ?></span>
                                </div>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
            </div>
        <?php }
        if ($villa['game_rotasi'] == 'ada') {
            ?>
            <div class="menu-opsional">
                <div class="divider">
                    <h4>Game Rotasi</h4>
                </div>
                <div class="album-container">
                    <?php
                    if ($result_rotasi->num_rows > 0) {
                        while ($row = $result_rotasi->fetch_assoc()) {
                            $name_assets = $row['name_assets'];
                            $image_path = $row['file_path'];
                            $image_name = $row['file_name'];
                            $image_url = $imagekit_base_url . $image_path . $image_name;
                            ?>
                            <div class="image-with-text" onclick="openModal('<?php echo $image_url; ?>')">
                                <img src="<?php echo $image_url; ?>?tr=w-650,h-650,q-80" alt="<?php echo $name_assets; ?>"
                                    loading="lazy">
                                <div class="text-overlay">
                                    <span><?php echo $name_assets; ?></span>
                                </div>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
            </div>
            <?php
        }
    }
    ?>

    <!-- Modal -->
    <div id="imageModal" class="modal">
        <span class="close" onclick="closeModal()">&times;</span>
        <img class="modal-content" id="modalImage">
    </div>

    <div class="profile-album">
        <h3>Album Outbound</h3>
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

    <div class="profile-rekomendasi">
        <h3>Outbound Rekomendasi</h3>
        <ul>
            <?php
            $query_recommendations = "
                SELECT v.id, v.nama_outbound, v.harga, v.slug_outbound,
                       (SELECT CONCAT(vi.file_path, vi.file_name) 
                        FROM outbound_images vi 
                        WHERE vi.outbound_id = v.id 
                        LIMIT 1) AS first_image
                FROM outbound v
                WHERE v.id <> " . intval($villa['id']) . "
                LIMIT 8
            ";
            $result_recommendations = $conn->query($query_recommendations);

            if ($result_recommendations->num_rows > 0) {
                while ($recommended_villa = $result_recommendations->fetch_assoc()) {
                    $imageSrc = $recommended_villa['first_image'] ? $imagekit_base_url . $recommended_villa['first_image'] : 'path/to/default-image.jpg';
                    echo '
                        <li>
                            <a href="' . BASE_URL . '/outbound/' . $recommended_villa['slug_outbound'] . '">
                                <img src="' . htmlspecialchars($imageSrc) . '" alt="Outbound Image" class="recommended-image">
                                <div class="recommended-details">
                                    <h4>' . htmlspecialchars($recommended_villa['nama_outbound']) . '</h4>
                                    <p><i class="fa fa-map-marker" aria-hidden="true"></i> ' . htmlspecialchars($recommended_villa['harga']) . '</p>
                                </div>
                            </a>
                        </li>';
                }
            }
            ?>
        </ul>
    </div>
</div>

<script src='//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/Swiper/8.4.5/swiper-bundle.min.js'></script>
<script src="../assets/js/album_profile.js"></script>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Resort",
  "name": "Outbound <?php echo htmlspecialchars($villa['nama_outbound']); ?>",
  "url": "https://calydore.com/page/outbound/<?php echo urlencode($villa['slug_outbound']); ?>",
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
  "priceRange": "Mulai Dari Rp.<?php echo htmlspecialchars($villa['harga']); ?> per orang",
  "sameAs": [
    "https://www.instagram.com/calydore.official",
    "https://www.tiktok.com/@calydore.official",
    "https://www.facebook.com/CalydoreOfficial"
  ]
}
</script>
<?php
$ChatWa = "Permisi Kak? untuk " . htmlspecialchars($villa['nama_outbound']) . " Di, tanggal ... Ready ga ?";
$encodedText = urlencode($ChatWa);
$phoneNumber = "6287778911805";
$waLink = "https://api.whatsapp.com/send?phone={$phoneNumber}&text={$encodedText}";

include_once '../includes/wa-tengah.php';
require_once '../includes/footer.php';
?>