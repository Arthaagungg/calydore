<?php
header("Content-Type: application/xml; charset=UTF-8");

require_once 'dasboard-admin/database/koneksi.php';

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" 
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 
                    http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';

echo "<url>";
echo "<loc>https://calydore.com</loc>";
echo "<lastmod>" . date('Y-m-d') . "</lastmod>";
echo "<changefreq>weekly</changefreq>";
echo "<priority>1.0</priority>";
echo "</url>";

// Tambahkan Halaman Kategori Secara Manual
$categories = [
    "https://calydore.com/page/hotel",
    "https://calydore.com/page/villa",
    "https://calydore.com/page/villa-kamar",
    "https://calydore.com/page/glamping",
    "https://calydore.com/page/outbound",
    "https://calydore.com/page/catering",
    "https://calydore.com/page/tentang-kami",
    "https://calydore.com/page/syarat-dan-ketentuan"
];

foreach ($categories as $category) {
    echo "<url>";
    echo "<loc>" . htmlspecialchars($category) . "</loc>";
    echo "<lastmod>" . date('Y-m-d') . "</lastmod>";
    echo "<changefreq>weekly</changefreq>";
    echo "<priority>0.9</priority>";
    echo "</url>";
}

// Fungsi untuk mengambil data kategori & menambahkan profilnya ke sitemap
function addToSitemap($conn, $table, $slug_column, $base_url)
{
    $query = "SELECT $slug_column FROM $table";
    $result = $conn->query($query);

    while ($row = $result->fetch_assoc()) {
        echo "<url>";
        echo "<loc>" . $base_url . htmlspecialchars($row[$slug_column]) . "</loc>";
        echo "<lastmod>" . date('Y-m-d') . "</lastmod>";
        echo "<changefreq>weekly</changefreq>";
        echo "<priority>0.8</priority>";
        echo "</url>";
    }
}

// Tambahkan setiap kategori ke sitemap
addToSitemap($conn, "villa", "slug_villa", "https://calydore.com/villa/");
addToSitemap($conn, "catering", "slug_catering", "https://calydore.com/catering/");
addToSitemap($conn, "hotel", "slug_hotel", "https://calydore.com/hotel/");
addToSitemap($conn, "glamping", "slug_glamping", "https://calydore.com/glamping/");
addToSitemap($conn, "villa_kamar", "slug_villa_kamar", "https://calydore.com/villa-kamar/");
addToSitemap($conn, "outbound", "slug_outbound", "https://calydore.com/outbound/");

// ✅ Tambahkan daftar kategori catering berdasarkan `category_catering`
addToSitemap($conn, "catering", "category_catering", "https://calydore.com/daftar-catering/");

// ✅ Tambahkan daftar kategori outbound berdasarkan `category_outbound`
addToSitemap($conn, "outbound", "category_outbound", "https://calydore.com/daftar-outbound/");

echo "</urlset>";
?>