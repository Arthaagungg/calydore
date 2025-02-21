<?php

include 'session.php';
require_once '../database/config-imagekit.php'; // Panggil konfigurasi ImageKit

$errors = [];

// **Sanitize dan Validasi Input**
$category = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['category']); // Membersihkan karakter selain huruf, angka, dan underscore
$category_data = $_POST['categorys'] ?? [];

// Mengamankan dan memvalidasi input user
$nama_catering = htmlspecialchars(strip_tags($category_data['nama_catering'] ?? ''), ENT_QUOTES, 'UTF-8');
$deskripsi = htmlspecialchars(strip_tags($category_data['deskripsi'] ?? ''), ENT_QUOTES, 'UTF-8');
$harga = filter_var($category_data['harga'] ?? '', FILTER_VALIDATE_FLOAT);
$category_catering = htmlspecialchars(strip_tags($category_data['category_catering'] ?? ''), ENT_QUOTES, 'UTF-8');

// **Validasi input wajib**
if (!$nama_catering || !$category_catering || !$harga) {
    $errors[] = 'Nama catering, kategori catering, dan harga wajib diisi dengan format yang benar.';
}

// **Cek apakah nama_catering sudah ada**
function getUniqueName($conn, $nama_catering)
{
    $original_name = $nama_catering;
    $counter = 1;

    $stmt = $conn->prepare("SELECT COUNT(*) FROM catering WHERE nama_catering = ?");
    $stmt->bind_param("s", $nama_catering);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    while ($count > 0) {
        $nama_catering = $original_name . ' ' . $counter;
        $stmt = $conn->prepare("SELECT COUNT(*) FROM catering WHERE nama_catering = ?");
        $stmt->bind_param("s", $nama_catering);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        $counter++;
    }

    return $nama_catering;
}

// **Buat slug dari nama catering**
function generateSlug($nama_catering)
{
    $slug = strtolower(str_replace(' ', '-', $nama_catering)); // Ganti spasi dengan "-"
    $slug = preg_replace('/[^a-z0-9\-]/', '', $slug); // Hapus karakter tidak valid
    return $slug;
}

// **Cek dan buat nama unik**
$nama_catering = getUniqueName($conn, $nama_catering);
$slug_catering = generateSlug($nama_catering);

/**
 * **Fungsi Upload ke ImageKit**
 */
function uploadToImageKit($image, $folder, $nama_catering, $category_catering, $image_type)
{
    global $imageKit;

    if (!isset($image['tmp_name']) || empty($image['tmp_name'])) {
        return ['error' => 'Tidak ada file yang diunggah.'];
    }

    $imageName = uniqid($nama_catering . '_') . '.webp';

    $imagePath = $image['tmp_name'];
    $tmpFilePath = tempnam(sys_get_temp_dir(), 'imagekit_');
    $imageResource = imagecreatefromstring(file_get_contents($imagePath));

    if ($imageResource !== false) {
        imagewebp($imageResource, $tmpFilePath, 75);
        imagedestroy($imageResource);
    } else {
        return ['error' => 'Gagal mengkonversi gambar ke format WebP.'];
    }

    $folder = preg_replace('/[^a-zA-Z0-9_]/', '', $category_catering);
    $nama_catering = str_replace([' ', '(', ')'], ['-', '', ''], $nama_catering);
    $folder = str_replace([' ', '(', ')'], ['-', '', ''], $folder);

    $folderPath = "/catering/{$folder}/{$nama_catering}/";

    $upload = $imageKit->uploadFile([
        'file' => fopen($tmpFilePath, 'r'),
        'fileName' => $imageName,
        'folder' => $folderPath,
        'tags' => ['webp', 'lossless'],
        'useUniqueFileName' => true,
    ]);

    unlink($tmpFilePath);

    if (isset($upload->result) && $upload->result->url) {
        $parsed_url = parse_url($upload->result->url, PHP_URL_PATH);
        $file_name = basename($parsed_url);
        return [
            'file_id' => $upload->result->fileId,
            'file_name' => $file_name,
            'file_path' => $folderPath,
            'image_type' => $image_type, // Tambahkan image_type di sini
        ];
    } else {
        return ['error' => 'Gagal mengupload ke ImageKit. ' . json_encode($upload)];
    }
}

/**
 * **Fungsi Proses Upload Banyak Gambar**
 */
function handleImageUpload($images, $folder, $nama_catering, $category_catering, $image_type)
{
    $uploaded_images = [];

    if (isset($images) && !empty($images['name'][0])) {
        $totalFiles = count($images['name']);

        for ($i = 0; $i < $totalFiles; $i++) {
            $file = [
                'name' => $images['name'][$i],
                'tmp_name' => $images['tmp_name'][$i],
            ];
            $result = uploadToImageKit($file, $folder, $nama_catering, $category_catering, $image_type);
            if (isset($result['error'])) {
                return ['error' => $result['error']];
            }
            $uploaded_images[] = $result;
        }
    }

    return $uploaded_images;
}

// **Proses Upload Gambar**
$catering_images = handleImageUpload($_FILES['images_catering'] ?? [], 'catering', $nama_catering, $category_catering, 'catering');
$menu_images = handleImageUpload($_FILES['images_menu'] ?? [], 'menu', $nama_catering, $category_catering, 'menu');
$opsional_images = handleImageUpload($_FILES['images_opsional'] ?? [], 'opsional', $nama_catering, $category_catering, 'opsional');

if (isset($catering_images['error']) || isset($menu_images['error']) || isset($opsional_images['error'])) {
    $errors[] = $catering_images['error'] ?? $menu_images['error'] ?? $opsional_images['error'];
}

if (!empty($errors)) {
    echo json_encode(['status' => 'error', 'messages' => $errors]);
    exit;
}

// **Simpan Data ke Database**
try {
    $conn->begin_transaction();

    $stmt = $conn->prepare("INSERT INTO catering (nama_catering, deskripsi, harga, category_catering, slug_catering) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $nama_catering, $deskripsi, $harga, $category_catering, $slug_catering);
    $stmt->execute();
    $catering_id = $conn->insert_id;

    $stmt = $conn->prepare("INSERT INTO catering_images (catering_id, image_type, fileid, file_name, file_path, file_type) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $catering_id, $image_type, $file_id, $file_name, $file_path, $file_type);

    foreach (array_merge($catering_images, $menu_images, $opsional_images) as $image) {
        $image_type = $image['image_type']; // Ambil image_type dari hasil upload
        $file_id = $image['file_id'];
        $file_name = $image['file_name'];
        $file_path = $image['file_path'];
        $file_type = 'webp';
        $stmt->execute();
    }

    $conn->commit();
    echo "<script>alert('Berhasil mengupload data!'); window.location.href = '$redirect_page';</script>";
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => 'Failed to upload data.', 'error' => $e->getMessage()]);
}