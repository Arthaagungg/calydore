<?php
session_start();

// Periksa apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login-admin.php");
    exit();
}
// Validasi CSRF token
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Invalid CSRF token. Permintaan ini tidak sah.";
        header("Location: /website2/index.php");
        exit();
    }
}

$redirect_page = $_POST['redirect_page'] ?? $_SESSION['previous_page'] ?? '/dasboard-admin/page-admin/index-admin.php';

include_once '../database/koneksi.php';
