<?php if (!defined('SECURE_ACCESS')) {
  http_response_code(404);
  header("Location: " . BASE_URL . " /error.php");
  exit();
} ?>
<footer class="container-footer text-white pt-3 pb-1 mt-5">
  <div class="container">
    <div class="row">
      <div class="col-lg-6">
        <h3>Alamat Kami</h3>
        <div class="card w-50 ">
          <div class="ratio ratio-1x1">
            <iframe title="Peta lokasi Calidore, Bogor"
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3962.803938188856!2d106.92411747356142!3d-6.671199665215391!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69b7dbafec68e7%3A0xab98a7f71ccde373!2sCalidore!5e0!3m2!1sid!2sid!4v1739364790148!5m2!1sid!2sid"
              width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"
              referrerpolicy="no-referrer-when-downgrade">
            </iframe>

          </div>
        </div>
        <p>Jl. Raya Puncak KM. 79 No. 62, Kopo, Cisarua, Jl. Raya Puncak No.KM. 79 No.62, Kopo, Kec. Cisarua,
          Kabupaten
          Bogor, Jawa Barat 16750</p>
      </div>

      <div class="col-lg-3">
        <h3>Sosial Media</h3>
        <p><i class="bi bi-instagram"></i> @calydore.official</p>
        <p><i class="bi bi-tiktok"></i> @calydore.official</p>
        <p><i class="bi bi-facebook"></i> Calydore Official</p>
        </ul>
      </div>

      <div class="col-lg-3">
        <h3>Contact</h3>
        <p><i class="bi bi-geo-alt-fill"></i> Jl. Raya Puncak, Kopo, Kec. Cisarua, Kabupaten Bogor, Jawa Barat 16750
        </p>
        <p><i class="bi bi-telephone-fill"></i> +62 877 7891 1805</p>
        <p><i class="bi bi-envelope-fill"></i> calydoreofficial@gmail.com</p>
      </div>
    </div>

    <div class="text-center mt-4">
      <p>&copy; 2025 Calydore.</p>
    </div>
  </div>
</footer>
<script>
  if ('scrollRestoration' in history) {
    history.scrollRestoration = "manual";
  }
</script>
</body>

</html>