-- ============================================================
-- Aura Artifacts - Universal Database Schema
-- Compatible with: MySQL 5.7+, MySQL 8.x, MariaDB 10.x
-- Works on: XAMPP, Laragon, WAMP, MAMP, Linux servers
-- ============================================================
-- Admin login:  admin@auraartifacts.com / password
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET time_zone = '+00:00';

-- ──────────────────────────────────────
-- Create database
-- ──────────────────────────────────────
CREATE DATABASE IF NOT EXISTS `auradb`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `auradb`;

-- ──────────────────────────────────────
-- Drop all tables (reverse FK order)
-- ──────────────────────────────────────
DROP TABLE IF EXISTS `notification_read`;
DROP TABLE IF EXISTS `password_reset_request`;
DROP TABLE IF EXISTS `newsletter`;
DROP TABLE IF EXISTS `contact_message`;
DROP TABLE IF EXISTS `wishlist`;
DROP TABLE IF EXISTS `order_item`;
DROP TABLE IF EXISTS `order`;
DROP TABLE IF EXISTS `cart_item`;
DROP TABLE IF EXISTS `cart`;
DROP TABLE IF EXISTS `braceletpreview`;
DROP TABLE IF EXISTS `product`;
DROP TABLE IF EXISTS `gemstone`;
DROP TABLE IF EXISTS `charm`;
DROP TABLE IF EXISTS `category`;
DROP TABLE IF EXISTS `customer`;
DROP TABLE IF EXISTS `admin`;

-- ══════════════════════════════════════
--  REFERENCE / CATALOG TABLES
-- ══════════════════════════════════════

-- ──────────────────────────────────────
-- Table: admin
-- ──────────────────────────────────────
CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `admin` (`admin_id`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'admin', 'admin@auraartifacts.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', CURRENT_TIMESTAMP);

-- ──────────────────────────────────────
-- Table: category
-- ──────────────────────────────────────
CREATE TABLE `category` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `category` (`category_id`, `name`, `description`) VALUES
(1, 'Bracelets', 'Handcrafted gemstone bracelets'),
(2, 'Earrings', 'Fine gemstone earrings'),
(3, 'Necklaces', 'Elegant necklaces and pendants'),
(4, 'Rings', 'Gemstone rings'),
(5, 'Anklets', 'Delicate anklets'),
(6, 'Pendants', NULL),
(7, 'Sunglasses', NULL);

-- ──────────────────────────────────────
-- Table: customer
-- ──────────────────────────────────────
CREATE TABLE `customer` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- (empty - fresh site)

