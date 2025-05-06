<?php
// Koneksi ke database
include 'config/koneksi.php';

$query = base64_decode($_POST['query']);
$data = $conn->query($query);

header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=transaksi.xls");

echo "<table border='1'>
<tr><th>No</th><th>Nama Barang</th><th>Tanggal</th><th>Tipe</th><th>Jumlah</th><th>Keterangan</th></tr>";
$no = 1;
while ($r = $data->fetch_assoc()) {
    echo "<tr>
            <td>$no</td>
            <td>{$r['nama_barang']}</td>
            <td>{$r['tanggal']}</td>
            <td>{$r['tipe']}</td>
            <td>{$r['jumlah']}</td>
            <td>{$r['keterangan']}</td>
          </tr>";
    $no++;
}
echo "</table>";
