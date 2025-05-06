
<!-- Begin Page Content -->
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Pengaturan Sistem</h1>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= $_SESSION['success'] ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= $_SESSION['error'] ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="row">
        <!-- Profil Pengguna -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user me-2"></i>Profil Pengguna
                    </h6>
                </div>
                <div class="card-body">
                    <form action="pengaturan_update.php" method="POST">
                        <div class="form-group">
                            <label class="font-weight-bold">Username</label>
                            <input type="text" name="username" class="form-control" value="<?= $user['username'] ?>" required>
                        </div>
                        <div class="form-group mt-3">
                            <label class="font-weight-bold">Password Baru</label>
                            <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diubah">
                        </div>
                        <button type="submit" class="btn btn-primary mt-3">
                            <i class="fas fa-save me-2"></i>Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Pengaturan Sistem -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-cog me-2"></i>Pengaturan Sistem
                    </h6>
                </div>
                <div class="card-body">
                    <form action="pengaturan_sistem_update.php" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label class="font-weight-bold">Nama Toko</label>
                            <input type="text" name="nama_toko" class="form-control" value="<?= $settings['nama_toko'] ?>" required>
                        </div>
                        <div class="form-group mt-3">
                            <label class="font-weight-bold">Alamat</label>
                            <textarea name="alamat_toko" class="form-control" rows="3" required><?= $settings['alamat_toko'] ?></textarea>
                        </div>
                        <div class="form-group mt-3">
                            <label class="font-weight-bold">Stok Minimal Default</label>
                            <input type="number" name="stok_minimal_default" class="form-control" value="<?= $settings['stok_minimal_default'] ?>" required>
                        </div>
                        <div class="form-group mt-3">
                            <label class="font-weight-bold">Logo</label>
                            <input type="file" name="logo_toko" class="form-control">
                            <?php if ($settings['logo_toko']) { ?>
                                <img src="uploads/<?= $settings['logo_toko'] ?>" class="img-thumbnail mt-2" style="max-width: 100px;" alt="Logo">
                            <?php } ?>
                        </div>
                        <div class="form-group mt-3">
                            <label class="font-weight-bold">Format Tanggal</label>
                            <select name="format_tanggal" class="form-control">
                                <option value="d-m-Y" <?= ($settings['format_tanggal'] == 'd-m-Y') ? 'selected' : '' ?>>DD-MM-YYYY</option>
                                <option value="Y-m-d" <?= ($settings['format_tanggal'] == 'Y-m-d') ? 'selected' : '' ?>>YYYY-MM-DD</option>
                            </select>
                        </div>
                        <div class="form-group mt-3">
                            <label class="font-weight-bold">Tema Tampilan</label>
                            <select name="tema" class="form-control">
                                <option value="light" <?= ($settings['tema'] == 'light') ? 'selected' : '' ?>>Light</option>
                                <option value="dark" <?= ($settings['tema'] == 'dark') ? 'selected' : '' ?>>Dark</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success mt-3">
                            <i class="fas fa-save me-2"></i>Simpan Pengaturan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /.container-fluid --> 