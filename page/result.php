<?php
define('SECURE_ACCESS', true);

include_once '../dasboard-admin/database/koneksi.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';
$table = isset($_GET['table']) ? $_GET['table'] : '';

$search = $conn->real_escape_string($search);
$table = $conn->real_escape_string($table);

$results = [];
$limit = 4;
$uniqueIds = [];
$imagekit_base_url = "https://ik.imagekit.io/bkx7wk6gv";

if (!empty($search) && !empty($table)) {

    $columnsQuery = $conn->query("SHOW COLUMNS FROM `$table`");
    $columns = [];
    $relations = [];

    while ($columnRow = $columnsQuery->fetch_array()) {
        $column = $columnRow['Field'];
        $columns[] = $column;

        if (preg_match('/(.+)_id$/', $column, $matches)) {
            $relations[$column] = $matches[1];
        }
    }


    $conditions = [];
    foreach ($columns as $column) {
        $conditions[] = "`$column` LIKE ?";
    }
    $query = "SELECT DISTINCT * FROM `$table` WHERE " . implode(' OR ', $conditions) . " LIMIT ?";

    $stmt = $conn->prepare($query);
    $searchTerm = "%$search%";
    $params = array_fill(0, count($columns), $searchTerm);
    $params[] = $limit;
    $types = str_repeat('s', count($columns)) . 'i';
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        if (!in_array($row['id'], $uniqueIds)) {
            $uniqueIds[] = $row['id'];


            foreach ($relations as $foreignKey => $relatedTable) {
                $relatedId = $row[$foreignKey];
                if (!empty($relatedId)) {

                    $checkTableQuery = $conn->query("SHOW TABLES LIKE '$relatedTable'");
                    if ($checkTableQuery->num_rows > 0) {
                        $relatedQuery = $conn->query("SELECT * FROM `$relatedTable` WHERE id = '$relatedId' LIMIT 1");
                        if ($relatedQuery->num_rows > 0) {
                            $relatedData = $relatedQuery->fetch_assoc();
                            $row = array_merge($row, $relatedData);
                        }
                    }
                }
            }

            $results[] = $row;
        }
    }
    $stmt->close();


    $totalQuery = "SELECT COUNT(DISTINCT id) AS total FROM `$table` WHERE " . implode(' OR ', $conditions);
    $stmt = $conn->prepare($totalQuery);
    $stmt->bind_param(str_repeat('s', count($columns)), ...array_fill(0, count($columns), $searchTerm));
    $stmt->execute();
    $totalResult = $stmt->get_result();
    $totalRows = $totalResult->fetch_assoc()['total'];
    $stmt->close();
}
$pageType = "website";
$pageURL = BASE_URL;
$pageTitle = "Calydore | Puncak Cisarua: Sewa Villa, Hotel, Glamping, Catering & Outbound Terbaik!";

require_once '../includes/header.php';
?>

