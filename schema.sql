-- AMC Domain & Hosting Management Schema (MySQL 8+)
-- Import this file manually in phpMyAdmin or MySQL CLI.

CREATE DATABASE IF NOT EXISTS amc_hosting CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE amc_hosting;

CREATE TABLE IF NOT EXISTS admins (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS clients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(180) NOT NULL,
    company VARCHAR(180) NULL,
    email VARCHAR(190) NOT NULL,
    phone VARCHAR(80) NULL,
    renewal_date DATE NULL,
    status ENUM('Active', 'Disabled') NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_clients_deleted_at (deleted_at),
    INDEX idx_clients_status (status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS services (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    service_type ENUM('domain', 'hosting') NOT NULL,
    name VARCHAR(190) NOT NULL,
    renewal_date DATE NULL,
    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    ownership_type ENUM('our', 'client') NOT NULL DEFAULT 'client',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_services_client_deleted (client_id, deleted_at),
    INDEX idx_services_type_deleted (service_type, deleted_at),
    CONSTRAINT fk_services_client FOREIGN KEY (client_id) REFERENCES clients(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS billings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    service_type ENUM('domain', 'hosting') NOT NULL,
    service_ref VARCHAR(190) NOT NULL,
    renewal_date DATE NOT NULL,
    last_billing_date DATE NULL,
    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    status ENUM('Active', 'Paid', 'Disabled') NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_billings_client_deleted (client_id, deleted_at),
    INDEX idx_billings_renewal_deleted (renewal_date, deleted_at),
    CONSTRAINT fk_billings_client FOREIGN KEY (client_id) REFERENCES clients(id)
) ENGINE=InnoDB;

-- This audit table is intentionally not used in UI pages (kept hidden from admin screens).
CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(80) NOT NULL,
    row_id BIGINT UNSIGNED NOT NULL,
    action_type ENUM('insert', 'update', 'delete') NOT NULL,
    actor VARCHAR(120) NULL,
    action_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    meta_json JSON NULL
) ENGINE=InnoDB;
