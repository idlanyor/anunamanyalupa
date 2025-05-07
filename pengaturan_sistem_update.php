<?php
include 'config/koneksi.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Create settings table if not exists
$create_table = "CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_toko VARCHAR(100) NOT NULL,
    alamat_toko TEXT NOT NULL,
    stok_minimal_default INT NOT NULL DEFAULT 10,
    format_tanggal VARCHAR(20) NOT NULL DEFAULT 'd-m-Y',
    tema VARCHAR(20) NOT NULL DEFAULT 'light',
    logo_toko VARCHAR(255) DEFAULT NULL
)";
mysqli_query($conn, $create_table);

// Insert default settings if not exists
$check_settings = "SELECT COUNT(*) as count FROM settings WHERE id = 1";
$result = mysqli_query($conn, $check_settings);
$row = mysqli_fetch_assoc($result);

if ($row['count'] == 0) {
    $insert_default = "INSERT INTO settings (id, nama_toko, alamat_toko, stok_minimal_default, format_tanggal, tema) 
                      VALUES (1, 'Nama Toko', 'Alamat Toko', 10, 'd-m-Y', 'light')";
    mysqli_query($conn, $insert_default);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Create uploads directory if not exists
    $upload_dir = 'uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Handle logo upload
    $logo_toko = '';
    if (isset($_FILES['logo_toko']) && $_FILES['logo_toko']['error'] == 0) {
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        $file_info = pathinfo($_FILES['logo_toko']['name']);
        $extension = strtolower($file_info['extension']);
        
        if (in_array($extension, $allowed_types)) {
            $filename = 'logo_' . time() . '.' . $extension;
            $filepath = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['logo_toko']['tmp_name'], $filepath)) {
                $logo_toko = $filename;
            }
        }
    }

    // Update settings
    $nama_toko = mysqli_real_escape_string($conn, $_POST['nama_toko']);
    $alamat_toko = mysqli_real_escape_string($conn, $_POST['alamat_toko']);
    $stok_minimal_default = (int)$_POST['stok_minimal_default'];
    $format_tanggal = mysqli_real_escape_string($conn, $_POST['format_tanggal']);
    $tema = mysqli_real_escape_string($conn, $_POST['tema']);

    $sql = "UPDATE settings SET 
            nama_toko = '$nama_toko',
            alamat_toko = '$alamat_toko',
            stok_minimal_default = $stok_minimal_default,
            format_tanggal = '$format_tanggal',
            tema = '$tema'";
    
    if ($logo_toko) {
        $sql .= ", logo_toko = '$logo_toko'";
    }
    
    $sql .= " WHERE id = 1";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Pengaturan berhasil diperbarui";
    } else {
        $_SESSION['error'] = "Gagal memperbarui pengaturan: " . mysqli_error($conn);
    }
}

header('Location: index.php?page=pengaturan');
exit;

