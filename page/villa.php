<?php
define('SECURE_ACCESS', true);

require_once __DIR__ . '/../dasboard-admin/database/koneksi.php';


$limit = 4;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) {
    $page = 1;
}
$offset = ($page - 1) * $limit;
$imagekit_base_url = "https://ik.imagekit.io/bkx7wk6gv";

$stmt = $conn->prepare("SELECT * FROM villa");
$stmt->execute();
$result2 = $stmt->get_result();

$pageTitle = 'Calydore | Sewa Villa Terbaik di Puncak Cisarua – Harga Terjangkau';
$description = "Sewa villa eksklusif di Puncak Bogor, Cisarua dengan pemandangan indah dan fasilitas lengkap. Nikmati liburan yang tenang dan nyaman di kawasan Cisarua, Puncak. Booking villa terbaik Anda sekarang!";
require_once __DIR__ . '/../includes/header.php';
$pageType = "villa";
$pageURL = BASE_URL .
    '/page/villa';
?>

<ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="villa-tabs" data-bs-toggle="tab" data-bs-target="#villa_tab" type="button"
            role="tab" aria-controls="villa_tab" aria-selected="true">Semua</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="rekomendasi-tabs" data-bs-toggle="tab" data-bs-target="#rekomendasi_tab"
            type="button" role="tab" aria-controls="rekomendasi_tab" aria-selected="false">Rekomendasi</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="terlaris_tabs" data-bs-toggle="tab" data-bs-target="#terlaris_tab" type="button"
            role="tab" aria-controls="terlaris_tab" aria-selected="false">Terlaris</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="harga" data-bs-toggle="tab" data-bs-target="#harga-tab" type="button" role="tab"
            aria-controls="harga-tab" aria-selected="false" onclick="toggleArrow()">
            Harga
            <i class="fas fa-arrows-alt-v ms-2" id="arrowIcon"></i>
        </button>
    </li>
</ul>

