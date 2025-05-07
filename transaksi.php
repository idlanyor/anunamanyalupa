<?php
// Koneksi ke database
include 'config/koneksi.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Handle delete transaction
if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];

    // Get transaction details first
    $query = "SELECT p.*, b.stok FROM persediaan p 
              JOIN barang b ON p.id_barang = b.id 
              WHERE p.id = $id_hapus";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        $id_barang = $data['id_barang'];
        $jumlah = $data['jumlah'];
        $tipe = $data['tipe'];
        $stok_sekarang = $data['stok'];

        // Update stock based on transaction type
        if ($tipe == 'masuk') {
            $stok_baru = $stok_sekarang - $jumlah;
        } else {
            $stok_baru = $stok_sekarang + $jumlah;
        }

        // Start transaction
        mysqli_begin_transaction($conn);

        try {
            // Update stock
            $update_stok = "UPDATE barang SET stok = $stok_baru WHERE id = $id_barang";
            mysqli_query($conn, $update_stok);

            // Delete transaction
            $delete_transaksi = "DELETE FROM persediaan WHERE id = $id_hapus";
            mysqli_query($conn, $delete_transaksi);

            // Commit transaction
            mysqli_commit($conn);

            $_SESSION['success'] = "Transaksi berhasil dihapus";
        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($conn);
            $_SESSION['error'] = "Gagal menghapus transaksi: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "Transaksi tidak ditemukan";
    }

    header('Location: index.php?page=transaksi');
    exit;
}

