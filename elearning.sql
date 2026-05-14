-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 16, 2026 at 08:14 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `elearning`
--

-- --------------------------------------------------------

--
-- Table structure for table `materi`
--

CREATE TABLE `materi` (
  `id` int(11) NOT NULL,
  `judul` varchar(100) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `matkul_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `materi`
--

INSERT INTO `materi` (`id`, `judul`, `file`, `matkul_id`) VALUES
(5, 'MATERI BASIS DATA', '1767729424_HANYA_UNTUK_UJI_COBA_SAJA.docx', 2),
(6, 'MATERI PEMEGROGRAMAN WEB', '1767730282_HANYA_UNTUK_UJI_COBA_SAJA.docx', 1),
(7, 'MATERI BASIS DATA DUA', '1767730391_HANYA_UNTUK_UJI_COBA_SAJA.docx', 2),
(8, 'MATERI UAS BASIS DATA', '1767967642_HANYA_UNTUK_UJI_COBA_SAJA.docx', 2);

-- --------------------------------------------------------

--
-- Table structure for table `matkul`
--

CREATE TABLE `matkul` (
  `id` int(11) NOT NULL,
  `prodi_id` int(11) DEFAULT NULL,
  `nama_matkul` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `matkul`
--

INSERT INTO `matkul` (`id`, `prodi_id`, `nama_matkul`) VALUES
(1, 1, 'Pemrograman Web'),
(2, 1, 'Basis Data'),
(3, 2, 'Pengantar Ekonomi'),
(4, 2, 'Manajemen Keuangan'),
(5, 3, 'Anatomi Manusia'),
(6, 3, 'Fisiologi Kedokteran'),
(7, 4, 'Psikologi Umum'),
(8, 4, 'Psikologi Perkembangan');

-- --------------------------------------------------------

--
-- Table structure for table `pengumpulan_tugas`
--

CREATE TABLE `pengumpulan_tugas` (
  `id` int(11) NOT NULL,
  `tugas_id` int(11) DEFAULT NULL,
  `mahasiswa_id` int(11) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `presensi`
--

CREATE TABLE `presensi` (
  `id` int(11) NOT NULL,
  `tanggal` date DEFAULT NULL,
  `status` enum('buka','tutup') DEFAULT NULL,
  `matkul_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `presensi`
--

INSERT INTO `presensi` (`id`, `tanggal`, `status`, `matkul_id`) VALUES
(9, '2026-01-06', 'tutup', 2),
(10, '2026-01-06', 'tutup', 1),
(11, '2026-01-09', 'tutup', 2),
(12, '2026-01-15', 'buka', 2);

-- --------------------------------------------------------

--
-- Table structure for table `presensi_mahasiswa`
--

CREATE TABLE `presensi_mahasiswa` (
  `id` int(11) NOT NULL,
  `presensi_id` int(11) DEFAULT NULL,
  `mahasiswa` varchar(100) DEFAULT NULL,
  `keterangan` enum('Hadir','Izin','Alfa') DEFAULT NULL,
  `waktu` time DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `presensi_mahasiswa`
--

INSERT INTO `presensi_mahasiswa` (`id`, `presensi_id`, `mahasiswa`, `keterangan`, `waktu`) VALUES
(10, 9, 'hakabotak', 'Hadir', '02:59:21'),
(11, 11, 'hakabotak', '', '09:38:55');

-- --------------------------------------------------------

--
-- Table structure for table `prodi`
--

CREATE TABLE `prodi` (
  `id` int(11) NOT NULL,
  `nama_prodi` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prodi`
--

INSERT INTO `prodi` (`id`, `nama_prodi`) VALUES
(1, 'Informatika'),
(2, 'Ekonomi'),
(3, 'Kedokteran'),
(4, 'Psikologi');

-- --------------------------------------------------------

--
-- Table structure for table `tugas`
--

CREATE TABLE `tugas` (
  `id` int(11) NOT NULL,
  `judul` varchar(100) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `matkul_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tugas`
--

INSERT INTO `tugas` (`id`, `judul`, `deskripsi`, `deadline`, `file`, `matkul_id`) VALUES
(7, 'TUGAS BASIS DATA', NULL, '2026-01-08', NULL, 2),
(8, 'TUGAS PEMEGROGRAMAN WEB', NULL, '2026-01-09', NULL, 1),
(9, 'TUGAS BASIS DATA DUA', 'UNTUK UAS\r\n', '2026-01-12', '1767967564_HANYA_UNTUK_UJI_COBA_SAJA.docx', 2);

-- --------------------------------------------------------

--
-- Table structure for table `tugas_kumpul`
--

CREATE TABLE `tugas_kumpul` (
  `id` int(11) NOT NULL,
  `tugas_id` int(11) DEFAULT NULL,
  `mahasiswa` varchar(100) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tugas_kumpul`
--

INSERT INTO `tugas_kumpul` (`id`, `tugas_id`, `mahasiswa`, `file`, `tanggal`) VALUES
(5, 7, 'hakabotak', '1767729548_HANYA UNTUK UJI COBA SAJA.docx', '2026-01-06 19:59:08'),
(6, 9, 'hakabotak', '1767968196_HANYA UNTUK UJI COBA SAJA.docx', '2026-01-09 14:16:36'),
(7, 9, 'hakabotak', '1767968706_HANYA UNTUK UJI COBA SAJA.docx', '2026-01-09 14:25:06'),
(8, 9, 'hakabotak', '1767969447_HANYA UNTUK UJI COBA SAJA.docx', '2026-01-09 14:37:27'),
(9, 9, 'hakabotak', '1767970201_HANYA UNTUK UJI COBA SAJA.docx', '2026-01-09 14:50:01');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','dosen','mahasiswa') DEFAULT NULL,
  `prodi_id` int(11) DEFAULT NULL,
  `matkul_id` int(11) DEFAULT NULL,
  `umur` int(11) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `agama` varchar(50) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `nisn` varchar(20) DEFAULT NULL,
  `nidn` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `username`, `password`, `role`, `prodi_id`, `matkul_id`, `umur`, `no_hp`, `agama`, `alamat`, `nisn`, `nidn`) VALUES
(1, 'Admin', 'admin', '0192023a7bbd73250516f069df18b500', 'admin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'zacnvors', 'zacn', '6b8cd2ed0457e820c2bcc9dbade16072', 'dosen', 1, 2, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'hakabotak', 'haka', '7b1c1bc9da1e4b8a1b9e5efa88beaefc', 'mahasiswa', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 'fajarsadboy', 'fajar', '7bedc9fd30769590c992b8f7f23738f7', 'dosen', 1, 1, 1, '120', 'Islam', 'disitu', '', '002');

-- --------------------------------------------------------

--
-- Table structure for table `website_settings`
--

CREATE TABLE `website_settings` (
  `id` int(11) NOT NULL,
  `nama_website` varchar(255) NOT NULL DEFAULT 'E-Learning Platform',
  `deskripsi` varchar(500) DEFAULT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `twitter` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `jam_buka` varchar(50) DEFAULT NULL,
  `jam_tutup` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `website_settings`
--

INSERT INTO `website_settings` (`id`, `nama_website`, `deskripsi`, `alamat`, `telepon`, `email`, `facebook`, `twitter`, `instagram`, `jam_buka`, `jam_tutup`, `updated_at`) VALUES
(1, 'E-Learning', 'Platform pembelajaran online untuk universitas', 'Jl. Pendidikan No. 123', '(021) 1234-5678', 'info@elearning.com', '', '', '', NULL, NULL, '2026-01-09 13:29:06');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` enum('pending','confirmed','rejected') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `confirmed_by` int(11) DEFAULT NULL,
  `confirmed_date` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `student_id`, `amount`, `description`, `status`, `payment_method`, `transaction_id`, `payment_date`, `confirmed_by`, `confirmed_date`, `notes`) VALUES
(1, 6, 500000.00, 'Biaya Kuliah Semester 1', 'pending', 'Transfer Bank', 'TXN123456', '2026-05-12 10:00:00', NULL, NULL, 'Menunggu konfirmasi admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `materi`
--
ALTER TABLE `materi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `matkul`
--
ALTER TABLE `matkul`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pengumpulan_tugas`
--
ALTER TABLE `pengumpulan_tugas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `presensi`
--
ALTER TABLE `presensi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `presensi_mahasiswa`
--
ALTER TABLE `presensi_mahasiswa`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `prodi`
--
ALTER TABLE `prodi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tugas`
--
ALTER TABLE `tugas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tugas_kumpul`
--
ALTER TABLE `tugas_kumpul`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `website_settings`
--
ALTER TABLE `website_settings`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `materi`
--
ALTER TABLE `materi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `matkul`
--
ALTER TABLE `matkul`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `pengumpulan_tugas`
--
ALTER TABLE `pengumpulan_tugas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `presensi`
--
ALTER TABLE `presensi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `presensi_mahasiswa`
--
ALTER TABLE `presensi_mahasiswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `prodi`
--
ALTER TABLE `prodi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tugas`
--
ALTER TABLE `tugas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tugas_kumpul`
--
ALTER TABLE `tugas_kumpul`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `website_settings`
--
ALTER TABLE `website_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
