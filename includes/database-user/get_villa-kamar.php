<?php
session_start();
include '../../dasboard-admin/database/koneksi.php';


// Query untuk mengambil 3 data villa_kamar secara acak
$query = "
SELECT v.id, v.nama_villa_kamar, v.deskripsi, v.harga_weekday, v.harga_weekend, 
       v.kapasitas, v.lokasi, v.rating, v.slug_villa_kamar, 
       GROUP_CONCAT(CONCAT(vi.file_path, vi.file_name) SEPARATOR ',') AS images
FROM (
    SELECT id, nama_villa_kamar, deskripsi, harga_weekday, harga_weekend, kapasitas, lokasi, rating, slug_villa_kamar
    FROM villa_kamar
    ORDER BY RAND() 
    LIMIT 3
) AS v
LEFT JOIN villa_kamar_images vi ON v.id = vi.villa_kamar_id
GROUP BY v.id, v.nama_villa_kamar, v.deskripsi, v.harga_weekday, v.harga_weekend, 
         v.kapasitas, v.lokasi, v.rating, v.slug_villa_kamar;
";


$result = $conn->query($query);

$villa_kamarData = [];
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
        // Menambahkan data villa_kamar ke array $villa_kamarData
        $villa_kamarData[] = [
            'id' => $row['id'],
            'nama' => $row['nama_villa_kamar'],
            'deskripsi' => $row['deskripsi'],
            'harga_weekday' => $row['harga_weekday'],
            'harga_weekend' => $row['harga_weekend'],
            'kapasitas' => $row['kapasitas'],
            'lokasi' => $row['lokasi'],
            'rating' => $row['rating'],
            'slug' => $row['slug_villa_kamar'],
            'images' => $imageUrls
        ];
    }
}

$conn->close();
echo json_encode($villa_kamarData); // Mengembalikan sebagai array indexed
