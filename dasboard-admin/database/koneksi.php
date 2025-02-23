<?php

// Fungsi untuk membaca file .env
function load_env()
{
    // Tentukan path ke file .env berdasarkan environment
    if ($_SERVER['HTTP_HOST'] == 'localhost:8080' || $_SERVER['HTTP_HOST'] == '192.168.1.4') {
        // Environment lokal
        $envPath = __DIR__ . '/.env'; // File .env berada di folder yang sama dengan config.php
    } else {
        // Environment produksi
        $envPath = __DIR__ . '/../../../app/.env'; // File .env berada di luar root web
    }

    // Periksa apakah file .env ada
    if (!file_exists($envPath)) {
        throw new Exception("File .env tidak ditemukan di: $envPath");
    }

    // Baca file .env
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];

    foreach ($lines as $line) {
        // Abaikan komentar
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Pisahkan key dan value
        list($key, $value) = explode('=', $line, 2);
        $env[trim($key)] = trim($value);
    }

    return $env;
}

try {
    // Load file .env
    $env = load_env();

    // Tentukan apakah di localhost atau di hosting
    $isLocal = ($_SERVER['HTTP_HOST'] == 'localhost:8080' || $_SERVER['HTTP_HOST'] == '192.168.1.4');

    // Definisikan konstanta untuk database pertama
    define('BASE_URL', $isLocal ? $env['BASE_URL_LOCAL'] : $env['BASE_URL_PRODUCTION']);
    define('DB_HOST', $isLocal ? $env['DB_HOST_LOCAL'] : $env['DB_HOST_PRODUCTION']);
    define('DB_USER', $isLocal ? $env['DB_USER_LOCAL'] : $env['DB_USER_PRODUCTION']);
    define('DB_PASS', $isLocal ? $env['DB_PASS_LOCAL'] : $env['DB_PASS_PRODUCTION']);
    define('DB_NAME', $isLocal ? $env['DB_NAME_LOCAL'] : $env['DB_NAME_PRODUCTION']);

    // Definisikan konstanta untuk database kedua
    define('DB_HOST_2', $isLocal ? $env['DB_HOST_LOCAL_2'] : $env['DB_HOST_PRODUCTION_2']);
    define('DB_USER_2', $isLocal ? $env['DB_USER_LOCAL_2'] : $env['DB_USER_PRODUCTION_2']);
    define('DB_PASS_2', $isLocal ? $env['DB_PASS_LOCAL_2'] : $env['DB_PASS_PRODUCTION_2']);
    define('DB_NAME_2', $isLocal ? $env['DB_NAME_LOCAL_2'] : $env['DB_NAME_PRODUCTION_2']);

    // Membuat koneksi ke database pertama
    if (!isset($conn)) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($conn->connect_error) {
            error_log("Koneksi database pertama gagal: " . $conn->connect_error);
            die("Maaf, terjadi kesalahan. Silakan coba lagi nanti.");
        }
    }

    // Membuat koneksi ke database kedua
    if (!isset($conn2)) {
        $conn2 = new mysqli(DB_HOST_2, DB_USER_2, DB_PASS_2, DB_NAME_2);

        if ($conn2->connect_error) {
            error_log("Koneksi database kedua gagal: " . $conn2->connect_error);
            die("Maaf, terjadi kesalahan. Silakan coba lagi nanti.");
        }
    }
} catch (Exception $e) {
    // Tangani error
    error_log("Error: " . $e->getMessage());
    die("Maaf, terjadi kesalahan. Silakan coba lagi nanti.");
}
?>