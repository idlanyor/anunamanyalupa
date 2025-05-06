<?php
include 'config/koneksi.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Set backup directory
$backup_dir = 'backups/';
if (!file_exists($backup_dir)) {
    mkdir($backup_dir, 0777, true);
}

// Generate backup filename
$filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
$filepath = $backup_dir . $filename;

// Get all tables
$tables = array();
$result = mysqli_query($conn, "SHOW TABLES");
while ($row = mysqli_fetch_row($result)) {
    $tables[] = $row[0];
}

// Create backup file
$handle = fopen($filepath, 'w');

// Write header
fwrite($handle, "-- Database Backup\n");
fwrite($handle, "-- Generated: " . date('Y-m-d H:i:s') . "\n\n");

// Write table structure and data
foreach ($tables as $table) {
    // Get table structure
    $result = mysqli_query($conn, "SHOW CREATE TABLE `$table`");
    $row = mysqli_fetch_row($result);
    fwrite($handle, "\n-- Table structure for table `$table`\n");
    fwrite($handle, $row[1] . ";\n\n");

    // Get table data
    $result = mysqli_query($conn, "SELECT * FROM `$table`");
    if (mysqli_num_rows($result) > 0) {
        fwrite($handle, "-- Data for table `$table`\n");
        while ($row = mysqli_fetch_assoc($result)) {
            $values = array();
            foreach ($row as $value) {
                $values[] = "'" . mysqli_real_escape_string($conn, $value) . "'";
            }
            fwrite($handle, "INSERT INTO `$table` VALUES (" . implode(',', $values) . ");\n");
        }
        fwrite($handle, "\n");
    }
}

fclose($handle);

// Set success message
$_SESSION['success'] = "Backup database berhasil dibuat: " . $filename;

// Redirect back to settings page
header('Location: pengaturan.php');
exit;
?> 