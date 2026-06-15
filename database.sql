-- ============================================================
-- ScholarSync Academic - Database Export
-- MySQL 5.7+ / MariaDB 10.3+
-- ============================================================

CREATE DATABASE IF NOT EXISTS scholarsync CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE scholarsync;

-- -------------------------
-- Table: fakultas
-- -------------------------
CREATE TABLE IF NOT EXISTS `fakultas` (
  `id_fakultas` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_fakultas` VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------
-- Table: prodi
-- -------------------------
CREATE TABLE IF NOT EXISTS `prodi` (
  `id_prodi` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_prodi` VARCHAR(100) NOT NULL,
  `id_fakultas` INT NOT NULL,
  FOREIGN KEY (`id_fakultas`) REFERENCES `fakultas`(`id_fakultas`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------
-- Table: mahasiswa
-- -------------------------
CREATE TABLE IF NOT EXISTS `mahasiswa` (
  `NIM` VARCHAR(20) PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `id_fakultas` INT NOT NULL DEFAULT 1,
  `id_prodi` INT NOT NULL,
  `angkatan` YEAR NOT NULL,
  `status_aktif` ENUM('Aktif','Cuti','Probation','Lulus','DO') NOT NULL DEFAULT 'Aktif',
  FOREIGN KEY (`id_fakultas`) REFERENCES `fakultas`(`id_fakultas`) ON DELETE RESTRICT,
  FOREIGN KEY (`id_prodi`) REFERENCES `prodi`(`id_prodi`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------
-- Table: perkembangan_semester
-- -------------------------
CREATE TABLE IF NOT EXISTS `perkembangan_semester` (
  `id_progress` INT AUTO_INCREMENT PRIMARY KEY,
  `semester` TINYINT NOT NULL,
  `ipk` DECIMAL(3,2) NOT NULL,
  `NIM` VARCHAR(20) NOT NULL,
  FOREIGN KEY (`NIM`) REFERENCES `mahasiswa`(`NIM`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------
-- Table: users
-- -------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id_users` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `NIM` VARCHAR(20) DEFAULT NULL,
  `role` ENUM('admin','mahasiswa') NOT NULL DEFAULT 'mahasiswa',
  FOREIGN KEY (`NIM`) REFERENCES `mahasiswa`(`NIM`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SEED DATA
-- ============================================================

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
(63, 'Manajemen dan Kebijakan Publik (Administrasi Negara)', 15),
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

INSERT INTO `mahasiswa` (`NIM`, `nama`, `id_prodi`, `angkatan`, `status_aktif`) VALUES
('2023100142', 'Ahmad Fauzi', 1, 2023, 'Aktif'),
('2021200589', 'Siti Aminah', 4, 2021, 'Aktif'),
('2022400321', 'Budi Santoso', 6, 2022, 'Cuti'),
('2020100992', 'Diana Lestari', 2, 2020, 'Aktif'),
('2019300215', 'Eko Prasetyo', 3, 2019, 'Probation'),
('2022100010', 'Farida Nurul', 1, 2022, 'Aktif'),
('2021100075', 'Gilang Ramadan', 7, 2021, 'Aktif');

INSERT INTO `perkembangan_semester` (`semester`, `ipk`, `NIM`) VALUES
(1, 3.50, '2023100142'),
(2, 3.60, '2023100142'),
(1, 3.20, '2021200589'),
(2, 3.35, '2021200589'),
(3, 3.40, '2021200589'),
(4, 3.55, '2021200589'),
(1, 2.80, '2022400321'),
(2, 2.90, '2022400321'),
(1, 3.70, '2020100992'),
(2, 3.75, '2020100992'),
(3, 3.80, '2020100992'),
(4, 3.88, '2020100992'),
(1, 2.50, '2019300215'),
(2, 2.60, '2019300215'),
(1, 3.60, '2022100010'),
(2, 3.65, '2022100010'),
(1, 3.45, '2021100075'),
(2, 3.50, '2021100075'),
(3, 3.55, '2021100075'),
(4, 3.60, '2021100075');

-- Admin user: password = "admin123"
INSERT INTO `users` (`username`, `password`, `NIM`, `role`) VALUES
('admin', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'admin');
-- NOTE: run generate_password.php to regenerate hash, or use: password_hash('admin123', PASSWORD_DEFAULT)
