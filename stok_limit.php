<?php
// Koneksi ke database
include 'config/koneksi.php';  // Pastikan kamu sudah punya koneksi ke database

// Tentukan jumlah data per halaman
$per_page = 10;

// Ambil halaman saat ini dari parameter URL (default ke 1)
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$page = max(1, $page); // Supaya tidak bisa halaman < 1

// Hitung offset (data mulai dari baris ke berapa)
$offset = ($page - 1) * $per_page;

// Query data stok kurang dari stok minimal dengan LIMIT dan OFFSET
$query = "SELECT * FROM barang WHERE stok < stok_minimal LIMIT $offset, $per_page";
$result = mysqli_query($conn, $query);
if (!$result) {
    die("Query gagal: " . mysqli_error($conn));
}

// Query untuk menghitung total data
$total_query = "SELECT COUNT(*) as total FROM barang WHERE stok < stok_minimal";
$total_result = mysqli_query($conn, $total_query);
if (!$total_result) {
    die("Query total gagal: " . mysqli_error($conn));
}
$total_row = mysqli_fetch_assoc($total_result);
$total_data = $total_row['total'];

// Hitung total halaman
$total_pages = ceil($total_data / $per_page);
?>

<div class="container card">
    <!-- Tabel -->
     <h2 class="text-center mb-2">Daftar Stok Menipis</h2>
    <table class="data-table ">
        <thead>
            <tr>
                <th>ID Barang</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Stok</th>
                <th>Satuan</th>
                <th>Stok Minimal</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['kode_barang'] ?></td>
                        <td><?= $row['nama_barang'] ?></td>
                        <td><?= $row['stok'] ?></td>
                        <td><?= $row['satuan'] ?></td>
                        <td><?= $row['stok_minimal'] ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">Tidak ada barang dengan stok di bawah batas minimal.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <!-- Navigasi Pagination -->
    <nav aria-label="Page navigation">
        <ul class="pagination">
            <!-- Tombol Sebelumnya -->
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $page - 1 ?>">Sebelumnya</a>
            </li>

            <!-- Nomor halaman -->
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <!-- Tombol Berikutnya -->
            <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $page + 1 ?>">Berikutnya</a>
            </li>
        </ul>
    </nav>
</div>

<?php mysqli_close($conn); ?>