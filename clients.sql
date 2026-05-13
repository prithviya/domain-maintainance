-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 01, 2026 at 10:27 AM
-- Server version: 11.8.6-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u874184579_renewalswebapp`
--

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(180) NOT NULL,
  `company` varchar(180) DEFAULT NULL,
  `email` varchar(190) NOT NULL,
  `phone` varchar(80) DEFAULT NULL,
  `status` enum('Active','Disabled') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `name`, `company`, `email`, `phone`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Mohan', 'Flyi Toys', '', '96007 76508', 'Active', '2026-03-27 04:47:24', '2026-03-27 04:47:24', NULL),
(2, 'Tony', 'Green Safe', '', '99999 99999', 'Active', '2026-03-27 05:20:14', '2026-03-27 05:20:14', NULL),
(3, 'Imran', 'Saradeuz', '', '99999 99999', 'Active', '2026-03-27 05:27:39', '2026-03-27 05:27:39', NULL),
(4, 'Mr.X', 'Polo Premier', '', '99999 99999', 'Active', '2026-03-27 05:29:37', '2026-03-27 05:29:37', NULL),
(5, 'Purnaya', 'Purnaya', '', '99999 99999', 'Active', '2026-03-27 05:53:48', '2026-03-27 05:54:09', NULL),
(6, 'SkyX', 'SkyX - collaborator access', '', '99999 99999', 'Active', '2026-03-27 05:55:48', '2026-03-27 05:55:48', NULL),
(7, 'AdithyaTech', 'adithyatec', '', '99999 99999', 'Active', '2026-03-27 05:59:42', '2026-03-27 05:59:42', NULL),
(8, 'Dharani', 'Pump & Motors', '', '99999 99999', 'Disabled', '2026-03-27 06:01:34', '2026-03-27 09:38:21', NULL),
(9, 'Kitchen Captain - Shopify', 'Kitchen Captain', '', '99999 99999', 'Active', '2026-03-27 06:30:22', '2026-03-27 07:00:38', NULL),
(10, 'YEF', 'YEF', '', '99999 99999', 'Active', '2026-03-27 06:31:38', '2026-03-27 06:31:38', NULL),
(11, 'Mullai Aurora', 'Education', '', '99999 99999', 'Active', '2026-03-27 06:34:38', '2026-03-27 06:34:38', NULL),
(12, 'Legacy', 'Land Property', '', '99999 99999', 'Active', '2026-03-27 06:36:17', '2026-03-27 06:36:17', NULL),
(13, 'Gadli', 'UPVC & aluminium windows and doors', '', '99999 99999', 'Active', '2026-03-27 06:37:35', '2026-03-27 06:37:35', NULL),
(14, 'Tormac', 'Pump & Motors', '', '99999 99999', 'Active', '2026-03-27 06:38:47', '2026-03-27 06:38:47', NULL),
(15, 'Tree and Me', 'Interior', '', '99999 99999', 'Active', '2026-03-27 06:40:00', '2026-03-27 06:40:00', NULL),
(16, 'Daviser Prime', 'Pumps', '', '99999 99999', 'Active', '2026-03-27 06:41:16', '2026-03-27 06:41:16', NULL),
(17, 'Gemini Motors', 'Machinery', '', '99999 99999', 'Active', '2026-03-27 06:42:40', '2026-03-27 06:42:40', NULL),
(18, 'Nakodaa - Shopify', 'Textile', '', '99999 99999', 'Active', '2026-03-27 06:55:40', '2026-03-27 06:59:59', NULL),
(19, 'AET', 'Machinery', '', '99999 99999', 'Active', '2026-03-27 07:07:23', '2026-03-27 07:07:23', NULL),
(20, 'Hansontrends - Shopify', 'Clothing', '', '99999 99999', 'Active', '2026-03-27 07:08:30', '2026-03-27 07:08:52', NULL),
(21, 'Vishaka', 'Hotel', '', '99999 99999', 'Active', '2026-03-27 07:10:45', '2026-03-27 07:10:45', NULL),
(22, 'Gnanambiga', 'Cooking', '', '99999 99999', 'Active', '2026-03-27 07:11:37', '2026-03-27 07:11:37', NULL),
(23, 'Kshetra Annapoorani Foods - Shopify', 'Food Products', '', '99999 99999', 'Active', '2026-03-27 07:16:28', '2026-03-27 07:18:01', NULL),
(24, 'Webber', 'Machinery Tools', '', '99999 99999', 'Active', '2026-03-27 07:19:13', '2026-03-27 07:19:13', NULL),
(25, 'Swarnabala - Shopify', 'Handicraft Statues', '', '99999 99999', 'Disabled', '2026-03-27 07:25:18', '2026-03-27 07:26:20', NULL),
(26, 'Asvin', 'Building', '', '99999 99999', 'Active', '2026-03-27 07:36:29', '2026-03-27 07:36:29', NULL),
(27, 'Vinayaka', 'RMC', '', '99999 99999', 'Active', '2026-03-27 07:43:45', '2026-03-27 07:58:18', NULL),
(28, 'KumaranMedical', 'Hospital', '', '99999 99999', 'Active', '2026-03-27 07:54:30', '2026-03-27 07:54:30', NULL),
(29, 'GreenMedicare', 'Hospital Equipment', '', '99999 99999', 'Disabled', '2026-03-27 07:54:36', '2026-03-27 12:08:00', NULL),
(30, 'MSR', 'Coconut oil', '', '99999 99999', 'Active', '2026-03-27 09:33:31', '2026-03-27 09:33:54', NULL),
(31, 'Cbe Race Club', 'Car Race', '', '99999 99999', 'Active', '2026-03-27 10:08:26', '2026-03-27 10:08:26', NULL),
(32, 'Payanam', 'auto', '', '99999 99999', 'Active', '2026-03-27 10:28:27', '2026-03-27 10:28:27', NULL),
(33, 'Kubera', 'Land Property', '', '99999 99999', 'Active', '2026-03-27 10:48:21', '2026-03-27 10:48:21', NULL),
(34, 'IBC', 'Auditor', '', '99999 99999', 'Active', '2026-03-27 10:52:22', '2026-03-27 10:52:22', NULL),
(35, 'RAC', 'Motors', '', '99999 99999', 'Active', '2026-03-27 10:54:17', '2026-03-27 10:54:17', NULL),
(36, 'Aggroups', 'AGGroups', '', '99999 99999', 'Active', '2026-03-27 11:42:39', '2026-03-27 11:42:39', NULL),
(37, 'Beros', 'Food Products', '', '99999 99999', 'Active', '2026-03-27 11:45:35', '2026-03-27 11:45:35', NULL),
(38, 'PKA Bricks', 'Bricks', '', '99999 99999', 'Active', '2026-03-27 11:47:14', '2026-03-27 11:47:14', NULL),
(39, 'South India Motors', 'Car Service', '', '99999 99999', 'Active', '2026-03-27 11:54:01', '2026-03-27 11:54:01', NULL),
(40, 'RPFoundation', 'Education', '', '99999 99999', 'Active', '2026-03-27 11:57:07', '2026-03-27 11:57:07', NULL),
(41, 'Legendcorp', 'Legendcorp', '', '9876543210', 'Active', '2026-04-01 09:13:29', '2026-04-01 09:13:29', NULL),
(42, 'Kho Social', 'Kho Social LLP', '', '+91 91500 81302', 'Active', '2026-04-01 09:21:57', '2026-04-01 09:21:57', NULL),
(43, '#3 monks', 'DM', '', '99999 99999', 'Active', '2026-04-01 09:34:16', '2026-04-01 09:34:16', NULL),
(44, 'Bhagavan Gardens', 'Bhagavan Gardens', '', '9876543210', 'Active', '2026-04-01 09:34:29', '2026-04-01 09:34:29', NULL),
(45, 'SJ Info', 'DM', '', '99999 99999', 'Active', '2026-04-01 09:41:44', '2026-04-01 09:41:44', NULL),
(46, 'Zepte', 'Zepte', '', '99999 99999', 'Active', '2026-04-01 09:54:45', '2026-04-01 09:54:45', NULL),
(47, 'Texas', 'Texas', '', '99999 99999', 'Active', '2026-04-01 09:59:00', '2026-04-01 09:59:00', NULL),
(48, 'Unique', 'Events', '', '99999 99999', 'Active', '2026-04-01 09:59:34', '2026-04-01 09:59:34', NULL),
(49, 'sasuri', 'Education', '', '99999 99999', 'Active', '2026-04-01 10:01:17', '2026-04-01 10:01:17', NULL),
(50, 'EGS', '', '', '99999 99999', 'Active', '2026-04-01 10:08:31', '2026-04-01 10:08:31', NULL),
(51, 'Heytaya', '', '', '99999 99999', 'Active', '2026-04-01 10:09:14', '2026-04-01 10:09:14', NULL),
(52, 'Globe Medicare', 'Medical', '', '99999 99999', 'Active', '2026-04-01 10:11:56', '2026-04-01 10:11:56', NULL),
(53, 'Theaaronjohn', '', '', '99999 99999', 'Active', '2026-04-01 10:12:40', '2026-04-01 10:12:40', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_clients_deleted_at` (`deleted_at`),
  ADD KEY `idx_clients_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
