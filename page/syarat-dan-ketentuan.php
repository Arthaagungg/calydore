<?php
session_start();
define('SECURE_ACCESS', true);


include_once '../dasboard-admin/database/koneksi.php';
$logo = "SELECT * FROM assets_images WHERE image_type = 'logo'";
$result_logo = $conn->query($logo);
$imagekit_base_url = "https://ik.imagekit.io/bkx7wk6gv";
$logoImages = [];
if ($result_logo->num_rows > 0) {
    while ($imageRow = $result_logo->fetch_assoc()) {
        $filePath = rtrim($imageRow['file_path'], '/');
        $fileName = ltrim($imageRow['file_name'], '/');


        $logoImages[] = $imagekit_base_url . $filePath . '/' . $fileName;
    }
}
$pageTitle = "Calydore | Syarat Dan Ketentuan";
$description = "Harap baca syarat dan ketentuan penggunaan layanan kami di Puncak Cisarua, Bogor. Temukan informasi penting mengenai kebijakan, prosedur, dan aturan yang berlaku untuk penyewaan villa, hotel, glamping, outbound, dan catering. Pastikan Anda memahami ketentuan kami sebelum melakukan pemesanan.";
$pageType = "";
$pageURL = BASE_URL .
    '/page/syarat-dan-ketentuan';
include '../includes/header.php';
?>



<?php
if (isset($_SESSION['error'])) {
    echo '<div class="error-message">' . htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') . '</div>';
    unset($_SESSION['error']);
}
?>

