-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 09, 2026 at 12:36 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dbcobaexpedisi`
--

-- --------------------------------------------------------

--
-- Table structure for table `gudang`
--

CREATE TABLE `gudang` (
  `id` int(11) NOT NULL,
  `nama_gudang` varchar(100) NOT NULL,
  `alamat_lengkap` text NOT NULL,
  `kecamatan` varchar(100) DEFAULT NULL,
  `kelurahan` varchar(100) DEFAULT NULL,
  `kota` varchar(100) DEFAULT NULL,
  `provinsi` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gudang`
--

INSERT INTO `gudang` (`id`, `nama_gudang`, `alamat_lengkap`, `kecamatan`, `kelurahan`, `kota`, `provinsi`, `created_at`) VALUES
(12, 'Gudang Jatiasih', 'jalan jatiasih', 'Jatiasih', 'jatiasih', 'Bekasi', 'Jawabarat', '2026-01-08 00:55:29'),
(13, 'Gudang Pondok', 'jalan pondok', 'pondok gede', 'pondok gede', 'jakarta', 'jakarta', '2026-01-08 00:55:49');

-- --------------------------------------------------------

--
-- Table structure for table `harga_pengiriman`
--

CREATE TABLE `harga_pengiriman` (
  `id` int(11) NOT NULL,
  `wilayah` varchar(100) NOT NULL,
  `kota` varchar(100) NOT NULL,
  `berat_min` decimal(10,2) NOT NULL,
  `berat_max` decimal(10,2) NOT NULL,
  `harga` decimal(15,2) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `harga_pengiriman`
--

INSERT INTO `harga_pengiriman` (`id`, `wilayah`, `kota`, `berat_min`, `berat_max`, `harga`, `created_at`) VALUES
(16, 'jabodetabek', 'Bekasi', 0.00, 1.50, 15000.00, '2026-01-08 01:06:53'),
(17, 'jabodetabek', 'Bekasi', 1.60, 2.00, 20000.00, '2026-01-08 01:18:32');

-- --------------------------------------------------------

--
-- Table structure for table `karyawan`
--

CREATE TABLE `karyawan` (
  `id` int(11) NOT NULL,
  `nik` varchar(50) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
  `gudang_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `karyawan`
--

INSERT INTO `karyawan` (`id`, `nik`, `nama`, `email`, `password`, `foto`, `alamat`, `role_id`, `gudang_id`, `created_at`) VALUES
(1, 'ADM001', 'Administrator', 'admin@gmail.com', '$2y$10$oTMgazJMgzhss2Z2IzPlne4EHJGccEhPrSf49QDoq6zN5VjlnNUCK', NULL, NULL, 1, NULL, '2026-01-04 04:48:02'),
(13, 'Kh123451', 'irfan', 'irfan@gmail.com', '$2y$10$dmjVuR7X/iKzqkOpQKra0ekJ4F17ar/PyhMlO2ufBLjqmtkywKQ.e', '1767809239_826.png', 'jalan ', 2, 12, '2026-01-08 01:07:19'),
(14, 'Kh123452', 'jarulloh', 'jarulloh@gmail.com', '$2y$10$evnXLOXonDoJGZ5vAL9bF.c1tvt71VV1hN6oj2Dt5AMxXQAjio63K', '1767809292_272.png', 'jalan', 2, 13, '2026-01-08 01:08:12'),
(15, 'Kh123453', 'ir', 'ir@gmail.com', '', '1767809322_183.png', 'jalan', 3, 12, '2026-01-08 01:08:42'),
(16, 'Kh123454', 'jr', 'jr@gmail.com', '', '1767809385_704.png', 'jalan', 3, 13, '2026-01-08 01:09:45'),
(17, 'Kh123455', 'irfanjarulloh', 'irfanjarulloh@gmail.com', '$2y$10$uLO9/QBnVzQsBV0nDD57K.Pe/LjNKxu3BfCMIumRXMF3I5P9f/dBC', '1767809450_427.png', 'jalan', 4, 12, '2026-01-08 01:10:50'),
(19, 'Kh123456', 'jarulloh irfan', 'jarullohirfan@gmail.com', '$2y$10$r07SOTIx3jroKIQjBCAyj.Vv3jC6SlCelAju9ohG5wqJDX1E23HoC', '1767809795_879.png', 'jalan', 4, 13, '2026-01-08 01:16:35');

-- --------------------------------------------------------

--
-- Table structure for table `paket`
--

CREATE TABLE `paket` (
  `id` int(11) NOT NULL,
  `no_resi` varchar(50) NOT NULL,
  `nama_paket` varchar(100) NOT NULL,
  `qty` int(11) NOT NULL,
  `nama_pengirim` varchar(100) NOT NULL,
  `telp_pengirim` varchar(50) DEFAULT NULL,
  `alamat_pengirim` text DEFAULT NULL,
  `nama_penerima` varchar(100) NOT NULL,
  `telp_penerima` varchar(50) DEFAULT NULL,
  `alamat_penerima` text DEFAULT NULL,
  `provinsi_penerima` varchar(100) DEFAULT NULL,
  `kota_penerima` varchar(100) DEFAULT NULL,
  `kecamatan_penerima` varchar(100) DEFAULT NULL,
  `kelurahan_penerima` varchar(100) DEFAULT NULL,
  `berat` decimal(10,2) NOT NULL,
  `harga_ongkir` decimal(15,2) NOT NULL,
  `cod` decimal(15,2) DEFAULT 0.00,
  `harga_cod` decimal(15,2) DEFAULT 0.00,
  `foto_paket` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `nama_role` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `nama_role`) VALUES
(1, 'Admin'),
(2, 'Sorter'),
(3, 'Supir'),
(4, 'Kurir');

-- --------------------------------------------------------

--
-- Table structure for table `scan_paket`
--

CREATE TABLE `scan_paket` (
  `id` int(11) NOT NULL,
  `paket_id` int(11) NOT NULL,
  `jenis_scan` enum('masuk','keluar','diantar','bermasalah','terkirim') NOT NULL,
  `gudang_id` int(11) DEFAULT NULL,
  `scan_by` int(11) DEFAULT NULL,
  `supir_id` int(11) DEFAULT NULL,
  `kurir_id` int(11) DEFAULT NULL,
  `scan_time` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `status_paket`
--

CREATE TABLE `status_paket` (
  `id` int(11) NOT NULL,
  `paket_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `foto_penerima` varchar(255) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `gudang`
--
ALTER TABLE `gudang`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `harga_pengiriman`
--
ALTER TABLE `harga_pengiriman`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `karyawan`
--
ALTER TABLE `karyawan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `gudang_id` (`gudang_id`);

--
-- Indexes for table `paket`
--
ALTER TABLE `paket`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_resi` (`no_resi`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `scan_paket`
--
ALTER TABLE `scan_paket`
  ADD PRIMARY KEY (`id`),
  ADD KEY `paket_id` (`paket_id`),
  ADD KEY `gudang_id` (`gudang_id`),
  ADD KEY `scan_by` (`scan_by`),
  ADD KEY `supir_id` (`supir_id`),
  ADD KEY `kurir_id` (`kurir_id`);

--
-- Indexes for table `status_paket`
--
ALTER TABLE `status_paket`
  ADD PRIMARY KEY (`id`),
  ADD KEY `paket_id` (`paket_id`),
  ADD KEY `updated_by` (`updated_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `gudang`
--
ALTER TABLE `gudang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `harga_pengiriman`
--
ALTER TABLE `harga_pengiriman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `karyawan`
--
ALTER TABLE `karyawan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `paket`
--
ALTER TABLE `paket`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `scan_paket`
--
ALTER TABLE `scan_paket`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `status_paket`
--
ALTER TABLE `status_paket`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `karyawan`
--
ALTER TABLE `karyawan`
  ADD CONSTRAINT `karyawan_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `karyawan_ibfk_2` FOREIGN KEY (`gudang_id`) REFERENCES `gudang` (`id`);

--
-- Constraints for table `paket`
--
ALTER TABLE `paket`
  ADD CONSTRAINT `paket_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `karyawan` (`id`);

--
-- Constraints for table `scan_paket`
--
ALTER TABLE `scan_paket`
  ADD CONSTRAINT `scan_paket_ibfk_1` FOREIGN KEY (`paket_id`) REFERENCES `paket` (`id`),
  ADD CONSTRAINT `scan_paket_ibfk_2` FOREIGN KEY (`gudang_id`) REFERENCES `gudang` (`id`),
  ADD CONSTRAINT `scan_paket_ibfk_3` FOREIGN KEY (`scan_by`) REFERENCES `karyawan` (`id`),
  ADD CONSTRAINT `scan_paket_ibfk_4` FOREIGN KEY (`supir_id`) REFERENCES `karyawan` (`id`),
  ADD CONSTRAINT `scan_paket_ibfk_5` FOREIGN KEY (`kurir_id`) REFERENCES `karyawan` (`id`);

--
-- Constraints for table `status_paket`
--
ALTER TABLE `status_paket`
  ADD CONSTRAINT `status_paket_ibfk_1` FOREIGN KEY (`paket_id`) REFERENCES `paket` (`id`),
  ADD CONSTRAINT `status_paket_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `karyawan` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
