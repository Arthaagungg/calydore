<?php
function writeLog($message, $type = 'INFO') {
    $logDir = __DIR__ . '/logs'; // Lokasi folder log di dalam proyek
    $logFile = $logDir . '/logger.log'; // Nama file log

    try {
        // Pastikan folder log ada
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true); // Membuat folder jika belum ada
        }

        // Format pesan log
        $time = date('Y-m-d H:i:s');
        $logMessage = "[{$time}] [{$type}] - {$message}\n";

        // Tulis pesan ke file log
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX); // Gunakan LOCK_EX untuk mencegah race condition
    } catch (Exception $e) {
        // Jika terjadi error, Anda bisa log ke tempat lain atau abaikan
        error_log("Gagal menulis log: " . $e->getMessage());
    }
}
?>
