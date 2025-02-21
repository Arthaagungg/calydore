<?php

include 'session.php';

// Masukkan konfigurasi ImageKit
require_once '../database/config-imagekit.php'; // Panggil konfigurasi ImageKit

// Cek apakah objek ImageKit terinisialisasi
if (!$imageKit) {
    $errors[] = 'ImageKit tidak terinisialisasi dengan benar.';
    echo json_encode(['status' => 'error', 'messages' => $errors]);
    exit;
}

// Initialize an array to store errors
$errors = [];

// Sanitize and validate category
$category = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['category']);
$category_data = $_POST['categorys'] ?? [];
$id = intval($_POST['id']);
// Validate and sanitize input data
$nama_catering = htmlspecialchars($category_data['nama_catering'] ?? '', ENT_QUOTES, 'UTF-8');
$nama_cateringDB = htmlspecialchars($category_data['nama_catering'] ?? '', ENT_QUOTES, 'UTF-8');
$deskripsi = htmlspecialchars($category_data['deskripsi'] ?? '', ENT_QUOTES, 'UTF-8');
$harga = filter_var($category_data['harga'] ?? '', FILTER_VALIDATE_FLOAT);
$category_catering = htmlspecialchars($category_data['category_catering'] ?? '', ENT_QUOTES, 'UTF-8');

if (!$nama_catering || !$category_catering || !$harga) {
    $errors[] = 'Nama catering, kategori catering, dan harga wajib diisi dengan format yang benar.';
}

function uploadToImageKit($image, $folder, $nama_catering, $category_catering)
{
    global $imageKit;

    if (!isset($image['tmp_name']) || empty($image['tmp_name'])) {
        return ['error' => 'Tidak ada file yang diunggah.'];
    }

    // **Buat nama file unik berdasarkan nama catering**
    $imageName = uniqid($nama_catering . '_') . '.webp'; // Ubah ekstensi menjadi .webp

    // **Konversi gambar ke WebP**
    $imagePath = $image['tmp_name'];
    $tmpFilePath = tempnam(sys_get_temp_dir(), 'imagekit_'); // Membuat file sementara
    $imageResource = imagecreatefromstring(file_get_contents($imagePath)); // Membaca gambar

    // Jika gambar adalah JPEG atau PNG, konversi ke WebP
    if ($imageResource !== false) {
        imagewebp($imageResource, $tmpFilePath, 75); // Konversi ke WebP dengan kualitas 75
        imagedestroy($imageResource); // Bersihkan memori
    } else {
        return ['error' => 'Gagal mengkonversi gambar ke format WebP.'];
    }

    // Membuat nama unik untuk file
    $imageName = uniqid($folder . '_') . '.webp';

    // Folder tujuan di ImageKit, menggunakan kategori
    // Sanitasi nama folder untuk menghindari spasi dan karakter khusus
    $folder = preg_replace('/[^a-zA-Z0-9_]/', '', $category_catering); // Pastikan kategori hanya berisi karakter yang valid
    $nama_catering = preg_replace('/[^a-zA-Z0-9_ -]/', '', $nama_catering); // Pastikan nama unik tidak mengandung karakter yang tidak valid

    // Ganti spasi dengan tanda hubung (-) dan hilangkan tanda kurung
    $nama_catering = str_replace(' ', '-', $nama_catering);
    $nama_catering = str_replace(['(', ')'], '', $nama_catering);

    $folder = str_replace(' ', '-', $folder);
    $folder = str_replace(['(', ')'], '', $folder);

    // Membuat folder path yang valid

    $folderPath = "/catering/{$folder}/{$nama_catering}/"; // Struktur folder yang lebih aman

    $upload = $imageKit->uploadFile([
        'file' => fopen($tmpFilePath, 'r'), // Gunakan file sementara
        'fileName' => $imageName, // Nama file di ImageKit
        'folder' => $folderPath, // Folder berdasarkan kategori
        'tags' => ['webp', 'lossless'], // Menambahkan tag untuk pencarian nanti
        'useUniqueFileName' => true, // Nama file unik
    ]);

    // Hapus file sementara setelah diupload
    unlink($tmpFilePath);

    // **Cek apakah upload berhasil**
    if (isset($upload->result) && $upload->result->url) {
        $parsed_url = parse_url($upload->result->url, PHP_URL_PATH);
        $file_name = basename($parsed_url); // Ambil hanya nama file
        return [
            'file_id' => $upload->result->fileId,
            'file_name' => $file_name,
            'file_path' => $folderPath,
        ];
    } else {
        return ['error' => 'Gagal mengupload ke ImageKit. ' . json_encode($upload)];
    }
}

// Function to handle image upload
function handleImageUpload($images, $folder, $nama_catering, $category_catering)
{
    $uploaded_images = [];

    // **Cek apakah ada gambar yang diunggah**
    if (isset($images) && !empty($images['name'][0])) {
        $totalFiles = count($images['name']);

        // **Loop untuk upload tiap gambar**
        for ($i = 0; $i < $totalFiles; $i++) {
            $file = [
                'name' => $images['name'][$i],
                'tmp_name' => $images['tmp_name'][$i],
            ];
            $result = uploadToImageKit($file, $folder, $nama_catering, $category_catering);
            if (isset($result['error'])) {
                return ['error' => $result['error']];
            }
            $uploaded_images[] = $result;
        }
    }

    return $uploaded_images;
}
$slug = str_replace(' ', '-', $nama_catering);
$slug = preg_replace('/[^a-zA-Z0-9_-]/', '', $slug); // Sanitasi untuk slug yang valid

