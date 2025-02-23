<?php
include 'session.php';
require_once '../database/config-imagekit.php'; // Panggil konfigurasi ImageKit

// Validasi ID villa
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    die("ID villa tidak valid.");
}
$id = intval($_POST['id']);

// Ambil data dari form
$categorys = $_POST['categorys'];
$features = $_POST['features'] ?? [];
$category = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['category']); // Sanitasi nama kategori
$images = $_FILES['images'] ?? null;

$allowed_categories = ['villa', 'villa_kamar', 'hotel', 'glamping', 'outbound'];
if (!in_array($category, $allowed_categories)) {
    die("Kategori tidak valid.");
}

$imagekit_base_url = "https://ik.imagekit.io/bkx7wk6gv"; // ImageKit base URL

// Fungsi untuk menangani upload gambar ke ImageKit
function handleImageUpload($images, $category, $unique_name, $imageKit)
{
    $uploaded_images = [];
    if (isset($images) && !empty($images['name'][0])) {
        $totalFiles = count($images['name']);
        for ($i = 0; $i < $totalFiles; $i++) {
            $ext = strtolower(pathinfo($images['name'][$i], PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'raw'];

            // Validasi ekstensi file
            if (!in_array($ext, $allowed_ext)) {
                return ["error" => "Format gambar tidak valid!"];
            }

            // Cek MIME Type
            $mime_type = mime_content_type($images['tmp_name'][$i]);
            if (strpos($mime_type, 'image/') !== 0 && $mime_type !== 'application/octet-stream') {
                return ["error" => "File yang di-upload bukan gambar atau RAW!"];
            }

            // Membuat nama unik untuk file
            $imageName = uniqid($category . '_') . '.webp';

            // Folder tujuan di ImageKit, menggunakan kategori
            $safe_category = preg_replace('/[^a-zA-Z0-9_]/', '', $category);
            $unique_name = preg_replace('/[^a-zA-Z0-9_ -]/', '', $unique_name); // Sanitasi nama unik

            // Membuat folder path yang valid
            $folderPath = '/' . $safe_category . '/' . $unique_name . '/';

            // Membuka gambar berdasarkan ekstensi
            switch ($ext) {
                case 'jpg':
                case 'jpeg':
                    $imagePath = imagecreatefromjpeg($images['tmp_name'][$i]);
                    break;
                case 'png':
                    $imagePath = imagecreatefrompng($images['tmp_name'][$i]);
                    break;
                case 'gif':
                    $imagePath = imagecreatefromgif($images['tmp_name'][$i]);
                    break;
                default:
                    return ["error" => "Format gambar tidak didukung!"];
            }

            // Menyimpan gambar ke file sementara dalam format WebP
            $tmpFilePath = tempnam(sys_get_temp_dir(), 'imagekit_');
            imagewebp($imagePath, $tmpFilePath, 75); // Mengkonversi gambar ke WebP dengan kualitas lossless
            imagedestroy($imagePath); // Membersihkan memori

            try {
                // Menggunakan ImageKit untuk mengupload gambar
                $upload = $imageKit->uploadFile([
                    'file' => fopen($tmpFilePath, 'r'),
                    'fileName' => $imageName,
                    'folder' => $folderPath,
                    'tags' => ['webp', 'lossless'],
                    'useUniqueFileName' => true,
                ]);

                // Mengecek apakah hasil upload berhasil dan mendapatkan URL file
                if (isset($upload->result) && $upload->result->url) {
                    $parsed_url = parse_url($upload->result->url, PHP_URL_PATH);
                    $file_name = basename($parsed_url); // Ambil hanya nama file
                    $fileId = $upload->result->fileId;
                    $uploaded_images[] = ['file_name' => $file_name, 'file_path' => $folderPath, 'file_id' => $fileId];
                } else {
                    return ["error" => "Gagal mengupload gambar ke ImageKit. Error: " . (isset($upload->error) ? $upload->error->message : 'Tidak ada pesan error.')];
                }
            } catch (Exception $e) {
                return ["error" => "Error: " . $e->getMessage()];
            } finally {
                unlink($tmpFilePath); // Menghapus file sementara setelah selesai
            }
        }
    }
    return $uploaded_images;
}



try {
    $conn->begin_transaction();

    // Ambil nama kategori saat ini dari database
    $sql_get_category_name = "SELECT nama_{$category} FROM {$category} WHERE id = ?";
    $stmt_get_category_name = $conn->prepare($sql_get_category_name);
    $stmt_get_category_name->bind_param("i", $id);
    $stmt_get_category_name->execute();
    $result = $stmt_get_category_name->get_result();
    $result_category_name = $result->fetch_assoc();

    // Pastikan hasil tidak kosong
    if (!$result_category_name) {
        throw new Exception("Data kategori tidak ditemukan.");
    }

    $category_name_key = "nama_" . $category;
    $new_category_name = isset($categorys[$category_name_key]) ? preg_replace('/[^a-zA-Z0-9_ -]/', '', $categorys[$category_name_key]) : '';

    if ($result_category_name["nama_{$category}"] !== $categorys[$category_name_key]) {

        $new_category_name = str_replace(' ', '-', $new_category_name);
        $new_category_name = str_replace(['(', ')'], '', $new_category_name);
        $new_folder_path = "/{$category}/{$new_category_name}/";

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

    // Update data utama kategori
    foreach ($categorys as $column_name => $column_value) {
        if ($column_name !== 'id' && preg_match('/^[a-zA-Z0-9_]+$/', $column_name)) {
            $sql_update_category = "UPDATE {$category} SET {$column_name} = ? WHERE id = ?";
            $stmt_category = $conn->prepare($sql_update_category);
            $stmt_category->bind_param("si", $column_value, $id);
            $stmt_category->execute();
        }
    }

    foreach ($features as $feature_name => $feature_value) {
        if (empty($feature_value)) {
            continue;
        }

        $lowercase_feature_name = strtolower($feature_name);

        // Cek apakah feature_name sudah ada di salah satu tabel fitur dan ambil icons_link jika ada
        $tables_to_check = [
            'villa_features',
            'villa_kamar_features',
            'hotel_features',
            'glamping_features',
            'outbound_features'
        ];
        $icons_link = null; // Default NULL jika tidak ditemukan

        foreach ($tables_to_check as $table) {
            $sql_check = "SELECT icons_link FROM `$table` WHERE feature_name = ? LIMIT 1";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("s", $lowercase_feature_name);
            $stmt_check->execute();
            $result = $stmt_check->get_result();

            if ($row = $result->fetch_assoc()) {
                $icons_link = $row['icons_link']; // Bisa NULL atau string
                break;
            }
        }

        // Gunakan NULL atau nilai yang diambil dari database
        $sql_update_feature = "
            INSERT INTO {$category}_features ({$category}_id, feature_name, feature_value, icons_link) 
            VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE 
            feature_value = VALUES(feature_value), 
            icons_link = VALUES(icons_link)";

        $stmt_feature = $conn->prepare($sql_update_feature);
        $stmt_feature->bind_param(
            "isss",
            $id,
            $lowercase_feature_name,
            $feature_value,
            $icons_link
        );
        $stmt_feature->execute();
    }

    $slug = str_replace(' ', '-', $new_category_name);
    $slug = preg_replace('/[^a-zA-Z0-9_-]/', '', $slug); // Sanitasi untuk slug yang valid

    $sql_update_slug = "UPDATE {$category} SET slug_{$category} = ? WHERE id = ?";
    $stmt_update_slug = $conn->prepare($sql_update_slug);
    $stmt_update_slug->bind_param("si", $slug, $id);
    $stmt_update_slug->execute();


    // Proses upload gambar
    $category_name_key = "nama_" . $category;
    $unique_name = isset($categorys[$category_name_key]) ? preg_replace('/[^a-zA-Z0-9_ -]/', '', $categorys[$category_name_key]) : '';
    $unique_name = str_replace(' ', '-', $unique_name);
    $unique_name = str_replace(['(', ')'], '', $unique_name);
    $uploaded_images = handleImageUpload($_FILES['images'], $category, $unique_name, $imageKit);
    if (isset($uploaded_images['error'])) {
        throw new Exception($uploaded_images['error']);
    }

    $stmt_image = $conn->prepare("INSERT INTO {$category}_images ({$category}_id, file_name, file_path, file_type, fileid) VALUES (?, ?, ?, ?, ?)");

    // Cek jika query prepare gagal untuk gambar
    if (!$stmt_image) {
        throw new Exception("Gagal menyiapkan query untuk menyimpan gambar: " . $conn->error);
    }

    foreach ($uploaded_images as $image) {
        $file_name = $image['file_name']; // Nama file gambar
        $file_path = $image['file_path']; // Path gambar tanpa nama file
        $file_type = 'webp';
        $file_id = $image['file_id'];
        $stmt_image->bind_param("issss", $id, $file_name, $file_path, $file_type, $file_id);
        $stmt_image->execute();
    }

    $conn->commit();
    echo "<script>alert('Data berhasil diperbarui!'); window.location.href = '$redirect_page';</script>";
    exit();
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
    echo "<script>alert('Gagal memperbarui data: " . addslashes($e->getMessage()) . "');</script>";
    exit();
}
