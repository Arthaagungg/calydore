<?php

include 'session.php';

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo "<script>alert('ID tidak valid !'); window.location.href = '$redirect_page';</script>";
    exit();
}

$id = intval($_POST['id']);
$feature_name = $_POST['feature_name'];
$category = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['category']);  // Sanitasi kategori

try {
    // Mulai transaksi
    $conn->begin_transaction();

    // Nama tabel berdasarkan kategori
    $table_name = "{$category}_features";

    // Query untuk menghapus data fitur berdasarkan nama fitur dan id
    $sql = "DELETE FROM $table_name WHERE feature_name = ? AND {$category}_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $feature_name, $id);

    if ($stmt->execute()) {
        // Commit transaksi jika berhasil
        $conn->commit();
        echo "<script>alert('Fasilitas berhasil dihapus!'); window.location.href = '$redirect_page';</script>";
    } else {
        // Rollback transaksi jika terjadi kesalahan
        $conn->rollback();
        echo "<script>alert('Gagal menghapus fasilitas!'); window.location.href = '$redirect_page';</script>";
    }
} catch (Exception $e) {
    // Tangani kesalahan dan rollback transaksi
    $conn->rollback();
    echo "<script>alert('Terjadi kesalahan: " . $e->getMessage() . "'); window.location.href = '$redirect_page';</script>";
}
