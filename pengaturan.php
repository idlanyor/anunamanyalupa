<?php
include 'config/koneksi.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user data
$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// Get settings data
$settings_query = "SELECT * FROM settings WHERE id = 1";
$settings_result = mysqli_query($conn, $settings_query);
$settings = mysqli_fetch_assoc($settings_result);

// Initialize settings with default values if not exists
if (!$settings) {
    $settings = array(
        'nama_toko' => 'Nama Toko',
        'alamat_toko' => 'Alamat Toko',
        'stok_minimal_default' => 10,
        'format_tanggal' => 'd-m-Y',
        'tema' => 'light',
        'logo_toko' => ''
    );
}

// Handle success/error messages
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['success']);
unset($_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - Sistem Inventori</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e73df;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --secondary-color: #858796;
            --light-color: #f8f9fc;
            --dark-color: #5a5c69;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--light-color);
        }

        .card {
            border: none;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            padding: 1rem;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2e59d9;
        }

        .logo-preview {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
        }

        .backup-list {
            max-height: 300px;
            overflow-y: auto;
        }

        .backup-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .backup-item:last-child {
            border-bottom: none;
        }

        .backup-actions {
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Main Content -->
            <div class="col-md-12 col-lg-12 ms-auto px-4 py-3">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Pengaturan</h1>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $success ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- User Settings -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Pengaturan Pengguna</h6>
                    </div>
                <div class="card-body">
                    <form action="pengaturan_update.php" method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                        </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password Baru</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah">
                        </div>
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </form>
                </div>
            </div>

                <!-- System Settings -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Pengaturan Sistem</h6>
        </div>
                <div class="card-body">
                    <form action="pengaturan_sistem_update.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="nama_toko" class="form-label">Nama Toko</label>
                                <input type="text" class="form-control" id="nama_toko" name="nama_toko" value="<?= htmlspecialchars($settings['nama_toko']) ?>" required>
                        </div>
                            <div class="mb-3">
                                <label for="alamat_toko" class="form-label">Alamat Toko</label>
                                <textarea class="form-control" id="alamat_toko" name="alamat_toko" rows="3" required><?= htmlspecialchars($settings['alamat_toko']) ?></textarea>
                        </div>
                            <div class="mb-3">
                                <label for="stok_minimal_default" class="form-label">Stok Minimal Default</label>
                                <input type="number" class="form-control" id="stok_minimal_default" name="stok_minimal_default" value="<?= $settings['stok_minimal_default'] ?>" required>
                        </div>
                            <div class="mb-3">
                                <label for="format_tanggal" class="form-label">Format Tanggal</label>
                                <select class="form-select" id="format_tanggal" name="format_tanggal" required>
                                    <option value="d-m-Y" <?= $settings['format_tanggal'] == 'd-m-Y' ? 'selected' : '' ?>>DD-MM-YYYY</option>
                                    <option value="d/m/Y" <?= $settings['format_tanggal'] == 'd/m/Y' ? 'selected' : '' ?>>DD/MM/YYYY</option>
                                    <option value="Y-m-d" <?= $settings['format_tanggal'] == 'Y-m-d' ? 'selected' : '' ?>>YYYY-MM-DD</option>
                                    <option value="d F Y" <?= $settings['format_tanggal'] == 'd F Y' ? 'selected' : '' ?>>DD Month YYYY</option>
                                </select>
                        </div>
                            <div class="mb-3">
                                <label for="tema" class="form-label">Tema</label>
                                <select class="form-select" id="tema" name="tema" required>
                                    <option value="light" <?= $settings['tema'] == 'light' ? 'selected' : '' ?>>Light</option>
                                    <option value="dark" <?= $settings['tema'] == 'dark' ? 'selected' : '' ?>>Dark</option>
                            </select>
                        </div>
                        <div class="mb-3">
                                <label for="logo_toko" class="form-label">Logo Toko</label>
                                <input type="file" class="form-control" id="logo_toko" name="logo_toko" accept="image/*">
                                <?php if ($settings['logo_toko']): ?>
                                    <img src="uploads/<?= htmlspecialchars($settings['logo_toko']) ?>" alt="Logo Toko" class="logo-preview">
                                <?php endif; ?>
                            </div>
                            <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                        </form>
                    </div>
                </div>

                <!-- Database Backup -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Backup Database</h6>
                        <button type="button" class="btn btn-success" onclick="createBackup()">
                            <i class="fas fa-download me-2"></i>Buat Backup
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="backup-list">
                            <?php
                            $backup_dir = 'backups/';
                            if (!file_exists($backup_dir)) {
                                mkdir($backup_dir, 0777, true);
                            }
                            
                            $backups = glob($backup_dir . '*.sql');
                            rsort($backups); // Sort by newest first
                            
                            if (empty($backups)) {
                                echo '<div class="text-center text-muted">Belum ada backup</div>';
                            } else {
                                foreach ($backups as $backup) {
                                    $filename = basename($backup);
                                    $date = date('d/m/Y H:i:s', filemtime($backup));
                                    $size = round(filesize($backup) / 1024, 2) . ' KB';
                                    
                                    echo '<div class="backup-item">
                                        <div>
                                            <div class="fw-bold">' . htmlspecialchars($filename) . '</div>
                                            <small class="text-muted">' . $date . ' - ' . $size . '</small>
                                        </div>
                                        <div class="backup-actions">
                                            <a href="download_backup.php?file=' . urlencode($filename) . '" class="btn btn-sm btn-primary">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteBackup(\'' . htmlspecialchars($filename) . '\')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview logo when selected
        document.getElementById('logo_toko').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    let preview = document.querySelector('.logo-preview');
                    if (!preview) {
                        preview = document.createElement('img');
                        preview.className = 'logo-preview';
                        document.getElementById('logo_toko').parentNode.appendChild(preview);
                    }
                    preview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });

        // Backup functions
        function createBackup() {
            if (confirm('Apakah Anda yakin ingin membuat backup database?')) {
                window.location.href = 'create_backup.php';
            }
        }

        function deleteBackup(filename) {
            if (confirm('Apakah Anda yakin ingin menghapus backup ini?')) {
                window.location.href = 'delete_backup.php?file=' + encodeURIComponent(filename);
            }
        }
    </script>
</body>
</html>
