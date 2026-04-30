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
    profile_image VARCHAR(255) NOT NULL DEFAULT 'uploads/profiles/default_profile.png',
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
--    uploads/profiles/default_profile.png

-- =========================================================
-- Demo Seed Data for Final Presentation
-- Default demo password for all users: Password123
-- Image paths must match the PHP code:
-- profile images: uploads/profiles/<filename>
-- product images: uploads/products/<filename>
-- default profile: uploads/profiles/default_profile.png
-- =========================================================

INSERT INTO users
(user_id, full_name, email, password_hash, role, account_status, phone_number, address, profile_image)
VALUES
(1, 'System Admin', 'admin@harvestlink.com', '$2y$12$X5zB8Y8T1BuwYzWSBA0AneEE9G5bCLAuwp5VZ2fJS2YoVuNV7vt7.', 'admin', 'active', '0500000001', 'Riyadh, Saudi Arabia', 'uploads/profiles/admin_profile.png'),

(2, 'Ahmed Alharbi', 'ahmed.farmer@harvestlink.com', '$2y$12$X5zB8Y8T1BuwYzWSBA0AneEE9G5bCLAuwp5VZ2fJS2YoVuNV7vt7.', 'farmer', 'active', '0501111111', 'Al-Kharj, Saudi Arabia', 'uploads/profiles/farmer_ahmed.png'),
(3, 'Hassan Alqahtani', 'hassan.farmer@harvestlink.com', '$2y$12$X5zB8Y8T1BuwYzWSBA0AneEE9G5bCLAuwp5VZ2fJS2YoVuNV7vt7.', 'farmer', 'active', '0502222222', 'Qassim, Saudi Arabia', 'uploads/profiles/farmer_hassan.png'),
(4, 'Fatima Alotaibi', 'fatima.farmer@harvestlink.com', '$2y$12$X5zB8Y8T1BuwYzWSBA0AneEE9G5bCLAuwp5VZ2fJS2YoVuNV7vt7.', 'farmer', 'active', '0503333333', 'Hail, Saudi Arabia', 'uploads/profiles/farmer_fatima.png'),
(5, 'Khalid Almutairi', 'khalid.farmer@harvestlink.com', '$2y$12$X5zB8Y8T1BuwYzWSBA0AneEE9G5bCLAuwp5VZ2fJS2YoVuNV7vt7.', 'farmer', 'active', '0504444444', 'Riyadh Outskirts, Saudi Arabia', 'uploads/profiles/default_profile.png'),

(6, 'Green Hope Foundation', 'greenhope@harvestlink.com', '$2y$12$X5zB8Y8T1BuwYzWSBA0AneEE9G5bCLAuwp5VZ2fJS2YoVuNV7vt7.', 'charity', 'active', '0505555555', 'Riyadh, Saudi Arabia', 'uploads/profiles/charity_greenhope.png'),
(7, 'Helping Hands Society', 'helpinghands@harvestlink.com', '$2y$12$X5zB8Y8T1BuwYzWSBA0AneEE9G5bCLAuwp5VZ2fJS2YoVuNV7vt7.', 'charity', 'active', '0506666666', 'Jeddah, Saudi Arabia', 'uploads/profiles/charity_helpinghands.png'),
(8, 'Care and Share Community', 'careandshare@harvestlink.com', '$2y$12$X5zB8Y8T1BuwYzWSBA0AneEE9G5bCLAuwp5VZ2fJS2YoVuNV7vt7.', 'charity', 'active', '0507777777', 'Dammam, Saudi Arabia', 'uploads/profiles/default_profile.png');

INSERT INTO administrators (admin_id, user_id)
VALUES
(1, 1);

INSERT INTO farmers (farmer_id, user_id, farm_location)
VALUES
(1, 2, 'Al-Kharj agricultural area'),
(2, 3, 'Qassim farms'),
(3, 4, 'Hail rural farms'),
(4, 5, 'Riyadh outskirts farms');

INSERT INTO charitable_organizations
(charity_id, user_id, organization_name, organization_type, contact_number)
VALUES
(1, 6, 'Green Hope Foundation', 'Food Support Charity', '0115551001'),
(2, 7, 'Helping Hands Society', 'Community Aid Organization', '0115551002'),
(3, 8, 'Care and Share Community', 'Local Donation Organization', '0115551003');

INSERT INTO surplus_products
(product_id, farmer_id, crop_type, quantity, expiration_date, product_condition, image, product_status)
VALUES
(1, 1, 'Fresh Tomatoes', 120.00, '2026-05-20', 'Fresh', 'uploads/products/product_tomatoes.png', 'Available'),
(2, 1, 'Crisp Cucumbers', 85.00, '2026-05-18', 'Fresh', 'uploads/products/product_cucumbers.png', 'Available'),
(3, 2, 'Golden Potatoes', 200.00, '2026-06-10', 'Fresh', 'uploads/products/product_potatoes.png', 'Available'),
(4, 2, 'Organic Carrots', 95.00, '2026-05-25', 'Fresh', 'uploads/products/product_carrots.png', 'Available'),
(5, 3, 'Green Lettuce', 60.00, '2026-05-12', 'Near Expiry', 'uploads/products/product_lettuce.png', 'Available'),
(6, 3, 'Ripe Bananas', 110.00, '2026-05-09', 'Near Expiry', 'uploads/products/product_bananas.png', 'Available'),
(7, 4, 'Red Apples', 140.00, '2026-06-01', 'Fresh', 'uploads/products/product_apples.png', 'Available'),
(8, 4, 'Sweet Corn', 75.00, '2026-05-22', 'Fresh', 'uploads/products/product_corn.png', 'Blocked');

INSERT INTO donation_requests
(request_id, product_id, charity_id, requested_quantity, request_status, request_date, decision_date, delivered_date)
VALUES
(1, 1, 1, 30.00, 'Pending', '2026-04-20 10:15:00', NULL, NULL),
(2, 3, 2, 50.00, 'Approved', '2026-04-20 12:30:00', '2026-04-20 15:00:00', NULL),
(3, 5, 3, 20.00, 'Rejected', '2026-04-21 09:45:00', '2026-04-21 11:00:00', NULL),
(4, 7, 1, 40.00, 'Delivered', '2026-04-21 13:10:00', '2026-04-21 14:00:00', '2026-04-22 09:00:00');

INSERT INTO notifications
(notification_id, user_id, request_id, message, notification_date, is_read)
VALUES
(1, 2, 1, 'Green Hope Foundation submitted a request for Fresh Tomatoes.', '2026-04-20 10:16:00', FALSE),
(2, 7, 2, 'Your request for Golden Potatoes was approved.', '2026-04-20 15:05:00', FALSE),
(3, 8, 3, 'Your request for Green Lettuce was rejected.', '2026-04-21 11:05:00', TRUE),
(4, 6, 4, 'Your request for Red Apples was marked as delivered.', '2026-04-22 09:10:00', FALSE);

ALTER TABLE users AUTO_INCREMENT = 9;
ALTER TABLE administrators AUTO_INCREMENT = 2;
ALTER TABLE farmers AUTO_INCREMENT = 5;
ALTER TABLE charitable_organizations AUTO_INCREMENT = 4;
ALTER TABLE surplus_products AUTO_INCREMENT = 9;
ALTER TABLE donation_requests AUTO_INCREMENT = 5;
ALTER TABLE notifications AUTO_INCREMENT = 5;
