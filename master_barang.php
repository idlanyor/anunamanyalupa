<?php
// Koneksi ke database

include 'config/koneksi.php';

// Get initial data
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

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Master Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
            max-width: 1400px;
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
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
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

        .total-data {
            background-color: var(--primary-color);
            color: white;
            padding: 8px 15px;
            border-radius: 8px;
            font-weight: 500;
        }

        .badge {
            padding: 8px 12px;
            font-weight: 500;
        }

        @media (max-width: 768px) {
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
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h4 class="mb-0">
                            <i class="fas fa-boxes me-2"></i>Master Barang
                        </h4>
                    </div>
                    <div class="col-md-6">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInput" class="form-control" 
                                   placeholder="Cari nama atau kode barang..."
                                   value="<?= htmlspecialchars($keyword) ?>">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="tableContainer">
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
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
                url: 'ajax_master_barang.php',
                type: 'GET',
                data: {
                    keyword: $('#searchInput').val(),
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
            // Handle input changes with debounce
            $('#searchInput').on('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(loadData, 300);
            });

            // Handle pagination clicks with event delegation
            $(document).on('click', '.pagination a', function(e) {
                e.preventDefault();
                const page = $(this).attr('href').split('page=')[1];
                loadData(page);
            });
        });
    </script>
</body>
</html>