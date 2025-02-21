<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include 'session.php';
require_once '../database/config-imagekit.php'; // Panggil konfigurasi ImageKit
// Fungsi untuk mengelola upload gambar ke ImageKit dan konversi ke WebP
function handleImageUpload($images, $category, $unique_name, $imageKit)
{
    global $imageKit; // Menggunakan instance ImageKit global
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
            $safe_category = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['category']);
            $imageName = uniqid($safe_category . '_') . '.webp';

            // Folder tujuan di ImageKit, menggunakan kategori
            // Sanitasi nama folder untuk menghindari spasi dan karakter khusus
            $safe_category = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['category']); // Pastikan kategori hanya berisi karakter yang valid
            $unique_name = preg_replace('/[^a-zA-Z0-9_ -]/', '', $unique_name); // Pastikan nama unik tidak mengandung karakter yang tidak valid

            // Ganti spasi dengan tanda hubung (-) dan hilangkan tanda kurung
            $unique_name = str_replace(' ', '-', $unique_name);
            $unique_name = str_replace(['(', ')'], '', $unique_name);

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
                    'file' => fopen($tmpFilePath, 'r'), // Menggunakan file sementara
                    'fileName' => $imageName, // Nama file di ImageKit
                    'folder' => $folderPath, // Folder berdasarkan kategori
                    'tags' => ['webp', 'lossless'], // Menambahkan tag untuk pencarian nanti
                    'useUniqueFileName' => true, // Nama file unik

                ]);

                // Mengecek apakah hasil upload berhasil dan mendapatkan URL file
                if (isset($upload->result) && $upload->result->url) {
                    $fileId = $upload->result->fileId; // Ambil fileId dari respons ImageKit
                    $parsed_url = parse_url($upload->result->url, PHP_URL_PATH);
                    $file_name = basename($parsed_url); // Ambil hanya nama file
                    $uploaded_images[] = ['file_name' => $file_name, 'file_path' => $folderPath, 'fileid' => $fileId];
                } else {
                    // Menangani error jika hasil upload tidak berhasil
                    return ["error" => "Gagal mengupload gambar ke ImageKit. Error: " . (isset($upload->error) ? $upload->error->message : 'Tidak ada pesan error.')];
                }
            } catch (Exception $e) {
                return ["error" => "Error: " . $e->getMessage()];
            } finally {
                // Menghapus file sementara setelah selesai
                unlink($tmpFilePath);
            }
        }
    }
    return $uploaded_images;
}
function createSlug($string)
{
    $slug = strtolower(trim($string));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return $slug;
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $category = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['category']);
    $category_data = $_POST['categorys'] ?? [];
    $features = $_POST['features'] ?? [];

    $allowed_categories = ['villa', 'villa_kamar', 'hotel', 'glamping', 'outbound', 'catering'];
    if (!in_array($category, $allowed_categories)) {
        die("Kategori tidak valid.");
    }

    $name_column = "nama_{$category}";
    $original_name = $category_data[$name_column];
    $unique_name = $original_name;

    $slug_column = "slug_{$category}";
    $slug = createSlug($original_name); // Membuat slug secara otomatis

    $count = 1;

    do {
        $sql_check = "SELECT COUNT(*) AS count FROM `{$category}` WHERE `{$name_column}` = ?";
        $stmt_check = $conn->prepare($sql_check);

        // Cek jika query prepare gagal
        if (!$stmt_check) {
            die("Gagal menyiapkan query untuk pengecekan nama: " . $conn->error);
        }

        $stmt_check->bind_param('s', $unique_name);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $row = $result_check->fetch_assoc();

        if ($row['count'] > 0) {
            $count++;
            $unique_name = "{$original_name} {$count}";
        } else {
            break;
        }
    } while (true);
    $countSlug = 1;
    do {
        $sql_check = "SELECT COUNT(*) AS count FROM `{$category}` WHERE `{$slug_column}` = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param('s', $slug);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $row = $result_check->fetch_assoc();

        if ($row['count'] > 0) {
            $countSlug++;
            $slug = "{$slug}-{$count}"; // Jika slug sudah ada, tambahkan angka
        } else {
            break;
        }
    } while (true);
    $category_data[$slug_column] = $slug;

    $category_data[$name_column] = $unique_name;

    try {
        $columns = array_keys($category_data);
        $placeholders = implode(',', array_fill(0, count($columns), '?'));
        $sql_category = "INSERT INTO `{$category}` (" . implode(',', $columns) . ") VALUES ($placeholders)";
        $columns[] = $slug; // Menambahkan slug ke dalam parameter yang akan di-bind

        $stmt_category = $conn->prepare($sql_category);
        // Contoh jika ada beberapa kolom yang bertipe selain string
        // Cek hasil array_keys untuk memastikan semua kolom sesuai
        $types = ''; // Inisialisasi tipe data
        foreach ($category_data as $key => $value) {
            // Tentukan tipe data berdasarkan nilai dari $value
            if (is_int($value)) {
                $types .= 'i'; // Jika integer
            } elseif (is_double($value)) {
                $types .= 'd'; // Jika double
            } else {
                $types .= 's'; // Jika string
            }
        }


        // Menyusun query dengan benar

        // Cek jika query prepare gagal
        if (!$stmt_category) {
            throw new Exception("Gagal menyiapkan query untuk menyimpan data kategori: " . $conn->error);
        }
        $stmt_category->bind_param($types, ...array_values($category_data)); // Bind parameter sesuai dengan tipe

        if (!$stmt_category->execute()) {
            throw new Exception("Gagal menyimpan data kategori: " . $stmt_category->error);
        }

        $category_id = $conn->insert_id;

        if (!empty($features)) {
            $feature_values = [];
            foreach ($features as $feature_name => $feature_value) {
                if (!empty($feature_value)) {
                    $lowercase_feature_name = strtolower($feature_name);
                    $escaped_feature_name = $conn->real_escape_string($lowercase_feature_name);
                    $escaped_feature_value = $conn->real_escape_string($feature_value);

                    // Cek apakah feature_name sudah ada di salah satu tabel fitur
                    $tables_to_check = [
                        'villa_features',
                        'villa_kamar_features',
                        'hotel_features',
                        'glamping_features',
                        'outbound_features'
                    ];
                    $icons_link = null; // Default NULL jika tidak ditemukan

                    foreach ($tables_to_check as $table) {
                        $sql_check = "SELECT icons_link FROM `$table` WHERE feature_name = '$escaped_feature_name' LIMIT 1";
                        $result = $conn->query($sql_check);

                        if ($result && $row = $result->fetch_assoc()) {
                            $icons_link = $row['icons_link']; // Bisa NULL atau string
                            break;
                        }
                    }

                    // Pastikan icons_link selalu dalam format string atau NULL untuk SQL
                    $icons_link = isset($icons_link) ? "'" . $conn->real_escape_string($icons_link) . "'" : "NULL";

                    // Tambahkan data ke array untuk diinsert
                    $feature_values[] = "('$category_id', '$escaped_feature_name', '$escaped_feature_value', $icons_link)";
                }
            }

            // Jika ada fitur yang valid untuk disimpan
            if (!empty($feature_values)) {
                $features_table = "{$category}_features";
                $sql_features = "INSERT INTO `{$features_table}` ({$category}_id, feature_name, feature_value, icons_link) VALUES " . implode(',', $feature_values);

                if (!$conn->query($sql_features)) {
                    throw new Exception("Gagal menyimpan fitur: " . $conn->error);
                }
            }
        }


        // Proses upload dan konversi gambar ke ImageKit
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
            $file_name = $image['file_name']; // Simpan hanya nama file
            $file_path = $image['file_path']; // Simpan hanya path tanpa file name
            $file_type = 'webp';
            $file_id = $image['fileid'];

            $stmt_image->bind_param("issss", $category_id, $file_name, $file_path, $file_type, $file_id);
            $stmt_image->execute();
        }


        echo "<script>alert('Berhasiil mengupload data!'); window.location.href = '$redirect_page';</script>";
        exit();
    } catch (Exception $e) {
        echo "<script>alert('Error: " . htmlspecialchars($e->getMessage()) . "'); window.history.back();</script>";
    } finally {
        if (isset($stmt_category)) {
            $stmt_category->close();
        }
        if (isset($stmt_image)) {
            $stmt_image->close();
        }
        $conn->close();
    }
}
