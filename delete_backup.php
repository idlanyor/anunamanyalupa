<?php
include 'config/koneksi.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['file'])) {
    header('Location: pengaturan.php');
    exit;
}

$filename = basename($_GET['file']);
$filepath = 'backups/' . $filename;

if (!file_exists($filepath)) {
    $_SESSION['error'] = "File backup tidak ditemukan";
    header('Location: pengaturan.php');
    exit;
}

if (unlink($filepath)) {
    $_SESSION['success'] = "Backup berhasil dihapus";
} else {
    $_SESSION['error'] = "Gagal menghapus backup";
}

header('Location: pengaturan.php');
exit;
?> 