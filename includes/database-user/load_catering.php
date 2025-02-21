<?php
session_start();
header('Content-Type: application/json');

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ob_start(); // Tangkap output tambahan


include '../../dasboard-admin/database/koneksi.php';

$category = $_POST['category'] ?? '';
$page = isset($_POST['page']) ? (int) $_POST['page'] : 1; // Ambil $page dari AJAX
$limit = 4;
$offset = ($page - 1) * $limit;
$imagekit_base_url = "https://ik.imagekit.io/bkx7wk6gv";
$data = [];
$html = '';

if (!empty($category)) {
  $query = "SELECT id, nama_catering, deskripsi, harga, category_catering, slug_catering 
              FROM catering 
              WHERE category_catering = ? 
              LIMIT ? OFFSET ?";

  $stmt = $conn->prepare($query);
  if ($stmt === false) {
    error_log("Failed to prepare the SQL query.");
    http_response_code(500);
    exit(json_encode(['status' => 'error', 'message' => 'Database query failed']));
  }

  $stmt->bind_param('sii', $category, $limit, $offset);
  $stmt->execute();
  $result = $stmt->get_result();

  $imageQuery = "SELECT catering_id, file_name, file_path FROM catering_images";
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

  if ($result->num_rows > 0) {
    while ($villa = $result->fetch_assoc()) {
      $villaId = $villa['id'];
      $images = $villaImages[$villaId] ?? [];
      $imagePath = "../dasboard-admin/proses/db_image/";

      $html .= '<div class="col">
                        <div class="outbound-box">
                          <div class="sub-box">
                            <div id="carousel-' . htmlspecialchars($villaId) . '" class="carousel slide" data-bs-ride="carousel">
                              <div class="carousel-inner">';

      $isActive = true;
      foreach ($images as $image) {
        $html .= '<div class="carousel-item ' . ($isActive ? 'active' : '') . '">
                              <img src="' . htmlspecialchars($image) . '?tr=w-640,h-360,q-80" class="d-block w-100" alt="Villa" loading="lazy">
                          </div>';
        $isActive = false;
      }

      $html .= '</div>
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
  }
}

// Hitung total data sesuai kategori
$totalVillaQuery = "SELECT COUNT(*) AS total FROM catering WHERE category_catering = ?";
$totalVillaStmt = $conn->prepare($totalVillaQuery);
$totalVillaStmt->bind_param('s', $category);
$totalVillaStmt->execute();
$totalVillaResult = $totalVillaStmt->get_result();
$totalVillaRow = $totalVillaResult->fetch_assoc();
$totalVilla = $totalVillaRow['total'];
$totalPages = ceil($totalVilla / $limit);
$isLastPage = ($page >= $totalPages);

// Kirim JSON response
header('Content-Type: application/json');
echo json_encode([
  'status' => 'success',
  'data' => [
    'html' => $html,
    'isLastPage' => $isLastPage
  ]
]);
