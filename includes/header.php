<?php
session_start();
require_once __DIR__ . '/../dasboard-admin/database/koneksi.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);
if (!defined('SECURE_ACCESS')) {
  http_response_code(404);
  header("Location: " . BASE_URL . "/error.php");
  exit();

}


$sql = "SELECT file_path, file_name, image_type FROM assets_images WHERE image_type IN ('logo', 'logo-header', 'logo-menu')";
$result = $conn->query($sql);

$logoImages = [];
$logoHeaderImages = [];
$logoMenuImages = [];

$imagekit_base_url = "https://ik.imagekit.io/bkx7wk6gv";
if ($result->num_rows > 0) {
  while ($imageRow = $result->fetch_assoc()) {
    $filePath = rtrim($imageRow['file_path'], '/');
    $fileName = ltrim($imageRow['file_name'], '/');
    $imageUrl = $imagekit_base_url . $filePath . '/' . $fileName;

    switch ($imageRow['image_type']) {
      case 'logo':
        $logoImages[] = $imageUrl;
        break;
      case 'logo-header':
        $logoHeaderImages[] = $imageUrl;
        break;
      case 'logo-menu':
        $logoMenuImages[] = $imageUrl;
        break;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="robots" content="index, follow">
  <meta name="keywords"
    content="puncak, cisarua bogor, villa di kawasan puncak,hotel puncak cisarua , glamping di alam puncak, outbound puncak, catering puncak, reservasi puncak, puncak cisarua bogor">
  <meta name="description"
    content="<?php echo isset($description) ? htmlspecialchars($description) : 'Jelajahi pengalaman tak terlupakan bersama Calydore, platform all-in-one untuk pemesanan Hotel, Villa, Staycation, Glamping, Camping, serta layanan Outbound, Gathering, dan Catering. Pemesanan aman, cepat, dan terpercaya—semua diatur oleh admin profesional, bebas ribet! Kunjungi website resmi atau ikuti kami di Instagram & TikTok @CALYDORE.OFFICIAL untuk info lebih lanjut. Ayo, jadwalkan liburan impianmu sekarang!'; ?>">
  <meta name="google-site-verification" content="RhdrDePP0v2qgkZKnDYbHCEnG0x-R7jaHCeK6IkSXlw" />

  <meta property="og:title" content="<?php echo htmlspecialchars($pageTitle); ?>" />
  <meta property="og:url" content="<?php echo htmlspecialchars($pageURL); ?>" />

  <?php
  if (!empty($pageType)) {
    switch ($pageType) {
      case 'villa':
      case 'hotel':
      case 'glamping':
        $ogType = 'place';
        break;
      case 'catering':
      case 'outbound':
      case 'villa kamar':
        $ogType = 'article';
        break;
      default:
        $ogType = 'website';
    }
  } else {
    $ogType = 'website'; // Default jika $pageType kosong
  }

  ?>
  <meta property="og:type" content="<?php echo isset($ogType) ? $ogType : "website"; ?>" />

  <title><?php echo isset($pageTitle) ? $pageTitle : "calydore"; ?></title>


  <link rel="icon" type="image/x-icon" href="<?php echo BASE_URL; ?>/assets/favico.ico">
  <!-- Import custom CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick-theme.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/style/style.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/style/villa.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/style/category.css">

  <!-- Google Tag Manager -->
  <script>(function (w, d, s, l, i) {
      w[l] = w[l] || []; w[l].push({
        'gtm.start':
          new Date().getTime(), event: 'gtm.js'
      }); var f = d.getElementsByTagName(s)[0],
        j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : ''; j.async = true; j.src =
          'https://www.googletagmanager.com/gtm.js?id=' + i + dl; f.parentNode.insertBefore(j, f);
    })(window, document, 'script', 'dataLayer', 'GTM-T8H2Z9KK');</script>
  <!-- End Google Tag Manager -->
  <?php
  // Ambil URL saat ini
  $path = $_SERVER['REQUEST_URI'];

  // Definisi breadcrumb dasar (Home)
  $breadcrumbs = [
    [
      "@type" => "ListItem",
      "position" => 1,
      "name" => "Home",
      "item" => "https://calydore.com"
    ]
  ];

  // Jika halaman kategori
  if (preg_match("#^/page/([^/]+)$#", $path, $matches)) {
    $category = ucfirst(str_replace('-', ' ', $matches[1]));
    $breadcrumbs[] = [
      "@type" => "ListItem",
      "position" => 2,
      "name" => $category,
      "item" => "https://calydore.com/page/" . htmlspecialchars($matches[1])
    ];
  }

  // Jika halaman detail villa/hotel/glamping
  elseif (preg_match("#^/(villa|hotel|glamping|villa-kamar)/([^/]+)$#", $path, $matches)) {
    $category = ucfirst($matches[1]);
    $slug = str_replace('-', ' ', $matches[2]);

    $breadcrumbs[] = [
      "@type" => "ListItem",
      "position" => 2,
      "name" => $category,
      "item" => "https://calydore.com/page/" . htmlspecialchars($matches[1])
    ];
    $breadcrumbs[] = [
      "@type" => "ListItem",
      "position" => 3,
      "name" => htmlspecialchars($slug),
      "item" => "https://calydore.com" . htmlspecialchars($path)
    ];
  }

  // Jika halaman daftar catering/outbound
  elseif (preg_match("#^/daftar-(catering|outbound)/([^/]+)$#", $path, $matches)) {
    $category = ucfirst($matches[1]);
    $subcategory = str_replace('%20', ' ', $matches[2]);

    $breadcrumbs[] = [
      "@type" => "ListItem",
      "position" => 2,
      "name" => $category,
      "item" => "https://calydore.com/page/" . htmlspecialchars($matches[1])
    ];
    $breadcrumbs[] = [
      "@type" => "ListItem",
      "position" => 3,
      "name" => htmlspecialchars($subcategory),
      "item" => "https://calydore.com" . htmlspecialchars($path)
    ];
  }

  // Jika halaman detail catering/outbound
  elseif (preg_match("#^/(catering|outbound)/([^/]+)$#", $path, $matches)) {
    $category = ucfirst($matches[1]);
    $slug = str_replace('-', ' ', $matches[2]);

    $breadcrumbs[] = [
      "@type" => "ListItem",
      "position" => 2,
      "name" => $category,
      "item" => "https://calydore.com/page/" . htmlspecialchars($matches[1])
    ];
    $breadcrumbs[] = [
      "@type" => "ListItem",
      "position" => 3,
      "name" => htmlspecialchars($slug),
      "item" => "https://calydore.com" . htmlspecialchars($path)
    ];
  }

  // Convert ke JSON
  $breadcrumbJson = json_encode([
    "@context" => "https://schema.org",
    "@type" => "BreadcrumbList",
    "itemListElement" => $breadcrumbs
  ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
  ?>

  <script type="application/ld+json">
<?php echo $breadcrumbJson; ?>
</script>

</head>

<body>
  <!-- Google Tag Manager (noscript) -->
  <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-T8H2Z9KK" height="0" width="0"
      style="display:none;visibility:hidden"></iframe></noscript>
  <!-- End Google Tag Manager (noscript) -->
  <div class="top-container">
    <img src="<?php echo htmlspecialchars($logoHeaderImages[0]); ?>" loading="lazy" alt="Logo">
  </div>


  <div class="header" id="header">

    <div class="search-container">
      <div class="logo">
        <img src="<?php echo htmlspecialchars($logoImages[0]); ?>" alt="Logo" class="logo-icon">
      </div>
      <input type="text" class="search-input" id="searchInput" name="search" placeholder="Cari Semua Di Calydore"
        autocomplete="off" />
      <ul id="suggestions"></ul>
    </div>


    <div class="sec-center">
      <input class="dropdown" type="checkbox" id="dropdown" name="dropdown" aria-label="Aktifkan Dropdown">
      <button class="btn btn-menu" type="button" name="dropdown" data-bs-toggle="offcanvas"
        data-bs-target="#offcanvasRight" aria-controls="offcanvasRight" aria-label="Buka Menu">
        <i class="fa-solid fa-bars"></i> <!-- Contoh ikon -->
      </button>

    </div>
  </div>
  <!-- Offcanvas Menu -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
    <div class="offcanvas-header d-flex align-items-center justify-content-between">
      <div>
        <img src="<?php echo htmlspecialchars($logoImages[0]); ?>" alt="Villa" class="offcanvas-logo">
        <img src="<?php echo htmlspecialchars($logoMenuImages[0]); ?>" alt="Villa" class="offcanvas-logo-2">
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
      <ul class="list-group">
        <li class="list-group-item">
          <a href="<?php echo BASE_URL; ?>/" class="text">Home</a>
        </li>
        <li class="list-group-item menu-item-produk">
          <a href="#" class="text d-flex justify-content-between align-items-center toggle-submenu">
            Katalog <i class="bi bi-chevron-down"></i>
          </a>
        </li>

        <ul class="submenu" id="submenuProduk">
          <li class="list-group-item"><a href="<?php echo BASE_URL; ?>/page/villa" class="text">Villa</a></li>
          <li class="list-group-item"><a href="<?php echo BASE_URL; ?>/page/hotel" class="text">Hotel</a></li>
          <li class="list-group-item"><a href="<?php echo BASE_URL; ?>/page/villa-kamar" class="text">Villa Kamar</a>
          </li>
          <li class="list-group-item"><a href="<?php echo BASE_URL; ?>/page/glamping" class="text">Glamping</a></li>
          <li class="list-group-item"><a href="<?php echo BASE_URL; ?>/page/outbound" class="text">Outbound</a></li>
          <li class="list-group-item"><a href="<?php echo BASE_URL; ?>/page/catering" class="text">Catering</a></li>
        </ul>

        <li class="list-group-item">
          <a href="<?php echo BASE_URL; ?>/page/tentang-kami" class="text">Tentang Kami</a>
        </li>
        <li class="list-group-item">
          <a href="<?php echo BASE_URL; ?>/page/syarat-dan-ketentuan" class="text">S&K</a>
        </li>
      </ul>
    </div>
  </div>


  <style>
    .list-group {
      padding-left: 0;
      list-style: none;
    }

    .list-group-item {
      border-bottom: 1px solid #ffffff;
      padding: 12px 20px;
      color: #ffffff;
      font-size: 14px;
      cursor: pointer;
      transition: background-color 0.3s, color 0.3s;
    }

    .list-group-item:hover {
      color: #ffffff;
    }

    .menu-item-produk {
      position: relative;

    }


    .submenu {
      padding-left: 0;
      display: none;
      left: 0;
      width: 100%;
      z-index: 10;
    }

    .submenu li {
      border-bottom: 1px solid #ffffff;
      padding: 12px 20px;
      color: #ffffff;
    }


    .submenu-visible {
      display: block !important;
      transition: all 0.3s ease-in-out;
    }

    .toggle-submenu i {
      transition: transform 0.3s ease-in-out;
      color: #ffffff;
    }

    .submenu-visible i {
      transform: rotate(180deg);
    }

    .btn-close {
      background-color: #ffd700;
      --bs-btn-close-opacity: 1;
    }

    .btn-close:focus {
      outline: none;
    }

    .offcanvas-header .d-flex {
      justify-content: space-between;
      align-items: center;
    }

    .offcanvas-logo,
    .offcanvas-logo-2 {
      width: 120px;
      height: auto;
    }

    .text {
      text-decoration: none;
      color: inherit;
    }

    .text:hover {
      color: white;
    }
  </style>

  <!-- JavaScript: Slick, jQuery, and Bootstrap -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
  <script src="<?php echo BASE_URL; ?>/assets/js/script.js"></script>
  <script src="<?php echo BASE_URL; ?>/assets/src/bootstrap-5.3.0-alpha1/js/bootstrap.min.js"></script>

  <script>
    $(document).ready(function () {
      $('.toggle-submenu').click(function () {
        $('#submenuProduk').slideToggle();
        $(this).find('i').toggleClass('submenu-visible');
      });
    });
  </script>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const searchInput = document.getElementById("searchInput");
      const suggestions = document.getElementById("suggestions");
      let debounceTimer;

      searchInput.addEventListener("input", function () {
        clearTimeout(debounceTimer);
        const query = this.value.trim();

        if (query.length > 0) {
          debounceTimer = setTimeout(() => {
            fetch(`<?php echo BASE_URL; ?>/includes/database-user/autocomplete.php?search=${encodeURIComponent(query)}`)
              .then(response => response.json())
              .then(data => {
                suggestions.innerHTML = "";
                if (data.length > 0) {
                  data.forEach(item => {
                    const li = document.createElement("li");
                    li.textContent = item.value;

                    let tableName = item.table.replace(/_/g, " ").replace(/\b\w/g, char => char.toUpperCase());
                    tableName = tableName.replace("Features", "Fasilitas");

                    const tableSpan = document.createElement("span");
                    tableSpan.textContent = tableName;
                    tableSpan.style.float = "right";
                    tableSpan.style.fontSize = "1em";
                    tableSpan.style.color = "#888";

                    li.appendChild(tableSpan);

                    li.addEventListener("click", function () {
                      searchInput.value = item.value;
                      window.location.href = `<?php echo BASE_URL; ?>/page/result.php?search=${encodeURIComponent(item.value)}&table=${encodeURIComponent(item.table)}`;
                    });

                    suggestions.appendChild(li);
                  });
                  showSuggestions();
                } else {
                  hideSuggestions();
                }
              })
              .catch(error => {
                console.error("Error:", error);
              });
          }, 300); // Debounce 300ms
        } else {
          hideSuggestions();
        }
      });

      document.addEventListener("click", function (e) {
        if (!searchInput.contains(e.target) && !suggestions.contains(e.target)) {
          hideSuggestions();
        }
      });

      function showSuggestions() {
        suggestions.style.display = "block";
        document.body.classList.add("no-scroll");
      }

      function hideSuggestions() {
        suggestions.innerHTML = "";
        suggestions.style.display = "none";
        document.body.classList.remove("no-scroll");
      }
    });

  </script>