$sql_update_slug = "UPDATE {$category} SET slug_{$category} = ? WHERE id = ?";
$stmt_update_slug = $conn->prepare($sql_update_slug);
$stmt_update_slug->bind_param("si", $slug, $id);
$stmt_update_slug->execute();
// **Proses Upload Gambar**
$catering_images = handleImageUpload($_FILES['images_catering'] ?? [], 'catering', $nama_catering, $category_catering);
$menu_images = handleImageUpload($_FILES['images_menu'] ?? [], 'menu', $nama_catering, $category_catering);
$opsional_images = handleImageUpload($_FILES['images_opsional'] ?? [], 'opsional', $nama_catering, $category_catering);

if (isset($catering_images['error']) || isset($menu_images['error']) || isset($opsional_images['error'])) {
    $errors[] = $catering_images['error'] ?? $menu_images['error'] ?? $opsional_images['error'];
}

// If there are errors, stop execution
if (!empty($errors)) {
    echo json_encode(['status' => 'error', 'messages' => $errors]);
    exit;
}

// Update data in the catering table
try {
    $conn->begin_transaction();

    // Ambil nama kategori saat ini dari database
    $sql_get_category_name = "SELECT nama_{$category}, category_{$category} FROM {$category} WHERE id = ?";
    $stmt_get_category_name = $conn->prepare($sql_get_category_name);
    $stmt_get_category_name->bind_param("i", $id);
    $stmt_get_category_name->execute();
    $result = $stmt_get_category_name->get_result();
    $result_category_name = $result->fetch_assoc();
    if (!$result_category_name) {
        throw new Exception("Data kategori tidak ditemukan.");
    }
    $folderCategory = $category_catering;
    if ($result_category_name["nama_{$category}"] !== $nama_catering || $result_category_name["category_{$category}"] !== $category_catering) {
        $nama_catering = str_replace(' ', '-', $nama_catering);
        $nama_catering = str_replace(['(', ')'], '', $nama_catering);

        $folderCategory = str_replace(' ', '-', $folderCategory);
        $folderCategory = str_replace(['(', ')'], '', $folderCategory);

        $new_folder_path = "/catering/{$folderCategory}/{$nama_catering}/";
        // Ambil semua gambar terkait dari database
        $sql_get_images = "SELECT id, file_name, file_path FROM {$category}_images WHERE {$category}_id = ?";
        $stmt_get_images = $conn->prepare($sql_get_images);
        $stmt_get_images->bind_param("i", $id);
        $stmt_get_images->execute();
        $result_images = $stmt_get_images->get_result();

        $old_images = [];
        $old_folders = [];

        while ($row = $result_images->fetch_assoc()) {
            $old_path = $row['file_path'] . $row['file_name'];
            $new_path = $new_folder_path . $row['file_name'];
            $old_images[] = [
                'id' => $row['id'],
                'file_name' => $row['file_name'],
                'old_path' => $old_path,
                'new_path' => $new_path,
                'path' => $row['file_path']
            ];

            $old_folders[$row['file_path']] = true;
        }

        // Pindahkan file ke folder baru di ImageKit
        foreach ($old_images as $old_image) {
            try {
                $moveFile = $imageKit->move([
                    'sourceFilePath' => $old_image['old_path'],
                    'destinationPath' => $new_folder_path
                ]);

                if (!$moveFile || isset($moveFile->error)) {
                    throw new Exception("Gagal memindahkan file: " . json_encode($moveFile->error));
                }

                $listFiles = $imageKit->listFiles([
                    'path' => $old_image['path']
                ]);
                if (empty($listFiles->result)) {
                    $deleteFolder = $imageKit->deleteFolder($old_image['path']);
                }

                // Update path di database
                $sql_update_path = "UPDATE {$category}_images SET file_path = ? WHERE id = ?";
                $stmt_update_path = $conn->prepare($sql_update_path);
                $stmt_update_path->bind_param("si", $new_folder_path, $old_image['id']);
                $stmt_update_path->execute();
            } catch (Exception $e) {
                throw new Exception("Gagal memindahkan file di ImageKit: " . $e->getMessage());
            }
        }
    }



    $stmt = $conn->prepare("UPDATE catering SET nama_catering = ?, deskripsi = ?, harga = ?, category_catering = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $nama_cateringDB, $deskripsi, $harga, $category_catering, $_POST['id']);
    $stmt->execute();
    $catering_id = $_POST['id'];  // Ini adalah ID catering yang ada di POST

    // **Simpan informasi gambar ke tabel catering_images**
    $stmt = $conn->prepare("INSERT INTO catering_images (catering_id, image_type, fileid, file_name, file_path, file_type) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $catering_id, $image_type, $file_id, $file_name, $file_path, $file_type);

    // **Simpan gambar catering**
    foreach ($catering_images as $image) {
        $image_type = 'catering';
        $file_id = $image['file_id'];
        $file_name = $image['file_name'];
        $file_path = $image['file_path'];
        $file_type = 'webp';
        $stmt->execute();
    }

    // **Simpan gambar menu**
    foreach ($menu_images as $image) {
        $image_type = 'menu';
        $file_id = $image['file_id'];
        $file_name = $image['file_name'];
        $file_path = $image['file_path'];
        $file_type = 'webp';
        $stmt->execute();
    }

    // **Simpan gambar opsional**
    foreach ($opsional_images as $image) {
        $image_type = 'opsional';
        $file_id = $image['file_id'];
        $file_name = $image['file_name'];
        $file_path = $image['file_path'];
        $file_type = 'webp';
        $stmt->execute();
    }


    $conn->commit();
    echo "<script>alert('Data berhasil di perbaharui !'); window.location.href = '$redirect_page';</script>";
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => 'Failed to update data.', 'error' => $e->getMessage()]);
}
