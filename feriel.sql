-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 05, 2025 at 03:09 PM
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
-- Database: `feriel`
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
(37, 'alii Najjaa', 'najjaaali3@gmail.com', 'tuniss', '2026-02-02 12:12:00', 'livré', '2025-05-03 21:38:26', '2025-05-04 19:31:58', '36.905431, 9.998657', '36.737508, 10.282379'),
(38, 'ferielll', 'feriel@esprit.tn', 'tunis', '2026-02-02 20:20:00', 'en attente', '2025-05-03 22:10:19', '2025-05-03 22:10:19', '36.718577, 9.782501', '36.858252, 10.248047'),
(39, 'dddddddd', 'dddd@gmail.com', 'Tunis', '2026-02-02 12:12:00', 'en attente', '2025-05-04 19:45:59', '2025-05-04 19:45:59', '36.161605, 8.693481', '35.739156, 10.819336'),
(40, 'souissi feriel', 'molkaajengui@gmail.com', 'Tunis', '2025-12-06 02:05:00', 'en attente', '2025-05-04 23:08:05', '2025-05-04 23:08:05', '35.104630, 6.714844', '35.050685, 11.658691'),
(41, 'haddad ayoub', 'ayoubmed72@gmail.com', 'ariana', '2025-06-25 12:12:00', 'en attente', '2025-05-05 09:20:26', '2025-05-05 09:20:26', '36.151626, 9.619629', '37.182640, 9.850342'),
(42, 'haddad ayoub', 'ayoubmed72@gmail.com', 'ariana', '2025-08-25 12:12:00', 'en attente', '2025-05-05 09:26:00', '2025-05-05 09:26:00', '35.486393, 7.133423', '35.785513, 8.880249'),
(43, 'haddad ayoub', 'ayoubmed72@gmail.com', 'ariana', '2025-08-26 12:12:00', 'en attente', '2025-05-05 09:30:20', '2025-05-05 09:30:20', '36.731290, 7.771729', '36.559401, 9.210937'),
(44, 'ali', 'ali@ali.ali', 'ariana', '2026-08-26 12:12:00', 'en attente', '2025-05-05 09:35:26', '2025-05-05 09:35:26', '36.743726, 10.212891', '38.492724, 16.299316'),
(45, 'haddad ayoub', 'ayoubmed72@gmail.com', 'ariana', '2025-08-26 12:12:00', 'en attente', '2025-05-05 13:06:37', '2025-05-05 13:06:37', '36.459729, 9.729492', '36.788172, -96.046875');

-- --------------------------------------------------------

--
-- Table structure for table `dishes`
--