<div class="tab-content" id="myTabContent">

    <!-- Tab ALL -->
    <div class="tab-pane fade show active" id="villa_tab" role="tabpanel" aria-labelledby="villa-tabs">
        <div class="container mb-3">
            <div class="row row-cols-1 row-cols-md-2 g-4 mb-5" id="villa">
                <?php
                $allVillaIdsQuery = "SELECT id FROM villa";
                $allVillaIdsResult = $conn->query($allVillaIdsQuery);
                $allVillaIds = [];
                while ($row = $allVillaIdsResult->fetch_assoc()) {
                    $allVillaIds[] = $row['id'];
                }
                shuffle($allVillaIds);
                $_SESSION['shuffled_villa_ids'] = $allVillaIds;
                $initialIds = array_slice($allVillaIds, 0, 4);
                $_SESSION['shuffled_villa_ids'] = array_slice($allVillaIds, 4);
                $idsString = implode(',', $initialIds);
                $query = "
                SELECT v.id, v.nama_villa, v.deskripsi, v.harga_weekday, v.harga_weekend, 
                       v.kapasitas, v.lokasi, v.rating, v.slug_villa,
                       (SELECT COUNT(*) FROM villa_features vf WHERE vf.villa_id = v.id) AS feature_count
                FROM villa v
                WHERE v.id IN ($idsString)
                ORDER BY FIELD(v.id, $idsString)
            ";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $result = $stmt->get_result();
                $displayedIds = [];
                if ($result->num_rows > 0) {

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

                    while ($villa = $result->fetch_assoc()) {
                        $villaId = $villa['id'];
                        $villa['deskripsi'] = strlen($villa['deskripsi']) > 200
                            ? substr($villa['deskripsi'], 0, 200) . '...'
                            : $villa['deskripsi'];

                        $displayedIds[] = $villaId; // Simpan ID villa yang sudah ditampilkan
                
                        $images = isset($villaImages[$villaId]) ? $villaImages[$villaId] : [];
                        $imagePath = "../dasboard-admin/proses/db_image/";
                        echo '<div class="col">
                        <div class="villa-box">
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
                } else {
                    echo '<p>Data villa tidak tersedia.</p>';
                } ?>
            </div>
            <?php if ($result2->num_rows > $limit) {
                echo '<button id="loadMoreVilla" class="btn loadmore btn-primary w-100" onclick="loadMore(\'villa\')" name="villa">Load More</button>';
            }
            ?>
        </div>
    </div>

    <!-- Tab Rekomendasi -->
    <div class="tab-pane fade " id="rekomendasi_tab" role="tabpanel" aria-labelledby="rekomendasi-tabs">
        <div class="container mb-3">
            <div class="row row-cols-1 row-cols-md-2 g-4 mb-5" id="rekomendasi">
                <?php
                $query = "
            SELECT v.id, v.nama_villa, v.deskripsi, v.harga_weekday, v.harga_weekend, 
                  v.kapasitas, v.lokasi, v.rating, v.slug_villa,
                  (SELECT COUNT(*) FROM villa_features vf WHERE vf.villa_id = v.id) AS feature_count
            FROM villa v
            ORDER BY feature_count DESC
            LIMIT $limit OFFSET $offset
          ";
                $result = $conn->query($query);

                if ($result->num_rows > 0) {
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
                    while ($villa = $result->fetch_assoc()) {
                        $villaId = $villa['id'];
                        $villa['deskripsi'] = strlen($villa['deskripsi']) > 200
                            ? substr($villa['deskripsi'], 0, 200) . '...'
                            : $villa['deskripsi'];

                        $images = isset($villaImages[$villaId]) ? $villaImages[$villaId] : [];
                        $imagePath = "../dasboard-admin/proses/db_image/";
                        echo '
                <div class="col">
                    <div class="villa-box">
                        <div class="sub-box">
                            <div id="carousel-' . $villa['id'] . '" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-inner">';


                        $isActive = true;
                        foreach ($images as $image) {
                            echo '
                              <div class="carousel-item ' . ($isActive ? 'active' : '') . '">
                                  <img src="' . htmlspecialchars($image) . '?tr=w-640,h-360,q-80" class="d-block w-100" alt="Villa" loading="lazy">
                              </div>';
                            $isActive = false;
                        }

                        echo '
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
                } else {
                    echo '<p>Data villa tidak tersedia.</p>';
                } ?>
            </div>
            <?php
            if ($result2->num_rows >= 5) {
                echo '<button id="loadMoreRekomendasi" class="btn loadmore btn-primary w-100" onclick="loadMore(\'rekomendasi\')" name="rekomendasi">Load More</button>';
            }
            ?>
        </div>

    </div>

    <!-- Tab Terlaris -->
    <div class="tab-pane fade " id="terlaris_tab" role="tabpanel" aria-labelledby="terlaris-tabs">
        <div class="container mb-3">
            <div class="row row-cols-1 row-cols-md-2 g-4 mb-5" id="terlaris">
                <?php

                $query = "SELECT * FROM villa ORDER BY rating DESC LIMIT $limit OFFSET $offset";
                $result = $conn->query($query);

                if ($result->num_rows > 0) {

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
                    while ($villa = $result->fetch_assoc()) {
                        $villaId = $villa['id'];
                        $villa['deskripsi'] = strlen($villa['deskripsi']) > 200
                            ? substr($villa['deskripsi'], 0, 200) . '...'
                            : $villa['deskripsi'];

                        $images = isset($villaImages[$villaId]) ? $villaImages[$villaId] : [];
                        $imagePath = "../dasboard-admin/proses/db_image/";
                        echo '
                  <div class="col">
                      <div class="villa-box">
                          <div class="sub-box">
                              <div id="carousel-' . $villa['id'] . '" class="carousel slide" data-bs-ride="carousel">
                                  <div class="carousel-inner">';


                        $isActive = true;
                        foreach ($images as $image) {
                            echo '
                                <div class="carousel-item ' . ($isActive ? 'active' : '') . '">
                                    <img src="' . htmlspecialchars($image) . '?tr=w-640,h-360,q-80" class="d-block w-100" alt="Villa" loading="lazy">
                                </div>';
                            $isActive = false;
                        }

                        echo '
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
                } else {
                    echo '<p>Data villa tidak tersedia.</p>';
                } ?>
            </div>
            <?php
            if ($result2->num_rows >= 5) {
                echo '<button id="loadMoreTerlaris" class="btn loadmore btn-primary w-100" onclick="loadMore(\'terlaris\')" name="terlaris">Load More</button>';
            }
            ?>
        </div>

    </div>

    <!-- Tab harga_up -->
    <div class="tab-pane fade " id="harga-tab-up" role="tabpanel" aria-labelledby="hargaup-tabs">
        <div class="container mb-3">
            <div class="row row-cols-1 row-cols-md-2 g-4 mb-5" id="hargaup">
                <?php

                $query = "SELECT * FROM villa ORDER BY harga_weekday DESC LIMIT $limit OFFSET $offset";
                $result = $conn->query($query);

                if ($result->num_rows > 0) {

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
                    while ($villa = $result->fetch_assoc()) {
                        $villaId = $villa['id'];
                        $villa['deskripsi'] = strlen($villa['deskripsi']) > 200
                            ? substr($villa['deskripsi'], 0, 200) . '...'
                            : $villa['deskripsi'];

                        $images = isset($villaImages[$villaId]) ? $villaImages[$villaId] : [];
                        $imagePath = "../dasboard-admin/proses/db_image/";
                        echo '
                    <div class="col">
                        <div class="villa-box">
                            <div class="sub-box">
                                <div id="carousel-' . $villa['id'] . '" class="carousel slide" data-bs-ride="carousel">
                                    <div class="carousel-inner">';


                        $isActive = true;
                        foreach ($images as $image) {
                            echo '
                                  <div class="carousel-item ' . ($isActive ? 'active' : '') . '">
                                      <img src="' . htmlspecialchars($image) . '?tr=w-640,h-360,q-80" class="d-block w-100" alt="Villa" loading="lazy">
                                  </div>';
                            $isActive = false;
                        }

                        echo '
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
                } else {
                    echo '<p>Data villa tidak tersedia.</p>';
                } ?>
            </div>
            <?php
            if ($result2->num_rows >= 5) {
                echo '<button id="loadMoreHargaup" class="btn loadmore btn-primary w-100" onclick="loadMore(\'hargaup\')" name="hargaup">Load More</button>';
            }
            ?>
        </div>

    </div>

    <!-- Tab harga_down -->
    <div class="tab-pane fade " id="harga-tab-down" role="tabpanel" aria-labelledby="hargadown-tabs">
        <div class="container mb-3">
            <div class="row row-cols-1 row-cols-md-2 g-4 mb-5" id="hargadown">
                <?php

                $query = "SELECT * FROM villa ORDER BY harga_weekday ASC LIMIT $limit OFFSET $offset";
                $result = $conn->query($query);

                if ($result->num_rows > 0) {

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
                    while ($villa = $result->fetch_assoc()) {
                        $villaId = $villa['id'];
                        $villa['deskripsi'] = strlen($villa['deskripsi']) > 200
                            ? substr($villa['deskripsi'], 0, 200) . '...'
                            : $villa['deskripsi'];

                        $images = isset($villaImages[$villaId]) ? $villaImages[$villaId] : [];
                        $imagePath = "../dasboard-admin/proses/db_image/";
                        echo '
                    <div class="col">
                        <div class="villa-box">
                            <div class="sub-box">
                                <div id="carousel-' . $villa['id'] . '" class="carousel slide" data-bs-ride="carousel">
                                    <div class="carousel-inner">';


                        $isActive = true;
                        foreach ($images as $image) {
                            echo '
                                  <div class="carousel-item ' . ($isActive ? 'active' : '') . '">
                                      <img src="' . htmlspecialchars($image) . '?tr=w-640,h-360,q-80" class="d-block w-100" alt="Villa" loading="lazy">
                                  </div>';
                            $isActive = false;
                        }

                        echo '
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
                } else {
                    echo '<p>Data villa tidak tersedia.</p>';
                } ?>
            </div>
            <?php
            if ($result2->num_rows >= 5) {
                echo '<button id="loadMoreHargadown" class="btn loadmore btn-primary w-100" onclick="loadMore(\'hargadown\')" name="hargadown">Load More</button>';
            }
            ?>
        </div>

    </div>


</div>




<script src="<?php echo BASE_URL; ?>/assets/js/load_villa.js"></script>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "LodgingBusiness",
  "name": "Calydore | Sewa Villa Terbaik di Puncak Cisarua",
  "url": "https://calydore.com/page/villa",
  "logo": "<?php echo BASE_URL; ?>/assets/favico.ico",
  "description": "Sewa villa terbaik di Puncak Cisarua dengan fasilitas lengkap dan harga terjangkau. Booking mudah dan terpercaya!",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "Jl. Raya Puncak KM. 79 No. 62, Kopo, Cisarua",
    "addressLocality": "Cisarua",
    "addressRegion": "Jawa Barat",
    "postalCode": "16750",
    "addressCountry": "ID"
  },
  "telephone": "+62 877 7891 1805",
  "email": "calydoreofficial@gmail.com",
  "sameAs": [
    "https://www.instagram.com/calydore.official",
    "https://www.tiktok.com/@calydore.official",
    "https://www.facebook.com/CalydoreOfficial"
  ]
}
</script>


<?php
$ChatWa = "Permisi Kak? Mau tanya tentang Villa di Calydore...";
$encodedText = urlencode($ChatWa);
$phoneNumber = "6287778911805";
$waLink = "https://api.whatsapp.com/send?phone={$phoneNumber}&text={$encodedText}";

include_once '../includes/wa-kanan.php';
require_once '../includes/footer.php';
?>