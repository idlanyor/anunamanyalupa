<?php
include 'config/koneksi.php';

// Check if settings table has any records
$check = mysqli_query($conn, "SELECT id FROM settings LIMIT 1");
if (mysqli_num_rows($check) == 0) {
    // Insert default settings
    $sql = "INSERT INTO settings (
        nama_toko,
        alamat_toko,
        stok_minimal_default,
        format_tanggal,
        tema
    ) VALUES (
        'Nama Toko',
        'Alamat Toko',
        5,
        'd-m-Y',
        'light'
    )";
    
    if (mysqli_query($conn, $sql)) {
        echo "Pengaturan default berhasil dibuat";
    } else {
        echo "Gagal membuat pengaturan default: " . mysqli_error($conn);
    }
} else {
    echo "Pengaturan sudah ada";
}
