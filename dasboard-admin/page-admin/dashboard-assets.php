<?php
session_start();

// Periksa apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    // Redirect ke halaman login jika belum login
    header("Location: login-admin.php");
    exit();
}
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Menghasilkan CSRF token yang aman
}
$_SESSION['previous_page'] = $_SERVER['PHP_SELF']; // Simpan halaman asal

include_once '../database/koneksi.php';
$sql = "SELECT * FROM assets_images";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">


<body>
    <div class="d-flex">
        <?php
        include '../property/partials/header.php';
        ?>
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
                        <h3>Dashboard Assets</h3>
                        <button class="btn btn-primary" type="button" data-bs-toggle="modal"
                            data-bs-target="#ModalVilla">Tambahkan assets <i class="uil uil-plus"></i></button>
                    </div>
                    <!-- Modal -->
                    <div class="modal fade" id="ModalVilla" tabindex="-1" aria-labelledby="exampleModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">Tambahkan assetss</h5>
                                </div>
                                <div class="modal-body">
                                    <form action="../proses/upload-assets.php" method="POST"
                                        enctype="multipart/form-data">
                                        <input type="hidden" name="csrf_token"
                                            value="<?php echo $_SESSION['csrf_token']; ?>" />

                                        <!-- Input fields, like name, description, etc., should be inside this form -->
                                        <div class="form-section">
                                            <label for="new-facility-name" class="form-label">Nama Assets :</label>
                                            <input type="text" class="form-control" id="nama-assets" name="nama-assets"
                                                required>
                                        </div>
                                        <div class="form-section">
                                            <label for="new-facility-name" class="form-label">type :</label>
                                            <input type="text" class="form-control" id="assets-type" name="assets-type"
                                                required>
                                        </div>
                                        <div class="input-group input-group-sm mb-3">
                                            <input type="file" name="images[]" id="images" class="form-control"
                                                accept="image/*" required>
                                        </div>

                                        <!-- Tombol Submit untuk mengirimkan form -->
                                        <button type="submit" class="btn btn-primary">Save changes</button>
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Close</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Menampilkan Semua Villa -->
                <div class="row d-flex flex-wrap justify-content-center">
                    <?php
                    // Mengecek jika data villa ada
                    $imagekit_base_url = "https://ik.imagekit.io/bkx7wk6gv"; // Sesuaikan dengan domain ImageKit
                    
                    if ($result->num_rows > 0) {
                        // Loop untuk menampilkan setiap villa
                        while ($assets = $result->fetch_assoc()) {
                            // Mengambil gambar pertama (sebelum koma)
                            $images = explode(',', $assets['file_type']);
                            $first_image = isset($images[0]) && !empty($images[0]) ? $images[0] : 'default.png';
                            ?>
                            <div class="col-md-4 mb-4">
                                <div class="card shadow-sm h-100">
                                    <img src="<?php echo htmlspecialchars($imagekit_base_url . $assets['file_path'] . $assets['file_name']); ?>"
                                        class="card-img-top" alt="<?php echo htmlspecialchars($assets['id']); ?>">
                                    <p class="card-text">
                                        <?php echo htmlspecialchars($assets['name_assets']); ?><br>
                                        <?php echo htmlspecialchars($assets['image_type']); ?>
                                    </p>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <form action="../proses/delete-image.php" method="POST">
                                                <input type="hidden" name="category" value="assets">
                                                <input type="hidden" name="id"
                                                    value="<?php echo htmlspecialchars($assets['assets_id']); ?>">
                                                <input type="hidden" name="image"
                                                    value="<?php echo htmlspecialchars($assets['fileid']); ?>">
                                                <input type="hidden" name="csrf_token"
                                                    value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                                <button type="submit" class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Hapus gambar ini?')">Hapus</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo "<p>Tidak ada assets yang tersedia.</p>";
                    }
                    ?>
                </div>

        </div>
        </main>
    </div>
    </div>

    <script src="../property/js/sidebar.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>