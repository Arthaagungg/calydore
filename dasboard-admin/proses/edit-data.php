<?php

include 'session.php';

// Validasi ID villa dari URL
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo "<script>alert('ID tidak valid !'); window.location.href = '$redirect_page';</script>";
    exit();
}
$id = intval($_POST['id']);

// Tentukan kategori
$category = $_POST['category'] ?? ''; // Mengambil kategori dari POST

// Query untuk mendapatkan data berdasarkan kategori
$sql = "SELECT * FROM $category WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id); // Bind parameter id
$stmt->execute();
$result = $stmt->get_result();
$categorys = []; // Variabel untuk menyimpan data yang ditemukan

// Menyimpan hasil query ke dalam array $categorys
while ($row = $result->fetch_assoc()) {
    $categorys[] = $row;
}

// Cek apakah data ditemukan
if ($result->num_rows == 1) {
    // Query untuk mendapatkan gambar-gambar dari tabel terkait
    $sql_images = "SELECT file_name, file_path, fileid FROM {$category}_images WHERE {$category}_id = ?";
    $stmt_images = $conn->prepare($sql_images);
    $stmt_images->bind_param("i", $id);
    $stmt_images->execute();
    $result_images = $stmt_images->get_result();

    // Menyimpan gambar-gambar ke dalam array
    $images = [];
    while ($image = $result_images->fetch_assoc()) {
        $images[] = [
            'file_name' => $image['file_name'],
            'file_path' => $image['file_path'],
            'fileid' => $image['fileid'],
        ];
    }

    // Query untuk mendapatkan fitur terkait
    $sql_features = "SELECT feature_name, feature_value FROM {$category}_features WHERE {$category}_id = ?";
    $stmt_features = $conn->prepare($sql_features);
    $stmt_features->bind_param("i", $id);
    $stmt_features->execute();
    $result_features = $stmt_features->get_result();

    $features = [];
    while ($feature = $result_features->fetch_assoc()) {
        $features[$feature['feature_name']] = $feature['feature_value'];
    }
} else {
    echo "<script>alert('Data Category tidak di temukan'); window.location.href = '$redirect_page';</script>";
    exit();
}

