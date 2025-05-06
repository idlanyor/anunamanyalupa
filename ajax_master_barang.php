<?php
include 'config/koneksi.php';

// Get search parameters
$keyword = $_GET['keyword'] ?? '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$start = ($page - 1) * $limit;

// Build where clause
$where = '';
if (!empty($keyword)) {
    $escaped = $conn->real_escape_string($keyword);
    $where = "WHERE nama_barang LIKE '%$escaped%' OR kode_barang LIKE '%$escaped%'";
}

// Get total count
$count_q = $conn->query("SELECT COUNT(*) as total FROM barang $where");
$total_data = $count_q->fetch_assoc()['total'] ?? 0;
$total_page = ceil($total_data / $limit);

// Get data
$query = "SELECT * FROM barang $where ORDER BY nama_barang ASC LIMIT $start, $limit";
$barang = $conn->query($query);
?>

<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>No</th>
            <th>Kode Barang</th>
            <th>Nama Barang</th>
            <th>Stok</th>
            <th>Satuan</th>
            <th>Stok Minimal</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($barang->num_rows): ?>
            <?php 
            $no = $start + 1;
            while ($b = $barang->fetch_assoc()): 
                $status = ($b['stok'] >= $b['stok_minimal']) ? 'Aman' : 'Perlu Restok';
                $status_class = ($status == 'Aman') ? 'success' : 'danger';
            ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($b['kode_barang']) ?></td>
                    <td><?= htmlspecialchars($b['nama_barang']) ?></td>
                    <td><?= $b['stok'] ?></td>
                    <td><?= htmlspecialchars($b['satuan']) ?></td>
                    <td><?= $b['stok_minimal'] ?></td>
                    <td>
                        <span class="badge bg-<?= $status_class ?>">
                            <?= $status ?>
                        </span>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="text-center py-4">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Data tidak ditemukan</p>
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<div class="d-flex justify-content-between align-items-center mt-3">
    <div class="total-data">
        <i class="fas fa-database me-2"></i>Total Data: <?= $total_data ?>
    </div>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-chevron-left"></i>
            </a>
        <?php endif; ?>
        
        <span>Halaman <?= $page ?> dari <?= $total_page ?></span>
        
        <?php if ($page < $total_page): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-chevron-right"></i>
            </a>
        <?php endif; ?>
    </div>
</div> 
 