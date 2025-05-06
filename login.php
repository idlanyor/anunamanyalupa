<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'config/koneksi.php';

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
<html lang="id" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TOKO NOVI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-light: #f8f9fa;
            --bg-dark: #1a1d21;
            --card-light: #ffffff;
            --card-dark: #242830;
            --text-light: #2c3e50;
            --text-dark: #e9ecef;
            --input-bg-light: #ffffff;
            --input-bg-dark: #2d3339;
            --input-border-light: #dee2e6;
            --input-border-dark: #404750;
            --primary-color: #4e73df;
            --primary-hover: #2e59d9;
        }

        [data-theme="dark"] {
            --bg-color: var(--bg-dark);
            --card-bg: var(--card-dark);
            --text-color: var(--text-dark);
            --input-bg: var(--input-bg-dark);
            --input-border: var(--input-border-dark);
        }

        [data-theme="light"] {
            --bg-color: var(--bg-light);
            --card-bg: var(--card-light);
            --text-color: var(--text-light);
            --input-bg: var(--input-bg-light);
            --input-border: var(--input-border-light);
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            transition: all 0.3s ease;
            font-family: 'Segoe UI', system-ui, -apple-system;
        }

        .login-box {
            background: var(--card-bg);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            transition: all 0.3s ease;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h2 {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        .form-control {
            background-color: var(--input-bg);
            border: 1px solid var(--input-border);
            color: var(--text-color);
            padding: 0.75rem 1rem;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
            background-color: var(--input-bg);
            color: var(--text-color);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 0.75rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
        }

        .form-check-label {
            color: var(--text-color);
        }

        .forgot-password {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .forgot-password:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }

        .theme-toggle {
            position: fixed;
            top: 1rem;
            right: 1rem;
            background: var(--card-bg);
            border: 1px solid var(--input-border);
            padding: 0.5rem;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.2s;
        }

        .theme-toggle:hover {
            transform: rotate(180deg);
        }

        .alert {
            border-radius: 8px;
            font-size: 0.875rem;
        }
    </style>
</head>

<body>
    <button class="theme-toggle" onclick="toggleTheme()">
        <i class="fas fa-moon"></i>
    </button>

    <div class="login-box">
        <div class="login-header">
            <i class="fas fa-store fa-3x mb-3" style="color: var(--primary-color);"></i>
            <h2>TOKO NOVI</h2>
            <p class="text-muted">Silakan login untuk melanjutkan</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" class="form-control" name="username" required autofocus>
                </div>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" name="password" required>
                </div>
            </div>
            <div class="mb-4 form-check">
                <input type="checkbox" class="form-check-input" name="remember_me" id="remember_me">
                <label class="form-check-label" for="remember_me">Ingat Saya</label>
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-3">
                <i class="fas fa-sign-in-alt me-2"></i> Login
            </button>
        </form>
        <div class="text-center">
            <a href="reset_password.php" class="forgot-password">Lupa Password?</a>
        </div>
    </div>

    <script>
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            html.setAttribute('data-theme', newTheme);

            const icon = document.querySelector('.theme-toggle i');
            icon.className = newTheme === 'light' ? 'fas fa-moon' : 'fas fa-sun';

            localStorage.setItem('theme', newTheme);
        }

        // Set theme on page load
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        document.querySelector('.theme-toggle i').className =
            savedTheme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
    </script>
</body>

</html>