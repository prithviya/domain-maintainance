-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 26, 2026 at 08:44 AM
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
-- Database: `amc_hosting`
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
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `billings`
--

INSERT INTO `billings` (`id`, `client_id`, `service_type`, `service_ref`, `renewal_date`, `last_billing_date`, `amount`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'domain', 'revathiinstitutions.com', '2026-03-31', '2025-03-31', 5000.00, 'Active', '2026-03-26 07:05:36', '2026-03-26 07:05:36', NULL);

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
(1, 'Revathi', 'Revathi Institutions', 'revathi@gmail.com', '9638520741', 'Active', '2026-03-26 06:59:44', '2026-03-26 06:59:44', NULL),
(2, 'kubproperties.com', 'kubproperties.com', 'kubproperties@gmail.com', '7418520963', 'Active', '2026-03-26 07:34:42', '2026-03-26 07:34:42', NULL);

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
(1, 1, 'hosting', 'revathiinstitutions.com', '', '', '2023-12-08', 20.00, 'client', '2026-03-26 07:02:42', '2026-03-26 07:02:42', NULL);

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
