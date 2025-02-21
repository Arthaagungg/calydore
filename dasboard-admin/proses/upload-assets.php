<?php
include 'session.php';
require_once '../database/config-imagekit.php'; // Panggil konfigurasi ImageKit
// Fungsi untuk mengelola upload gambar ke ImageKit dan konversi ke WebP
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_assets = $_POST['nama-assets'];
    $image_type = $_POST['assets-type']; // Set image type sebagai webp
    // Cek apakah nama asset sudah ada
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM assets_images WHERE name_assets LIKE ?");
    $stmt->bind_param("s", $nama_assets);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row['count'] > 0) {
        $nama_assets .= "_" . ($row['count'] + 1);
    }

    // Ambil assets_id terkecil yang tersedia
    $result = $conn->query("SELECT MIN(id) AS min_id FROM assets_images");
    $row = $result->fetch_assoc();
    $assets_id = $row['min_id'] ? $row['min_id'] : 1;

    // Periksa apakah file diunggah
    if (isset($_FILES['images']['tmp_name']) && !empty($_FILES['images']['tmp_name'])) {
        foreach ($_FILES['images']['name'] as $key => $name) {
            $fileTmpName = $_FILES['images']['tmp_name'][$key];
            $fileExt = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        }

        if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif'])) {
            // Konversi gambar ke WebP
            $image = imagecreatefromstring(file_get_contents($fileTmpName));
            $tmpFilePath = tempnam(sys_get_temp_dir(), 'imagekit_');
            imagewebp($image, $tmpFilePath, 75); // Kualitas lossless
            imagedestroy($image);

            // Unggah ke ImageKit
            $file_name = $nama_assets . uniqid() . ".webp";
            $upload = $imageKit->uploadFile([
                "file" => fopen($tmpFilePath, "r"),
                "fileName" => $file_name,
                "folder" => "/assets/"
            ]);

            unlink($tmpFilePath); // Hapus file sementara

            if (isset($upload->result) && $upload->result->url) {
                $fileId = $upload->result->fileId;
                $file_path = "/assets/";
                $file_type = 'webp';
                $parsed_url = parse_url($upload->result->url, PHP_URL_PATH);
                $filename = basename($parsed_url); // Ambil hanya nama file

                // Simpan data ke database
                $sql = "INSERT INTO assets_images (name_assets, assets_id, image_type, file_name, file_path, file_type, fileid, uploaded_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sisssss", $nama_assets, $assets_id, $image_type, $filename, $file_path, $file_type, $fileId);

                if ($stmt->execute()) {
                    echo "<script>alert('Berhasil mengupload data!'); window.location.href = '$redirect_page';</script>";
                } else {
                    echo "<script>alert('Gagal menyimpan ke database!'); window.location.href = '$redirect_page';</script>";
                }
                $stmt->close();
            } else {
                echo "<script>alert('Gagal mengunggah ke ImageKit!'); window.location.href = '$redirect_page';</script>";
            }
        } else {
            echo "<script>alert('Format file tidak didukung! Hanya JPG, JPEG, PNG, dan GIF.'); window.location.href = '$redirect_page';</script>";
        }
    } else {
        echo "<script>alert('Tidak ada file yang diunggah!'); window.location.href = '$redirect_page';</script>";
    }

    $conn->close();
} else {
    echo "<script>alert('Metode request tidak valid!'); window.location.href = '$redirect_page';</script>";
}