CREATE TABLE `dishes` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dishes`
--

INSERT INTO `dishes` (`id`, `name`, `price`, `image_url`, `created_at`) VALUES
(8, 'Slata Mechouia', 10.00, 'public/uploads/dishes/6817accd2ddbb.jpg', '2025-05-04 18:07:09'),
(10, 'Mille feuille', 5.00, 'public/uploads/dishes/6817ad631a931.jpg', '2025-05-04 18:09:39'),
(13, 'makroudh', 5.00, 'public/uploads/dishes/6817ee36091ec.jpg', '2025-05-04 22:46:14'),
(14, 'Poisson farci au four', 20.00, 'public/uploads/dishes/6817ee7e2b815.jpg', '2025-05-04 22:47:26'),
(15, 'Yoyo', 6.00, 'public/uploads/dishes/6817eebfacfe6.jpg', '2025-05-04 22:48:31'),
(16, 'Rouz Jerbi', 14.00, 'public/uploads/dishes/6817eefa05e4b.jpg', '2025-05-04 22:49:30'),
(17, 'Lablabi', 10.00, 'public/uploads/dishes/6817ef201cf26.jpg', '2025-05-04 22:50:08'),
(18, 'Kafteji', 11.00, 'public/uploads/dishes/6817ef4221fd4.jpg', '2025-05-04 22:50:42'),
(19, 'Brik à l\'oeuf', 6.00, 'public/uploads/dishes/6817ef5d3a312.jpg', '2025-05-04 22:51:09'),
(20, 'Assidat Zgougou', 9.00, 'public/uploads/dishes/6817ef8f376fc.jpg', '2025-05-04 22:51:59'),
(21, 'Chakchouka', 8.00, 'public/uploads/dishes/6817efc1d1769.jpg', '2025-05-04 22:52:49'),
(22, 'Couscous', 15.00, 'public/uploads/dishes/6817f01d7e241.jpg', '2025-05-04 22:54:21');

-- --------------------------------------------------------

--
-- Table structure for table `dish_likes`
--

CREATE TABLE `dish_likes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `dish_id` int(11) NOT NULL,
  `action` enum('like','dislike') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dish_likes`
--

INSERT INTO `dish_likes` (`id`, `user_id`, `dish_id`, `action`, `created_at`) VALUES
(1, 1, 21, 'like', '2025-05-05 07:54:11'),
(2, 1, 20, 'dislike', '2025-05-05 07:54:24'),
(3, 1, 22, 'like', '2025-05-05 08:14:11'),
(4, 2, 21, 'like', '2025-05-05 08:14:33'),
(5, 2, 10, 'like', '2025-05-05 08:14:44'),
(6, 3, 10, 'like', '2025-05-05 08:24:51'),
(7, 3, 21, 'like', '2025-05-05 08:51:51'),
(8, 3, 16, 'dislike', '2025-05-05 09:33:38'),
(9, 3, 15, 'like', '2025-05-05 09:33:44'),
(10, 3, 22, 'dislike', '2025-05-05 13:05:44'),
(11, 3, 19, 'dislike', '2025-05-05 13:05:50'),
(12, 3, 14, 'dislike', '2025-05-05 13:05:56');

-- --------------------------------------------------------

--
-- Table structure for table `livraisons`
--

CREATE TABLE `livraisons` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `plats` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`plats`)),
  `delivery_time` datetime NOT NULL,
  `status` enum('en attente','en préparation','livré') DEFAULT 'en attente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_dishes`
--

CREATE TABLE `order_dishes` (
  `id` int(11) NOT NULL,
  `commande_id` int(11) NOT NULL,
  `dish_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_dishes`
--

INSERT INTO `order_dishes` (`id`, `commande_id`, `dish_id`, `quantity`) VALUES
(55, 39, 10, 3),
(56, 40, 14, 1),
(57, 41, 14, 1),
(58, 41, 13, 1),
(59, 41, 10, 1),
(60, 41, 8, 1),
(61, 42, 14, 1),
(62, 42, 13, 1),
(63, 43, 14, 1),
(64, 43, 13, 1),
(65, 44, 17, 6),
(66, 44, 16, 3),
(67, 44, 15, 3),
(68, 44, 10, 1),
(69, 44, 8, 1),
(70, 45, 21, 10);

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `client_name` varchar(100) NOT NULL,
  `client_email` varchar(100) NOT NULL,
  `client_phone` varchar(20) NOT NULL,
  `reservation_date` date NOT NULL,
  `reservation_time` time NOT NULL,
  `guest_count` int(11) NOT NULL,
  `special_requests` text DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `client_name`, `client_email`, `client_phone`, `reservation_date`, `reservation_time`, `guest_count`, `special_requests`, `status`, `created_at`, `updated_at`) VALUES
(3, 'oussama jaouadi', 'oussama.jaouadi.jd@gmail.com', '29689389', '2025-04-21', '11:00:00', 1, 'vvv', 'pending', '2025-04-20 07:00:33', '2025-04-20 07:00:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `commandes`
--
ALTER TABLE `commandes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dishes`
--
ALTER TABLE `dishes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dish_likes`
--
ALTER TABLE `dish_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`dish_id`);

--
-- Indexes for table `livraisons`
--
ALTER TABLE `livraisons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_dishes`
--
ALTER TABLE `order_dishes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`commande_id`),
  ADD KEY `dish_id` (`dish_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reservation_date` (`reservation_date`),
  ADD KEY `idx_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `commandes`
--
ALTER TABLE `commandes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `dishes`
--
ALTER TABLE `dishes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `dish_likes`
--
ALTER TABLE `dish_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `livraisons`
--
ALTER TABLE `livraisons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_dishes`
--
ALTER TABLE `order_dishes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_dishes`
--
ALTER TABLE `order_dishes`
  ADD CONSTRAINT `order_dishes_ibfk_1` FOREIGN KEY (`commande_id`) REFERENCES `commandes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_dishes_ibfk_2` FOREIGN KEY (`dish_id`) REFERENCES `dishes` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
