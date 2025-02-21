<?php
session_start();

// Periksa apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login-admin.php");
    exit();
}
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Menghasilkan CSRF token yang aman
}
$_SESSION['previous_page'] = $_SERVER['PHP_SELF']; // Simpan halaman asal

include_once '../database/koneksi.php';

// Query data dari tiga tabel
$tables = ['glamping_features', 'villa_features', 'villa_kamar_features', 'outbound_features', 'hotel_features'];
$features = [];
$seen_features = [];  // Array untuk melacak fitur yang sudah ditampilkan

foreach ($tables as $table) {
    $sql = "SELECT feature_name, icons_link FROM $table";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Hanya menambahkan fitur yang belum ada di $seen_features
            if (!in_array($row['feature_name'], $seen_features)) {
                $features[] = [
                    'feature_name' => $row['feature_name'],
                    'icons_link' => $row['icons_link'],
                ];
                $seen_features[] = $row['feature_name'];  // Tambahkan ke daftar yang sudah dilihat
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Icons</title>
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <!-- Menggunakan Font Awesome versi terbaru -->
    <link rel="stylesheet" href="ambil di src">

</head>

<body>
    <div class="d-flex">
        <?php include '../property/partials/header.php'; ?>
        <!-- Main Component -->
        <div class="main">
            <nav class="navbar navbar-expand">
                <button class="toggler-btn" type="button">
                    <i class="lni lni-text-align-left"></i>
                </button>
            </nav>
            <main class="p-3">
                <div class="container-fluid">
                    <div class="mb-3 text-start">
                        <h3>Dashboard Icons</h3>
                    </div>
                    <!-- Display Features -->
                    <div class="features-list">
                        <?php foreach ($features as $feature): ?>
                            <form action="../proses/upload-icons.php" method="POST" enctype="multipart/form-data">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">
                                        <?php echo ($feature['icons_link'] ?? ''); ?>
                                        <?php echo htmlspecialchars($feature['feature_name']); ?>
                                    </span>
                                    <input type="text" class="form-control" name="new_icon"
                                        value="<?php echo htmlspecialchars($feature['icons_link'] ?? ''); ?>">
                                    <input type="hidden" name="feature_name"
                                        value="<?php echo htmlspecialchars($feature['feature_name']); ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="lni lni-checkmark"></i>
                                    </button>
                                </div>
                            </form>
                        <?php endforeach; ?>

                        <?php if (empty($features)): ?>
                            <p class="text-muted">Tidak ada fitur yang tersedia.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="../property/js/sidebar.js"></script>
    <script src="https://kit.fontawesome.com/bb901c5970.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>