<?php

include '../../dasboard-admin/database/koneksi.php';

// Ambil parameter pencarian
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search = $conn->real_escape_string($search);

$suggestions = [];

// Daftar tabel yang akan dicari
$tables = [
    'villa',
    'villa_kamar',
    'glamping',
    'catering',
    'outbound',
    'villa_features',
    'villa_kamar_features',
    'catering_features'
];

// Kolom yang dikecualikan dari pencarian
$excludedColumns = ['id', 'deskripsi', 'icons_link', 'updated_at', 'created_at', 'villa_id', 'villa_kamar_id', 'glamping_id', 'catering_id', 'outbound_id'];

if (!empty($search)) {
    // Loop melalui tabel yang diinginkan
    foreach ($tables as $table) {
        // Cek apakah tabel ada sebelum melakukan query
        $tableExistsQuery = $conn->query("SHOW TABLES LIKE '$table'");
        if ($tableExistsQuery && $tableExistsQuery->num_rows > 0) {
            // Mendapatkan daftar kolom di tabel
            $columnsQuery = $conn->query("SHOW COLUMNS FROM `$table`");
            if ($columnsQuery) {
                $columns = [];
                while ($columnRow = $columnsQuery->fetch_array()) {
                    $column = $columnRow['Field'];
                    // Cek jika kolom tidak termasuk dalam pengecualian
                    if (!in_array($column, $excludedColumns)) {
                        $columns[] = $column;
                    }
                }

                // Membangun query pencarian untuk setiap kolom
                foreach ($columns as $column) {
                    $query = "SELECT DISTINCT `$column` FROM `$table` WHERE `$column` LIKE ? LIMIT 5";
                    $stmt = $conn->prepare($query);
                    $searchTerm = "%$search%"; // Menambahkan wildcard di sekitar input pencarian
                    $stmt->bind_param("s", $searchTerm); // Mengikat parameter
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_array()) {
                            $value = $row[$column];
                            // Tambahkan data beserta nama tabelnya
                            if (!in_array(['value' => $value, 'table' => $table], $suggestions)) {
                                $suggestions[] = ['value' => $value, 'table' => $table];
                            }
                        }
                    }
                    $stmt->close();
                }
            }
        }
    }
}

// Kembalikan data sebagai JSON
header('Content-Type: application/json');
echo json_encode($suggestions);
