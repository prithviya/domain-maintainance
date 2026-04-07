-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 07, 2026 at 06:44 AM
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
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(120) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `table_name` varchar(80) NOT NULL,
  `row_id` bigint(20) UNSIGNED NOT NULL,
  `action_type` enum('insert','update','delete') NOT NULL,
  `actor` varchar(120) DEFAULT NULL,
  `action_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `meta_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta_json`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `billings`
--

CREATE TABLE `billings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `service_type` enum('domain','hosting') NOT NULL,
  `service_ref` varchar(190) NOT NULL,
  `renewal_date` date NOT NULL,
  `last_billing_date` date DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('Active','Paid','Disabled') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `payment_mode` enum('Bank Transfer','GPay') NOT NULL DEFAULT 'Bank Transfer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `billings`
--

INSERT INTO `billings` (`id`, `client_id`, `service_type`, `service_ref`, `renewal_date`, `last_billing_date`, `amount`, `status`, `created_at`, `updated_at`, `deleted_at`, `payment_mode`) VALUES
(2, 14, 'hosting', 'https://tormacpumps.com/', '2027-02-03', '2026-02-03', 4500.00, 'Active', '2026-04-01 11:35:35', '2026-04-01 11:35:35', NULL, 'Bank Transfer');

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
  `deleted_at` timestamp NULL DEFAULT NULL,
  `renewal_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `name`, `company`, `email`, `phone`, `status`, `created_at`, `updated_at`, `deleted_at`, `renewal_date`) VALUES
(1, 'Mohan', 'Flyi Toys', '', '96007 76508', 'Active', '2026-03-27 04:47:24', '2026-03-27 04:47:24', NULL, NULL),
(2, 'Green Safe', 'Green Safe', '', '99999 99999', 'Active', '2026-03-27 05:20:14', '2026-04-01 11:52:25', NULL, NULL),
(3, 'Imran', 'Saradeuz', '', '99999 99999', 'Active', '2026-03-27 05:27:39', '2026-03-27 05:27:39', NULL, NULL),
(4, 'Polo premier', 'Polo Premier', '', '99999 99999', 'Active', '2026-03-27 05:29:37', '2026-04-01 12:02:01', NULL, NULL),
(5, 'Purnaya', 'Purnaya', '', '99999 99999', 'Active', '2026-03-27 05:53:48', '2026-03-27 05:54:09', NULL, NULL),
(6, 'SkyX', 'SkyX - collaborator access', '', '99999 99999', 'Active', '2026-03-27 05:55:48', '2026-03-27 05:55:48', NULL, NULL),
(7, 'AdithyaTech', 'adithyatec', '', '99999 99999', 'Active', '2026-03-27 05:59:42', '2026-03-27 05:59:42', NULL, NULL),
(8, 'Dharani', 'Pump & Motors', '', '99999 99999', 'Disabled', '2026-03-27 06:01:34', '2026-03-27 09:38:21', NULL, NULL),
(9, 'Kitchen Captain - Shopify', 'Kitchen Captain', '', '99999 99999', 'Active', '2026-03-27 06:30:22', '2026-03-27 07:00:38', NULL, NULL),
(10, 'YEF', 'YEF', '', '99999 99999', 'Active', '2026-03-27 06:31:38', '2026-03-27 06:31:38', NULL, NULL),
(11, 'Mullai Aurora', 'Education', '', '99999 99999', 'Active', '2026-03-27 06:34:38', '2026-03-27 06:34:38', NULL, NULL),
(12, 'Legacy', 'Land Property', '', '99999 99999', 'Active', '2026-03-27 06:36:17', '2026-03-27 06:36:17', NULL, NULL),
(13, 'Gadli', 'UPVC & aluminium windows and doors', '', '99999 99999', 'Active', '2026-03-27 06:37:35', '2026-03-27 06:37:35', NULL, NULL),
(14, 'Tormac', 'Pump & Motors', '', '99999 99999', 'Active', '2026-03-27 06:38:47', '2026-03-27 06:38:47', NULL, NULL),
(15, 'Tree and Me', 'Interior', '', '99999 99999', 'Active', '2026-03-27 06:40:00', '2026-03-27 06:40:00', NULL, NULL),
(16, 'Daviser Prime', 'Pumps', '', '99999 99999', 'Active', '2026-03-27 06:41:16', '2026-03-27 06:41:16', NULL, NULL),
(17, 'Gemini Motors', 'Machinery', '', '99999 99999', 'Active', '2026-03-27 06:42:40', '2026-03-27 06:42:40', NULL, NULL),
(18, 'Nakodaa - Shopify', 'Textile', '', '99999 99999', 'Active', '2026-03-27 06:55:40', '2026-03-27 06:59:59', NULL, NULL),
(19, 'AET', 'Machinery', '', '99999 99999', 'Active', '2026-03-27 07:07:23', '2026-03-27 07:07:23', NULL, NULL),
(20, 'Hansontrends - Shopify', 'Clothing', '', '99999 99999', 'Active', '2026-03-27 07:08:30', '2026-03-27 07:08:52', NULL, NULL),
(21, 'Vishaka', 'Hotel', '', '99999 99999', 'Active', '2026-03-27 07:10:45', '2026-03-27 07:10:45', NULL, NULL),
(22, 'Gnanambiga', 'Cooking', '', '99999 99999', 'Active', '2026-03-27 07:11:37', '2026-03-27 07:11:37', NULL, NULL),
(23, 'Kshetra Annapoorani Foods - Shopify', 'Food Products', '', '99999 99999', 'Active', '2026-03-27 07:16:28', '2026-03-27 07:18:01', NULL, NULL),
(24, 'Webber', 'Machinery Tools', '', '99999 99999', 'Active', '2026-03-27 07:19:13', '2026-03-27 07:19:13', NULL, NULL),
(25, 'Swarnabala - Shopify', 'Handicraft Statues', '', '99999 99999', 'Disabled', '2026-03-27 07:25:18', '2026-03-27 07:26:20', NULL, NULL),
(26, 'Asvin', 'Building', '', '99999 99999', 'Active', '2026-03-27 07:36:29', '2026-03-27 07:36:29', NULL, NULL),
(27, 'Vinayaka', 'RMC', '', '99999 99999', 'Active', '2026-03-27 07:43:45', '2026-03-27 07:58:18', NULL, NULL),
(28, 'KumaranMedical', 'Hospital', '', '99999 99999', 'Active', '2026-03-27 07:54:30', '2026-03-27 07:54:30', NULL, NULL),
(29, 'GreenMedicare', 'Hospital Equipment', '', '99999 99999', 'Disabled', '2026-03-27 07:54:36', '2026-03-27 12:08:00', NULL, NULL),
(30, 'MSR', 'Coconut oil', '', '99999 99999', 'Active', '2026-03-27 09:33:31', '2026-03-27 09:33:54', NULL, NULL),
(31, 'Cbe Race Club', 'Car Race', '', '99999 99999', 'Active', '2026-03-27 10:08:26', '2026-03-27 10:08:26', NULL, NULL),
(32, 'Payanam', 'auto', '', '99999 99999', 'Active', '2026-03-27 10:28:27', '2026-03-27 10:28:27', NULL, NULL),
(33, 'Kubera', 'Land Property', '', '99999 99999', 'Active', '2026-03-27 10:48:21', '2026-03-27 10:48:21', NULL, NULL),
(34, 'IBC', 'Auditor', '', '99999 99999', 'Active', '2026-03-27 10:52:22', '2026-03-27 10:52:22', NULL, NULL),
(35, 'RAC', 'Motors', '', '99999 99999', 'Active', '2026-03-27 10:54:17', '2026-03-27 10:54:17', NULL, NULL),
(36, 'Aggroups', 'AGGroups', '', '99999 99999', 'Active', '2026-03-27 11:42:39', '2026-03-27 11:42:39', NULL, NULL),
(37, 'Beros', 'Food Products', '', '99999 99999', 'Active', '2026-03-27 11:45:35', '2026-03-27 11:45:35', NULL, NULL),
(38, 'PKA Bricks', 'Bricks', '', '99999 99999', 'Active', '2026-03-27 11:47:14', '2026-03-27 11:47:14', NULL, NULL),
(39, 'South India Motors', 'Car Service', '', '99999 99999', 'Active', '2026-03-27 11:54:01', '2026-03-27 11:54:01', NULL, NULL),
(40, 'RPFoundation', 'Education', '', '99999 99999', 'Active', '2026-03-27 11:57:07', '2026-03-27 11:57:07', NULL, NULL),
(41, 'Legendcorp', 'Legendcorp', '', '9876543210', 'Active', '2026-04-01 09:13:29', '2026-04-06 06:51:57', NULL, NULL),
(42, 'Kho Social', 'Kho Social LLP', '', '+91 91500 81302', 'Active', '2026-04-01 09:21:57', '2026-04-01 09:21:57', NULL, NULL),
(43, '#3 monks', 'DM', '', '99999 99999', 'Active', '2026-04-01 09:34:16', '2026-04-01 09:34:16', NULL, NULL),
(44, 'Bhagavan Gardens', 'Bhagavan Gardens', '', '9876543210', 'Active', '2026-04-01 09:34:29', '2026-04-01 09:34:29', NULL, NULL),
(45, 'SJ Info', 'DM', '', '99999 99999', 'Active', '2026-04-01 09:41:44', '2026-04-01 09:41:44', NULL, NULL),
(46, 'Zepte', 'Zepte', '', '99999 99999', 'Active', '2026-04-01 09:54:45', '2026-04-01 09:54:45', NULL, NULL),
(47, 'Texas', 'Texas', '', '99999 99999', 'Active', '2026-04-01 09:59:00', '2026-04-01 09:59:00', NULL, NULL),
(48, 'Unique', 'Events', '', '99999 99999', 'Active', '2026-04-01 09:59:34', '2026-04-01 09:59:34', NULL, NULL),
(49, 'sasuri', 'Education', '', '99999 99999', 'Active', '2026-04-01 10:01:17', '2026-04-01 10:01:17', NULL, NULL),
(50, 'EGS', '', '', '99999 99999', 'Active', '2026-04-01 10:08:31', '2026-04-01 10:08:31', NULL, NULL),
(51, 'Heytaya', '', '', '99999 99999', 'Active', '2026-04-01 10:09:14', '2026-04-01 10:09:14', NULL, NULL),
(52, 'Globe Medicare', 'Medical', '', '99999 99999', 'Active', '2026-04-01 10:11:56', '2026-04-01 10:11:56', NULL, NULL),
(53, 'Theaaronjohn', '', '', '99999 99999', 'Active', '2026-04-01 10:12:40', '2026-04-01 10:12:40', NULL, NULL),
(54, 'Revathi', 'Education', '', '99999 99999', 'Active', '2026-04-01 12:30:50', '2026-04-01 12:31:28', NULL, '2023-12-18');

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
  `deleted_at` timestamp NULL DEFAULT NULL,
  `comment` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `client_id`, `service_type`, `name`, `provider`, `url`, `renewal_date`, `amount`, `ownership_type`, `created_at`, `updated_at`, `deleted_at`, `comment`) VALUES
(1, 1, 'domain', 'https://myflyi.com/', NULL, NULL, '2026-08-03', 1599.00, 'our', '2026-03-27 05:18:44', '2026-04-01 09:48:05', NULL, NULL),
(2, 2, 'hosting', 'https://www.greensafe.com.sg/', NULL, NULL, '2026-06-23', 4500.00, 'our', '2026-03-27 05:22:55', '2026-04-01 12:10:40', NULL, NULL),
(3, 3, 'hosting', 'https://saradeuz.com/', NULL, NULL, '2026-05-16', 4500.00, 'our', '2026-03-27 05:30:08', '2026-04-01 11:51:48', NULL, NULL),
(4, 4, 'domain', 'https://pplindia.co.in/', NULL, NULL, '2026-09-02', 749.00, 'our', '2026-03-27 05:30:42', '2026-04-01 10:09:14', NULL, NULL),
(5, 4, 'domain', 'https://eclindia.co.in/', NULL, NULL, '2026-09-02', 749.00, 'our', '2026-03-27 05:40:38', '2026-04-01 10:09:41', NULL, NULL),
(6, 4, 'domain', 'https://equinesportsindia.com/', NULL, NULL, '2026-09-02', 1599.00, 'our', '2026-03-27 05:42:37', '2026-04-01 10:07:51', NULL, NULL),
(7, 5, 'hosting', 'https://purnayadevelopers.com/', NULL, NULL, '2026-11-09', 4500.00, 'our', '2026-03-27 05:57:32', '2026-04-01 11:56:54', NULL, NULL),
(8, 6, 'domain', 'https://skyx.co.in/', NULL, NULL, NULL, 0.00, 'our', '2026-03-27 05:58:17', '2026-03-27 05:58:17', NULL, NULL),
(9, 7, 'hosting', 'https://adithyatec.com/', NULL, NULL, '2027-06-29', 4500.00, 'our', '2026-03-27 05:59:51', '2026-04-01 11:49:55', NULL, NULL),
(10, 8, 'domain', 'https://dharani-india.com/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 06:01:46', '2026-03-27 06:01:46', NULL, NULL),
(11, 8, 'hosting', 'https://dharani-india.com/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 06:01:46', '2026-03-27 06:01:46', NULL, NULL),
(12, 9, 'domain', 'https://kitchenscaptain.com/', NULL, NULL, '2026-12-21', 1599.00, 'our', '2026-03-27 06:30:48', '2026-04-01 10:14:29', NULL, NULL),
(13, 10, 'domain', 'https://www.yefederation.org/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 06:31:59', '2026-03-27 06:32:35', NULL, NULL),
(14, 11, 'domain', 'https://mullaiacademia.com/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 06:35:29', '2026-03-27 06:35:29', NULL, NULL),
(15, 11, 'domain', 'http://www.auroraoverseas.com/', NULL, NULL, '2026-05-09', 1599.00, 'our', '2026-03-27 06:35:29', '2026-04-01 09:20:59', NULL, NULL),
(16, 12, 'domain', 'https://legacyventures.in/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 06:36:27', '2026-03-27 06:36:27', NULL, NULL),
(17, 13, 'domain', 'https://gadli.com/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 06:37:48', '2026-03-27 06:37:48', NULL, NULL),
(18, 14, 'domain', 'https://tormacpumps.com/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 06:38:56', '2026-03-27 06:38:56', NULL, NULL),
(19, 14, 'hosting', 'https://tormacpumps.com/', NULL, NULL, '2027-02-03', 4500.00, 'our', '2026-03-27 06:38:56', '2026-04-01 12:22:23', NULL, NULL),
(20, 15, 'hosting', 'https://treeandme.in/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 06:40:15', '2026-03-27 12:34:09', NULL, NULL),
(21, 16, 'domain', 'https://daviscerprime.ae/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 06:41:35', '2026-03-27 06:41:35', NULL, NULL),
(22, 16, 'hosting', 'https://daviscerprime.ae/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 06:41:35', '2026-03-27 06:41:35', NULL, NULL),
(23, 17, 'domain', 'https://geminimachinery.com/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 06:42:51', '2026-03-27 06:42:51', NULL, NULL),
(24, 18, 'domain', 'https://www.shreenakodaa.com/', NULL, NULL, '2027-06-18', 1599.00, 'our', '2026-03-27 06:55:51', '2026-04-01 10:16:44', NULL, NULL),
(25, 19, 'hosting', 'http://affordableet.com/', NULL, NULL, '2026-05-02', 4500.00, 'our', '2026-03-27 07:07:38', '2026-04-01 12:13:54', NULL, NULL),
(26, 20, 'domain', 'https://hansontrends.com/', NULL, NULL, '2026-07-31', 1599.00, 'our', '2026-03-27 07:08:41', '2026-04-01 09:43:47', NULL, NULL),
(27, 21, 'hosting', 'https://vishakapaark.com/', NULL, NULL, '2026-08-06', 4500.00, 'our', '2026-03-27 07:10:55', '2026-04-01 12:12:15', NULL, NULL),
(28, 22, 'hosting', 'https://gnanambiga.in/', NULL, NULL, '2026-07-10', 899.00, 'our', '2026-03-27 07:11:48', '2026-04-01 09:40:37', NULL, NULL),
(29, 22, 'domain', 'https://clipsandthreads.myshopify.com/', NULL, NULL, NULL, 0.00, 'our', '2026-03-27 07:13:16', '2026-03-27 12:05:21', '2026-03-27 12:05:21', NULL),
(30, 23, 'domain', 'https://kshetrafoods.com', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 07:16:54', '2026-03-27 07:16:54', NULL, NULL),
(31, 24, 'domain', 'https://webberhydraulics.com/', NULL, NULL, '2026-07-22', 1599.00, 'our', '2026-03-27 07:19:28', '2026-04-01 11:36:51', NULL, NULL),
(32, 24, 'hosting', 'https://webbercrimpex.com/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 07:20:42', '2026-03-27 09:42:42', NULL, NULL),
(33, 25, 'hosting', 'https://swarnabalas.myshopify.com/', NULL, NULL, NULL, 0.00, 'our', '2026-03-27 07:25:58', '2026-03-27 07:25:58', NULL, NULL),
(34, 26, 'domain', 'https://asvin.in/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 07:41:24', '2026-03-27 07:41:24', NULL, NULL),
(35, 26, 'hosting', 'https://asvin.in/', NULL, NULL, NULL, 0.00, 'our', '2026-03-27 07:41:24', '2026-03-27 07:41:39', NULL, NULL),
(36, 28, 'hosting', 'https://kumarancollegeofnursing.com/', NULL, NULL, '2026-05-02', 4500.00, 'our', '2026-03-27 07:55:14', '2026-04-01 12:34:42', NULL, NULL),
(37, 28, 'hosting', 'https://kumaranmedical.com/', NULL, NULL, NULL, 0.00, 'our', '2026-03-27 07:55:14', '2026-03-27 10:53:06', NULL, NULL),
(38, 27, 'domain', 'https://www.srivinayakarmc.com/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 07:58:27', '2026-03-27 12:08:45', '2026-03-27 12:08:45', NULL),
(39, 27, 'hosting', 'https://www.srivinayakarmc.com/', NULL, NULL, NULL, 0.00, 'our', '2026-03-27 07:58:27', '2026-03-27 11:43:34', NULL, NULL),
(40, 29, 'domain', 'https://greenmedicare.com/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 07:59:46', '2026-03-27 07:59:46', NULL, NULL),
(41, 29, 'hosting', 'https://greenmedicare.com/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 07:59:46', '2026-03-27 07:59:46', NULL, NULL),
(42, 30, 'hosting', 'msrcoconutoil.com', NULL, NULL, NULL, 4500.00, 'our', '2026-03-27 09:33:42', '2026-04-01 11:31:29', NULL, NULL),
(43, 31, 'domain', 'https://casc.co.in/', NULL, NULL, NULL, 0.00, 'our', '2026-03-27 10:08:45', '2026-03-27 10:18:17', '2026-03-27 10:18:17', NULL),
(44, 31, 'hosting', 'https://casc.co.in/', NULL, NULL, '2027-07-05', 4500.00, 'our', '2026-03-27 10:08:45', '2026-04-01 11:47:46', NULL, NULL),
(45, 31, 'hosting', 'pollachimotorcycleclub.com', NULL, NULL, '2026-08-03', 4500.00, 'our', '2026-03-27 10:10:50', '2026-04-01 11:30:42', NULL, NULL),
(46, 32, 'hosting', 'https://thepayanam.com/', NULL, NULL, '2026-08-19', 4500.00, 'our', '2026-03-27 10:28:40', '2026-04-01 11:28:36', NULL, NULL),
(47, 33, 'domain', 'https://kubproperties.com/', NULL, NULL, '2026-07-25', 1599.00, 'our', '2026-03-27 10:50:41', '2026-04-01 12:03:13', NULL, NULL),
(48, 34, 'hosting', 'ibccerts.com', NULL, NULL, '2026-04-18', 4500.00, 'our', '2026-03-27 10:52:33', '2026-04-01 11:27:31', NULL, NULL),
(49, 35, 'hosting', 'https://renugaaircompressor.com/', NULL, NULL, '2026-12-14', 4500.00, 'our', '2026-03-27 10:54:25', '2026-04-01 11:26:47', NULL, NULL),
(50, 1, 'hosting', 'https://toolcom.in/', NULL, NULL, '2026-07-30', 4500.00, 'our', '2026-03-27 10:55:37', '2026-04-01 12:33:10', NULL, NULL),
(51, 36, 'hosting', 'aggroups.in', NULL, NULL, '2026-02-19', 4500.00, 'our', '2026-03-27 11:42:54', '2026-04-01 11:27:57', NULL, NULL),
(52, 37, 'hosting', 'https://beros.in/', NULL, NULL, NULL, 4500.00, 'our', '2026-03-27 11:45:49', '2026-04-01 11:31:01', NULL, NULL),
(53, 38, 'hosting', 'https://pkabricks.com/', NULL, NULL, '2026-08-05', 4500.00, 'our', '2026-03-27 11:47:52', '2026-04-01 11:11:09', NULL, NULL),
(54, 39, 'hosting', 'https://southindiamotors.in/', NULL, NULL, '2026-06-21', 899.00, 'our', '2026-03-27 11:54:08', '2026-04-01 09:39:31', NULL, NULL),
(55, 40, 'hosting', 'https://rpfoundation.org.in/', NULL, NULL, '2026-07-05', 4500.00, 'our', '2026-03-27 11:57:25', '2026-04-01 11:08:34', NULL, NULL),
(56, 17, 'hosting', 'https://clipsandthreads.myshopify.com/', NULL, NULL, NULL, 0.00, 'our', '2026-03-27 12:05:40', '2026-03-27 12:05:40', NULL, NULL),
(57, 40, 'domain', 'https://treeandme.in/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 12:34:29', '2026-03-27 12:34:35', '2026-03-27 12:34:35', NULL),
(58, 15, 'domain', 'https://treeandme.in/', NULL, NULL, NULL, 0.00, 'client', '2026-03-27 12:34:58', '2026-03-27 12:34:58', NULL, NULL),
(59, 40, 'domain', 'https://rpfoundation.org.in/', NULL, NULL, '2026-07-05', 749.00, 'our', '2026-04-01 09:08:50', '2026-04-01 09:08:50', NULL, NULL),
(60, 41, 'domain', 'https://legendcorp.in/', NULL, NULL, '2026-02-16', 899.00, 'our', '2026-04-01 09:17:32', '2026-04-06 07:24:16', NULL, 'Expired'),
(61, 36, 'domain', 'aggroups.in', NULL, NULL, '2026-02-19', 899.00, 'our', '2026-04-01 09:18:05', '2026-04-01 09:18:05', NULL, NULL),
(62, 34, 'domain', 'ibccerts.com', NULL, NULL, '2026-04-18', 1591.00, 'our', '2026-04-01 09:18:41', '2026-04-01 09:18:41', NULL, NULL),
(63, 19, 'domain', 'http://affordableet.com/', NULL, NULL, '2026-05-02', 1599.00, 'our', '2026-04-01 09:19:24', '2026-04-01 09:19:24', NULL, NULL),
(64, 28, 'domain', 'https://kumarancollegeofnursing.com/', NULL, NULL, '2026-05-02', 1599.00, 'our', '2026-04-01 09:19:58', '2026-04-01 09:19:58', NULL, NULL),
(65, 42, 'domain', 'khosocial.com', NULL, NULL, '2026-05-16', 1599.00, 'our', '2026-04-01 09:22:34', '2026-04-01 09:22:34', NULL, NULL),
(66, 42, 'domain', 'http://khosocial.net/', NULL, NULL, '2026-05-16', 1799.00, 'our', '2026-04-01 09:24:02', '2026-04-01 09:24:02', NULL, NULL),
(67, 42, 'domain', 'https://khosocial.org/', NULL, NULL, '2026-05-16', 1599.00, 'our', '2026-04-01 09:27:59', '2026-04-01 09:27:59', NULL, NULL),
(68, 42, 'domain', 'https://kho.social/', NULL, NULL, '2026-05-16', 6036.00, 'our', '2026-04-01 09:28:40', '2026-04-01 09:28:40', NULL, NULL),
(69, 42, 'domain', 'https://khosocial.in/', NULL, NULL, '2026-05-16', 899.00, 'our', '2026-04-01 09:31:09', '2026-04-01 09:31:09', NULL, NULL),
(70, 42, 'domain', 'https://khosocial.info/', NULL, NULL, '2026-05-16', 2999.00, 'our', '2026-04-01 09:31:52', '2026-04-01 09:31:52', NULL, NULL),
(71, 43, 'domain', '3monksdigital.in', NULL, NULL, '2026-06-02', 899.00, 'our', '2026-04-01 09:34:24', '2026-04-01 09:36:23', NULL, NULL),
(72, 43, 'domain', '3monksdigital.com', NULL, NULL, '2026-06-14', 1599.00, 'our', '2026-04-01 09:34:48', '2026-04-01 09:38:43', NULL, NULL),
(73, 44, 'domain', 'bhagavangardens.com', NULL, NULL, '2026-05-17', 1599.00, 'our', '2026-04-01 09:34:48', '2026-04-01 09:34:48', NULL, NULL),
(74, 43, 'domain', '3monks.in', NULL, NULL, '2026-06-15', 899.00, 'our', '2026-04-01 09:35:00', '2026-04-01 09:39:04', NULL, NULL),
(75, 42, 'domain', 'https://khode.in/', NULL, NULL, '2026-06-03', 899.00, 'our', '2026-04-01 09:37:00', '2026-04-06 04:21:27', NULL, NULL),
(76, 31, 'domain', 'pollachimotorcycleclub.com', NULL, NULL, '2026-08-03', 1599.00, 'our', '2026-04-01 09:38:46', '2026-04-01 09:46:00', NULL, NULL),
(77, 21, 'domain', 'vishakapark.com', NULL, NULL, '2026-08-06', 1599.00, 'our', '2026-04-01 09:40:44', '2026-04-01 09:50:19', NULL, NULL),
(78, 45, 'hosting', 'https://sjinfotechs.com/', NULL, NULL, '2026-08-11', 0.00, 'our', '2026-04-01 09:42:21', '2026-04-01 11:06:29', NULL, NULL),
(79, 45, 'domain', 'https://sjinfotechs.com/', NULL, NULL, '2026-08-11', 1599.00, 'our', '2026-04-01 09:42:21', '2026-04-01 09:51:10', NULL, NULL),
(80, 45, 'domain', 'sjinfotech.co', NULL, NULL, '2026-08-11', 1599.00, 'our', '2026-04-01 09:42:42', '2026-04-01 09:51:24', NULL, NULL),
(81, 1, 'domain', 'https://toolcom.in/', NULL, NULL, '2026-07-30', 899.00, 'our', '2026-04-01 09:43:06', '2026-04-01 09:43:06', NULL, NULL),
(82, 42, 'domain', 'khodeacademy.com', NULL, NULL, NULL, 0.00, 'our', '2026-04-01 09:43:18', '2026-04-01 09:54:14', '2026-04-01 09:54:14', NULL),
(83, 42, 'domain', 'khodecampus.com', NULL, NULL, '2026-08-27', 1599.00, 'our', '2026-04-01 09:43:50', '2026-04-01 09:54:37', NULL, NULL),
(84, 38, 'domain', 'https://pkabricks.com/', NULL, NULL, '2026-08-05', 1599.00, 'our', '2026-04-01 09:48:55', '2026-04-01 09:48:55', NULL, NULL),
(85, 32, 'domain', 'https://thepayanam.com/', NULL, NULL, '2026-08-19', 1599.00, 'our', '2026-04-01 09:52:19', '2026-04-01 09:52:19', NULL, NULL),
(86, 42, 'domain', 'https://khodeacademy.com/', NULL, NULL, '2026-08-27', 1599.00, 'our', '2026-04-01 09:53:41', '2026-04-01 09:53:41', NULL, NULL),
(87, 46, 'domain', 'atmcin.com', NULL, NULL, '2026-09-01', 1599.00, 'our', '2026-04-01 09:55:20', '2026-04-01 10:05:09', NULL, NULL),
(88, 46, 'domain', 'plusatm.com', NULL, NULL, '2026-09-02', 1599.00, 'our', '2026-04-01 09:55:20', '2026-04-01 10:05:55', NULL, NULL),
(89, 4, 'domain', 'equinesportsindia.in', NULL, NULL, '2026-09-02', 899.00, 'our', '2026-04-01 09:56:27', '2026-04-01 10:08:41', NULL, NULL),
(90, 4, 'domain', 'equinesports.in', NULL, NULL, '2026-09-02', 899.00, 'our', '2026-04-01 09:57:44', '2026-04-01 10:10:10', NULL, NULL),
(91, 47, 'domain', 'texasclothing.in', NULL, NULL, '2026-09-05', 899.00, 'our', '2026-04-01 09:59:09', '2026-04-01 10:11:06', NULL, NULL),
(92, 48, 'domain', 'uniquesurpriseevents.com', NULL, NULL, '2026-09-07', 1599.00, 'our', '2026-04-01 09:59:39', '2026-04-01 10:11:58', NULL, NULL),
(93, 49, 'domain', 'sasuriepdasalaicbse.com', NULL, NULL, '2026-09-12', 1599.00, 'our', '2026-04-01 10:02:13', '2026-04-01 10:12:26', NULL, NULL),
(94, 49, 'domain', 'sasuriepdasalaimatric.com', NULL, NULL, '2026-09-12', 1599.00, 'our', '2026-04-01 10:02:26', '2026-04-01 10:12:49', NULL, NULL),
(95, 42, 'domain', 'khocart.com', NULL, NULL, '2026-10-31', 1599.00, 'client', '2026-04-01 10:04:27', '2026-04-01 10:13:14', NULL, NULL),
(96, 35, 'domain', 'renugaircompressor.com', NULL, NULL, '2026-12-14', 1599.00, 'our', '2026-04-01 10:04:46', '2026-04-01 10:13:48', NULL, NULL),
(97, 50, 'domain', 'egsind.com', NULL, NULL, '2027-01-30', 1599.00, 'our', '2026-04-01 10:08:40', '2026-04-01 10:15:24', NULL, NULL),
(98, 51, 'domain', 'heytaya.com', NULL, NULL, '2027-02-11', 1599.00, 'our', '2026-04-01 10:09:19', '2026-04-01 10:15:58', NULL, NULL),
(99, 52, 'domain', 'globemedicare.net', NULL, NULL, '2027-10-08', 1799.00, 'our', '2026-04-01 10:12:04', '2026-04-01 10:17:51', NULL, NULL),
(100, 53, 'domain', 'https://theaaronjohn.com/', NULL, NULL, '2028-06-23', 1599.00, 'our', '2026-04-01 10:12:47', '2026-04-01 10:18:25', NULL, NULL),
(101, 31, 'domain', 'https://casc.co.in/', NULL, NULL, '2027-07-05', 749.00, 'our', '2026-04-01 10:46:09', '2026-04-01 11:46:10', NULL, NULL),
(102, 39, 'domain', 'southindiamotors.in', NULL, NULL, '2026-06-21', 4500.00, 'our', '2026-04-01 11:09:44', '2026-04-01 11:10:22', NULL, NULL),
(103, 39, 'hosting', 'southindiamotors.in', NULL, NULL, '2026-06-21', 0.00, 'client', '2026-04-01 11:09:44', '2026-04-01 11:09:58', '2026-04-01 11:09:58', NULL),
(104, 24, 'hosting', 'https://webberhydraulics.com/', NULL, NULL, '2026-11-19', 4500.00, 'our', '2026-04-01 11:37:33', '2026-04-01 12:19:51', NULL, NULL),
(105, 11, 'hosting', 'http://www.auroraoverseas.com/', NULL, NULL, '2026-05-09', 4500.00, 'our', '2026-04-01 11:43:39', '2026-04-01 11:55:00', NULL, NULL),
(106, 44, 'hosting', 'bhagavangardens.com', NULL, NULL, '2026-05-17', 4500.00, 'our', '2026-04-01 11:51:25', '2026-04-01 11:51:25', NULL, NULL),
(107, 4, 'hosting', 'equinesportsindia.com', NULL, NULL, '2026-09-02', 4500.00, 'our', '2026-04-01 12:01:25', '2026-04-01 12:01:25', NULL, NULL),
(108, 4, 'hosting', 'equinesports.in', NULL, NULL, '2026-09-02', 4500.00, 'our', '2026-04-01 12:01:25', '2026-04-01 12:01:25', NULL, NULL),
(109, 4, 'hosting', 'eclindia.co.in', NULL, NULL, '2026-09-02', 4500.00, 'our', '2026-04-01 12:01:25', '2026-04-01 12:01:25', NULL, NULL),
(110, 4, 'hosting', 'equinesportsindia.in', NULL, NULL, '2026-09-02', 4500.00, 'our', '2026-04-01 12:01:25', '2026-04-01 12:01:25', NULL, NULL),
(111, 4, 'hosting', 'pplindia.co.in', NULL, NULL, '2026-09-02', 4500.00, 'our', '2026-04-01 12:01:25', '2026-04-01 12:01:25', NULL, NULL),
(112, 33, 'hosting', 'https://kubproperties.com/', NULL, NULL, '2026-07-25', 4500.00, 'our', '2026-04-01 12:03:55', '2026-04-01 12:03:55', NULL, NULL),
(113, 41, 'hosting', 'legendcorp.in', NULL, NULL, '2026-02-16', 4500.00, 'our', '2026-04-01 12:07:52', '2026-04-06 09:22:35', NULL, NULL),
(114, 48, 'hosting', 'uniquesurpriseevents.com', NULL, NULL, '2026-09-07', 4500.00, 'our', '2026-04-01 12:09:27', '2026-04-01 12:09:27', NULL, NULL),
(115, 22, 'hosting', 'gnanambiga.in', NULL, NULL, '2026-07-10', 4500.00, 'our', '2026-04-01 12:24:24', '2026-04-01 12:24:24', NULL, NULL),
(116, 35, 'hosting', 'ads.renugaaircompressor.com', NULL, NULL, '2026-12-14', 4500.00, 'our', '2026-04-01 12:26:09', '2026-04-01 12:26:09', NULL, NULL),
(117, 54, 'hosting', 'revathiinstitutions.com', NULL, NULL, '2023-12-18', 4500.00, 'our', '2026-04-01 12:31:51', '2026-04-06 07:54:49', NULL, NULL),
(118, 42, 'hosting', 'Prabuns.com', NULL, NULL, NULL, 0.00, 'our', '2026-04-01 12:36:56', '2026-04-01 12:36:56', NULL, NULL),
(119, 3, 'domain', 'https://saradeuz.com/', NULL, NULL, '2026-05-16', 0.00, 'client', '2026-04-02 06:59:06', '2026-04-02 06:59:06', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `billings`
--
ALTER TABLE `billings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_billings_client_deleted` (`client_id`,`deleted_at`),
  ADD KEY `idx_billings_renewal_deleted` (`renewal_date`,`deleted_at`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_clients_deleted_at` (`deleted_at`),
  ADD KEY `idx_clients_status` (`status`);

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
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `billings`
--
ALTER TABLE `billings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `billings`
--
ALTER TABLE `billings`
  ADD CONSTRAINT `fk_billings_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`);

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `fk_services_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