// Buat CSRF token untuk keamanan
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit <?php echo ucfirst($category); ?></title>
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<body>
    <div class="d-flex">
        <?php include 'sidebar-edit.php'; ?>

        <div class="main">
            <nav class="navbar navbar-expand">
                <button class="toggler-btn" type="button">
                    <i class="lni lni-text-align-left"></i>
                </button>
            </nav>
            <main class="p-3">
                <div class="container mt-5">
                    <h2>Edit <?php echo ucwords(str_replace('_', ' ', $category)); ?>
                        <?php echo htmlspecialchars($categorys[0]["nama_{$category}"]); ?>
                    </h2>
                    <!-- Form untuk Update Fasilitas -->
                    <form action="update-data.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?php echo $categorys[0]['id']; ?>">
                        <input type="hidden" name="category" value="<?php echo $category ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                        <div class="mb-3">
                            <h4>Category <?php echo ucwords(str_replace('_', ' ', $category)); ?></h4>
                            <?php
                            // Loop untuk fitur yang ada
                            foreach ($categorys[0] as $column_name => $column_value) {
                                if ($column_name !== 'id' && $column_name !== 'created_at' && $column_name !== 'updated_at') { // Melewatkan ID
                                    ?>
                                    <div class="mb-3" <?php echo ($column_name === 'slug_' . $category) ? 'style="display:none;"' : ''; ?>>
                                        <label for="category_<?php echo htmlspecialchars($column_name); ?>" class="form-label">
                                            <?php echo ucwords(str_replace('_', ' ', $column_name)); ?>
                                        </label>
                                        <?php if ($column_name === 'slug_' . $category): ?>
                                            <input type="hidden" id="category_<?php echo htmlspecialchars($column_name); ?>"
                                                name="categorys[<?php echo htmlspecialchars($column_name); ?>]"
                                                value="<?php echo htmlspecialchars($column_value); ?>">
                                        <?php elseif ($column_name === 'deskripsi'): ?>
                                            <textarea class="form-control"
                                                id="category_<?php echo htmlspecialchars($column_name); ?>"
                                                name="categorys[<?php echo htmlspecialchars($column_name); ?>]"
                                                rows="4"><?php echo htmlspecialchars($column_value); ?></textarea>
                                        <?php else: ?>
                                            <input type="text" class="form-control"
                                                id="category_<?php echo htmlspecialchars($column_name); ?>"
                                                name="categorys[<?php echo htmlspecialchars($column_name); ?>]"
                                                value="<?php echo htmlspecialchars($column_value); ?>">
                                        <?php endif; ?>
                                    </div>
                                    <?php
                                }
                            }

                            ?>
                        </div>

                        <!-- Fitur Umum untuk Update -->
                        <h4>Fitur <?php echo ucwords(str_replace('_', ' ', $category)); ?></h4>
                        <?php
                        // Loop untuk fitur yang ada (update secara bersamaan)
                        foreach ($features as $feature_name => $feature_value) {
                            ?>
                            <div class="mb-3">
                                <label for="feature_<?php echo htmlspecialchars($feature_name); ?>" class="form-label">
                                    <?php echo ucwords(str_replace('_', ' ', $feature_name)); ?>
                                </label>
                                <input type="text" class="form-control"
                                    id="feature_<?php echo htmlspecialchars($feature_name); ?>"
                                    name="features[<?php echo htmlspecialchars($feature_name); ?>]"
                                    value="<?php echo htmlspecialchars($feature_value); ?>">
                            </div>
                            <?php
                        }
                        ?>

                        <h4><label for="label" class="form-label"> Tambah Fasilitas : </label></h4>
                        <!-- Input untuk menambahkan fasilitas -->
                        <div class="form-section mb-1">
                            <label for="new-facility-name" class="form-label">Masukkan Name Fasilitas:</label>
                            <input type="text" class="form-control" id="new-facility-name"
                                placeholder="Contoh: ac, wifi, kolam_berenang">
                        </div>
                        <button type="button" class="btn btn-primary mb-4" id="add-more">Tambah</button>
                        <!-- Bagian fasilitas yang akan diulang -->
                        <div id="facilities-container"></div>

                        <!-- Gambar -->
                        <div class="mb-3">
                            <h4><label for="images" class="form-label">Tambahkan Gambar Baru : </label></h4>
                            <input type="file" name="images[]" id="images" class="form-control" accept="image/*"
                                multiple>
                        </div>

                        <button type="submit" class="btn btn-primary mb-3">Simpan Perubahan</button>
                        <a href="<?php echo $redirect_page ?>" class="btn btn-secondary mb-3">Batal</a>
                    </form>

                    <h4>Galeri Gambar</h4>
                    <div class="row mb-3">
                        <?php if (!empty($images)) {
                            $imagekit_base_url = "https://ik.imagekit.io/bkx7wk6gv"; // Sesuaikan dengan domain ImageKit
                        
                            foreach ($images as $image) { ?>
                                <div class="col-md-3 mb-3">
                                    <div class="card">
                                        <img src="<?php echo htmlspecialchars($imagekit_base_url . $image['file_path'] . $image['file_name']); ?>"
                                            class="card-img-top" alt="Gambar <?php echo ucfirst($category); ?>">

                                        <div class="card-body text-center">
                                            <form action="delete-image.php" method="POST">
                                                <input type="hidden" name="id"
                                                    value="<?php echo htmlspecialchars($categorys[0]['id']); ?>">
                                                <input type="hidden" name="image"
                                                    value="<?php echo htmlspecialchars($image['fileid']); ?>">
                                                <input type="hidden" name="category"
                                                    value="<?php echo htmlspecialchars($category); ?>">
                                                <input type="hidden" name="csrf_token"
                                                    value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                                <button type="submit" class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Hapus gambar ini?')">Hapus</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php }
                        } else { ?>
                            <p>Tidak ada gambar tersedia.</p>
                        <?php } ?>
                    </div>

                    <!-- Form untuk Delete Fasilitas Secara Terpisah -->
                    <h4>Hapus Fasilitas</h4>
                    <div class="row">
                        <?php
                        foreach ($features as $feature_name => $feature_value) {
                            ?>
                            <div class="col-md-3 mb-3"> <!-- Menampilkan 5 kolom di desktop -->
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h6 class="card-title"><?php echo ucwords(str_replace('_', ' ', $feature_name)); ?>
                                        </h6>
                                        <form action="delete-fasilitas.php" method="POST" enctype="multipart/form-data">
                                            <button class="btn btn-danger w-100" type="submit">
                                                <i class="lni lni-trash-can"></i></button>
                                            <input type="hidden" name="feature_name" value="<?php echo $feature_name ?>">
                                            <input type="hidden" name="id" value="<?php echo $categorys[0]['id']; ?>">
                                            <input type="hidden" name="category" value="<?php echo $category ?>">
                                            <input type="hidden" name="csrf_token"
                                                value="<?php echo $_SESSION['csrf_token']; ?>">
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>


                </div>
            </main>
        </div>
    </div>

    <script src="../property/js/sidebar.js"></script>
    <script src="../property/js/insert.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>