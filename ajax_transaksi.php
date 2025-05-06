<?php
include 'config/koneksi.php';

// Get search parameters
$keyword = $_GET['keyword'] ?? '';
$tanggal_dari = $_GET['tanggal_dari'] ?? '';
$tanggal_sampai = $_GET['tanggal_sampai'] ?? '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$start = ($page - 1) * $limit;

// Build where clause
$where = [];
if (!empty($keyword)) {
    $escaped = $conn->real_escape_string($keyword);
    $where[] = "(b.nama_barang LIKE '%$escaped%' OR b.kode_barang LIKE '%$escaped%')";
}
if (!empty($tanggal_dari) && !empty($tanggal_sampai)) {
    $dari = $conn->real_escape_string($tanggal_dari);
    $sampai = $conn->real_escape_string($tanggal_sampai);
    $where[] = "DATE(p.tanggal) BETWEEN '$dari' AND '$sampai'";
} elseif (!empty($tanggal_dari)) {
    $dari = $conn->real_escape_string($tanggal_dari);
    $where[] = "DATE(p.tanggal) >= '$dari'";
} elseif (!empty($tanggal_sampai)) {
    $sampai = $conn->real_escape_string($tanggal_sampai);
    $where[] = "DATE(p.tanggal) <= '$sampai'";
}

$where_sql = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total count
$count_q = $conn->query("SELECT COUNT(*) as total FROM persediaan p JOIN barang b ON p.id_barang = b.id $where_sql");
$total_data = $count_q->fetch_assoc()['total'] ?? 0;
$total_page = ceil($total_data / $limit);

// Get data
$query = "SELECT p.*, b.nama_barang, b.kode_barang FROM persediaan p 
          JOIN barang b ON p.id_barang = b.id 
          $where_sql 
          ORDER BY p.tanggal DESC 
          LIMIT $start, $limit";
$riwayat = $conn->query($query);
?>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Nama Barang</th>
            <th>Kode</th>
            <th>Tanggal</th>
            <th>Tipe</th>
            <th>Jumlah</th>
            <th>Keterangan</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($riwayat->num_rows): ?>
            <?php while ($r = $riwayat->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($r['nama_barang']) ?></td>
                    <td><?= htmlspecialchars($r['kode_barang']) ?></td>
                    <td><?= htmlspecialchars($r['tanggal']) ?></td>
                    <td><?= ucfirst($r['tipe']) ?></td>
                    <td><?= $r['jumlah'] ?></td>
                    <td><?= htmlspecialchars($r['keterangan']) ?></td>
                    <td>
                        <a href="?hapus=<?= $r['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="text-center">Data tidak ditemukan</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<div class="d-flex justify-content-between align-items-center mt-3">
    <div>
        <span>Total Data: <?= $total_data ?></span>
    </div>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="btn btn-primary btn-sm me-2">
                <i class="fas fa-chevron-left"></i>
            </a>
        <?php endif; ?>
        
        <span class="mx-2">Halaman <?= $page ?> dari <?= $total_page ?></span>
        
        <?php if ($page < $total_page): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="btn btn-primary btn-sm ms-2">
                <i class="fas fa-chevron-right"></i>
            </a>
        <?php endif; ?>
    </div>
</div> 