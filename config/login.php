<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'config/koneksi.php';

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];

        // Jika "Ingat Saya" dicentang, simpan cookie untuk login otomatis
        if (isset($_POST['remember_me'])) {
            setcookie('user_id', $user['id'], time() + (60 * 60 * 24 * 30), "/"); // cookie berlaku 30 hari
            setcookie('username', $user['username'], time() + (60 * 60 * 24 * 30), "/");
        }

        header('Location: index.php');
        exit;
    } else {
        $error = 'Username atau password salah';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .login-box {
            background: white;
            padding: 25px 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 350px;
        }
        .login-box h2 {
            margin-bottom: 20px;
            font-size: 22px;
            text-align: center;
        }
        .form-control {
            font-size: 14px;
            padding: 8px 10px;
        }
        .btn {
            font-size: 14px;
            width: 100%;
        }
        .alert {
            font-size: 13px;
            padding: 8px;
        }
        .form-check-label {
            font-size: 12px;
        }
        .forgot-password {
            font-size: 12px;
            display: block;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Login</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" name="username" required autofocus>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" name="remember_me" id="remember_me">
                <label class="form-check-label" for="remember_me">Ingat Saya</label>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        <a href="reset_password.php" class="forgot-password">Lupa Password?</a>
    </div>
</body>
</html>