<?php
include 'config/koneksi.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID transaksi tidak valid";
    header('Location: index.php?page=transaksi');
    exit;
}

$id_hapus = (int)$_GET['id'];

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
