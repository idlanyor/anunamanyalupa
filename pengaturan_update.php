<?php
include 'config/koneksi.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Check if username already exists
    $check = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username' AND id != $user_id");
    if (mysqli_num_rows($check) > 0) {
        $_SESSION['error'] = "Username sudah digunakan";
        header('Location: index.php?page=pengaturan');
        exit;
    }

    $sql = "UPDATE users SET username = '$username'";
    
    // Update password if provided
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql .= ", password = '$hashed_password'";
    }
    
    $sql .= " WHERE id = $user_id";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Profil berhasil diperbarui";
    } else {
        $_SESSION['error'] = "Gagal memperbarui profil: " . mysqli_error($conn);
    }

    header('Location: index.php?page=pengaturan');
    exit;
}
?>
