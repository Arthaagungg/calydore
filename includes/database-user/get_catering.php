<?php
session_start();
include '../../dasboard-admin/database/koneksi.php';


// Query untuk mengambil 3 data catering secara acak
$query = "
SELECT v.id, v.nama_catering, v.deskripsi, v.harga, v.slug_catering, 
       GROUP_CONCAT(CONCAT(vi.file_path, vi.file_name) SEPARATOR ',') AS images
FROM (
    SELECT id, nama_catering, deskripsi, harga, slug_catering
    FROM catering
    ORDER BY RAND() 
    LIMIT 3
) AS v
LEFT JOIN catering_images vi 
    ON v.id = vi.catering_id
    AND vi.image_type = 'catering'  -- Filter image_type di sini
GROUP BY v.id, v.nama_catering, v.deskripsi, v.harga, v.slug_catering;
";



$result = $conn->query($query);

$cateringData = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Memecah nama file gambar yang digabungkan oleh GROUP_CONCAT
        $imagekit_base_url = "https://ik.imagekit.io/bkx7wk6gv";

        $images = !empty($row['images']) ? explode(',', $row['images']) : [];
        $imageUrls = [];
        foreach ($images as $image) {
            $imageUrl = $imagekit_base_url . $image;
            $imageUrl = stripslashes($imageUrl);

            $imageUrls[] = $imageUrl;
        }
        // Menambahkan data catering ke array $cateringData
        $cateringData[] = [
            'id' => $row['id'],
            'nama' => $row['nama_catering'],
            'deskripsi' => $row['deskripsi'],
            'harga' => $row['harga'],
            'slug' => $row['slug_catering'],
            'images' => $imageUrls
        ];
    }
}

$conn->close();
echo json_encode($cateringData); // Mengembalikan sebagai array indexed