-- ──────────────────────────────────────
-- Table: product
-- ──────────────────────────────────────
CREATE TABLE `product` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `image_url` varchar(255) DEFAULT NULL,
  `image_url_2` varchar(255) DEFAULT NULL,
  `image_url_3` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `managed_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`product_id`),
  KEY `category_id` (`category_id`),
  KEY `managed_by` (`managed_by`),
  CONSTRAINT `product_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON DELETE SET NULL,
  CONSTRAINT `product_ibfk_2` FOREIGN KEY (`managed_by`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `product` (`product_id`, `name`, `description`, `base_price`, `stock`, `image_url`, `image_url_2`, `image_url_3`, `category_id`, `managed_by`) VALUES
(1, 'Pink Tourmaline Drop Earrings', 'Genuine pear-cut Pink Tourmaline drops with brilliant-cut diamonds. Art Deco-inspired in rose gold.', 450.00, 10, 'assets/images/firstpick-1.jpg', 'assets/images/firstpick-2.jpg', 'assets/images/firstpick-3.jpg', 2, 1),
(2, 'Golden Radiance', 'Premium Topaz stone set in 18K gold band. A stunning statement ring with timeless appeal.', 550.00, 14, 'assets/images/secoundpick_1.jpg', 'assets/images/secoundpick_2.jpg', 'assets/images/secoundpick_3.jpg', 4, 1),
(3, 'Royal Purple', 'Stunning Amethyst stones on delicate chain. A vibrant pendant celebrating spiritual wisdom.', 420.00, 5, 'assets/images/thirdpick_1.jpg', 'assets/images/thirdpick_2.jpg', 'assets/images/thirdpick_3.jpg', 6, 1),
(4, 'Sapphire Serenity', 'Magnificent Blue Sapphire stone in silver setting. A serene ring with timeless elegance.', 450.00, 8, 'assets/images/blue-1.jpg', 'assets/images/blue-2.jpg', 'assets/images/blue-3.jpg', 4, 1),
(5, 'Passionate Fire', 'Vibrant Ruby stones on delicate 18K gold chain. A stunning anklet that brings passion and energy to every step.', 580.00, 20, 'assets/images/ancklet_11.jpg', 'assets/images/ankelt_2.jpg', 'assets/images/ankelt_3.jpg', 5, 1),
(6, 'Crystal Radiance', 'Premium sunglasses featuring genuine multi-colored gemstone crystals. A sophisticated statement piece for the modern mystic.', 350.00, 25, 'assets/images/sun_1.jpg', 'assets/images/sun_2.jpg', 'assets/images/sun_3.jpg', 7, 1),
(7, 'Emerald Essence', 'Rich Emerald crystal pendant with brilliant diamonds on silver chain. A luxurious and sophisticated accessory.', 2500.00, 10, 'assets/images/FOURTHPICK-1.jpg', 'assets/images/FOURTHPICK-2.jpg', 'assets/images/FOURTHPICK-3.jpg', 6, NULL),
(8, 'Luminous Pearl', 'Lustrous Pearl crystals with silver accents. A serene and elegant bracelet for everyday grace.', 850.00, 10, 'assets/images/FIFTHPICK-1.jpg', 'assets/images/fifthpick_2.jpg', 'assets/images/fifthpick_3.jpg', 1, NULL),
(9, 'Timeless Brilliance', 'Most precious Diamond on platinum chain. A timeless pendant with unmatched brilliance.', 5000.00, 5, 'assets/images/dimond-1.jpg', 'assets/images/dimond-2.jpg', 'assets/images/dimond-3.jpg', 6, NULL);

-- ──────────────────────────────────────
-- Table: gemstone
-- ──────────────────────────────────────
CREATE TABLE `gemstone` (
  `gemstone_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `color` varchar(50) DEFAULT NULL,
  `price_add` decimal(10,2) DEFAULT 0.00,
  `stock` int(11) DEFAULT 0,
  `image_url` varchar(255) DEFAULT NULL,
  `meaning` text DEFAULT NULL,
  `energy_badge` varchar(50) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`gemstone_id`),
  KEY `updated_by` (`updated_by`),
  CONSTRAINT `gemstone_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `gemstone` (`gemstone_id`, `name`, `color`, `price_add`, `stock`, `image_url`, `meaning`, `energy_badge`, `updated_by`) VALUES
(1, 'Amethyst', 'Purple', 15.00, 100, 'assets/images/gemstones/amethyst.png', 'Stone of peace and intuition', 'Calming', 1),
(2, 'Rose Quartz', 'Pink', 12.00, 120, 'assets/images/gemstones/rose-quartz.png', 'Stone of universal love', 'Love', 1),
(3, 'Blue Zircon', 'Blue', 20.00, 80, 'assets/images/gemstones/blue-zircon.png', 'Stone of wisdom and clarity', 'Clarity', 1),
(4, 'Black Tourmaline', 'Black', 18.00, 90, 'assets/images/gemstones/black-tourmaline.png', 'Protective stone', 'Protection', 1),
(5, 'Citrine', 'Yellow', 14.00, 110, 'assets/images/gemstones/citrine.png', 'Stone of abundance', 'Energy', 1),
(6, 'Lapis Lazuli', 'Deep Blue', 16.00, 70, 'assets/images/gemstones/lapis.png', 'Stone of truth', 'Truth', 1);

-- ──────────────────────────────────────
-- Table: charm
-- ──────────────────────────────────────
CREATE TABLE `charm` (
  `charm_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `symbol` varchar(50) DEFAULT NULL,
  `price_add` decimal(10,2) DEFAULT 0.00,
  `stock` int(11) DEFAULT 0,
  `image_url` varchar(255) DEFAULT NULL,
  `category_folder` varchar(100) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`charm_id`),
  KEY `updated_by` (`updated_by`),
  CONSTRAINT `charm_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `charm` (`charm_id`, `name`, `symbol`, `price_add`, `stock`, `image_url`, `category_folder`, `updated_by`) VALUES
(1, 'Silver Heart', 'Heart', 10.00, 200, 'assets/images/charms/heart.png', 'Basic', 1),
(2, 'Star', 'Star', 8.00, 180, 'assets/images/charms/star.png', 'Basic', 1),
(3, 'Moon', 'Moon', 10.00, 150, 'assets/images/charms/moon.png', 'Basic', 1),
(4, 'Evil Eye', 'EvilEye', 12.00, 130, 'assets/images/charms/evileye.png', 'Protection', 1),
(5, 'Butterfly', 'Butterfly', 11.00, 120, 'assets/images/charms/butterfly.png', 'Nature', 1);



-- ══════════════════════════════════════
--  TRANSACTIONAL TABLES (empty)
-- ══════════════════════════════════════

-- ──────────────────────────────────────
-- Table: braceletpreview
-- ──────────────────────────────────────
CREATE TABLE `braceletpreview` (
  `preview_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `total_price` decimal(10,2) DEFAULT 0.00,
  `custom_images` longtext DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`preview_id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `braceletpreview_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- ──────────────────────────────────────
-- Table: cart
-- ──────────────────────────────────────
CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `total_price` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`cart_id`),
  UNIQUE KEY `customer_id` (`customer_id`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────
-- Table: cart_item
-- ──────────────────────────────────────
CREATE TABLE `cart_item` (
  `cart_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `cart_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `gemstone_id` int(11) DEFAULT NULL,
  `charm_id` int(11) DEFAULT NULL,
  `preview_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`cart_item_id`),
  KEY `cart_id` (`cart_id`),
  KEY `product_id` (`product_id`),
  KEY `gemstone_id` (`gemstone_id`),
  KEY `charm_id` (`charm_id`),
  KEY `preview_id` (`preview_id`),
  CONSTRAINT `cart_item_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`cart_id`) ON DELETE CASCADE,
  CONSTRAINT `cart_item_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE SET NULL,
  CONSTRAINT `cart_item_ibfk_3` FOREIGN KEY (`gemstone_id`) REFERENCES `gemstone` (`gemstone_id`) ON DELETE SET NULL,
  CONSTRAINT `cart_item_ibfk_4` FOREIGN KEY (`charm_id`) REFERENCES `charm` (`charm_id`) ON DELETE SET NULL,
  CONSTRAINT `cart_item_ibfk_5` FOREIGN KEY (`preview_id`) REFERENCES `braceletpreview` (`preview_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────
-- Table: order
-- ──────────────────────────────────────
CREATE TABLE `order` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `address` varchar(500) DEFAULT NULL,
  `payment` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `processed_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  KEY `customer_id` (`customer_id`),
  KEY `processed_by` (`processed_by`),
  CONSTRAINT `order_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE,
  CONSTRAINT `order_ibfk_2` FOREIGN KEY (`processed_by`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────
-- Table: order_item
-- ──────────────────────────────────────
CREATE TABLE `order_item` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `charm_id` int(11) DEFAULT NULL,
  `gemstone_id` int(11) DEFAULT NULL,
  `preview_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`item_id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  KEY `charm_id` (`charm_id`),
  KEY `gemstone_id` (`gemstone_id`),
  KEY `preview_id` (`preview_id`),
  CONSTRAINT `order_item_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `order` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `order_item_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE SET NULL,
  CONSTRAINT `order_item_ibfk_3` FOREIGN KEY (`charm_id`) REFERENCES `charm` (`charm_id`) ON DELETE SET NULL,
  CONSTRAINT `order_item_ibfk_4` FOREIGN KEY (`gemstone_id`) REFERENCES `gemstone` (`gemstone_id`) ON DELETE SET NULL,
  CONSTRAINT `order_item_ibfk_5` FOREIGN KEY (`preview_id`) REFERENCES `braceletpreview` (`preview_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────
-- Table: wishlist
-- ──────────────────────────────────────
CREATE TABLE `wishlist` (
  `wishlist_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `added_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`wishlist_id`),
  UNIQUE KEY `unique_wishlist` (`customer_id`, `product_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE,
  CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────
-- Table: contact_message
-- ──────────────────────────────────────
CREATE TABLE `contact_message` (
  `message_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `submitted_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────
-- Table: newsletter
-- ──────────────────────────────────────
CREATE TABLE `newsletter` (
  `newsletter_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `subscribed_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`newsletter_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────
-- Table: password_reset_request
-- ──────────────────────────────────────
CREATE TABLE `password_reset_request` (
  `request_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `user_type` enum('customer','admin') DEFAULT 'customer',
  `token` varchar(255) DEFAULT NULL,
  `status` enum('pending','completed','expired') DEFAULT 'pending',
  `requested_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────
-- Table: notification_read
-- ──────────────────────────────────────
CREATE TABLE `notification_read` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `notif_key` varchar(100) NOT NULL,
  `read_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_read` (`admin_id`, `notif_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────
-- Re-enable checks
-- ──────────────────────────────────────
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- Done! Fresh install complete.
-- Admin login:  admin@auraartifacts.com  /  password
-- ============================================================
