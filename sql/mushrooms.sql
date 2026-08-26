-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 20, 2026 at 03:33 PM
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
-- Database: `bwpsold`
--

-- --------------------------------------------------------

--
-- Table structure for table `mushrooms`
--

CREATE TABLE `mushrooms` (
  `mushroomType` int(11) NOT NULL,
  `rewardAmount` int(11) NOT NULL,
  `rewardType` varchar(255) NOT NULL,
  `validUntil` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mushrooms`
--

INSERT INTO `mushrooms` (`mushroomType`, `rewardAmount`, `rewardType`, `validUntil`) VALUES
(1, 5, 'mulch', NULL),
(2, 10, 'mulch', NULL),
(3, 20, 'mulch', NULL),
(4, 50, 'mulch', NULL),
(5, 100, 'mulch', NULL),
(6, 500, 'mulch', NULL),
(7, 2, 'xp', NULL),
(8, 5, 'xp', NULL),
(9, 10, 'xp', NULL),
(10, 20, 'xp', NULL),
(11, 50, 'xp', NULL),
(12, 75, 'mulch', NULL),
(13, 15, 'xp', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `mushrooms`
--
ALTER TABLE `mushrooms`
  ADD PRIMARY KEY (`mushroomType`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `mushrooms`
--
ALTER TABLE `mushrooms`
  MODIFY `mushroomType` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
