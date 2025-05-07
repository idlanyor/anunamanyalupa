<?php
// Koneksi ke database
include 'config/koneksi.php';
// Simpan Data
if (isset($_POST['save'])) {
    $kode_barang = $_POST['kode_barang'];
    $nama_barang = $_POST['nama_barang'];
    $stok = $_POST['stok'];
    $satuan = $_POST['satuan'];
    $stok_minimal = $_POST['stok_minimal'];

    $sql = "INSERT INTO barang (kode_barang, nama_barang, stok, satuan, stok_minimal)
            VALUES ('$kode_barang', '$nama_barang', '$stok', '$satuan', '$stok_minimal')";
    $conn->query($sql);
}

// Update Data
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $kode_barang = $_POST['kode_barang'];
    $nama_barang = $_POST['nama_barang'];
    $stok = $_POST['stok'];
    $satuan = $_POST['satuan'];
    $stok_minimal = $_POST['stok_minimal'];

    $sql = "UPDATE barang SET 
                kode_barang='$kode_barang', 
                nama_barang='$nama_barang', 
                stok='$stok', 
                satuan='$satuan', 
                stok_minimal='$stok_minimal'
            WHERE id=$id";
    $conn->query($sql);
}

// Ambil data untuk Edit
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM barang WHERE id=$id");
    $row = $result->fetch_assoc();
}

// Hapus Data
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM barang WHERE id=$id");
}

// Pagination
$limit = 10; // jumlah data per halaman
$page = isset($_GET['halaman']) ? (int) $_GET['halaman'] : 1;
$offset = ($page - 1) * $limit;

// Hitung total data
$sql_count = "SELECT COUNT(*) as total FROM barang";
$result_count = $conn->query($sql_count);
$total_data = $result_count->fetch_assoc()['total'];
$total_halaman = ceil($total_data / $limit);

// Ambil data dengan limitasi untuk pagination
$sql_data = "SELECT * FROM barang ORDER BY nama_barang ASC LIMIT $limit OFFSET $offset";
$data_barang = $conn->query($sql_data);
?>

<!-- HTML -->

<h2>Form Barang</h2>
<form action="form_barang.php" method="POST">
    <input type="hidden" name="id" value="<?=  $row['id'] ?? '' ?>">

    <label for="kode_barang">Kode Barang:</label>
    <input type="text" name="kode_barang" required value="<?= $row['kode_barang'] ?? '' ?>">

    <label for="nama_barang">Nama Barang:</label>
    <input type="text" name="nama_barang" required value="<?= $row['nama_barang'] ?? '' ?>">

    <label for="stok">Stok:</label>
    <input type="number" name="stok" required value="<?= $row['stok'] ?? '' ?>">

    <label for="satuan">Satuan:</label>
    <input type="text" name="satuan" required value="<?= $row['satuan'] ?? '' ?>">

    <label for="stok_minimal">Stok Minimal:</label>
    <input type="number" name="stok_minimal" required value="<?= $row['stok_minimal'] ?? '' ?>">

    <?php if (isset($row['id'])): ?>
        <button type="submit" name="update">Update</button>
    <?php else: ?>
        <button type="submit" name="save">Simpan</button>
    <?php endif; ?>
</form>

<hr>

<h2>Daftar Barang</h2>
<table class="data-table">
    <tr>
        <th>ID</th>
        <th>Kode</th>
        <th>Nama</th>
        <th>Stok</th>
        <th>Satuan</th>
        <th>Stok Minimal</th>
        <th>Aksi</th>
    </tr>
    <?php while ($item = $data_barang->fetch_assoc()): ?>
        <tr>
            <td><?= $item['id'] ?></td>
            <td><?= $item['kode_barang'] ?></td>
            <td><?= $item['nama_barang'] ?></td>
            <td><?= $item['stok'] ?></td>
            <td><?= $item['satuan'] ?></td>
            <td><?= $item['stok_minimal'] ?></td>
            <td class="action-links">
                <a href="form_barang.php?edit=<?= $item['id'] ?>" class="edit-link">Edit</a> |
                <a href="form_barang.php?delete=<?= $item['id'] ?>" class="delete-link"
                    onclick="return confirm('Yakin ingin hapus?')">Hapus</a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

<!-- Navigasi Halaman dengan Tombol "Sebelumnya" dan "Berikutnya" -->
<div class="pagination">
    <!-- Tombol Sebelumnya -->
    <?php if ($page > 1): ?>
        <a href="index.php?page=form_barang&halaman=<?= $page - 1 ?>" class="pagination-prev">Sebelumnya</a>
    <?php else: ?>
        <span class="pagination-prev">Sebelumnya</span>
    <?php endif; ?>

    <!-- Navigasi Halaman -->
    Halaman <?= $page ?> dari <?= $total_halaman ?>

    <!-- Tombol Berikutnya -->
    <?php if ($page < $total_halaman): ?>
        <a href="index.php?page=form_barang&halaman=<?= $page + 1 ?>" class="pagination-next">Berikutnya</a>
    <?php else: ?>
        <span class="pagination-next">Berikutnya</span>
    <?php endif; ?>
</div>


<?php $conn->close(); ?>