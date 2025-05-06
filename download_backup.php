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

// Set headers for download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Output file content
readfile($filepath);
exit;
?> 