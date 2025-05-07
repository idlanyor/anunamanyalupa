<?php
include 'config/koneksi.php';

// Handle export request
if (isset($_GET['export'])) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="dashboard_export_' . date('Y-m-d') . '.xls"');
    
    // Get all data for export
    $export_barang = $conn->query("SELECT * FROM barang");
    $export_transaksi = $conn->query("
        SELECT p.*, b.nama_barang, b.kode_barang 
        FROM persediaan p 
        JOIN barang b ON p.id_barang = b.id 
        ORDER BY p.tanggal DESC
    ");
    
    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <style>
            table { border-collapse: collapse; width: 100%; }
            th { background-color: #4e73df; color: white; font-weight: bold; text-align: center; padding: 8px; }
            td { border: 1px solid #ddd; padding: 8px; text-align: center; }
            .header { background-color: #4e73df; color: white; font-weight: bold; text-align: center; padding: 8px; }
        </style>
    </head>
    <body>';
    
    // Data Barang
    echo '<table>
        <tr><th colspan="5" class="header">DATA BARANG</th></tr>
        <tr>
            <th>Kode Barang</th>
            <th>Nama Barang</th>
            <th>Stok</th>
            <th>Satuan</th>
            <th>Stok Minimal</th>
        </tr>';
    
    while ($row = $export_barang->fetch_assoc()) {
        echo '<tr>
            <td>' . htmlspecialchars($row['kode_barang']) . '</td>
            <td>' . htmlspecialchars($row['nama_barang']) . '</td>
            <td>' . $row['stok'] . '</td>
            <td>' . htmlspecialchars($row['satuan']) . '</td>
            <td>' . $row['stok_minimal'] . '</td>
        </tr>';
    }
    
    echo '</table><br><br>';
    
    // Data Transaksi
    echo '<table>
        <tr><th colspan="6" class="header">DATA TRANSAKSI</th></tr>
        <tr>
            <th>Tanggal</th>
            <th>Kode Barang</th>
            <th>Nama Barang</th>
            <th>Jumlah</th>
            <th>Tipe</th>
            <th>Keterangan</th>
        </tr>';
    
    while ($row = $export_transaksi->fetch_assoc()) {
        echo '<tr>
            <td>' . date('d/m/Y', strtotime($row['tanggal'])) . '</td>
            <td>' . htmlspecialchars($row['kode_barang']) . '</td>
            <td>' . htmlspecialchars($row['nama_barang']) . '</td>
            <td>' . $row['jumlah'] . '</td>
            <td>' . ucfirst($row['tipe']) . '</td>
            <td>' . htmlspecialchars($row['keterangan']) . '</td>
        </tr>';
    }
    
    echo '</table></body></html>';
    exit;
}

// Get statistics
$total_barang = $conn->query("SELECT COUNT(*) as total FROM barang")->fetch_assoc()['total'];
$total_transaksi = $conn->query("SELECT COUNT(*) as total FROM persediaan")->fetch_assoc()['total'];
$barang_restok = $conn->query("SELECT COUNT(*) as total FROM barang WHERE stok <= stok_minimal")->fetch_assoc()['total'];

// Get recent transactions
$recent_transaksi = $conn->query("
    SELECT p.*, b.nama_barang, b.kode_barang 
    FROM persediaan p 
    JOIN barang b ON p.id_barang = b.id 
    ORDER BY p.tanggal DESC 
    LIMIT 5
");

// Get stock status
$stock_status = $conn->query("
    SELECT 
        COUNT(CASE WHEN stok > stok_minimal THEN 1 END) as aman,
        COUNT(CASE WHEN stok <= stok_minimal THEN 1 END) as perlu_restok
    FROM barang
")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Inventori</title>
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

        .sidebar {
            background: linear-gradient(180deg, var(--primary-color) 0%, #224abe 100%);
            min-height: 100vh;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 1rem;
            font-weight: 600;
        }

        .sidebar .nav-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar .nav-link i {
            margin-right: 0.5rem;
        }

        .card {
            border: none;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
        }

        .stat-card {
            border-left: 0.25rem solid;
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card.primary {
            border-left-color: var(--primary-color);
        }

        .stat-card.success {
            border-left-color: var(--success-color);
        }

        .stat-card.info {
            border-left-color: var(--info-color);
        }

        .stat-card.warning {
            border-left-color: var(--warning-color);
        }

        .stat-card .stat-icon {
            color: rgba(0, 0, 0, 0.15);
            font-size: 2rem;
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        .table th {
            border-top: none;
            font-weight: 600;
            color: var(--dark-color);
        }

        .badge {
            padding: 0.5em 0.75em;
            font-weight: 600;
        }

        .btn-export {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .btn-export:hover {
            background-color: #2e59d9;
            color: white;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">

            <!-- Main Content -->
            <div class="col-md-12 col-lg-12 ms-auto px-4 py-3">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card primary h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Barang
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= number_format($total_barang) ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-boxes stat-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card success h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Total Transaksi
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= number_format($total_transaksi) ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-exchange-alt stat-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card warning h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Perlu Restok
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= number_format($barang_restok) ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-exclamation-triangle stat-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card info h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Status Stok
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= number_format($stock_status['aman']) ?> Aman
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-chart-pie stat-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts and Tables -->
                <div class="row">
                    <!-- Recent Transactions -->
                    <div class="col-xl-8 col-lg-7">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Transaksi Terakhir</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Barang</th>
                                                <th>Kode</th>
                                                <th>Jumlah</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($t = $recent_transaksi->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?= date('d/m/Y', strtotime($t['tanggal'])) ?></td>
                                                    <td><?= htmlspecialchars($t['nama_barang']) ?></td>
                                                    <td><?= htmlspecialchars($t['kode_barang']) ?></td>
                                                    <td><?= $t['jumlah'] ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= $t['tipe'] == 'masuk' ? 'success' : 'danger' ?>">
                                                            <?= ucfirst($t['tipe']) ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stock Status -->
                    <div class="col-xl-4 col-lg-5">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Status Stok</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="stockChart"></canvas>
                                </div>
                                <div class="mt-4 text-center small">
                                    <span class="mr-2">
                                        <i class="fas fa-circle text-success"></i> Aman
                                    </span>
                                    <span class="mr-2">
                                        <i class="fas fa-circle text-danger"></i> Perlu Restok
                                    </span>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Stock Status Chart
        const ctx = document.getElementById('stockChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Aman', 'Perlu Restok'],
                datasets: [{
                    data: [<?= $stock_status['aman'] ?>, <?= $stock_status['perlu_restok'] ?>],
                    backgroundColor: ['#1cc88a', '#e74a3b'],
                    hoverBackgroundColor: ['#17a673', '#be2617'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                cutout: '80%',
            }
        });
    </script>
</body>
</html> 