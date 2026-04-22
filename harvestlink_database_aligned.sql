-- HarvestLink Database Schema (Aligned Version)
-- This version is aligned with the submitted Phase 3 report and diagrams,
-- while keeping practical implementation improvements needed for Phase 4.
-- Main alignment decisions:
-- 1) User profile is NOT a separate table.
-- 2) Each user has exactly one profile stored in users.
-- 3) profile_image has a default image path.
-- 4) Core table/column names remain close to the report and ERD.
-- 5) Scope stays digital-only: no payment, no logistics, no external integrations.

CREATE DATABASE IF NOT EXISTS harvestlink_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE harvestlink_db;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS donation_requests;
DROP TABLE IF EXISTS surplus_products;
DROP TABLE IF EXISTS charitable_organizations;
DROP TABLE IF EXISTS farmers;
DROP TABLE IF EXISTS administrators;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    user_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('farmer', 'charity', 'admin') NOT NULL,
    account_status ENUM('active', 'blocked') NOT NULL DEFAULT 'active',
    phone_number VARCHAR(20) NULL,
    address VARCHAR(255) NULL,
    profile_image VARCHAR(255) NOT NULL DEFAULT 'assets/images/default-profile.png',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    UNIQUE KEY uq_users_email (email),
    INDEX idx_users_role (role),
    INDEX idx_users_status (account_status)
) ENGINE=InnoDB;

CREATE TABLE farmers (
    farmer_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    farm_location VARCHAR(255) NULL,
    PRIMARY KEY (farmer_id),
    UNIQUE KEY uq_farmers_user_id (user_id),
    CONSTRAINT fk_farmers_user
        FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE charitable_organizations (
    charity_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    organization_name VARCHAR(150) NOT NULL,
    organization_type VARCHAR(100) NULL,
    contact_number VARCHAR(20) NULL,
    PRIMARY KEY (charity_id),
    UNIQUE KEY uq_charities_user_id (user_id),
    CONSTRAINT fk_charities_user
        FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE administrators (
    admin_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (admin_id),
    UNIQUE KEY uq_admins_user_id (user_id),
    CONSTRAINT fk_admins_user
        FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE surplus_products (
    product_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    farmer_id BIGINT UNSIGNED NOT NULL,
    crop_type VARCHAR(100) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    expiration_date DATE NOT NULL,
    product_condition ENUM('Fresh', 'Near Expiry') NOT NULL,
    image VARCHAR(255) NULL,
    product_status ENUM('Available', 'Blocked', 'Deleted') NOT NULL DEFAULT 'Available',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (product_id),
    CONSTRAINT chk_products_quantity_positive CHECK (quantity > 0),
    CONSTRAINT fk_products_farmer
        FOREIGN KEY (farmer_id) REFERENCES farmers(farmer_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    INDEX idx_products_crop_type (crop_type),
    INDEX idx_products_status (product_status),
    INDEX idx_products_expiration (expiration_date),
    INDEX idx_products_farmer (farmer_id)
) ENGINE=InnoDB;

CREATE TABLE donation_requests (
    request_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id BIGINT UNSIGNED NOT NULL,
    charity_id BIGINT UNSIGNED NOT NULL,
    requested_quantity DECIMAL(10,2) NOT NULL,
    request_status ENUM('Pending', 'Approved', 'Rejected', 'Delivered') NOT NULL DEFAULT 'Pending',
    request_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    decision_date DATETIME NULL,
    delivered_date DATETIME NULL,
    PRIMARY KEY (request_id),
    CONSTRAINT chk_requests_quantity_positive CHECK (requested_quantity > 0),
    CONSTRAINT fk_requests_product
        FOREIGN KEY (product_id) REFERENCES surplus_products(product_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_requests_charity
        FOREIGN KEY (charity_id) REFERENCES charitable_organizations(charity_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    INDEX idx_requests_product (product_id),
    INDEX idx_requests_charity (charity_id),
    INDEX idx_requests_status (request_status),
    INDEX idx_requests_date (request_date)
) ENGINE=InnoDB;

CREATE TABLE notifications (
    notification_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    request_id BIGINT UNSIGNED NULL,
    message VARCHAR(255) NOT NULL,
    notification_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_read BOOLEAN NOT NULL DEFAULT FALSE,
    PRIMARY KEY (notification_id),
    CONSTRAINT fk_notifications_user
        FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_notifications_request
        FOREIGN KEY (request_id) REFERENCES donation_requests(request_id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    INDEX idx_notifications_user (user_id),
    INDEX idx_notifications_read (is_read),
    INDEX idx_notifications_date (notification_date)
) ENGINE=InnoDB;

-- Example notes for implementation:
-- 1) Farmer / Charity registration inserts into users first, then into the related role table.
-- 2) Admin is created by the development team / system setup, not through public registration.
-- 3) If the user uploads no profile image, the default image path remains:
--    assets/images/default-profile.png
