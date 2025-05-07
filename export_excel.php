<?php
// Koneksi ke database
include 'config/koneksi.php';

// Function to create Excel content
function createExcelContent($data, $title) {
    $content = "<table border='1'>
    <tr><th colspan='6' style='text-align:center;font-weight:bold;'>$title</th></tr>
    <tr><th>No</th><th>Nama Barang</th><th>Kode Barang</th><th>Tanggal</th><th>Jumlah</th><th>Keterangan</th></tr>";
    
    $no = 1;
    while ($r = $data->fetch_assoc()) {
        $content .= "<tr>
            <td>$no</td>
            <td>{$r['nama_barang']}</td>
            <td>{$r['kode_barang']}</td>
            <td>{$r['tanggal']}</td>
            <td>{$r['jumlah']}</td>
            <td>{$r['keterangan']}</td>
        </tr>";
        $no++;
    }
    $content .= "</table>";
    return $content;
}

// Set headers for Excel download
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=transaksi_barang.xls");

// Get queries from POST data
$query_masuk = base64_decode($_POST['query_masuk']);
$query_keluar = base64_decode($_POST['query_keluar']);

// Execute queries
$data_masuk = $conn->query($query_masuk);
$data_keluar = $conn->query($query_keluar);

// Create Excel content with multiple sheets
echo "<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:x='urn:schemas-microsoft-com:office:excel' xmlns='http://www.w3.org/TR/REC-html40'>";
echo "<head>";
echo "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>";
echo "<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Transaksi</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->";
echo "</head>";
echo "<body>";

// Sheet 1: Stok Masuk
echo "<h1>Stok Masuk</h1>";
echo createExcelContent($data_masuk, "Laporan Stok Masuk");

// Sheet 2: Stok Keluar
echo "<h1>Stok Keluar</h1>";
echo createExcelContent($data_keluar, "Laporan Stok Keluar");

echo "</body></html>";

$conn->close();
?>
