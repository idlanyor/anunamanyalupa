<?php
include 'config/koneksi.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = $_POST['password'];

if (!empty($password)) {
    $hashed = password_hash($password,PASSWORD_DEFAULT);
    $query = "UPDATE users SET username='$username', password='$hashed' WHERE id=$user_id";
} else {
    $query = "UPDATE users SET username='$username' WHERE id=$user_id";
}

if (mysqli_query($conn, $query)) {
    echo "<script>alert('Profil berhasil diperbarui'); window.location='pengaturan.php';</script>";
} else {
    echo "<script>alert('Gagal menyimpan perubahan'); window.location='pengaturan.php';</script>";
}
?>
<?php
include 'config/koneksi.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = $_POST['password'];

if (!empty($password)) {
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $query = "UPDATE users SET username='$username', password='$hashed' WHERE id=$user_id";
} else {
    $query = "UPDATE users SET username='$username' WHERE id=$user_id";
}

if (mysqli_query($conn, $query)) {
    echo "<script>alert('Profil berhasil diperbarui'); window.location='pengaturan.php';</script>";
} else {
    echo "<script>alert('Gagal menyimpan perubahan'); window.location='pengaturan.php';</script>";
}

