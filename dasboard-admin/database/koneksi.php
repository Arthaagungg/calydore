<?php

// Fungsi untuk membaca file .env
function load_env($file = __DIR__ . '/.env')
{
    if (!file_exists($file)) {
        return [];
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];

    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0)
            continue; // Abaikan komentar
        list($key, $value) = explode('=', $line, 2);
        $env[trim($key)] = trim($value);
    }

    return $env;
}
$env = load_env();  // Pastikan fungsi load_env() didefinisikan sebelumnya

// Tentukan apakah di localhost atau di hosting
$isLocal = ($_SERVER['HTTP_HOST'] == 'localhost:8080' || $_SERVER['HTTP_HOST'] == '192.168.1.4');

define('BASE_URL', $isLocal ? $env['BASE_URL_LOCAL'] : $env['BASE_URL_PRODUCTION']);
define('DB_HOST', $isLocal ? $env['DB_HOST_LOCAL'] : $env['DB_HOST_PRODUCTION']);
define('DB_USER', $isLocal ? $env['DB_USER_LOCAL'] : $env['DB_USER_PRODUCTION']);
define('DB_PASS', $isLocal ? $env['DB_PASS_LOCAL'] : $env['DB_PASS_PRODUCTION']);
define('DB_NAME', $isLocal ? $env['DB_NAME_LOCAL'] : $env['DB_NAME_PRODUCTION']);

// Membuat koneksi ke database jika belum dibuat
if (!isset($conn)) {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        error_log("Koneksi database gagal Koneksi: " . $conn->connect_error);
        die("Maaf, terjadi kesalahan. Silakan coba lagi nanti.");
    }
}
?>