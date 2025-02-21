<?php
// Pastikan header JSON dikirim
header('Content-Type: application/json');

// Menangani error dan warning agar tidak terlihat oleh pengguna
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Mulai output buffering
ob_start();

// Termasuk koneksi database
include '../../dasboard-admin/database/koneksi.php';

// Ambil input dari POST
$search = isset($_POST['search']) ? $_POST['search'] : '';
$table = isset($_POST['table']) ? $_POST['table'] : '';
$offset = isset($_POST['offset']) ? (int) $_POST['offset'] : 0;
$limit = 4; // Batas jumlah data yang dimuat per request
$imagekit_base_url = "https://ik.imagekit.io/bkx7wk6gv";
// Escape input untuk keamanan
$search = $conn->real_escape_string($search);
$table = $conn->real_escape_string($table);

$results = [];

// Validasi input, pastikan search dan table tidak kosong
if (!empty($search) && !empty($table)) {
    // Ambil kolom-kolom yang ada pada tabel
    $columnsQuery = $conn->query("SHOW COLUMNS FROM `$table`");
    $columns = [];
    while ($columnRow = $columnsQuery->fetch_array()) {
        $columns[] = $columnRow['Field'];
    }

    // Mempersiapkan kondisi LIKE untuk pencarian
    $conditions = [];
    foreach ($columns as $column) {
        $conditions[] = "`$column` LIKE ?";
    }

    // Query untuk mengambil data dengan kondisi LIKE dan batas offset & limit
    $query = "SELECT DISTINCT * FROM `$table` WHERE " . implode(' OR ', $conditions) . " LIMIT ?, ?";
    $stmt = $conn->prepare($query);

    // Tentukan parameter pencarian
    $searchTerm = "%$search%";
    $params = array_fill(0, count($columns), $searchTerm);
    $params[] = $offset;
    $params[] = $limit;
    $types = str_repeat('s', count($columns)) . 'ii'; // 's' untuk LIKE, 'ii' untuk offset & limit

    // Bind parameter dan eksekusi query
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    // Menyiapkan respons
    $response = '';

    // Ambil data dan gambar terkait
    while ($row = $result->fetch_assoc()) {
        // Koreksi nama tabel untuk menghapus '_features' jika ada
        $processedTable = str_replace('_features', '', $table);

        // Query untuk mengambil gambar terkait villa
        $imageQuery = "SELECT {$processedTable}_id, file_name, file_path FROM {$processedTable}_images WHERE {$processedTable}_id = ?";
        $imageStmt = $conn->prepare($imageQuery);
        $imageStmt->bind_param("i", $row['id']);
        $imageStmt->execute();
        $imageResult = $imageStmt->get_result();

        $villaImages = [];
        if ($imageResult->num_rows > 0) {
            while ($imageRow = $imageResult->fetch_assoc()) {
                $villaId = $imageRow['glamping_id'];
                $filePath = rtrim($imageRow['file_path'], '/');
                $fileName = ltrim($imageRow['file_name'], '/');


                $villaImages[$imageRow["{$processedTable}_id"]][] = $imagekit_base_url . $filePath . '/' . $fileName;
            }
        }
        $imageStmt->close();

        // Menampilkan data villa dengan gambar
        $response .= generateVillaCard($row, $table, $villaImages); // Kirim data dalam format HTML
    }

    // Jika tidak ada data lebih lanjut, kirimkan string kosong
    if (empty($response)) {
        $response = ''; // Tidak ada data lebih lanjut
    }

    $stmt->close();
}

// Fungsi untuk men-generate HTML card dari villa
function generateVillaCard($villa, $table, $villaImages)
{
    // Tentukan harga sesuai dengan tabel
    $harga = ($table === "outbound" || $table === "catering") ? $villa['harga'] : $villa['harga_weekday'];

    // Jika gambar ada, gunakan gambar pertama dari array
    $imageSrc = !empty($villaImages) ? htmlspecialchars($villaImages[$villa['id']][0] ?? '') : '';

    return '
    <div class="col villa-item">
        <div class="villa-box">
            <div class="sub-box">
                <img src="' . $imageSrc . '?tr=w-640,h-360,q-80" class="d-block w-100" alt="Villa" loading="lazy">
                <div class="sub-box-content">
                    <p class="title">' . htmlspecialchars($villa["nama_" . $table] ?? 'Tidak Ada Nama') . '</p>
                    <p class="descTitle">' . htmlspecialchars($villa['deskripsi'] ?? 'Tidak Ada Deskripsi') . '</p>
                    <div class="price-info">
                        <p class="desc1">Mulai dari</p>
                        <div class="price-row">
                            <p class="price">Rp ' . number_format($harga, 0, ',', '.') . '</p>
                            <p class="desc2">Per Malam</p>
                        </div>
                    </div>
                </div>
                <a href="' . htmlspecialchars(BASE_URL) . '/' . htmlspecialchars(str_replace('_', '-', $table)) . '/' . htmlspecialchars($villa["slug_" . $table]) . '" class="btn-selengkapnya">Selengkapnya</a>                </div>
        </div>
    </div>';
}

// Query untuk menghitung jumlah total data yang ada di tabel
$queryTotal = "SELECT COUNT(*) as total FROM `$table`"; // Tanpa kondisi LIKE
$stmtTotal = $conn->prepare($queryTotal);
$stmtTotal->execute();
$totalResult = $stmtTotal->get_result()->fetch_assoc();
$totalData = $totalResult['total']; // Ambil total data tanpa pencarian
$stmtTotal->close(); // Pastikan data yang dikirim dalam format JSON

// Siapkan output dalam bentuk JSON
$output = [
    'html' => $response,
    'isLastPage' => $totalData, // Cek apakah sudah halaman terakhir
];

// Tampilkan log debug jika perlu (opsional)
error_log("Output JSON: " . json_encode($output));

// Kirimkan respons JSON
echo json_encode([
    'status' => 'success',
    'data' => $output
]);

// Akhirnya, flush output buffer
ob_end_flush();
