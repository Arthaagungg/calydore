<?php
session_start();
require '../logs/logger.php';
unset($_SESSION['csrf_token']);
session_destroy();
writeLog("User berhasil keluar.", "LOGOUT");
// Redirect ke halaman login
header("Location: login-admin.php");
exit();
