<?php
session_start();

// Periksa apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    // Redirect ke halaman login jika belum login
    header("Location: ../login-admin.php");
    exit();
}
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Menghasilkan CSRF token yang aman
}
$_SESSION['previous_page'] = $_SERVER['PHP_SELF']; // Simpan halaman asal


include_once '../database/koneksi.php';
$sql = "SELECT * FROM catering";
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
                        <h3>Dashboard catering</h3>
                        <button class="btn btn-primary" type="button" data-bs-toggle="modal"
                            data-bs-target="#Modalcatering">Tambahkan catering <i class="uil uil-plus"></i></button>
                    </div>
                    <!-- Modal -->
                    <div class="modal fade" id="Modalcatering" tabindex="-1" aria-labelledby="exampleModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">Tambahkan catering</h5>
                                </div>
                                <div class="modal-body">
                                    <form action="../proses/upload-data-catering.php" method="POST"
                                        enctype="multipart/form-data">
                                        <!-- Input fields, like name, description, etc., should be inside this form -->
                                        <input type="hidden" name="csrf_token"
                                            value="<?php echo $_SESSION['csrf_token']; ?>" />
                                        <input type="hidden" name="category" value="catering">
                                        <input type="hidden" name="redirect_page"
                                            value="<?= htmlspecialchars($_SESSION['previous_page'] ?? '/dasboard-admin/page-admin/index-admin.php'); ?>">
                                        <div class="form-section">
                                            <label for="category_catering" class="form-label">Category Outbound </label>
                                            <input type="text" class="form-control" id="category_catering"
                                                name="categorys[category_catering]" placeholder="Contoh: box,grill"
                                                required>
                                        </div>
                                        <div class="form-section">
                                            <label for="nama_catering" class="form-label">Nama catering</label>
                                            <input type="text" class="form-control" id="nama_catering"
                                                name="categorys[nama_catering]" required>
                                        </div>

                                        <?php $categorys = [
                                            'deskripsi',
                                            'harga'
                                        ];
                                        foreach ($categorys as $category) {
                                            // Mengubah underscore menjadi spasi dan kapitalisasi huruf pertama
                                            $label = ucwords(str_replace('_', ' ', $category));
                                            if ($category == 'category_catering') {
                                                $label = 'Kategori catering (game/adventure)';
                                            }
                                            // Menampilkan elemen HTML untuk setiap fitur
                                            echo '<div class="form-section">';
                                            echo '<label for="' . $category . '" class="form-label">' . $label . '</label>';
                                            if ($category === 'deskripsi') {
                                                echo '<textarea class="form-control" id="' . $category . '" name="categorys[' . $category . ']" rows="4" required></textarea>';
                                            } else {
                                                echo '<input type="text" class="form-control" id="' . $category . '" name="categorys[' . $category . ']" required>';
                                            }
                                            echo '</div>';
                                        }
                                        ?>

                                        <div class="form-section">
                                            <label for="foto" class="form-label">Foto Catering :</label>
                                            <input type="file" name="images_catering[]" id="images" class="form-control"
                                                accept="image/*" multiple required>
                                            <input type="hidden" name="image_type[]" value="catering">
                                        </div>

                                        <div class="form-section">
                                            <label for="foto" class="form-label">Foto Menu :</label>
                                            <input type="file" name="images_menu[]" id="images" class="form-control"
                                                accept="image/*" multiple required>
                                            <input type="hidden" name="image_type[]" value="menu">
                                        </div>

                                        <div class="form-section">
                                            <label for="foto" class="form-label">Foto Opsional :</label>
                                            <input type="file" name="images_opsional[]" id="images" class="form-control"
                                                accept="image/*" multiple>
                                            <input type="hidden" name="image_type[]" value="opsional">
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
                <!-- Menampilkan Semua catering -->
                <div class="row d-flex flex-wrap justify-content-center">
                    <?php
                    // Mengecek jika data catering ada
                    if ($result->num_rows > 0) {
                        // Loop untuk menampilkan setiap catering
                        while ($catering = $result->fetch_assoc()) {
                            // Query untuk mengambil gambar-gambar terkait catering dari tabel catering_images
                            $catering_id = $catering['id'];
                            $sql_images = "SELECT file_name, file_path FROM catering_images WHERE catering_id = ? LIMIT 1"; // Mengambil 1 gambar pertama
                            $stmt_images = $conn->prepare($sql_images);
                            $stmt_images->bind_param("i", $catering_id);
                            $stmt_images->execute();
                            $result_images = $stmt_images->get_result();
                            $imagekit_base_url = "https://ik.imagekit.io/bkx7wk6gv"; // Sesuaikan dengan domain ImageKit lo
                    
                            // Mengambil gambar pertama atau gambar default jika tidak ada
                            $first_image = 'default.png'; // Default image
                            if ($result_images->num_rows > 0) {
                                $image = $result_images->fetch_assoc();
                                $first_image = $imagekit_base_url . $image['file_path'] . $image['file_name']; // Gabungkan URL
                            }

                            ?>
                            <div class="col-md-4 mb-4">
                                <div class="card shadow-sm h-100">
                                    <img src="<?php echo htmlspecialchars($first_image); ?>" class="card-img-top"
                                        alt="<?php echo htmlspecialchars($catering['nama_catering']); ?>">
                                    <div class="card-body">
                                        <h5 class="deskripsi"><?php echo htmlspecialchars($catering['category_catering']); ?>
                                        </h5>

                                        <h5 class="card-title"><?php echo htmlspecialchars($catering['nama_catering']); ?></h5>
                                        <p class="card-text">

                                        </p>
                                        <!-- Form Edit catering -->
                                        <div class="d-flex justify-content-between">
                                            <form action="../proses/edit-data-catering.php" method="post"
                                                style="display:inline;">
                                                <input type="hidden" name="id" value="<?php echo $catering['id']; ?>">
                                                <input type="hidden" name="category" value="catering">
                                                <input type="hidden" name="csrf_token"
                                                    value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Edit</button>
                                            </form>
                                            <!-- Form untuk Delete dengan CSRF Token -->
                                            <form action="../proses/delete-data.php" method="post" style="display:inline;">
                                                <input type="hidden" name="id" value="<?php echo $catering['id']; ?>">
                                                <input type="hidden" name="category" value="catering">
                                                <input type="hidden" name="csrf_token"
                                                    value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Apakah Anda yakin ingin menghapus catering ini?')">Delete</button>
                                            </form>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<p class="text-center">Tidak ada data catering yang tersedia.</p>';
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