<?php
ob_start();
define('SECURE_ACCESS', true);

include_once '../dasboard-admin/database/koneksi.php';


$category = isset($_GET['category']) ? str_replace('-', ' ', $_GET['category']) : '';
$imagekit_base_url = "https://ik.imagekit.io/bkx7wk6gv";

$totalQuery = "SELECT COUNT(*) as total FROM catering WHERE category_catering = ?";
$stmtTotal = $conn->prepare($totalQuery);
$stmtTotal->bind_param('s', $category);
$stmtTotal->execute();
$resultDetailsCateringTotal = $stmtTotal->get_result();
$totalRows = $resultDetailsCateringTotal->fetch_assoc()['total'] ?? 0;


$imageQuery = "SELECT catering_id, file_name, file_path 
               FROM catering_images 
               WHERE image_type = 'catering'";

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

if (!empty($category)) {
    $query = "SELECT id, nama_catering, deskripsi, harga, category_catering, slug_catering FROM catering WHERE category_catering = ? LIMIT 4";

    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log("Failed to prepare the SQL query.");
        http_response_code(500);
        exit();
    }
    $stmt->bind_param('s', $category);
    $stmt->execute();
    $resultDetailsCatering = $stmt->get_result();

    if ($resultDetailsCatering->num_rows === 0) {
        header("Location: " . BASE_URL . "/403.php");
        exit();
    }

} else {
    http_response_code(403);
    header("Location: " . BASE_URL . "/403.php");
    exit();
}

ob_end_flush();
$pageTitle = 'Calydore | ' . $category . ' Dengan Harga terjangkau hanya di Cisarua Puncak';
$description = "Penyedia jasa catering di Puncak Cisarua, Bogor untuk berbagai acara Anda. Sajikan hidangan lezat dengan layanan catering profesional untuk pernikahan, rapat, atau acara lainnya di kawasan Puncak yang sejuk.";
$pageType = "catering";
$pageURL = BASE_URL .
    '/daftar-catering/' . $category;
include '../includes/header.php';
?>

<!-- Stylesheets -->
<link rel="stylesheet" href="../assets/style/outbound-details.css">

<?php
$deskripsi = "";
switch ($category) {
    case 'Nasi Box':
        $deskripsi = "Nasi Box dari Catering Calydore adalah pilihan praktis dan lezat untuk berbagai acara di Puncak Cisarua Bogor. Dengan porsi yang pas dan cita rasa yang terjaga, kami menghadirkan hidangan berkualitas yang siap dinikmati kapan saja dengan kemasan rapi dan higienis.";
        break;
    case 'paintball':
        $deskripsi = "Nikmati pengalaman paintball terbaik.";
        break;
    default:
        $deskripsi = "Kategori tidak ditemukan.";
        break;
}
?>

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
        while ($villa = $resultDetailsCatering->fetch_assoc()) {
            $villaId = $villa['id'];
            $images = $villaImages[$villaId] ?? [];

            echo '<div class="col">
                      <div class="outbound-box">
                        <div class="sub-box">
                          <div id="carousel-' . htmlspecialchars($villaId) . '" class="carousel slide" data-bs-ride="carousel">
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
                    <p class="title">' . htmlspecialchars($villa['nama_catering']) . '</p>
                    <div class="price-info">
                      <p class="desc1">Mulai dari</p>
                      <div class="price-row">
                        <p class="price">Rp ' . number_format($villa['harga'], 0, ',', '.') . '</p>
                        <p class="desc2">per orang</p>
                      </div>
                    </div>
                  </div>
                    <a href="' . BASE_URL . '/catering/' . htmlspecialchars($villa['slug_catering']) . '" class="btn-selengkapnya">Selengkapnya</a>
                </div>
              </div>
            </div>';
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
<script src="<?php echo BASE_URL; ?>/assets/js/load_catering.js"></script>




<?php
$ChatWa = "Permisi Kak, Mau tau info lebih jelas tentang paket " . htmlspecialchars($category) . " ?";
$encodedText = urlencode($ChatWa);
$phoneNumber = "6287778911805";
$waLink = "https://api.whatsapp.com/send?phone={$phoneNumber}&text={$encodedText}";

include_once '../includes/wa-kanan.php';
require_once '../includes/footer.php';
?>