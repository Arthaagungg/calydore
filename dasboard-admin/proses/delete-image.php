<?php

include 'session.php';
require_once '../database/config-imagekit.php'; // Panggil konfigurasi ImageKit

// Validasi ID dan nama gambar
if (isset($_POST['id']) && isset($_POST['image']) && !empty($_POST['id']) && !empty($_POST['image'])) {
    $id = intval($_POST['id']);
    $image_name = $_POST['image'];
    $category = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['category']);
    // Path file gambar
    $image_fild = $image_name;
    $deleteFile = $imageKit->deleteFile($image_fild);


    // Hapus gambar dari database
    $sql = "DELETE FROM {$category}_images WHERE {$category}_id = ? AND fileid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $id, $image_name);
    $stmt->execute();
    // Redirect kembali ke halaman edit villa
    echo "<script>alert('Foto berhasil di delete !'); window.location.href = '$redirect_page';</script>";
    exit();
} else {
    echo "Gagal menghapus gambar.";
}
