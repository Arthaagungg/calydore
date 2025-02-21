<?php
define('SECURE_ACCESS', true);

include_once '../dasboard-admin/database/koneksi.php';


$villaSlug = isset($_GET['slug']) ? $_GET['slug'] : '';
$imagekit_base_url = "https://ik.imagekit.io/bkx7wk6gv";




$query = "
    SELECT v.*, 
           GROUP_CONCAT(CONCAT(vi.file_path, vi.file_name)) AS images
    FROM catering v
    LEFT JOIN catering_images vi ON v.id = vi.catering_id AND vi.image_type = 'catering'
    WHERE v.slug_catering = ?
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
    alert("catering Tidak Ditemukan!");
    window.location.href = "catering.php";
    </script>';
    exit;
}


$imageArray = isset($villa['images']) ? explode(',', $villa['images']) : [];
$imageArray = array_map(function ($image) use ($imagekit_base_url) {
    return $imagekit_base_url . $image;
}, $imageArray);

$pageTitle = 'Calydore | ' . $villa['category_catering'] . ' Menu Lezat untuk Semua menemani Acara Area Cisarua Puncak & Area Bogor';
$description = htmlspecialchars($villa['deskripsi'] ?? 'Penyedia jasa catering terbaik di Puncak Cisarua, Bogor, untuk acara spesial Anda. Sajikan hidangan lezat dengan layanan catering profesional untuk pernikahan, rapat, atau acara lainnya. Hubungi kami untuk catering berkualitas dengan pilihan menu yang variatif.', ENT_QUOTES, 'UTF-8');

$pageType = "catering";
$pageURL = BASE_URL .
    '/catering/' . $villaSlug;


require_once '../includes/header.php';
?>
<link rel='stylesheet' href='../assets/src/swiper/swiper-bundle.min.css'>
<link rel="stylesheet" href="../assets/style/album_profile.css">
<link rel="stylesheet" href="../assets/style/profile.css">
<div class="profile-container">
    <?php
    $isActive = true;
    $imageHTML = ''; // Simpan HTML gambar di sini agar bisa dipakai di 2 tempat
    foreach ($imageArray as $image) {
        $imageTag = '<div class="carousel-item ' . ($isActive ? 'active' : '') . '">
                    <img src="' . htmlspecialchars($image) . '?tr=q-80,c-at_max,w-800,dpr-2" class="d-block w-100" alt="Villa" loading="lazy">
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
            <h2><?php echo htmlspecialchars($villa['nama_catering']); ?></h2>
            <p class="description"><?php echo nl2br(htmlspecialchars($villa['deskripsi'])); ?></p>
            <div class="price-info">
                <p class="desc1">Mulai dari</p>
                <p class="price">Rp <?php echo number_format($villa['harga'], 0, ',', '.'); ?> / Orang</p>
            </div>
        </div>
    </div>


    <div class="menu-utama">
        <div class="divider">
            <span>Menu Utama</span>
        </div>
        <div class="album-container">
            <?php
            $query_menu = "
                SELECT file_path, file_name FROM catering_images
                WHERE catering_id = " . $villa['id'] . " AND image_type = 'menu'
            ";
            $result_menu = $conn->query($query_menu);
            if ($result_menu->num_rows > 0) {
                while ($menu = $result_menu->fetch_assoc()) {
                    $menuSrc = $imagekit_base_url . $menu['file_path'] . $menu['file_name'];
                    echo '<div class="album-item">
                            <img src="' . htmlspecialchars($menuSrc) . '?tr=w-850,q-80" alt="Villa Image" class="album-image"  loading="lazy">
                          </div>';
                }
            } else {
                echo '<p>Menu tidak tersedia.</p>';
            }
            ?>
        </div>
    </div>


    <?php
    $query_menu_opsional = "
                            SELECT file_path, file_name FROM catering_images
                WHERE catering_id = " . $villa['id'] . "  AND image_type = 'opsional'
                        ";
    $result_menu_opsional = $conn->query($query_menu_opsional);

    if ($result_menu_opsional->num_rows > 0) {
        ?>
        <div class="menu-opsional">
            <div class="divider">
                <span>Menu Opsional</span>
            </div>
            <div class="album-container">
                <?php

                while ($menu = $result_menu_opsional->fetch_assoc()) {
                    $menuSrc = $imagekit_base_url . $menu['file_path'] . $menu['file_name'];
                    echo '
                        <div class="album-item">
                            <img src="' . htmlspecialchars($menuSrc) . '?tr=w-850,q-80" alt="Villa Image" class="album-image"  loading="lazy">
                        </div>';
                }

                ?>
            </div>
        </div>
    <?php } ?>

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
        <h3>Catering Rekomendasi</h3>
        <ul>
            <?php

            $query_recommendations = "
            SELECT v.id, v.nama_catering, v.harga, slug_catering,
                (SELECT CONCAT(vi.file_path, vi.file_name) 
                 FROM catering_images vi 
                 WHERE vi.catering_id = v.id LIMIT 1) AS first_image
            FROM catering v
            WHERE v.id <> " . intval($villa['id']) . "
            LIMIT 8
        ";

            $result_recommendations = $conn->query($query_recommendations);

            if ($result_recommendations->num_rows > 0) {
                while ($recommended_villa = $result_recommendations->fetch_assoc()) {
                    $imageSrc = $recommended_villa['first_image'] ? $imagekit_base_url . $recommended_villa['first_image'] : 'path/to/default-image.jpg';

                    echo '
                        <li>
                            <a href="' . BASE_URL . '/catering/' . $recommended_villa['slug_catering'] . '">
                                <img src="' . htmlspecialchars($imageSrc) . '?tr=w-640,h-360,q-80" alt="Villa Image" class="recommended-image" loading="lazy">
                                <div class="recommended-details">
                                    <h4>' . htmlspecialchars($recommended_villa['nama_catering']) . '</h4>
                                    <p><i class="uil uil-tag"></i> Rp ' . number_format($recommended_villa['harga'], 0, ',', '.') . '</p>
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
  "name": "Catering <?php echo htmlspecialchars($villa['nama_catering']); ?>",
  "url": "https://calydore.com/page/catering/<?php echo urlencode($villa['slug_catering']); ?>",
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
$ChatWa = "Permisi Kak? untuk " . htmlspecialchars($villa['nama_catering']) . " Ready ga ?";
$encodedText = urlencode($ChatWa);
$phoneNumber = "6287778911805";
$waLink = "https://api.whatsapp.com/send?phone={$phoneNumber}&text={$encodedText}";

include_once '../includes/wa-tengah.php';
require_once '../includes/footer.php';
?>