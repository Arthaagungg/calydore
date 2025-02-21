<?php

include 'session.php';
require_once '../database/config-imagekit.php'; // Panggil konfigurasi ImageKit

// Validasi ID villa
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo "<script>alert('ID tidak valid !'); window.location.href = '$redirect_page';</script>";
    exit();
}
$id = intval($_POST['id']);
$category = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['category']);
try {
    // Mulai transaksi
    $conn->begin_transaction();

    // Hapus gambar villa dari database dan file system
    $sql_get_images = "SELECT file_path FROM {$category}_images WHERE {$category}_id = ?";
    $stmt_images = $conn->prepare($sql_get_images);
    $stmt_images->bind_param("i", $id);
    $stmt_images->execute();
    $result_images = $stmt_images->get_result();

    // Menghapus file gambar dari server
    while ($row = $result_images->fetch_assoc()) {
        $file_path = $row['file_path'];
        $deleteFolder = $imageKit->deleteFolder($file_path);
    }

    // Hapus data gambar dari database
    $sql_delete_images = "DELETE FROM {$category}_images WHERE {$category}_id = ?";
    $stmt_delete_images = $conn->prepare($sql_delete_images);
    $stmt_delete_images->bind_param("i", $id);
    $stmt_delete_images->execute();

    // Hapus fitur villa
    if ($category != "catering") {
        $sql_delete_features = "DELETE FROM {$category}_features WHERE {$category}_id = ?";
        $stmt_delete_features = $conn->prepare($sql_delete_features);
        $stmt_delete_features->bind_param("i", $id);
        $stmt_delete_features->execute();
    }
    // Hapus data villa
    $sql_delete_villa = "DELETE FROM {$category} WHERE id = ?";
    $stmt_delete_villa = $conn->prepare($sql_delete_villa);
    $stmt_delete_villa->bind_param("i", $id);
    $stmt_delete_villa->execute();

    // Commit transaksi
    $conn->commit();

    // Redirect dengan pesan sukses
    echo "<script>alert('Data berhasil di hapus !'); window.location.href = '$redirect_page';</script>";
    exit();
} catch (Exception $e) {
    // Rollback jika terjadi kesalahan
    $conn->rollback();
    $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
    header("Location: ../../page-admin/dashboard-villa.php");
    exit();
}
