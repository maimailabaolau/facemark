-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 05, 2025 at 07:04 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `facemark`
--

-- --------------------------------------------------------

--
-- Table structure for table `diem`
--

CREATE TABLE `diem` (
  `ma_diem` int(11) NOT NULL,
  `diem_giua_ky` int(11) NOT NULL,
  `diem_cuoi_ky` int(11) NOT NULL,
  `diem_tong` int(11) NOT NULL,
  `hoc_ky` varchar(20) NOT NULL,
  `ma_sv` int(11) NOT NULL,
  `ma_monhoc` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

-- --------------------------------------------------------

--
-- Table structure for table `diemdanh`
--

CREATE TABLE `diemdanh` (
  `ma_sv` int(11) NOT NULL,
  `ma_gv` int(11) NOT NULL,
  `ma_monhoc` int(11) NOT NULL,
  `ma_lop` int(11) NOT NULL,
  `ngay_diem_danh` date NOT NULL,
  `trang_thai` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `diemdanh`
--

INSERT INTO `diemdanh` (`ma_sv`, `ma_gv`, `ma_monhoc`, `ma_lop`, `ngay_diem_danh`, `trang_thai`) VALUES
(1, 1, 1, 1, '2025-03-05', 1),
(2, 1, 1, 1, '2025-03-05', 0);

-- --------------------------------------------------------

--
-- Table structure for table `giangday`
--

CREATE TABLE `giangday` (
  `ma_gv` int(11) NOT NULL,
  `ma_monhoc` int(11) NOT NULL,
  `ma_nien_khoa` int(11) NOT NULL,
  `ma_lop` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `giangday`
--

INSERT INTO `giangday` (`ma_gv`, `ma_monhoc`, `ma_nien_khoa`, `ma_lop`) VALUES
(1, 1, 16, 1),
(2, 2, 16, 2);

-- --------------------------------------------------------

--
-- Table structure for table `giangvien`
--

CREATE TABLE `giangvien` (
  `ma_gv` int(11) NOT NULL,
  `ten_gv` varchar(255) NOT NULL,
  `ngay_sinh` date NOT NULL,
  `gioi_tinh` bit(1) NOT NULL,
  `sdt` int(20) NOT NULL,
  `dia_chi` text NOT NULL,
  `email` varchar(255) NOT NULL,
  `nhan_dien` longblob NOT NULL,
  `ma_khoa` int(10) NOT NULL,
  `username` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `giangvien`
--

INSERT INTO `giangvien` (`ma_gv`, `ten_gv`, `ngay_sinh`, `gioi_tinh`, `sdt`, `dia_chi`, `email`, `nhan_dien`, `ma_khoa`, `username`) VALUES
(1, 'Trần Mạnh Hùn', '2003-03-19', b'1', 123456789, 'Cầu Gì ấy, Hà Lội', 'ahaha@gmail.com', '', 1, 'hehe'),
(2, 'Quách Đà Phúc', '2003-12-23', b'1', 999999999, 'Sóc chăng?', 'kekek@gmail.com', '', 2, 'hihi');

-- --------------------------------------------------------

--
-- Table structure for table `khoa`
--

CREATE TABLE `khoa` (
  `ma_khoa` int(10) NOT NULL,
  `ten_khoa` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `khoa`
--

INSERT INTO `khoa` (`ma_khoa`, `ten_khoa`) VALUES
(1, 'Kỹ thuât công nghệ'),
(2, 'Công nghệ thực phẩm'),
(3, 'Khoa học Môi trường');

-- --------------------------------------------------------

--
-- Table structure for table `login`
--

CREATE TABLE `login` (
  `username` varchar(250) NOT NULL,
  `password` varchar(250) NOT NULL,
  `ma_gv` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `login`
--

INSERT INTO `login` (`username`, `password`, `ma_gv`) VALUES
('hehe', '1', 1),
('hihi', '1', 2);

-- --------------------------------------------------------

--
-- Table structure for table `lop`
--

CREATE TABLE `lop` (
  `ma_lop` int(10) NOT NULL,
  `ten_lop` varchar(255) NOT NULL,
  `ma_khoa` int(10) NOT NULL,
  `ma_nien_khoa` int(11) NOT NULL,
  `ma_monhoc` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `lop`
--

INSERT INTO `lop` (`ma_lop`, `ten_lop`, `ma_khoa`, `ma_nien_khoa`, `ma_monhoc`) VALUES
(1, 'CNTT', 1, 16, 1),
(2, 'KTCN', 2, 16, 2);

-- --------------------------------------------------------

--
-- Table structure for table `monhoc`
--

CREATE TABLE `monhoc` (
  `ma_monhoc` int(10) NOT NULL,
  `ten_monhoc` varchar(100) NOT NULL,
  `so_tin_chi` int(11) NOT NULL,
  `ma_khoa` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `monhoc`
--

INSERT INTO `monhoc` (`ma_monhoc`, `ten_monhoc`, `so_tin_chi`, `ma_khoa`) VALUES
(1, 'Toán', 3, 1),
(2, 'Nấu Ăn', 2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `nien_khoa`
--

CREATE TABLE `nien_khoa` (
  `ma_nien_khoa` int(11) NOT NULL,
  `ten_nien_khoa` varchar(20) NOT NULL,
  `nam_bat_dau` date NOT NULL,
  `nam_ket_thuc` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `nien_khoa`
--

INSERT INTO `nien_khoa` (`ma_nien_khoa`, `ten_nien_khoa`, `nam_bat_dau`, `nam_ket_thuc`) VALUES
(16, 'K16', '2021-01-01', '2025-01-01');

-- --------------------------------------------------------

--
-- Table structure for table `sinhvien`
--

CREATE TABLE `sinhvien` (
  `ma_sv` int(11) NOT NULL,
  `ten_sv` varchar(255) NOT NULL,
  `ngay_sinh` date NOT NULL,
  `gioi_tinh` bit(1) NOT NULL,
  `sdt` int(20) NOT NULL,
  `dia_chi` text NOT NULL,
  `email` varchar(255) NOT NULL,
  `nhan_dien` longblob NOT NULL,
  `ma_lop` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `sinhvien`
--

INSERT INTO `sinhvien` (`ma_sv`, `ten_sv`, `ngay_sinh`, `gioi_tinh`, `sdt`, `dia_chi`, `email`, `nhan_dien`, `ma_lop`) VALUES
(1, 'Trần Thanh Long', '2025-03-02', b'1', 174485123, 'Hà Nam', 'adasda@gmail.com', '', 1),
(2, 'Nguyễn Đu Đủ', '2025-01-07', b'0', 96742861, 'Hà Tĩnh', 'jilwk@gmail.com', '', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `diem`
--
ALTER TABLE `diem`
  ADD PRIMARY KEY (`ma_diem`),
  ADD KEY `ma_monhoc` (`ma_monhoc`),
  ADD KEY `ma_sv` (`ma_sv`);

--
-- Indexes for table `diemdanh`
--
ALTER TABLE `diemdanh`
  ADD UNIQUE KEY `unique_diemdanh` (`ma_sv`,`ma_gv`,`ma_monhoc`,`ma_lop`,`ngay_diem_danh`),
  ADD KEY `ma_gv` (`ma_gv`),
  ADD KEY `ma_monhoc` (`ma_monhoc`),
  ADD KEY `ma_lop` (`ma_lop`);

--
-- Indexes for table `giangday`
--
ALTER TABLE `giangday`
  ADD KEY `ma_gv` (`ma_gv`),
  ADD KEY `ma_monhoc` (`ma_monhoc`),
  ADD KEY `ma_nien_khoa` (`ma_nien_khoa`),
  ADD KEY `ma_lop` (`ma_lop`);

--
-- Indexes for table `giangvien`
--
ALTER TABLE `giangvien`
  ADD PRIMARY KEY (`ma_gv`),
  ADD KEY `ma_khoa` (`ma_khoa`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `khoa`
--
ALTER TABLE `khoa`
  ADD PRIMARY KEY (`ma_khoa`);

--
-- Indexes for table `login`
--
ALTER TABLE `login`
  ADD PRIMARY KEY (`username`),
  ADD KEY `ma_gv` (`ma_gv`);

--
-- Indexes for table `lop`
--
ALTER TABLE `lop`
  ADD PRIMARY KEY (`ma_lop`),
  ADD KEY `ma_khoa` (`ma_khoa`),
  ADD KEY `ma_nien_khoa` (`ma_nien_khoa`),
  ADD KEY `ma_monhoc` (`ma_monhoc`);

--
-- Indexes for table `monhoc`
--
ALTER TABLE `monhoc`
  ADD PRIMARY KEY (`ma_monhoc`),
  ADD KEY `ma_khoa` (`ma_khoa`);

--
-- Indexes for table `nien_khoa`
--
ALTER TABLE `nien_khoa`
  ADD PRIMARY KEY (`ma_nien_khoa`);

--
-- Indexes for table `sinhvien`
--
ALTER TABLE `sinhvien`
  ADD PRIMARY KEY (`ma_sv`),
  ADD KEY `ma_lop` (`ma_lop`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `diem`
--
ALTER TABLE `diem`
  MODIFY `ma_diem` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `giangvien`
--
ALTER TABLE `giangvien`
  MODIFY `ma_gv` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `khoa`
--
ALTER TABLE `khoa`
  MODIFY `ma_khoa` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `lop`
--
ALTER TABLE `lop`
  MODIFY `ma_lop` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `monhoc`
--
ALTER TABLE `monhoc`
  MODIFY `ma_monhoc` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `nien_khoa`
--
ALTER TABLE `nien_khoa`
  MODIFY `ma_nien_khoa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `sinhvien`
--
ALTER TABLE `sinhvien`
  MODIFY `ma_sv` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21794;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `diem`
--
ALTER TABLE `diem`
  ADD CONSTRAINT `diem_ibfk_1` FOREIGN KEY (`ma_monhoc`) REFERENCES `monhoc` (`ma_monhoc`),
  ADD CONSTRAINT `diem_ibfk_2` FOREIGN KEY (`ma_sv`) REFERENCES `sinhvien` (`ma_sv`);

--
-- Constraints for table `diemdanh`
--
ALTER TABLE `diemdanh`
  ADD CONSTRAINT `diemdanh_ibfk_1` FOREIGN KEY (`ma_gv`) REFERENCES `giangvien` (`ma_gv`),
  ADD CONSTRAINT `diemdanh_ibfk_2` FOREIGN KEY (`ma_monhoc`) REFERENCES `monhoc` (`ma_monhoc`),
  ADD CONSTRAINT `diemdanh_ibfk_3` FOREIGN KEY (`ma_sv`) REFERENCES `sinhvien` (`ma_sv`),
  ADD CONSTRAINT `diemdanh_ibfk_4` FOREIGN KEY (`ma_lop`) REFERENCES `lop` (`ma_lop`);

--
-- Constraints for table `giangday`
--
ALTER TABLE `giangday`
  ADD CONSTRAINT `giangday_ibfk_1` FOREIGN KEY (`ma_gv`) REFERENCES `giangvien` (`ma_gv`),
  ADD CONSTRAINT `giangday_ibfk_2` FOREIGN KEY (`ma_monhoc`) REFERENCES `monhoc` (`ma_monhoc`),
  ADD CONSTRAINT `giangday_ibfk_3` FOREIGN KEY (`ma_nien_khoa`) REFERENCES `nien_khoa` (`ma_nien_khoa`),
  ADD CONSTRAINT `giangday_ibfk_4` FOREIGN KEY (`ma_lop`) REFERENCES `lop` (`ma_lop`);

--
-- Constraints for table `giangvien`
--
ALTER TABLE `giangvien`
  ADD CONSTRAINT `giangvien_ibfk_1` FOREIGN KEY (`ma_khoa`) REFERENCES `khoa` (`ma_khoa`),
  ADD CONSTRAINT `giangvien_ibfk_2` FOREIGN KEY (`username`) REFERENCES `login` (`username`);

--
-- Constraints for table `login`
--
ALTER TABLE `login`
  ADD CONSTRAINT `login_ibfk_1` FOREIGN KEY (`ma_gv`) REFERENCES `giangvien` (`ma_gv`);

--
-- Constraints for table `lop`
--
ALTER TABLE `lop`
  ADD CONSTRAINT `lop_ibfk_1` FOREIGN KEY (`ma_khoa`) REFERENCES `khoa` (`ma_khoa`),
  ADD CONSTRAINT `lop_ibfk_2` FOREIGN KEY (`ma_nien_khoa`) REFERENCES `nien_khoa` (`ma_nien_khoa`),
  ADD CONSTRAINT `lop_ibfk_3` FOREIGN KEY (`ma_monhoc`) REFERENCES `monhoc` (`ma_monhoc`);

--
-- Constraints for table `monhoc`
--
ALTER TABLE `monhoc`
  ADD CONSTRAINT `monhoc_ibfk_1` FOREIGN KEY (`ma_khoa`) REFERENCES `khoa` (`ma_khoa`);

--
-- Constraints for table `sinhvien`
--
ALTER TABLE `sinhvien`
  ADD CONSTRAINT `sinhvien_ibfk_1` FOREIGN KEY (`ma_lop`) REFERENCES `lop` (`ma_lop`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
