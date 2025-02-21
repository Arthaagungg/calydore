<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
        }

        .error-container {
            max-width: 500px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .error-code {
            font-size: 80px;
            font-weight: bold;
            color: #dc3545;
        }

        .error-message {
            font-size: 18px;
            margin-bottom: 20px;
        }

        .btn-back {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: 0.3s;
        }

        .btn-back:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>

    <?php
    $error_code = isset($_GET['code']) ? $_GET['code'] : 404;

    if ($error_code == 403) {
        $message = "Anda tidak memiliki izin untuk mengakses halaman ini.";
    } else {
        $message = "Halaman yang Anda cari tidak ditemukan.";
    }
    ?>

    <div class="error-container">
        <div class="error-code"><?= $error_code; ?></div>
        <p class="error-message"><?= $message; ?></p>
        <a href="/" class="btn-back">Kembali ke Beranda</a>
    </div>

</body>

</html>