<div class="syarat-container" id="syarat-container">
    <div class="text-content-syarat">

        <h2>Syarat dan Ketentuan calydore</h2><br>
        <p>Selamat datang di calydore! Terima kasih telah memilih layanan kami. Dengan mengakses dan menggunakan
            platform kami, Anda dianggap telah membaca, memahami, dan menyetujui syarat & ketentuan yang berlaku.
        </p><br>
        <p>Syarat & ketentuan ini mengatur hak dan kewajiban antara pengguna dan calydore dalam pemesanan layanan,
            pembayaran, perubahan jadwal, pembatalan, serta ketentuan lainnya yang berkaitan dengan layanan yang
            tersedia di platform kami.</p><br>
        <p>Kami menyarankan Anda untuk membaca dengan saksama seluruh ketentuan ini sebelum melakukan pemesanan.
            Jika Anda tidak menyetujui salah satu bagian dari syarat & ketentuan ini, kami sarankan untuk tidak
            melanjutkan penggunaan layanan kami.</p><br>
        <p>Jika Anda memiliki pertanyaan, komentar, keluhan, atau klaim sehubungan dengan platform kami atau
            ketentuan ini, Anda dapat menghubungi
            <a href="https://wa.me/628521234567" target="_blank">Layanan Pelanggan.</a>
        </p>

        <p class="syarat">Syarat & Ketentuan Penggunaan Layanan</p>

        <h5>1. Ketentuan Umum</h5>
        <ul>
            <li>Dengan menggunakan layanan kami, Anda setuju untuk terikat dengan syarat & ketentuan ini.</li>
            <li>Layanan yang tersedia mencakup penyewaan Villa, Kamar Villa/Home Stay, Glamping/Camping, Outbound,
                Catering, dan layanan lainnya yang akan berkembang di masa depan.</li>
            <li>Semua transaksi, termasuk pembayaran dan booking, dilakukan melalui platform kami.</li>
            <li>Kami berhak mengubah syarat & ketentuan ini sewaktu-waktu tanpa pemberitahuan sebelumnya.</li>
        </ul>

        <h5>2. Pemesanan & Pembayaran
        </h5>
        <ul>
            <li>Pemesanan dilakukan melalui website atau admin resmi kami.
            </li>
            <li>Pembayaran dilakukan via transfer bank atau metode lain yang tersedia.
            </li>
            <li>Booking dianggap sah setelah pembayaran diterima.
            </li>
            <li>Pelunasan wajib dilakukan H-1 sebelum check-in atau sebelum kegiatan dimulai.
            </li>
            <li>Semua pembayaran bersifat non-refundable, kecuali ada kebijakan khusus atau kesepakatan tertulis.
            </li>
        </ul>

        <h5>3. Perubahan & Pembatalan
        </h5>
        <ul>
            <li>Perubahan jadwal dapat dilakukan dengan ketentuan berikut:
            </li>
            <li>Maksimal 10 hari sebelum tanggal booking jika ingin dimajukan.
            </li>
            <li>Maksimal 7 hari sebelum tanggal booking jika ingin dimundurkan.
            </li>
            <li>Semua perubahan tergantung ketersediaan dan persetujuan pihak penyedia layanan.
            </li>
            <li>Pembatalan sepihak dari pengguna tidak mendapatkan refund.
            </li>
            <li>Jika terjadi force majeure (bencana alam, pandemi, atau kondisi luar biasa lainnya), booking dapat
                dijadwalkan ulang tanpa pengembalian dana.
            </li>
        </ul>

        <h5>4. Ketentuan Menginap & Penggunaan Layanan
        </h5>
        <ul>
            <li>Check-in: Jam 14:00 | Check-out: Jam 12:00
            </li>
            <li>Tamu wajib membawa identitas resmi (KTP/SIM/Paspor) saat check-in.
            </li>
            <li>Usia minimal tamu utama yang menginap adalah 18 tahun.
            </li>
            <li>Dilarang membawa hewan peliharaan tanpa izin.
            </li>
            <li>Dilarang merusak properti atau mengganggu kenyamanan tamu lain.
            </li>
            <li>Tamu bertanggung jawab atas barang pribadi mereka selama menginap.
            </li>
        </ul>
        <h5>5. Ketentuan Aktivitas (Outbound, Rafting, Paintball, dll.)
        </h5>
        <ul>
            <li>Usia minimal peserta: 7 tahun untuk outbound & trekking, 10 tahun untuk rafting, dan 12 tahun untuk
                paintball.
            </li>
            <li>Peserta wajib dalam kondisi sehat & mengikuti arahan dari instruktur.
            </li>
            <li>Menggunakan perlengkapan keselamatan yang disediakan adalah wajib.
            </li>
            <li>Dilarang membawa atau mengonsumsi alkohol & narkoba sebelum/during aktivitas.
            </li>
            <li>Jika terjadi cuaca ekstrem, aktivitas dapat dijadwalkan ulang tanpa refund.
            </li>
        </ul>
        <h5>6. Hak & Kewajiban Penyedia Layanan
        </h5>
        <ul>
            <li>Kami berhak menolak pemesanan jika terjadi pelanggaran aturan.
            </li>
            <li>Kami tidak bertanggung jawab atas kehilangan barang pribadi tamu.
            </li>
            <li>Kami berhak menyesuaikan harga atau kebijakan layanan tanpa pemberitahuan sebelumnya.
            </li>
        </ul>
        <h5>7. Kebijakan Privasi & Keamanan Data
        </h5>
        <ul>
            <li>Data pribadi yang diberikan oleh pengguna akan dijaga kerahasiaannya.
            </li>
            <li>Kami tidak akan membagikan informasi pelanggan ke pihak ketiga tanpa izin.
            </li>
            <li>Dengan menggunakan layanan kami, Anda dianggap telah membaca, memahami, dan menyetujui Syarat &
                Ketentuan ini.
            </li>
        </ul>
    </div>
</div>


<?php
$ChatWa = "Permisi Kak! Mau tanya tentang Calydore...";
$encodedText = urlencode($ChatWa);
$phoneNumber = "6287778911805";
$waLink = "https://api.whatsapp.com/send?phone={$phoneNumber}&text={$encodedText}";

include_once '../includes/wa-kanan.php';
include '../includes/footer.php';
?>