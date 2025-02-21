<?php
session_start();
include '../../dasboard-admin/database/koneksi.php';


// Query untuk mengambil 3 data hotel secara acak
$query = "
SELECT v.id, v.nama_hotel, v.deskripsi, v.harga_weekday, v.harga_weekend, 
       v.kapasitas, v.lokasi, v.rating, v.slug_hotel, 
       GROUP_CONCAT(CONCAT(vi.file_path, vi.file_name) SEPARATOR ',') AS images
FROM (
    SELECT id, nama_hotel, deskripsi, harga_weekday, harga_weekend, kapasitas, lokasi, rating, slug_hotel
    FROM hotel
    ORDER BY RAND() 
    LIMIT 3
) AS v
LEFT JOIN hotel_images vi ON v.id = vi.hotel_id
GROUP BY v.id, v.nama_hotel, v.deskripsi, v.harga_weekday, v.harga_weekend, 
         v.kapasitas, v.lokasi, v.rating, v.slug_hotel;
";


$result = $conn->query($query);

$hotelData = [];
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
        // Menambahkan data hotel ke array $hotelData
        $hotelData[] = [
            'id' => $row['id'],
            'nama' => $row['nama_hotel'],
            'deskripsi' => $row['deskripsi'],
            'harga_weekday' => $row['harga_weekday'],
            'harga_weekend' => $row['harga_weekend'],
            'kapasitas' => $row['kapasitas'],
            'lokasi' => $row['lokasi'],
            'rating' => $row['rating'],
            'slug' => $row['slug_hotel'],
            'images' => $imageUrls
        ];
    }
}

$conn->close();
echo json_encode($hotelData); // Mengembalikan sebagai array indexed
