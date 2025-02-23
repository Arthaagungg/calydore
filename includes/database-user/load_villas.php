<?php
session_start();
header('Content-Type: application/json');

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ob_start(); // Tangkap output tambahan

include '../../dasboard-admin/database/koneksi.php';

// Pastikan token CSRF sudah dibuat sebelumnya

// Ambil parameter halaman dan kategori
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 4;
$offset = ($page - 1) * $limit;
$tab = isset($_GET['tab']) ? htmlspecialchars($_GET['tab'], ENT_QUOTES, 'UTF-8') : 'semua';
$imagekit_base_url = "https://ik.imagekit.io/bkx7wk6gv";
// Jika tab adalah villa, gunakan daftar ID yang sudah diacak
if ($tab === 'villa') {
    // Ambil daftar ID yang sudah diacak dari session
    if (!isset($_SESSION['shuffled_villa_ids'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Daftar ID villa kamar tidak ditemukan.'
        ]);
        exit;
    }

    $shuffledIds = $_SESSION['shuffled_villa_ids'];

    // Ambil 4 ID berikutnya dari daftar yang sudah diacak
    $idsToFetch = array_slice($shuffledIds, 0, $limit);
    // Hapus ID yang sudah diambil dari daftar
    $_SESSION['shuffled_villa_ids'] = array_slice($_SESSION['shuffled_villa_ids'], $limit);    // Query untuk mengambil data villa berdasarkan ID yang sudah diacak
    if (!empty($idsToFetch)) {
        $idsString = implode(',', $idsToFetch);
        $query = "
            SELECT v.id, v.nama_villa, v.deskripsi, v.harga_weekday, v.harga_weekend, 
                   v.kapasitas, v.lokasi, v.rating, v.slug_villa,
                   (SELECT COUNT(*) FROM villa_features vf WHERE vf.villa_id = v.id) AS feature_count
            FROM villa v
            WHERE v.id IN ($idsString)
            ORDER BY FIELD(v.id, $idsString)
        ";
    } else {
        $query = "SELECT * FROM villa WHERE 1 = 0"; // Tidak ada data
    }
} else {
    // Logika untuk tab lainnya (rekomendasi, terlaris, hargaup, hargadown)
    $orderBy = "id";
    $orderDir = "ASC";
    switch ($tab) {
        case 'rekomendasi':
            $orderBy = "feature_count";
            $orderDir = "DESC";
            break;
        case 'terlaris':
            $orderBy = "rating";
            $orderDir = "DESC";
            break;
        case 'hargadown':
            $orderBy = "harga_weekday";
            $orderDir = "ASC";
            break;
        case 'hargaup':
            $orderBy = "harga_weekday";
            $orderDir = "DESC";
            break;
        default:
            $orderBy = "id";
            $orderDir = "ASC";
    }

    $query = "
        SELECT v.id, v.nama_villa, v.deskripsi, v.harga_weekday, v.harga_weekend, 
               v.kapasitas, v.lokasi, v.rating, v.slug_villa,
               (SELECT COUNT(*) FROM villa_features vf WHERE vf.villa_id = v.id) AS feature_count
        FROM villa v
        ORDER BY $orderBy $orderDir
        LIMIT ? OFFSET ?
    ";
}

$stmt = $conn->prepare($query);
if ($tab === 'villa') {
    $stmt->execute();
} else {
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
}
$result = $stmt->get_result();

// Query untuk mengambil gambar
$imageQuery = "SELECT villa_id, file_name, file_path FROM villa_images";
$imageResult = $conn->query($imageQuery);

$villaImages = [];
if ($imageResult->num_rows > 0) {
    while ($imageRow = $imageResult->fetch_assoc()) {
        $villaId = $imageRow['villa_id'];
        $filePath = rtrim($imageRow['file_path'], '/');
        $fileName = ltrim($imageRow['file_name'], '/');


        $villaImages[$villaId][] = $imagekit_base_url . $filePath . '/' . $fileName;
    }
}


// Bangun data HTML
$html = '';
while ($villa = $result->fetch_assoc()) {
    $villaId = $villa['id'];
    $images = isset($villaImages[$villaId]) ? $villaImages[$villaId] : [];

    $html .= '
    <div class="col">
        <div class="villa-box">
            <div class="sub-box">
                <div id="carousel-' . $villaId . '" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">';
    $isActive = true;
    foreach ($images as $image) {
        $html .= '
                    <div class="carousel-item ' . ($isActive ? 'active' : '') . '">
                        <img src="' . htmlspecialchars($image) . '?tr=w-640,h-360,q-80" class="d-block w-100" alt="Villa" loading="lazy">
                    </div>';
        $isActive = false;
    }
    $html .= '
                    </div>
                </div>
                <div class="sub-box-content">
                    <div class="location-rating">
                        <div class="location">
                            <i class="fa fa-map-marker" aria-hidden="true"></i> ' . htmlspecialchars($villa['lokasi']) . '
                        </div>
                        <div class="rating">
                            <i class="fa fa-star icon-small" aria-hidden="true"></i> ' . htmlspecialchars($villa['rating']) . ' / 5
                        </div>
                    </div>
                    <p class="title">' . htmlspecialchars($villa['nama_villa']) . '</p>
                    <p class="descTitle">' . htmlspecialchars($villa['deskripsi']) . '</p>
                    <div class="price-info">
                        <p class="desc1">Mulai dari</p>
                        <div class="price-row">
                            <p class="price">Rp ' . number_format($villa['harga_weekday'], 0, ',', '.') . '</p>
                            <p class="desc2">Per Malam</p>
                        </div>
                    </div>
                </div>
                                    <a href="' . BASE_URL . '/villa/' . htmlspecialchars($villa['slug_villa']) . '" class="btn-selengkapnya">Selengkapnya</a>
            </div>
        </div>
    </div>';
}

// Hitung halaman terakhir
$totalVillaQuery = "SELECT COUNT(*) AS total FROM villa";
$totalVillaResult = $conn->query($totalVillaQuery);
$totalVillaRow = $totalVillaResult->fetch_assoc();
$totalVilla = $totalVillaRow['total'];
$totalPages = ceil($totalVilla / $limit);

$isLastPage = ($page >= $totalPages);

header('Content-Type: application/json');
$output = [
    'html' => $html,
    'isLastPage' => $isLastPage
];
error_log("Output JSON: " . json_encode($output)); // Tambahkan log ini
echo json_encode([
    'status' => 'success',
    'data' => $output
]);
