-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 01, 2026 at 10:28 AM
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
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `service_type` enum('domain','hosting') NOT NULL,
  `name` varchar(190) NOT NULL,
  `provider` varchar(190) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `renewal_date` date DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `ownership_type` enum('our','client') NOT NULL DEFAULT 'client',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `client_id`, `service_type`, `name`, `provider`, `url`, `renewal_date`, `amount`, `ownership_type`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'domain', 'https://myflyi.com/', NULL, NULL, '2026-08-03', 1599.00, 'our', '2026-03-27 05:18:44', '2026-04-01 09:48:05', NULL),
(2, 2, 'hosting', 'https://www.greensafe.com.sg/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 05:22:55', '2026-03-27 10:11:22', NULL),
(3, 3, 'hosting', 'https://saradeuz.com/', NULL, NULL, NULL, 0.00, 'our', '2026-03-27 05:30:08', '2026-03-27 10:31:34', NULL),
(4, 4, 'domain', 'https://pplindia.co.in/', NULL, NULL, '2026-09-02', 749.00, 'our', '2026-03-27 05:30:42', '2026-04-01 10:09:14', NULL),
(5, 4, 'domain', 'https://eclindia.co.in/', NULL, NULL, '2026-09-02', 749.00, 'our', '2026-03-27 05:40:38', '2026-04-01 10:09:41', NULL),
(6, 4, 'domain', 'https://equinesportsindia.com/', NULL, NULL, '2026-09-02', 1599.00, 'our', '2026-03-27 05:42:37', '2026-04-01 10:07:51', NULL),
(7, 5, 'hosting', 'https://purnayadevelopers.com/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 05:57:32', '2026-03-27 09:54:15', NULL),
(8, 6, 'domain', 'https://skyx.co.in/', NULL, NULL, NULL, 0.00, 'our', '2026-03-27 05:58:17', '2026-03-27 05:58:17', NULL),
(9, 7, 'hosting', 'https://adithyatec.com/', NULL, NULL, NULL, 0.00, 'our', '2026-03-27 05:59:51', '2026-03-27 10:27:04', NULL),
(10, 8, 'domain', 'https://dharani-india.com/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 06:01:46', '2026-03-27 06:01:46', NULL),
(11, 8, 'hosting', 'https://dharani-india.com/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 06:01:46', '2026-03-27 06:01:46', NULL),
(12, 9, 'domain', 'https://kitchenscaptain.com/', NULL, NULL, '2026-12-21', 1599.00, 'our', '2026-03-27 06:30:48', '2026-04-01 10:14:29', NULL),
(13, 10, 'domain', 'https://www.yefederation.org/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 06:31:59', '2026-03-27 06:32:35', NULL),
(14, 11, 'domain', 'https://mullaiacademia.com/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 06:35:29', '2026-03-27 06:35:29', NULL),
(15, 11, 'domain', 'http://www.auroraoverseas.com/', NULL, NULL, '2026-05-09', 1599.00, 'our', '2026-03-27 06:35:29', '2026-04-01 09:20:59', NULL),
(16, 12, 'domain', 'https://legacyventures.in/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 06:36:27', '2026-03-27 06:36:27', NULL),
(17, 13, 'domain', 'https://gadli.com/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 06:37:48', '2026-03-27 06:37:48', NULL),
(18, 14, 'domain', 'https://tormacpumps.com/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 06:38:56', '2026-03-27 06:38:56', NULL),
(19, 14, 'hosting', 'https://tormacpumps.com/', NULL, NULL, NULL, 0.00, 'our', '2026-03-27 06:38:56', '2026-03-27 09:39:51', NULL),
(20, 15, 'hosting', 'https://treeandme.in/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 06:40:15', '2026-03-27 12:34:09', NULL),
(21, 16, 'domain', 'https://daviscerprime.ae/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 06:41:35', '2026-03-27 06:41:35', NULL),
(22, 16, 'hosting', 'https://daviscerprime.ae/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 06:41:35', '2026-03-27 06:41:35', NULL),
(23, 17, 'domain', 'https://geminimachinery.com/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 06:42:51', '2026-03-27 06:42:51', NULL),
(24, 18, 'domain', 'https://www.shreenakodaa.com/', NULL, NULL, '2027-06-18', 1599.00, 'our', '2026-03-27 06:55:51', '2026-04-01 10:16:44', NULL),
(25, 19, 'hosting', 'http://affordableet.com/', NULL, NULL, NULL, 0.00, 'our', '2026-03-27 07:07:38', '2026-03-27 09:51:12', NULL),
(26, 20, 'domain', 'https://hansontrends.com/', NULL, NULL, '2026-07-31', 1599.00, 'our', '2026-03-27 07:08:41', '2026-04-01 09:43:47', NULL),
(27, 21, 'hosting', 'https://vishakapaark.com/', NULL, NULL, NULL, 0.00, 'our', '2026-03-27 07:10:55', '2026-03-27 12:07:09', NULL),
(28, 22, 'hosting', 'https://gnanambiga.in/', NULL, NULL, '2026-07-10', 899.00, 'our', '2026-03-27 07:11:48', '2026-04-01 09:40:37', NULL),
(29, 22, 'domain', 'https://clipsandthreads.myshopify.com/', NULL, NULL, NULL, 0.00, 'our', '2026-03-27 07:13:16', '2026-03-27 12:05:21', '2026-03-27 12:05:21'),
(30, 23, 'domain', 'https://kshetrafoods.com', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 07:16:54', '2026-03-27 07:16:54', NULL),
(31, 24, 'hosting', 'https://webberhydraulics.com/', NULL, NULL, '2026-07-22', 1599.00, 'our', '2026-03-27 07:19:28', '2026-04-01 09:41:20', NULL),
(32, 24, 'hosting', 'https://webbercrimpex.com/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 07:20:42', '2026-03-27 09:42:42', NULL),
(33, 25, 'hosting', 'https://swarnabalas.myshopify.com/', NULL, NULL, NULL, 0.00, 'our', '2026-03-27 07:25:58', '2026-03-27 07:25:58', NULL),
(34, 26, 'domain', 'https://asvin.in/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 07:41:24', '2026-03-27 07:41:24', NULL),
(35, 26, 'hosting', 'https://asvin.in/', NULL, NULL, NULL, 0.00, 'our', '2026-03-27 07:41:24', '2026-03-27 07:41:39', NULL),
(36, 28, 'hosting', 'https://kumarancollegeofnursing.com/', NULL, NULL, NULL, 0.00, 'our', '2026-03-27 07:55:14', '2026-03-27 10:53:27', NULL),
(37, 28, 'hosting', 'https://kumaranmedical.com/', NULL, NULL, NULL, 0.00, 'our', '2026-03-27 07:55:14', '2026-03-27 10:53:06', NULL),
(38, 27, 'domain', 'https://www.srivinayakarmc.com/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 07:58:27', '2026-03-27 12:08:45', '2026-03-27 12:08:45'),
(39, 27, 'hosting', 'https://www.srivinayakarmc.com/', NULL, NULL, NULL, 0.00, 'our', '2026-03-27 07:58:27', '2026-03-27 11:43:34', NULL),
(40, 29, 'domain', 'https://greenmedicare.com/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 07:59:46', '2026-03-27 07:59:46', NULL),
(41, 29, 'hosting', 'https://greenmedicare.com/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 07:59:46', '2026-03-27 07:59:46', NULL),
(42, 30, 'hosting', 'msrcoconutoil.com', NULL, NULL, NULL, 0.00, 'our', '2026-03-27 09:33:42', '2026-03-27 10:28:00', NULL),
(43, 31, 'domain', 'https://casc.co.in/', NULL, NULL, NULL, 0.00, 'our', '2026-03-27 10:08:45', '2026-03-27 10:18:17', '2026-03-27 10:18:17'),
(44, 31, 'hosting', 'https://casc.co.in/', NULL, NULL, '2027-07-05', 749.00, 'our', '2026-03-27 10:08:45', '2026-04-01 10:17:14', NULL),
(45, 31, 'hosting', 'pollachimotorcycleclub.com', NULL, NULL, NULL, 0.00, 'our', '2026-03-27 10:10:50', '2026-04-01 09:39:03', NULL),
(46, 32, 'hosting', 'https://thepayanam.com/', NULL, NULL, NULL, 0.00, 'our', '2026-03-27 10:28:40', '2026-03-27 10:28:40', NULL),
(47, 33, 'hosting', 'https://kubproperties.com/', NULL, NULL, '2026-07-25', 1599.00, 'our', '2026-03-27 10:50:41', '2026-04-01 09:42:03', NULL),
(48, 34, 'hosting', 'ibccerts.com', NULL, NULL, NULL, 0.00, 'our', '2026-03-27 10:52:33', '2026-03-27 10:52:33', NULL),
(49, 35, 'hosting', 'https://renugaaircompressor.com/', NULL, NULL, NULL, 0.00, 'our', '2026-03-27 10:54:25', '2026-03-27 10:56:04', NULL),
(50, 1, 'hosting', 'https://toolcom.in/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 10:55:37', '2026-03-27 10:55:37', NULL),
(51, 36, 'hosting', 'aggroups.in', NULL, NULL, NULL, 0.00, 'our', '2026-03-27 11:42:54', '2026-03-27 11:42:54', NULL),
(52, 37, 'hosting', 'https://beros.in/', NULL, NULL, NULL, 0.00, 'our', '2026-03-27 11:45:49', '2026-03-27 11:55:24', NULL),
(53, 38, 'hosting', 'https://pkabricks.com/', NULL, NULL, NULL, 0.00, 'our', '2026-03-27 11:47:52', '2026-03-27 11:48:02', NULL),
(54, 39, 'hosting', 'https://southindiamotors.in/', NULL, NULL, '2026-06-21', 899.00, 'our', '2026-03-27 11:54:08', '2026-04-01 09:39:31', NULL),
(55, 40, 'hosting', 'https://rpfoundation.org.in/', NULL, NULL, NULL, 0.00, 'our', '2026-03-27 11:57:25', '2026-03-27 11:57:25', NULL),
(56, 17, 'hosting', 'https://clipsandthreads.myshopify.com/', NULL, NULL, NULL, 0.00, 'our', '2026-03-27 12:05:40', '2026-03-27 12:05:40', NULL),
(57, 40, 'domain', 'https://treeandme.in/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 12:34:29', '2026-03-27 12:34:35', '2026-03-27 12:34:35'),
(58, 15, 'domain', 'https://treeandme.in/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 12:34:58', '2026-03-27 12:34:58', NULL),
(59, 40, 'domain', 'https://rpfoundation.org.in/', NULL, NULL, '2026-07-05', 749.00, 'our', '2026-04-01 09:08:50', '2026-04-01 09:08:50', NULL),
(60, 41, 'domain', 'https://legendcorp.in/', NULL, NULL, '2026-02-16', 899.00, 'our', '2026-04-01 09:17:32', '2026-04-01 09:17:32', NULL),
(61, 36, 'domain', 'aggroups.in', NULL, NULL, '2026-02-19', 899.00, 'our', '2026-04-01 09:18:05', '2026-04-01 09:18:05', NULL),
(62, 34, 'domain', 'ibccerts.com', NULL, NULL, '2026-04-18', 1591.00, 'our', '2026-04-01 09:18:41', '2026-04-01 09:18:41', NULL),
(63, 19, 'domain', 'http://affordableet.com/', NULL, NULL, '2026-05-02', 1599.00, 'our', '2026-04-01 09:19:24', '2026-04-01 09:19:24', NULL),
(64, 28, 'domain', 'https://kumarancollegeofnursing.com/', NULL, NULL, '2026-05-02', 1599.00, 'our', '2026-04-01 09:19:58', '2026-04-01 09:19:58', NULL),
(65, 42, 'domain', 'khosocial.com', NULL, NULL, '2026-05-16', 1599.00, 'our', '2026-04-01 09:22:34', '2026-04-01 09:22:34', NULL),
(66, 42, 'domain', 'http://khosocial.net/', NULL, NULL, '2026-05-16', 1799.00, 'our', '2026-04-01 09:24:02', '2026-04-01 09:24:02', NULL),
(67, 42, 'domain', 'https://khosocial.org/', NULL, NULL, '2026-05-16', 1599.00, 'our', '2026-04-01 09:27:59', '2026-04-01 09:27:59', NULL),
(68, 42, 'domain', 'https://kho.social/', NULL, NULL, '2026-05-16', 6036.00, 'our', '2026-04-01 09:28:40', '2026-04-01 09:28:40', NULL),
(69, 42, 'domain', 'https://khosocial.in/', NULL, NULL, '2026-05-16', 899.00, 'our', '2026-04-01 09:31:09', '2026-04-01 09:31:09', NULL),
(70, 42, 'domain', 'https://khosocial.info/', NULL, NULL, '2026-05-16', 2999.00, 'our', '2026-04-01 09:31:52', '2026-04-01 09:31:52', NULL),
(71, 43, 'domain', '3monksdigital.in', NULL, NULL, '2026-06-02', 899.00, 'our', '2026-04-01 09:34:24', '2026-04-01 09:36:23', NULL),
(72, 43, 'domain', '3monksdigital.com', NULL, NULL, '2026-06-14', 1599.00, 'our', '2026-04-01 09:34:48', '2026-04-01 09:38:43', NULL),
(73, 44, 'domain', 'bhagavangardens.com', NULL, NULL, '2026-05-17', 1599.00, 'our', '2026-04-01 09:34:48', '2026-04-01 09:34:48', NULL),
(74, 43, 'domain', '3monks.in', NULL, NULL, '2026-06-15', 899.00, 'our', '2026-04-01 09:35:00', '2026-04-01 09:39:04', NULL),
(75, 42, 'domain', 'https://khode.in/', NULL, NULL, '2026-05-03', 899.00, 'our', '2026-04-01 09:37:00', '2026-04-01 09:37:00', NULL),
(76, 31, 'domain', 'pollachimotorcycleclub.com', NULL, NULL, '2026-08-03', 1599.00, 'our', '2026-04-01 09:38:46', '2026-04-01 09:46:00', NULL),
(77, 21, 'domain', 'vishakapark.com', NULL, NULL, '2026-08-06', 1599.00, 'our', '2026-04-01 09:40:44', '2026-04-01 09:50:19', NULL),
(78, 45, 'hosting', 'https://sjinfotechs.com/', NULL, NULL, NULL, 0.00, 'our', '2026-04-01 09:42:21', '2026-04-01 09:44:10', NULL),
(79, 45, 'domain', 'https://sjinfotechs.com/', NULL, NULL, '2026-08-11', 1599.00, 'our', '2026-04-01 09:42:21', '2026-04-01 09:51:10', NULL),
(80, 45, 'domain', 'sjinfotech.co', NULL, NULL, '2026-08-11', 1599.00, 'our', '2026-04-01 09:42:42', '2026-04-01 09:51:24', NULL),
(81, 1, 'domain', 'https://toolcom.in/', NULL, NULL, '2026-07-30', 899.00, 'our', '2026-04-01 09:43:06', '2026-04-01 09:43:06', NULL),
(82, 42, 'domain', 'khodeacademy.com', NULL, NULL, NULL, 0.00, 'our', '2026-04-01 09:43:18', '2026-04-01 09:54:14', '2026-04-01 09:54:14'),
(83, 42, 'domain', 'khodecampus.com', NULL, NULL, '2026-08-27', 1599.00, 'our', '2026-04-01 09:43:50', '2026-04-01 09:54:37', NULL),
(84, 38, 'domain', 'https://pkabricks.com/', NULL, NULL, '2026-08-05', 1599.00, 'our', '2026-04-01 09:48:55', '2026-04-01 09:48:55', NULL),
(85, 32, 'domain', 'https://thepayanam.com/', NULL, NULL, '2026-08-19', 1599.00, 'our', '2026-04-01 09:52:19', '2026-04-01 09:52:19', NULL),
(86, 42, 'domain', 'https://khodeacademy.com/', NULL, NULL, '2026-08-27', 1599.00, 'our', '2026-04-01 09:53:41', '2026-04-01 09:53:41', NULL),
(87, 46, 'domain', 'atmcin.com', NULL, NULL, '2026-09-01', 1599.00, 'our', '2026-04-01 09:55:20', '2026-04-01 10:05:09', NULL),
(88, 46, 'domain', 'plusatm.com', NULL, NULL, '2026-09-02', 1599.00, 'our', '2026-04-01 09:55:20', '2026-04-01 10:05:55', NULL),
(89, 4, 'domain', 'equinesportsindia.in', NULL, NULL, '2026-09-02', 899.00, 'our', '2026-04-01 09:56:27', '2026-04-01 10:08:41', NULL),
(90, 4, 'domain', 'equinesports.in', NULL, NULL, '2026-09-02', 899.00, 'our', '2026-04-01 09:57:44', '2026-04-01 10:10:10', NULL),
(91, 47, 'domain', 'texasclothing.in', NULL, NULL, '2026-09-05', 899.00, 'our', '2026-04-01 09:59:09', '2026-04-01 10:11:06', NULL),
(92, 48, 'domain', 'uniquesurpriseevents.com', NULL, NULL, '2026-09-07', 1599.00, 'our', '2026-04-01 09:59:39', '2026-04-01 10:11:58', NULL),
(93, 49, 'domain', 'sasuriepdasalaicbse.com', NULL, NULL, '2026-09-12', 1599.00, 'our', '2026-04-01 10:02:13', '2026-04-01 10:12:26', NULL),
(94, 49, 'domain', 'sasuriepdasalaimatric.com', NULL, NULL, '2026-09-12', 1599.00, 'our', '2026-04-01 10:02:26', '2026-04-01 10:12:49', NULL),
(95, 42, 'domain', 'khocart.com', NULL, NULL, '2026-10-31', 1599.00, 'client', '2026-04-01 10:04:27', '2026-04-01 10:13:14', NULL),
(96, 35, 'domain', 'renugaircompressor.com', NULL, NULL, '2026-12-14', 1599.00, 'our', '2026-04-01 10:04:46', '2026-04-01 10:13:48', NULL),
(97, 50, 'domain', 'egsind.com', NULL, NULL, '2027-01-30', 1599.00, 'our', '2026-04-01 10:08:40', '2026-04-01 10:15:24', NULL),
(98, 51, 'domain', 'heytaya.com', NULL, NULL, '2027-02-11', 1599.00, 'our', '2026-04-01 10:09:19', '2026-04-01 10:15:58', NULL),
(99, 52, 'domain', 'globemedicare.net', NULL, NULL, '2027-10-08', 1799.00, 'our', '2026-04-01 10:12:04', '2026-04-01 10:17:51', NULL),
(100, 53, 'domain', 'https://theaaronjohn.com/', NULL, NULL, '2028-06-23', 1599.00, 'our', '2026-04-01 10:12:47', '2026-04-01 10:18:25', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_services_client_deleted` (`client_id`,`deleted_at`),
  ADD KEY `idx_services_type_deleted` (`service_type`,`deleted_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `fk_services_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
