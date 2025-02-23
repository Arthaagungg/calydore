<?php
ob_start();
define('SECURE_ACCESS', true);

include_once '../dasboard-admin/database/koneksi.php';


$category = isset($_GET['category']) ? str_replace('-', ' ', $_GET['category']) : '';
$imagekit_base_url = "https://ik.imagekit.io/bkx7wk6gv";


$totalQuery = "SELECT COUNT(*) as total FROM outbound WHERE category_outbound = ?";
$stmtTotal = $conn->prepare($totalQuery);
$stmtTotal->bind_param('s', $category);
$stmtTotal->execute();
$resultDetailsOutboundTotal = $stmtTotal->get_result();
$totalRows = $resultDetailsOutboundTotal->fetch_assoc()['total'] ?? 0;


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

if (!empty($category)) {
    $query = "SELECT id, nama_outbound, deskripsi, harga, slug_outbound, category_outbound FROM outbound WHERE category_outbound = ? LIMIT 4";

    $stmt = $conn->prepare($query); ?>


    <?php

    if ($stmt === false) {
        error_log("Failed to prepare the SQL query.");
        http_response_code(500);
        exit();
    }
    $stmt->bind_param('s', $category);
    $stmt->execute();
    $resultDetailsOutbound = $stmt->get_result();

    if ($resultDetailsOutbound->num_rows === 0) {
        http_response_code(403);
        header("Location: ' . BASE_URL . '/403.php");
        exit();
    }
} else {
    http_response_code(403);
    header("Location: ' . BASE_URL . '/403.php");
    exit();
}

$pageTitle = 'Calydore | Berbagai paket ' . $category . ' Di Outbound Cisarua Puncak';
$description = "Ikuti kegiatan outbound seru di Puncak Cisarua, Bogor. Aktivitas luar ruangan untuk tim atau keluarga, cocok untuk membangun kerjasama dan kekompakan di kawasan alam yang asri. Hubungi kami untuk paket outbound terbaik.";
$pageType = "outbound";
$pageURL = BASE_URL .
    '/daftar-outbound/' . $category;

include_once '../includes/header.php';
$deskripsi = "";
switch ($category) {
    case 'game':
        $deskripsi = "Permainan seru untuk semua kalangan.";
        break;
    case 'paintball':
        $deskripsi = "Nikmati pengalaman paintball terbaik.";
        break;
    default:
        $deskripsi = "Kategori tidak ditemukan.";
        break;
}
?>
<link rel="stylesheet" href="../assets/style/outbound-details.css">

<div class="grid-container">
    <div class="grid-item item1">
        <div class="title-category">
            <?php echo htmlspecialchars($category); ?>
        </div>
        <p>
            <?php echo htmlspecialchars($deskripsi); ?>
        </p>
    </div>

    <div class="grid-item item2">
        <div id="carouselExampleControls" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php
                $isFirstItem = true;
                foreach ($villaImages as $images) {
                    foreach ($images as $image) {
                        echo '
                        <div class="carousel-item ' . ($isFirstItem ? 'active' : '') . '">
                            <img class="slideshow" src="' . htmlspecialchars($image) . '?tr=w-640,h-360,q-80" alt="Slide" loading="lazy">
                        </div>';
                        $isFirstItem = false;
                    }
                }
                ?>
            </div>
        </div>
    </div>
</div>

<div class="divider-outbound">
    <span><?php echo htmlspecialchars($category); ?></span>
</div>

<div class="container mb-3">
    <div class="row row-cols-1 row-cols-md-2 g-4 mb-5" id="villa">
        <?php
        if ($resultDetailsOutbound->num_rows > 0) {
            while ($villa = $resultDetailsOutbound->fetch_assoc()) {
                $villaId = $villa['id'];
                $images = isset($villaImages[$villaId]) ? $villaImages[$villaId] : [];

                echo '<div class="col">
                          <div class="outbound-box">
                            <div class="sub-box">
                              <div id="carousel-' . $villaId . '" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-inner">';

                $isActive = true;
                foreach ($images as $image) {
                    echo '<div class="carousel-item ' . ($isActive ? 'active' : '') . '">
                              <img src="' . htmlspecialchars($image) . '?tr=w-640,h-360,q-80" class="d-block w-100" alt="Villa" loading="lazy">
                            </div>';
                    $isActive = false;
                }

                echo '</div>
                      </div>
                      <div class="sub-box-content">
                        <p class="title">' . htmlspecialchars($villa['nama_outbound']) . '</p>
                        <div class="price-info">
                          <p class="desc1">Mulai dari</p>
                          <div class="price-row">
                            <p class="price">Rp ' . number_format($villa['harga'], 0, ',', '.') . '</p>
                            <p class="desc2">per orang</p>

                          </div>
                        </div>
                      
                        </div>
                            <a href="' . BASE_URL . '/outbound/' . htmlspecialchars($villa['slug_outbound']) . '" class="btn-selengkapnya">Selengkapnya</a>

                    </div>
                  </div>
                </div>';
            }
        } else {
            echo '<p>Data villa tidak tersedia.</p>';
        }
        ?>
    </div>

    <?php if ($totalRows > 4): ?>
        <button id="loadMoreVilla" class="btn loadmore btn-primary w-100">Load More</button>
    <?php endif; ?>
</div>
<script>
    var category = "<?php echo urlencode($category); ?>";
</script>
<script src="<?php echo BASE_URL; ?>/assets/js/load_outbound.js"></script>


<?php
$ChatWa = "Permisi Kak? Mau tau info lebih jelas tentang paket " . htmlspecialchars($category) . " ?";
$encodedText = urlencode($ChatWa);
$phoneNumber = "6287778911805";
$waLink = "https://api.whatsapp.com/send?phone={$phoneNumber}&text={$encodedText}";

include_once '../includes/wa-kanan.php';
require_once '../includes/footer.php';
?>