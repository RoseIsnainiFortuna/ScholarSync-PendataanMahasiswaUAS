-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for scholarsync
CREATE DATABASE IF NOT EXISTS `scholarsync` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `scholarsync`;

-- Dumping structure for table scholarsync.fakultas
CREATE TABLE IF NOT EXISTS `fakultas` (
  `id_fakultas` int NOT NULL AUTO_INCREMENT,
  `nama_fakultas` varchar(100) NOT NULL,
  PRIMARY KEY (`id_fakultas`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table scholarsync.fakultas: ~4 rows (approximately)
INSERT INTO `fakultas` (`id_fakultas`, `nama_fakultas`) VALUES
	(1, 'Fakultas Biologi'),
	(2, 'Fakultas Farmasi'),
	(3, 'Fakultas Geografi'),
	(4, 'Fakultas Kedokteran, Kesehatan Masyarakat, dan Keperawatan (FKKMK)'),
	(5, 'Fakultas Kedokteran Gigi'),
	(6, 'Fakultas Kedokteran Hewan'),
	(7, 'Fakultas Matematika dan Ilmu Pengetahuan Alam (FMIPA)'),
	(8, 'Fakultas Pertanian'),
	(9, 'Fakultas Kehutanan'),
	(10, 'Fakultas Teknologi Pertanian'),
	(11, 'Fakultas Teknik'),
	(12, 'Fakultas Ekonomika dan Bisnis (FEB)'),
	(13, 'Fakultas Hukum'),
	(14, 'Fakultas Ilmu Budaya (FIB)'),
	(15, 'Fakultas Ilmu Sosial dan Ilmu Politik (FISIPOL)'),
	(16, 'Fakultas Filsafat'),
	(17, 'Fakultas Psikologi'),
	(18, 'Sekolah Vokasi');

-- Dumping structure for table scholarsync.mahasiswa
CREATE TABLE IF NOT EXISTS `mahasiswa` (
  `NIM` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `id_prodi` int NOT NULL,
  `angkatan` year NOT NULL,
  `status_aktif` enum('Aktif','Cuti','Probation','Lulus','DO') NOT NULL DEFAULT 'Aktif',
  `ipk` decimal(3,2) DEFAULT '0.00',
  PRIMARY KEY (`NIM`),
  KEY `id_prodi` (`id_prodi`),
  CONSTRAINT `mahasiswa_ibfk_1` FOREIGN KEY (`id_prodi`) REFERENCES `prodi` (`id_prodi`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table scholarsync.mahasiswa: ~8 rows (approximately)
INSERT INTO `mahasiswa` (`NIM`, `nama`, `id_prodi`, `angkatan`, `status_aktif`, `ipk`) VALUES
	('2019300215', 'Eko Prasetyo', 10, '2019', 'Lulus', 0.00),
	('2020100992', 'Diana Lestari', 2, '2020', 'Aktif', 0.00),
	('2021100075', 'Gilang Ramadan', 7, '2021', 'Aktif', 0.00),
	('2021200589', 'Siti Aminah', 4, '2021', 'Aktif', 0.00),
	('2022100010', 'Farida Nurul', 1, '2022', 'Aktif', 0.00),
	('2022400321', 'Budi Santoso', 6, '2022', 'DO', 0.00),
	('2023100142', 'Ahmad Fauzi', 1, '2023', 'Aktif', 0.00),
	('25/556660/SV/25984', 'Rose Isnaini Fortuna', 6, '2025', 'Aktif', 3.82);

-- Dumping structure for table scholarsync.perkembangan_semester
CREATE TABLE IF NOT EXISTS `perkembangan_semester` (
  `id_progress` int NOT NULL AUTO_INCREMENT,
  `semester` tinyint NOT NULL,
  `ipk` decimal(3,2) NOT NULL,
  `NIM` varchar(20) NOT NULL,
  PRIMARY KEY (`id_progress`),
  KEY `NIM` (`NIM`),
  CONSTRAINT `perkembangan_semester_ibfk_1` FOREIGN KEY (`NIM`) REFERENCES `mahasiswa` (`NIM`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table scholarsync.perkembangan_semester: ~21 rows (approximately)
INSERT INTO `perkembangan_semester` (`id_progress`, `semester`, `ipk`, `NIM`) VALUES
	(1, 1, 3.50, '2023100142'),
	(2, 2, 3.60, '2023100142'),
	(3, 1, 3.20, '2021200589'),
	(4, 2, 3.35, '2021200589'),
	(5, 3, 3.40, '2021200589'),
	(6, 4, 3.55, '2021200589'),
	(7, 1, 2.80, '2022400321'),
	(8, 2, 2.90, '2022400321'),
	(9, 1, 3.70, '2020100992'),
	(10, 2, 3.75, '2020100992'),
	(11, 3, 3.80, '2020100992'),
	(12, 4, 3.88, '2020100992'),
	(13, 1, 2.50, '2019300215'),
	(14, 2, 2.60, '2019300215'),
	(15, 1, 3.60, '2022100010'),
	(16, 2, 3.65, '2022100010'),
	(17, 1, 3.45, '2021100075'),
	(18, 2, 3.50, '2021100075'),
	(19, 3, 3.55, '2021100075'),
	(20, 4, 3.60, '2021100075'),
	(21, 3, 3.50, '2019300215');

-- Dumping structure for table scholarsync.prodi
CREATE TABLE IF NOT EXISTS `prodi` (
  `id_prodi` int NOT NULL AUTO_INCREMENT,
  `nama_prodi` varchar(100) NOT NULL,
  `id_fakultas` int NOT NULL,
  PRIMARY KEY (`id_prodi`),
  KEY `id_fakultas` (`id_fakultas`),
  CONSTRAINT `prodi_ibfk_1` FOREIGN KEY (`id_fakultas`) REFERENCES `fakultas` (`id_fakultas`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=90 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table scholarsync.prodi: ~7 rows (approximately)
INSERT INTO `prodi` (`id_prodi`, `nama_prodi`, `id_fakultas`) VALUES
	(1, 'Biologi', 1),
	(2, 'Farmasi', 2),
	(3, 'Geografi Lingkungan', 3),
	(4, 'Kartografi dan Penginderaan Jauh', 3),
	(5, 'Pembangunan Wilayah', 3),
	(6, 'Kedokteran', 4),
	(7, 'Ilmu Keperawatan', 4),
	(8, 'Gizi Kesehatan', 4),
	(9, 'Kedokteran Gigi', 5),
	(10, 'Hygiene Gigi', 5),
	(11, 'Kedokteran Hewan', 6),
	(12, 'Matematika', 7),
	(13, 'Fisika', 7),
	(14, 'Kimia', 7),
	(15, 'Ilmu Komputer', 7),
	(16, 'Statistika', 7),
	(17, 'Geofisika', 7),
	(18, 'Elektronika dan Instrumentasi', 7),
	(19, 'Aktuaria', 7),
	(20, 'Agronomi', 8),
	(21, 'Ilmu Tanah', 8),
	(22, 'Proteksi Tanaman', 8),
	(23, 'Mikrobiologi Pertanian', 8),
	(24, 'Penyuluhan dan Komunikasi Pertanian', 8),
	(25, 'Ekonomi Pertanian dan Agribisnis', 8),
	(26, 'Akuakultur (Budidaya Perikanan)', 8),
	(27, 'Teknologi Hasil Perikanan', 8),
	(28, 'Manajemen Sumberdaya Akuatik', 8),
	(29, 'Kehutanan', 9),
	(30, 'Teknik Pertanian', 10),
	(31, 'Teknologi Pangan dan Hasil Pertanian', 10),
	(32, 'Teknik Industri Pertanian', 10),
	(33, 'Teknik Sipil', 11),
	(34, 'Teknik Mesin', 11),
	(35, 'Teknik Elektro', 11),
	(36, 'Teknik Kimia', 11),
	(37, 'Arsitektur', 11),
	(38, 'Perencanaan Wilayah dan Kota', 11),
	(39, 'Teknik Geologi', 11),
	(40, 'Teknik Geodesi', 11),
	(41, 'Teknik Nuklir', 11),
	(42, 'Teknologi Informasi', 11),
	(43, 'Teknik Industri', 11),
	(44, 'Teknik Fisika', 11),
	(45, 'Akuntansi', 12),
	(46, 'Ilmu Ekonomi', 12),
	(47, 'Manajemen', 12),
	(48, 'Ilmu Hukum', 13),
	(49, 'Sastra Indonesia', 14),
	(50, 'Sastra Inggris', 14),
	(51, 'Sastra Arab/Semit', 14),
	(52, 'Sastra Jepang', 14),
	(53, 'Sastra Korea', 14),
	(54, 'Sastra Prancis', 14),
	(55, 'Sejarah', 14),
	(56, 'Arkeologi', 14),
	(57, 'Antropologi', 14),
	(58, 'Pariwisata', 14),
	(59, 'Sastra Nusantara', 14),
	(60, 'Ilmu Hubungan Internasional', 15),
	(61, 'Ilmu Komunikasi', 15),
	(62, 'Sosiologi', 15),
	(63, 'Manajemen dan Kebijakan Publik', 15),
	(64, 'Politik dan Pemerintahan', 15),
	(65, 'Pembangunan Sosial dan Kesejahteraan', 15),
	(66, 'Ilmu Filsafat', 16),
	(67, 'Psikologi', 17),
	(68, 'Bahasa Jepang', 18),
	(69, 'Sistem Informasi Geografis', 18),
	(70, 'Akuntansi Sektor Publik', 18),
	(71, 'Bahasa Inggris', 18),
	(72, 'Bisnis Perjalanan Wisata', 18),
	(73, 'Manajemen dan Penilaian Properti', 18),
	(74, 'Manajemen Informasi Kesehatan', 18),
	(75, 'Pembangunan Ekonomi Kewilayahan', 18),
	(76, 'Pengelolaan Arsip dan Rekaman Informasi', 18),
	(77, 'Pengelolaan Hutan', 18),
	(78, 'Pengembangan Produk Agroindustri', 18),
	(79, 'Perbankan', 18),
	(80, 'Teknologi Veteriner', 18),
	(81, 'Teknik Pengelolaan dan Pemeliharaan Infrastruktur Sipil', 18),
	(82, 'Teknik Pengelolaan dan Perawatan Alat Berat', 18),
	(83, 'Teknik Rekayasa Pelaksanaan Bangunan Sipil', 18),
	(84, 'Teknologi Rekayasa Elektro', 18),
	(85, 'Teknologi Rekayasa Instrumentasi dan Kontrol', 18),
	(86, 'Teknologi Rekayasa Internet', 18),
	(87, 'Teknologi Rekayasa Mesin', 18),
	(88, 'Teknologi Rekayasa Perangkat Lunak', 18),
	(89, 'Teknologi Survei dan Pemetaan Dasar', 18);

-- Dumping structure for table scholarsync.users
CREATE TABLE IF NOT EXISTS `users` (
  `id_users` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `NIM` varchar(20) DEFAULT NULL,
  `role` enum('admin','mahasiswa') NOT NULL DEFAULT 'mahasiswa',
  PRIMARY KEY (`id_users`),
  UNIQUE KEY `username` (`username`),
  KEY `NIM` (`NIM`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`NIM`) REFERENCES `mahasiswa` (`NIM`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table scholarsync.users: ~2 rows (approximately)
INSERT INTO `users` (`id_users`, `username`, `password`, `NIM`, `role`) VALUES
	(1, 'admin', '$2y$10$w1bl9LHJ5pVO3TpdCzaK8uvrmfE7rinb7f6Bd.LXkrLPCS6h03yga', NULL, 'admin'),
	(7, 'rose', '$2y$10$Ooy8lR01fMZbrmBC11cZveO3ok0U3Y5PkWAxT4GR9I.FF6TKP6GUC', '25/556660/SV/25984', 'mahasiswa');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
