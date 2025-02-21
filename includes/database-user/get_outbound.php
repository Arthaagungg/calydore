<?php
session_start();
include '../../dasboard-admin/database/koneksi.php';


// Query untuk mengambil 3 data outbound secara acak
$query = "
SELECT v.id, v.nama_outbound, v.deskripsi, v.harga, v.slug_outbound, 
       GROUP_CONCAT(CONCAT(vi.file_path, vi.file_name) SEPARATOR ',') AS images
FROM (
    SELECT id, nama_outbound, deskripsi, harga, slug_outbound
    FROM outbound
    ORDER BY RAND() 
    LIMIT 3
) AS v
LEFT JOIN outbound_images vi ON v.id = vi.outbound_id
GROUP BY v.id, v.nama_outbound, v.deskripsi, v.harga, v.slug_outbound;
";


$result = $conn->query($query);

$outboundData = [];
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
        // Menambahkan data outbound ke array $outboundData
        $outboundData[] = [
            'id' => $row['id'],
            'nama' => $row['nama_outbound'],
            'deskripsi' => $row['deskripsi'],
            'harga' => $row['harga'],
            'slug' => $row['slug_outbound'],
            'images' => $imageUrls
        ];
    }
}

$conn->close();
echo json_encode($outboundData); // Mengembalikan sebagai array indexed