// Simpan data transaksi
if (isset($_POST['submit'])) {
    $nama_barang = $conn->real_escape_string(trim($_POST['nama_barang']));
    $kode_barang = $conn->real_escape_string(trim($_POST['kode_barang']));
    $tipe = $_POST['tipe'];
    $jumlah = intval($_POST['jumlah']);

    if (!$nama_barang || !$kode_barang) {
        echo "<script>alert('Nama dan kode barang wajib diisi!');</script>";
    } else {
        // Cek apakah barang sudah ada
        $cek = $conn->query("SELECT * FROM barang WHERE kode_barang = '$kode_barang' LIMIT 1");
        if ($cek->num_rows > 0) {
            $barang = $cek->fetch_assoc();
            $id_barang = $barang['id'];
            $stok_sekarang = (int) $barang['stok'];
        } else {
            // Tambah barang baru
            $conn->query("INSERT INTO barang (nama_barang, kode_barang, stok) VALUES ('$nama_barang', '$kode_barang', 0)");
            $id_barang = $conn->insert_id;
            $stok_sekarang = 0;
        }

        $stok_baru = ($tipe === 'masuk') ? $stok_sekarang + $jumlah : $stok_sekarang - $jumlah;

        if ($stok_baru < 0) {
            echo "<script>alert('Stok tidak cukup!');</script>";
        } else {
            $conn->query("INSERT INTO persediaan (id_barang, tipe, jumlah) 
                          VALUES ($id_barang, '$tipe', $jumlah)");
            $conn->query("UPDATE barang SET stok = $stok_baru WHERE id = $id_barang");
            echo "<script>alert('Data berhasil disimpan'); window.location.href='index.php?page=transaksi';</script>";
        }
    }
}

// Ambil semua barang buat keperluan JS auto-fill
$barang_data = [];
$result = $conn->query("SELECT nama_barang, kode_barang FROM barang");
while ($row = $result->fetch_assoc()) {
    $barang_data[$row['nama_barang']] = $row['kode_barang'];
}

// Filter pencarian
$keyword = $_GET['keyword'] ?? '';
$tanggal_dari = $_GET['tanggal_dari'] ?? '';
$tanggal_sampai = $_GET['tanggal_sampai'] ?? '';
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

$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$start = ($page - 1) * $limit;

$order_by = $_GET['order_by'] ?? 'p.tanggal';
$order_dir = ($_GET['order_dir'] ?? '') === 'asc' ? 'asc' : 'desc';

$query = "SELECT p.*, b.nama_barang, b.kode_barang FROM persediaan p 
          JOIN barang b ON p.id_barang = b.id 
          $where_sql 
          ORDER BY $order_by $order_dir 
          LIMIT $start, $limit";
$riwayat = $conn->query($query);

$count_q = $conn->query("SELECT COUNT(*) as total FROM persediaan p JOIN barang b ON p.id_barang = b.id $where_sql");
$total_data = $count_q->fetch_assoc()['total'] ?? 0;
$total_page = ceil($total_data / $limit);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Transaksi Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4bb543;
            --danger-color: #dc3545;
            --background: #f8f9fa;
            --card-bg: #ffffff;
            --text-color: #2b2d42;
            --border-radius: 12px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--background);
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            margin: 0 auto;
            padding: 20px;
        }

        .card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 20px;
            border: none;
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
            padding: 15px 20px;
        }

        .card-header h4 {
            margin: 0;
            font-weight: 600;
        }

        .form-control {
            border-radius: 8px;
            padding: 10px 15px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
        }

        .btn {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-2px);
        }

        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }

        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
            transform: translateY(-2px);
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: var(--text-color);
            border-bottom: 2px solid #e9ecef;
        }

        .table td {
            vertical-align: middle;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .table-hover tbody tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }

        .loading {
            position: relative;
        }

        .loading:after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .loading:before {
            content: 'Loading...';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1;
            color: var(--primary-color);
            font-weight: 500;
        }

        .pagination {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .pagination .btn {
            padding: 8px 12px;
        }

        .pagination span {
            color: var(--text-color);
            font-weight: 500;
        }

        .search-box {
            position: relative;
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }

        .search-box input {
            padding-left: 40px;
        }

        .date-range {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .date-range input {
            flex: 1;
        }

        .form-group label {
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--text-color);
        }

        .total-data {
            background-color: var(--primary-color);
            color: white;
            padding: 8px 15px;
            border-radius: 8px;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .date-range {
                flex-direction: column;
            }

            .pagination {
                flex-direction: column;
                align-items: stretch;
            }

            .card-header .row {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-exchange-alt me-2"></i>Transaksi Barang
                </h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <form method="POST" class="mb-4">
                            <div class="row g-3">
                                <div class="form-group">
                                    <label>Nama Barang</label>
                                    <select class="form-control select2" id="nama_barang" name="nama_barang" style="height: 45px;" required>
                                        <option value="">Pilih Barang</option>
                                        <?php
                                        $barang_query = "SELECT id, nama_barang, kode_barang FROM barang ORDER BY nama_barang";
                                        $barang_result = mysqli_query($conn, $barang_query);
                                        while ($barang = mysqli_fetch_assoc($barang_result)) {
                                            echo '<option value="' . $barang['id'] . '" data-kode="' . $barang['kode_barang'] . '">' .
                                                htmlspecialchars($barang['nama_barang']) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Kode Barang</label>
                                    <input type="text" id="kode_barang" name="kode_barang" class="form-control" style="height: 45px;" readonly required>
                                </div>
                                <div class="form-group">
                                    <label>Tipe</label>
                                    <select name="tipe" class="form-control" style="height: 45px;" required>
                                        <option value="masuk">Masuk</option>
                                        <option value="keluar">Keluar</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Jumlah</label>
                                    <input type="number" name="jumlah" class="form-control" style="height: 45px;" required>
                                </div>
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" name="submit" class="btn btn-primary w-100" style="height: 45px;">
                                        <i class="fas fa-save me-2"></i>Simpan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-8">


                        <div class="card">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        <div class="search-box">
                                            <i class="fas fa-search"></i>
                                            <input type="text" id="searchInput" class="form-control" placeholder="Cari nama/kode barang...">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <button id="resetSearch" class="btn btn-secondary w-100">
                                            <i class="fas fa-undo me-2"></i>Reset
                                        </button>
                                    </div>
                                    <div class="col-md-3">
                                        <button id="exportExcel" class="btn btn-success w-100">
                                            <i class="fas fa-file-excel me-2"></i>Export Excel
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="tableContainer">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Nama Barang</th>
                                                <th>Kode</th>
                                                <th>Tanggal</th>
                                                <th>Tipe</th>
                                                <th>Jumlah</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tableBody">
                                            <?php if ($riwayat->num_rows): ?>
                                                <?php while ($r = $riwayat->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($r['nama_barang']) ?></td>
                                                        <td><?= htmlspecialchars($r['kode_barang']) ?></td>
                                                        <td><?= htmlspecialchars($r['tanggal']) ?></td>
                                                        <td>
                                                            <span class="badge bg-<?= $r['tipe'] == 'masuk' ? 'success' : 'danger' ?>">
                                                                <?= ucfirst($r['tipe']) ?>
                                                            </span>
                                                        </td>
                                                        <td><?= $r['jumlah'] ?></td>
                                                        <td>
                                                            <a href="hapus_transaksi.php?id=<?= $r['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus?')">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="7" class="text-center py-4">
                                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
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
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Global variables
        let ajaxRequest = null;
        let debounceTimer = null;

        function loadData(page = 1) {
            // Cancel any pending AJAX request
            if (ajaxRequest) {
                ajaxRequest.abort();
            }

            // Show loading state
            $('#tableContainer').addClass('loading');

            // Make new AJAX request
            ajaxRequest = $.ajax({
                url: 'ajax_transaksi.php',
                type: 'GET',
                data: {
                    keyword: $('#searchInput').val(),
                    tanggal_dari: $('#dateFrom').val(),
                    tanggal_sampai: $('#dateTo').val(),
                    page: page
                },
                success: function(response) {
                    $('#tableContainer').html(response);
                },
                error: function(xhr, status, error) {
                    if (status !== 'abort') {
                        console.error('Error:', error);
                    }
                },
                complete: function() {
                    $('#tableContainer').removeClass('loading');
                    ajaxRequest = null;
                }
            });
        }

        $(document).ready(function() {
            // Handle form submission
            $('#searchForm').on('submit', function(e) {
                e.preventDefault();
                loadData();
            });

            // Handle pagination clicks with event delegation
            $(document).on('click', '#pagination a', function(e) {
                e.preventDefault();
                const page = $(this).attr('href').split('page=')[1];
                loadData(page);
            });

            // Debounced input handler
            function debounceSearch() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(loadData, 300);
            }

            // Handle input changes with debounce
            $('#searchInput').on('input', debounceSearch);
            $('#dateFrom, #dateTo').on('change', debounceSearch);

            // Reset search
            $('#resetSearch').on('click', function() {
                $('#searchInput').val('');
                $('#dateFrom').val('');
                $('#dateTo').val('');
                loadData();
            });

            // Handle Excel Export
            $('#exportExcel').on('click', function() {
                const keyword = $('#searchInput').val();
                const tanggalDari = $('#dateFrom').val();
                const tanggalSampai = $('#dateTo').val();

                // Build the query for export
                let where = [];
                if (keyword) {
                    where.push(`(b.nama_barang LIKE '%${keyword}%' OR b.kode_barang LIKE '%${keyword}%')`);
                }
                if (tanggalDari && tanggalSampai) {
                    where.push(`DATE(p.tanggal) BETWEEN '${tanggalDari}' AND '${tanggalSampai}'`);
                } else if (tanggalDari) {
                    where.push(`DATE(p.tanggal) >= '${tanggalDari}'`);
                } else if (tanggalSampai) {
                    where.push(`DATE(p.tanggal) <= '${tanggalSampai}'`);
                }

                const whereClause = where.length ? 'WHERE ' + where.join(' AND ') : '';

                // Create separate queries for masuk and keluar
                const queryMasuk = btoa(`SELECT p.*, b.nama_barang, b.kode_barang 
                    FROM persediaan p 
                    JOIN barang b ON p.id_barang = b.id 
                    ${whereClause} AND p.tipe = 'masuk'
                    ORDER BY p.tanggal DESC`);

                const queryKeluar = btoa(`SELECT p.*, b.nama_barang, b.kode_barang 
                    FROM persediaan p 
                    JOIN barang b ON p.id_barang = b.id 
                    ${whereClause} AND p.tipe = 'keluar'
                    ORDER BY p.tanggal DESC`);

                // Submit the form with the queries
                const form = $('<form>', {
                    'method': 'POST',
                    'action': 'export_excel.php',
                    'target': '_blank'
                });

                form.append($('<input>', {
                    'name': 'query_masuk',
                    'value': queryMasuk,
                    'type': 'hidden'
                }));

                form.append($('<input>', {
                    'name': 'query_keluar',
                    'value': queryKeluar,
                    'type': 'hidden'
                }));

                $('body').append(form);
                form.submit();
                form.remove();
            });

            // Initialize Select2
            $('.select2').select2({
                theme: 'bootstrap-5',
                placeholder: 'Pilih Barang',
                allowClear: true
            });

            // Auto-fill kode barang when nama barang is selected
            $('#nama_barang').on('change', function() {
                var selectedOption = $(this).find('option:selected');
                var kodeBarang = selectedOption.data('kode');
                $('#kode_barang').val(kodeBarang);
            });
        });
    </script>
</body>

</html>

<?php $conn->close(); ?>