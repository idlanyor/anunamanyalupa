-- Database Backup
-- Generated: 2025-05-07 12:53:24


-- Table structure for table `barang`
CREATE TABLE `barang` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kode_barang` varchar(20) NOT NULL,
  `nama_barang` varchar(100) NOT NULL,
  `stok` int NOT NULL,
  `satuan` varchar(20) NOT NULL,
  `stok_minimal` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE` (`kode_barang`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3;

-- Data for table `barang`
INSERT INTO `barang` VALUES ('1','RK0001','L.A Gold','15','Pcs','10');
INSERT INTO `barang` VALUES ('3','RK0002','L.A Black','0','Pcs','10');
INSERT INTO `barang` VALUES ('7','RK0003','L.A Red','20','Pcs','10');


-- Table structure for table `persediaan`
CREATE TABLE `persediaan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_barang` int NOT NULL,
  `tanggal` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tipe` enum('masuk','keluar') NOT NULL,
  `jumlah` int NOT NULL,
  `keterangan` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3;

-- Data for table `persediaan`
INSERT INTO `persediaan` VALUES ('2','7','2025-04-18 15:02:01','masuk','20','');
INSERT INTO `persediaan` VALUES ('5','3','2025-04-18 15:27:04','keluar','5','');


-- Table structure for table `settings`
CREATE TABLE `settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_toko` varchar(255) DEFAULT NULL,
  `alamat_toko` text,
  `stok_minimal_default` int DEFAULT NULL,
  `logo_toko` varchar(255) DEFAULT NULL,
  `format_tanggal` varchar(10) DEFAULT NULL,
  `tema` enum('light','dark') DEFAULT 'light',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

-- Data for table `settings`
INSERT INTO `settings` VALUES ('1','TOKO NOVI','Purwokerto','0','logo_1746259465.png','d-m-Y','light');


-- Table structure for table `users`
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb3;

-- Data for table `users`
INSERT INTO `users` VALUES ('6','admin','admin123');
INSERT INTO `users` VALUES ('11','admin1','$2y$10$gN3OVDh.93licVzl7VG82eo847h4I33DlU0jXm8jPD0FUPhO0JitG');

