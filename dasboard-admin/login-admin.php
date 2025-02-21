<?php
require '../logs/logger.php';
// Konfigurasi cookie session
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => true, // Set ke true jika menggunakan HTTPS
    'httponly' => true,
    'samesite' => 'Strict',
]);

// Mulai session
session_start();

// Sertakan file koneksi database
include_once 'database/koneksi.php';
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

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Periksa apakah username dan password tersedia
    if (isset($_POST['username'], $_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Gunakan prepared statement
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        if (!$stmt) {
            die("Kesalahan query: " . $conn->error);
        }
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // Verifikasi password
            if (password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                header("Location: page-admin/index-admin.php");
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                writeLog("User berhasil masuk.", "LOGIN");
                exit();
            } else {
                $error = "Password salah.";
            }
        } else {
            $error = "Username tidak ditemukan.";
        }
    } else {
        $error = "Mohon masukkan username dan password.";
    }
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Import CSS -->
    <link rel="stylesheet" href="property/css/style.css">
    <title>Welcome</title>
    <style>
        /* Importing fonts from Google */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap');

        /* Reseting */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #ecf0f3;
        }

        .wrapper {
            max-width: 350px;
            min-height: 500px;
            margin: 80px auto;
            padding: 40px 30px 30px 30px;
            background-color: #ecf0f3;
            border-radius: 15px;
            box-shadow: 13px 13px 20px #cbced1, -13px -13px 20px #fff;
        }

        .logo {
            width: 80px;
            margin: auto;
        }

        .logo img {
            width: 100%;
            height: 80px;
            object-fit: cover;
            border-radius: 50%;
            box-shadow: 0px 0px 3px #5f5f5f,
                0px 0px 0px 5px #ecf0f3,
                8px 8px 15px #a7aaa7,
                -8px -8px 15px #fff;
        }

        .wrapper .name {
            font-weight: 600;
            font-size: 1.4rem;
            letter-spacing: 1.3px;
            padding-left: 10px;
            color: #555;
        }

        .wrapper .form-field input {
            width: 100%;
            display: block;
            border: none;
            outline: none;
            background: none;
            font-size: 1.2rem;
            color: #666;
            padding: 10px 15px 10px 10px;
        }

        .wrapper .form-field {
            padding-left: 10px;
            margin-bottom: 20px;
            border-radius: 20px;
            box-shadow: inset 8px 8px 8px #cbced1, inset -8px -8px 8px #fff;
        }

        .wrapper .form-field .fas {
            color: #555;
        }

        .wrapper .btn {
            box-shadow: none;
            width: 100%;
            height: 40px;
            background-color: #03A9F4;
            color: #fff;
            border-radius: 25px;
            box-shadow: 3px 3px 3px #b1b1b1,
                -3px -3px 3px #fff;
            letter-spacing: 1.3px;
        }

        .wrapper .btn:hover {
            background-color: #039BE5;
        }

        .wrapper a {
            text-decoration: none;
            font-size: 0.8rem;
            color: #03A9F4;
        }

        .wrapper a:hover {
            color: #039BE5;
        }

        @media(max-width: 380px) {
            .wrapper {
                margin: 30px 20px;
                padding: 40px 15px 15px 15px;
            }
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="logo">
            <img src="<?php echo htmlspecialchars($logoImages[0]); ?>" alt="Villa" class="main-image">
        </div>
        <div class="text-center mt-4 name">
            calydore
        </div>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="POST" action="login-admin.php" class="p-3 mt-3">
            <div class="form-field d-flex align-items-center">
                <span class="far fa-user"></span>
                <input type="text" name="username" id="userName" placeholder="Username" required>
            </div>
            <div class="form-field d-flex align-items-center">
                <span class="fas fa-key"></span>
                <input type="password" name="password" id="pwd" placeholder="Password" required>
            </div>
            <button class="btn mt-3" type="submit">Login</button>
        </form>
    </div>
</body>

</html>