<div class="container mb-3">
    <h3 class="text-lg font-semibold text-white text-center mb-1">
        🔍 Hasil Pencarian untuk :
        <span class="font-medium">" <?= htmlspecialchars($search) ?> "</span>
    </h3>
    <h3 class="text-lg font-semibold text-white text-center mb-3">
        Untuk Katalog :
        " <?= htmlspecialchars(str_replace(['_', 'features'], [' ', 'fasilitas'], $table)) ?> "</span>
    </h3>
    <div class="row row-cols-1 row-cols-md-2 g-4 mb-5" id="result-container">
        <?php if (!empty($results)): ?>
            <?php foreach ($results as $villa):
                $relatedId = $villa['id'];
                $imageTable = "";
                $idColumn = "";
                $namaTable = "";

                if (!empty($relatedTable)) {
                    $imageTable = $relatedTable . "_images";
                    $idColumn = $relatedTable . "_id";
                    $namaTable = $relatedTable;
                } else {
                    $imageTable = $table . "_images";
                    $idColumn = $table . "_id";
                    $namaTable = $table;
                }

                $imageQuery = "SELECT file_name, file_path FROM $imageTable WHERE $idColumn = ? LIMIT 1";
                $stmt = $conn->prepare($imageQuery);
                $stmt->bind_param("i", $relatedId);
                $stmt->execute();
                $imageResult = $stmt->get_result();

                $villaImages = [];
                if ($imageResult->num_rows > 0) {
                    while ($imageRow = $imageResult->fetch_assoc()) {
                        $villaImages[] = $imagekit_base_url . $imageRow['file_path'] . $imageRow['file_name'];
                    }
                }
                $stmt->close();

                ?>

                <div class="col">
                    <div class="villa-box">
                        <div class="sub-box">
                            <div id="carousel-<?= htmlspecialchars($villa['id']) ?>" class="carousel slide"
                                data-bs-ride="carousel">
                                <div class="carousel-inner">
                                    <?php
                                    $isActive = true;
                                    foreach ($villaImages as $image): ?>
                                        <div class="carousel-item <?= $isActive ? 'active' : '' ?>">
                                            <img src="<?= htmlspecialchars($image) ?>?tr=w-640,h-360,q-80" class="d-block w-100"
                                                alt="Villa" loading="lazy">
                                        </div>
                                        <?php
                                        $isActive = false;
                                    endforeach; ?>
                                </div>
                            </div>
                            <div class="sub-box-content">
                                <p class="title"> <?= htmlspecialchars($villa["nama_" . $namaTable] ?? 'Tidak Ada Nama') ?> </p>
                                <p class="descTitle"> <?= htmlspecialchars($villa['deskripsi'] ?? 'Tidak Ada Deskripsi') ?> </p>
                                <div class="price-info">
                                    <p class="desc1">Mulai dari</p>
                                    <div class="price-row">
                                        <p class="price">Rp
                                            <?= number_format(!empty($villa['harga_weekday']) ? $villa['harga_weekday'] : $villa['harga'], 0, ',', '.') ?>
                                        </p>
                                        <?php
                                        if ($namaTable == "outbound" || $namaTable == "catering") {
                                            ?>
                                            <p class="desc2">Per pack</p>
                                            <?php
                                        } else {
                                            ?>
                                            <p class="desc2">Per Malam</p>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                            $slug = "slug_" . $namaTable;
                            ?>
                            <a href="<?php echo htmlspecialchars($baseUrl . '/' . str_replace('_', '-', $namaTable) . '/' . $villa[$slug]); ?>"
                                class="btn-selengkapnya">Selengkapnya</a>

                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
        <?php else: ?>
            <p>Data tidak ditemukan.</p>
        <?php endif; ?>
    </div>


    <?php if ($totalRows > $limit): ?>
        <button id="loadMoreVilla" class="btn loadmore btn-primary w-100">Load More</button>
    <?php endif; ?>

</div>

<?php
$ChatWa = "Permisi Kak! Mau tanya tentang Calydore...";
$encodedText = urlencode($ChatWa);
$phoneNumber = "6287778911805";
$waLink = "https://api.whatsapp.com/send?phone={$phoneNumber}&text={$encodedText}";

include_once '../includes/wa-kanan.php';
include '../includes/footer.php';
?>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const loadMoreButton = document.getElementById("loadMoreVilla");

        if (loadMoreButton) {
            let offset = 4;
            let limit = 4;
            const search = "<?= htmlspecialchars($search) ?>";
            const table = "<?= htmlspecialchars($table) ?>";


            function checkLoadMoreVisibility(totalData) {
                console.log(totalData, offset);
                const remainingData = totalData - offset;

                if (remainingData <= 0) {
                    loadMoreButton.style.display = "none";
                } else {
                    loadMoreButton.style.display = "block";
                }
            }

            function loadData() {
                let button = loadMoreButton;
                button.innerText = "Loading...";
                button.disabled = true;

                let xhr = new XMLHttpRequest();
                xhr.open("POST", "../includes/database-user/load_result.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                xhr.onload = function () {
                    if (xhr.status === 200) {
                        try {
                            let response = JSON.parse(xhr.responseText);
                            let totalData = response.data.isLastPage;
                            document.getElementById("result-container").innerHTML += response.data.html;

                            offset += limit;

                            button.innerText = "Load More";
                            button.disabled = false;
                            checkLoadMoreVisibility(totalData);
                        } catch (e) {
                            console.error("Error parsing JSON response:", e);
                        }
                    }
                };

                xhr.send("search=" + encodeURIComponent(search) + "&table=" + encodeURIComponent(table) + "&offset=" + offset);
            }

            loadMoreButton.addEventListener("click", loadData);
        }
    });
</script>