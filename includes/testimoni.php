<?php

if (!defined('SECURE_ACCESS')) {
    http_response_code(404);
    header("Location: " . BASE_URL . "/error.php");
    exit();
}

include_once 'dasboard-admin/database/koneksi.php';
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$testimoni = "SELECT * FROM assets_images WHERE image_type = 'testimoni'";
$result = $conn->query($testimoni);

$imagekit_base_url = "https://ik.imagekit.io/bkx7wk6gv";
?>
<style>
    .carousel-item img {
        width: 100%;
        max-height: 100%;
        object-fit: cover;
    }
</style>
<div class="container mb-2">
    <div class="divider-testimoni">
        <span>Testimoni</span>
    </div>
    <div id="carouselExampleInterval" class="carousel slide col-lg-8 offset-lg-2" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php

            if ($result->num_rows > 0) {

                $isActive = true;
                while ($row = $result->fetch_assoc()) {


                    $name_assets = $row['name_assets'];
                    $image_path = $row['file_path'];
                    $image_name = $row['file_name'];
                    $image_url = $imagekit_base_url . $image_path . $image_name;

                    ?>
                    <div class="carousel-item <?= $isActive ? 'active' : ''; ?>" data-bs-interval="2000">
                        <img src="<?= htmlspecialchars($image_url); ?>" class="d-block w-100" alt="Testimoni" loading="lazy">
                    </div>
                    <?php
                    $isActive = false;
                }
            } else {
                echo '<div class="carousel-item active">
                        <p class="text-center">Tidak ada testimoni yang tersedia</p>
                      </div>';
            }
            ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleInterval"
            data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleInterval"
            data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</div>

<?php
$conn->close();
?>