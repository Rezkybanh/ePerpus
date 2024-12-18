-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 30 Nov 2024 pada 23.46
-- Versi server: 10.4.18-MariaDB
-- Versi PHP: 8.0.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `eperpus`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `id_admin` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(225) NOT NULL,
  `role` enum('kepala_perpus','operator') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `anggota`
--

CREATE TABLE `anggota` (
  `id_anggota` int(11) NOT NULL,
  `id_admin` int(11) NOT NULL,
  `nisnip` varchar(50) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `kelas_unit` varchar(100) NOT NULL,
  `kontak` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `buku`
--

CREATE TABLE `buku` (
  `id_buku` int(11) NOT NULL,
  `id_admin` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `pengarang` varchar(255) NOT NULL,
  `penerbit` varchar(255) NOT NULL,
  `tahun_terbit` year(4) NOT NULL,
  `isbn` varchar(20) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `riwayat`
--

CREATE TABLE `riwayat` (
  `id_riwayat` int(11) NOT NULL,
  `id_transaksi` int(11) NOT NULL,
  `id_admin` int(11) NOT NULL,
  `nama_anggota` varchar(255) NOT NULL,
  `judul_buku` varchar(255) NOT NULL,
  `tanggal_pinjam` date NOT NULL,
  `tanggal_kembali` date NOT NULL,
  `status` enum('masih dipinjam','sudah dikembalikan') NOT NULL DEFAULT 'masih dipinjam',
  `hari_terlambat` int(11) DEFAULT 0,
  `total_denda` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `id_anggota` int(11) NOT NULL,
  `id_buku` int(11) NOT NULL,
  `id_admin` int(11) NOT NULL,
  `tanggal_pinjam` date NOT NULL,
  `tanggal_kembali` date DEFAULT NULL,
  `denda` decimal(10,2) DEFAULT 0.00,
  `jumlah_buku` int(11) NOT NULL,
  `jumlah_dikembalikan` int(11) DEFAULT 0,
  `status` enum('dipinjam','dikembalikan') DEFAULT 'dipinjam'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`);

--
-- Indeks untuk tabel `anggota`
--
ALTER TABLE `anggota`
  ADD PRIMARY KEY (`id_anggota`),
  ADD UNIQUE KEY `nisnip` (`nisnip`),
  ADD KEY `id_admin` (`id_admin`);

--
-- Indeks untuk tabel `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`id_buku`),
  ADD UNIQUE KEY `isbn` (`isbn`),
  ADD KEY `id_admin` (`id_admin`);

--
-- Indeks untuk tabel `riwayat`
--
ALTER TABLE `riwayat`
  ADD PRIMARY KEY (`id_riwayat`),
  ADD KEY `id_transaksi` (`id_transaksi`),
  ADD KEY `id_admin` (`id_admin`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_anggota` (`id_anggota`),
  ADD KEY `id_buku` (`id_buku`),
  ADD KEY `id_admin` (`id_admin`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `anggota`
--
ALTER TABLE `anggota`
  MODIFY `id_anggota` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `buku`
--
ALTER TABLE `buku`
  MODIFY `id_buku` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `riwayat`
--
ALTER TABLE `riwayat`
  MODIFY `id_riwayat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `anggota`
--
ALTER TABLE `anggota`
  ADD CONSTRAINT `anggota_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`);

--
-- Ketidakleluasaan untuk tabel `buku`
--
ALTER TABLE `buku`
  ADD CONSTRAINT `buku_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`);

--
-- Ketidakleluasaan untuk tabel `riwayat`
--
ALTER TABLE `riwayat`
  ADD CONSTRAINT `riwayat_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE,
  ADD CONSTRAINT `riwayat_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`);

--
-- Ketidakleluasaan untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_anggota`) REFERENCES `anggota` (`id_anggota`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`id_buku`) REFERENCES `buku` (`id_buku`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaksi_ibfk_3` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
