<?php
session_start();
define('SECURE_ACCESS', true);


include_once '../dasboard-admin/database/koneksi.php';
$logo = "SELECT * FROM assets_images WHERE image_type = 'logo'";
$result_logo = $conn->query($logo);
// Path dasar gambar
$imagekit_base_url = "https://ik.imagekit.io/bkx7wk6gv";
$logoImages = [];
if ($result_logo->num_rows > 0) {
    while ($imageRow = $result_logo->fetch_assoc()) {
        $filePath = rtrim($imageRow['file_path'], '/');
        $fileName = ltrim($imageRow['file_name'], '/');


        $logoImages[] = $imagekit_base_url . $filePath . '/' . $fileName;
    }
}

$description = "Pelajari lebih lanjut tentang kami, penyedia layanan penyewaan villa, hotel, glamping, outbound, dan catering terbaik di Puncak Cisarua, Bogor. Kami berkomitmen untuk memberikan pengalaman liburan yang nyaman dan tak terlupakan dengan layanan profesional dan fasilitas unggulan.";
$pageTitle = "Calydore | Tentang Kami";
$pageType = "";
$pageURL = BASE_URL .
    '/page/tentang-kami';
include '../includes/header.php';

if (isset($_SESSION['error'])) {
    echo '<div class="error-message">' . htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') . '</div>';
    unset($_SESSION['error']);
}
?>
<link rel="stylesheet" href="../assets/style/tentang-kami.css">

<div class="main-content" id="content-calydore">
    <img src="<?php echo htmlspecialchars($logoImages[0]); ?>" alt="Villa" class="main-image">
    <div class="text-content">
        <h1>calydore</h1>
        <p>calydore adalah platform inovatif yang menyediakan layanan pemesanan berbagai jenis akomodasi dan
            aktivitas, mulai dari Hotel, Villa, Villa Kamar, Glamping, Outbound, hingga Catering, semuanya dalam
            satu sistem yang aman, mudah, dan terpercaya. Kami hadir bukan hanya sebagai penghubung, tetapi sebagai
            wadah utama yang memastikan setiap layanan dapat diakses dengan mudah oleh pelanggan.</p>

    </div>
</div>

<div class="faq-container">
    <div class="faq-item">
        <div class="faq-question">Misi Kami ?</div>
        <div class="faq-answer">Kami berkomitmen untuk menghadirkan solusi terbaik dalam industri hospitality dan
            rekreasi dengan menyediakan layanan yang praktis, transparan, dan fleksibel. Dengan calydore, pelanggan
            dapat memilih berbagai jenis layanan sesuai dengan kebutuhan mereka.</div>
    </div>
    <div class="faq-item">
        <div class="faq-question">Layanan Kami ?</div>
        <div class="faq-answer">

            <div class="container produk-tk text-center">
                <div class="row gy-3">
                    <div class="col-md-4">
                        <a href="villa.php" class="produk-link">
                            <div class="produk-item">
                                <span class="produk-nama">Villa</span>
                                <i class="fas fa-home produk-icon"></i>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="hotel.php" class="produk-link">
                            <div class="produk-item">
                                <span class="produk-nama">Hotel</span>
                                <i class="fas fa-building produk-icon"></i>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="villa-kamar.php" class="produk-link">
                            <div class="produk-item">
                                <span class="produk-nama">Villa Kamar</span>
                                <i class="fas fa-bed produk-icon"></i>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="glamping.php" class="produk-link">
                            <div class="produk-item">
                                <span class="produk-nama">Glamping</span>
                                <i class="fas fa-coffee produk-icon"></i>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="outbound.php" class="produk-link">
                            <div class="produk-item">
                                <span class="produk-nama">Outbound</span>
                                <i class="fas fa-users produk-icon"></i>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="catering.php" class="produk-link">
                            <div class="produk-item">
                                <span class="produk-nama">Catering</span>
                                <i class="fas fa-cutlery produk-icon"></i>
                            </div>
                        </a>
                    </div>
                </div>
            </div>


        </div>
    </div>
    <div class="faq-item">
        <div class="faq-question">Cara Booking di calydore ?</div>
        <div class="faq-answer">
            Kami percaya bahwa kemudahan adalah kunci dari layanan terbaik. Untuk itu, kami menghadirkan sistem
            pemesanan yang mudah, cepat, dan efisien. untuk mendapatkan rekomendasi terbaik dan melakukan reservasi
            dalam hitungan menit.
            <br> &#8226; Konsultasi & Pemilihan Paket
            <br>Hubungi tim kami melalui WhatsApp untuk mendapatkan rekomendasi terbaik sesuai kebutuhan Anda.
            <br> &#8226; Konfirmasi Ketersediaan
            <br>Kami akan memeriksa ketersediaan layanan atau akomodasi yang Anda pilih dan memberikan detail
            lengkap.
            <br> &#8226; Pembayaran & Konfirmasi Reservasi
            <br>Lakukan pembayaran melalui metode yang tersedia, lalu kirimkan bukti pembayaran untuk memverifikasi
            reservasi Anda.
            <br> &#8226; Nikmati Pengalaman Tak Terlupakan
            <br>Setelah reservasi dikonfirmasi, Anda hanya perlu bersiap untuk pengalaman menginap atau berpetualang
            bersama calydore!
            <br>📍 Lokasi : Cisarua Puncak Bogor
            <br>📞 Hubungi Kami di WhatsApp: +62 877-7891-1805
            <br>🌐 Website Resmi: calydore.com
            <br>📩 Email: calydoreofficial@gmail.com
        </div>
    </div>
    <div class="faq-item">
        <div class="faq-question">Kenapa Memilih calydore?</div>
        <div class="faq-answer">
            Kami memahami bahwa setiap pengalaman liburan dan acara spesial harus sempurna. Inilah alasan mengapa
            calydore adalah pilihan terbaik untuk Anda:

            <br><i class="fas fa-check-square"></i> Paket Lengkap & Fleksibel
            <br> Sesuaikan pilihan layanan dengan kebutuhan Anda, mulai dari akomodasi hingga aktivitas outdoor.
            <br><i class="fas fa-check-square"></i> Kualitas & Kenyamanan Terjamin
            <br> Kami hanya menyediakan pilihan terbaik dengan standar layanan premium.
            <br><i class="fas fa-check-square"></i> Dukungan Customer Service 24/7
            <br> Tim kami siap membantu kapan pun Anda membutuhkan informasi atau bantuan.
            <br><i class="fas fa-check-square"></i> Harga Kompetitif & Penawaran Eksklusif
            <br> Dapatkan harga terbaik dengan layanan maksimal tanpa biaya tersembunyi.
            <br><i class="fas fa-check-square"></i> Proses Pemesanan Mudah & Cepat
            <br> Booking hanya dalam hitungan menit melalui WhatsApp.


        </div>
    </div>
</div>

<script>
    document.querySelectorAll(".faq-question").forEach(item => {
        item.addEventListener("click", () => {
            let parent = item.parentElement;
            let allItems = document.querySelectorAll(".faq-item");

            allItems.forEach(faq => {
                if (faq !== parent) {
                    faq.classList.remove("active");
                }
            });

            parent.classList.toggle("active");
        });
    });
</script>


<?php
$ChatWa = "Permisi Kak! Mau tanya tentang Calydore...";
$encodedText = urlencode($ChatWa);
$phoneNumber = "6287778911805";
$waLink = "https://api.whatsapp.com/send?phone={$phoneNumber}&text={$encodedText}";

include_once '../includes/wa-kanan.php';
include '../includes/footer.php';
?>