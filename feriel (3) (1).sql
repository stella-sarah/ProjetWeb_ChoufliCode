-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 03, 2025 at 09:46 PM
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
-- Database: `tunifyy`
--

-- --------------------------------------------------------

--
-- Table structure for table `commandes`
--

CREATE TABLE `commandes` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `city` varchar(255) NOT NULL,
  `delivery_time` datetime NOT NULL,
  `status` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `depart_place` text NOT NULL,
  `arrive_place` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `commandes`
--

INSERT INTO `commandes` (`id`, `customer_name`, `email`, `city`, `delivery_time`, `status`, `created_at`, `updated_at`, `depart_place`, `arrive_place`) VALUES
(1, 'latrachhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh', 'souissiferiel@yahoo.fr', 'Tunis', '2025-04-30 22:34:00', 'livré', '2025-04-19 21:34:43', '2025-04-20 06:30:43', '', ''),
(5, 'oussama jaouadi', 'oussama.jaouadi.jd@gmail.com', 'ariana', '2025-04-22 08:04:00', 'en préparation', '2025-04-20 07:04:27', '2025-04-20 07:04:27', '', ''),
(6, 'oussama jaouadi', 'oussama.jaouadi.jd@gmail.com', 'ariana', '2025-04-26 08:36:00', 'en préparation', '2025-04-20 07:36:35', '2025-04-20 07:36:35', '', ''),
(7, 'oussama jaouadi', 'oussama.jaouadi.jd@gmail.com', 'ariana', '2025-04-26 08:36:00', 'en préparation', '2025-04-20 07:39:59', '2025-04-20 07:39:59', '', ''),
(8, 'oussama jaouadioooo', 'oussama.jaouadi.jd@gmail.com', 'ariana', '2025-04-28 08:40:00', 'livré', '2025-04-20 07:40:35', '2025-04-20 07:44:04', '', ''),
(9, 'oussama jaouadi', 'oussama.jaouadi.jd@gmail.com', 'ariana', '2025-04-22 08:41:00', 'en préparation', '2025-04-20 07:41:05', '2025-04-20 07:41:05', '', ''),
(10, 'oussama jaouadi', 'oussama.jaouadi.jd@gmail.com', 'ariana', '2025-04-21 18:15:00', 'en préparation', '2025-04-20 17:15:10', '2025-04-20 17:15:10', '', ''),
(11, 'oussama jaouadi', 'oussama.jaouadi.jd@gmail.com', 'ariana', '2025-04-24 23:15:00', 'en préparation', '2025-04-20 17:15:33', '2025-04-20 17:15:33', '', ''),
(12, 'oussama jaouadi', 'oussama.jaouadi.jd@gmail.com', 'ariana', '2025-04-24 18:15:00', 'en préparation', '2025-04-20 17:15:53', '2025-04-20 17:15:53', '', ''),
(13, 'oussama jaouadi', 'oussama.jaouadi.jd@gmail.com', 'ariana', '2025-04-23 18:24:00', 'en préparation', '2025-04-20 17:24:16', '2025-04-20 17:24:16', '', ''),
(14, 'oussama jaouadi', 'oussama.jaouadi.jd@gmail.com', 'ariana', '2025-04-23 18:24:00', 'en préparation', '2025-04-20 17:24:34', '2025-04-20 17:24:34', '', ''),
(15, 'oussama jaouadi', 'oussama.jaouadi.jd@gmail.com', 'ariana', '2025-05-01 18:26:00', 'en préparation', '2025-04-20 17:26:27', '2025-04-20 17:26:27', '', ''),
(16, 'oussama jaouadi', 'oussama.jaouadi.jd@gmail.com', 'ariana', '2025-04-24 18:35:00', 'en préparation', '2025-04-20 17:35:50', '2025-04-20 17:35:50', '', ''),
(18, 'boj', 'oussama.jaouadi.jd@gmail.com', 'tunis', '2025-04-26 18:41:00', 'livré', '2025-04-20 17:41:16', '2025-04-21 19:14:05', '', ''),
(19, 'oussama jaouadi', 'oussama.jaouadi.jd@gmail.com', 'ariana', '2025-04-26 20:26:00', 'livré', '2025-04-20 19:26:13', '2025-04-20 19:29:36', '', ''),
(20, 'oussama jaouadi', 'oussama.jaouadi.jd@gmail.com', 'ariana', '2025-03-02 11:01:00', 'en préparation', '2025-04-21 20:07:48', '2025-04-21 20:07:48', '', ''),
(21, 'oussama jaouadi', 'oussama.jaouadi.jd@gmail.com', 'ariana', '0000-00-00 00:00:00', 'en préparation', '2025-04-21 20:37:44', '2025-04-21 20:37:44', '', ''),
(22, 'oussama jaouadi', 'oussama.jaouadi.jd@gmail.com', 'ariana', '0000-00-00 00:00:00', 'en préparation', '2025-04-21 20:38:24', '2025-04-21 20:38:24', '', ''),
(23, 'oussama feriel', 'oussama.jaouadi.jd@gmail.com', 'ariana', '0000-00-00 00:00:00', 'en préparation', '2025-04-21 20:43:12', '2025-04-21 20:43:12', '', ''),
(24, 'oussama jaouadi', 'oussama.jaouadi.jd@gmail.com', 'ariana', '0000-00-00 00:00:00', 'en préparation', '2025-04-21 21:24:25', '2025-04-21 21:24:25', '', ''),
(25, 'oussama jaouadi', 'oussama.jaouadi.jd@gmail.com', 'ariana', '0000-00-00 00:00:00', 'en préparation', '2025-04-21 21:31:48', '2025-04-21 21:31:48', '', ''),
(26, 'oussama jaouadi', 'oussama.jaouadi.jd@gmail.com', 'ariana', '0000-00-00 00:00:00', 'en préparation', '2025-04-21 21:39:13', '2025-04-21 21:39:13', '', ''),
(27, 'oussama jaouadi', 'oussama.jaouadi.jd@gmail.com', 'ariana', '2026-06-03 01:00:00', 'en préparation', '2025-04-21 21:46:01', '2025-04-21 21:46:01', '', ''),
(28, 'oussama jaouadi', 'oussama.jaouadi.jd@gmail.com', 'ariana', '2027-03-03 01:00:00', 'en préparation', '2025-04-21 21:49:24', '2025-04-21 21:49:24', '', ''),
(29, 'oussama jaouadi', 'oussama.jaouadi.jd@gmail.com', 'ariana', '2025-06-01 02:00:00', 'en préparation', '2025-04-21 22:59:05', '2025-04-21 22:59:05', '', ''),
(30, 'oussama jaouadi', 'oussama.jaouadi.jd@gmail.com', 'ariana', '2025-12-01 01:00:00', 'en préparation', '2025-04-21 23:02:14', '2025-04-21 23:02:14', '', ''),
(31, 'oussama jaouadi', 'oussama.jaouadi.jd@gmail.com', 'ariana', '2025-06-01 01:00:00', 'en attente', '2025-04-21 22:30:31', '2025-04-21 22:30:31', '', ''),
(32, 'oussama jaouadi', 'oussama.jaouadi.jd@gmail.com', 'ariana', '2026-01-01 01:02:00', 'en attente', '2025-04-21 22:32:04', '2025-04-21 22:32:04', '', ''),
(33, 'oussama jaouadi', 'oussama.jaouadi.jd@gmail.com', 'ariana', '2027-04-03 01:00:00', 'en préparation', '2025-04-22 00:41:43', '2025-05-03 18:44:35', '', ''),
(34, 'haddad ayoub', 'ayoubmed72@gmail.com', 'ariana', '2025-10-05 09:40:00', 'en attente', '2025-05-03 19:06:09', '2025-05-03 19:06:09', '35.843143, 10.112915', '36.816706, 11.002808'),
(35, 'haddad ayoub', 'ayoubmed72@gmail.com', 'ariana', '2025-10-05 09:40:00', 'en attente', '2025-05-03 19:13:16', '2025-05-03 19:13:16', '35.170441, 9.541626', '36.972935, 9.849243'),
(36, 'haddad ayoub', 'ayoubmed72@gmail.com', 'ariana', '2025-10-05 09:40:00', 'en attente', '2025-05-03 19:13:52', '2025-05-03 19:13:52', '34.269417, 9.475708', '35.593390, 10.827026');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `commandes`
--
ALTER TABLE `commandes`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `commandes`
--
ALTER TABLE `commandes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
