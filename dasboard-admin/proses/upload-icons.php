<?php

include 'session.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $feature_name = $_POST['feature_name'];
    $new_icon = $_POST['new_icon'];

    // Query untuk memperbarui ikon di semua tabel
    $tables = ['glamping_features', 'villa_features', 'villa_kamar_features', 'outbound_features'];

    foreach ($tables as $table) {        // Update ikon berdasarkan feature_name
        $sql = "UPDATE $table SET icons_link = ? WHERE feature_name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $new_icon, $feature_name);

        if (!$stmt->execute()) {
            echo "Error updating icon in table $table: " . $stmt->error;
        }
    }

    echo "<script>alert('Berhasil mengupload Icons !'); window.location.href = '$redirect_page';</script>";
    exit();
} else {
    echo "Invalid request method.";
}
