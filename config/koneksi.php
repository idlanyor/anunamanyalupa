<?php
$host = 'localhost';
$user = 'root'; // Ganti dengan username MySQL kamu
$pass = 'kanata'; // Ganti dengan password MySQL kamu
$db = 'stok_dagang'; // Nama database yang kamu buat

// Koneksi ke database
$conn = mysqli_connect($host, $user, $pass, $db);

// Cek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
