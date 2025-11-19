-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 18, 2025 at 08:32 AM
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
-- Database: `online_food_ordering_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `description`, `image_url`, `display_order`, `is_active`, `created_at`) VALUES
(1, 'Lomi Bowls', 'Hearty lomi soups with various toppings', NULL, 1, 1, '2025-11-14 12:54:35'),
(2, 'Silog Meals', 'Classic Filipino rice meals with egg', NULL, 2, 1, '2025-11-14 12:54:35'),
(3, 'Party Trays', 'Shareable trays for gatherings', NULL, 3, 1, '2025-11-14 12:54:35'),
(4, 'Drinks', 'Beverages and refreshments', NULL, 4, 1, '2025-11-14 12:54:35'),
(5, 'Sides', 'Side dishes and appetizers', NULL, 5, 1, '2025-11-14 12:54:35'),
(6, 'Panghimagas (Desserts)', 'Sweet treats and desserts', NULL, 6, 1, '2025-11-14 12:54:35');

-- --------------------------------------------------------

--
-- Table structure for table `contact_submissions`
--

CREATE TABLE `contact_submissions` (
  `id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `submitted_at` datetime NOT NULL,
  `is_processed` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_submissions`
--

INSERT INTO `contact_submissions` (`id`, `fullname`, `phone`, `email`, `message`, `submitted_at`, `is_processed`) VALUES
(1, 'dgdg dfgdfs', '09067035958', 'aldriebaquiran15@gmail.com', 'dlamdladakdlam', '2025-11-18 14:47:22', 0),
(2, 'dgdg dfgdfs', '09067035958', 'aldriebaquiran15@gmail.com', 'cmlcmlmc', '2025-11-18 07:58:48', 0),
(3, 'dgdg dfgdfs', '09067035958', 'aldriebaquiran15@gmail.com', 'nsdoskdskd', '2025-11-18 07:59:10', 0),
(4, 'dgdg dfgdfs', '09067035958', 'aldriebaquiran15@gmail.com', 'nsdoskdskd', '2025-11-18 08:08:46', 0);

-- --------------------------------------------------------

--
-- Table structure for table `deliverable_barangays`
--

CREATE TABLE `deliverable_barangays` (
  `barangay_id` int(11) NOT NULL,
  `barangay_name` varchar(100) NOT NULL,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deliverable_barangays`
--

INSERT INTO `deliverable_barangays` (`barangay_id`, `barangay_name`, `delivery_fee`, `is_active`) VALUES
(1, 'Wawa', 30.00, 1),
(2, 'Bucana', 20.00, 1),
(3, 'Lumbangan', 40.00, 1),
(4, 'Poblacion', 20.00, 1),
(5, 'Barangay 1', 20.00, 1),
(6, 'Barangay 2', 20.00, 1),
(7, 'Barangay 3', 20.00, 1),
(8, 'Barangay 4', 20.00, 1),
(10, 'Papaya', 100.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `delivery_zones`
--

CREATE TABLE `delivery_zones` (
  `zone_id` int(11) NOT NULL,
  `zone_name` varchar(100) NOT NULL,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_delivery_amount` decimal(10,2) DEFAULT NULL,
  `estimated_delivery_time` int(11) DEFAULT 30,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery_zones`
--

INSERT INTO `delivery_zones` (`zone_id`, `zone_name`, `delivery_fee`, `min_delivery_amount`, `estimated_delivery_time`, `is_active`) VALUES
(1, 'City Proper', 0.00, 200.00, 20, 1),
(2, 'Suburban Areas', 25.00, 300.00, 35, 1),
(3, 'Outskirts', 50.00, 500.00, 45, 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `order_number` varchar(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_type` enum('pickup','delivery') NOT NULL,
  `order_time` datetime NOT NULL,
  `preferred_time` datetime DEFAULT NULL,
  `status` enum('pending','confirmed','preparing','ready','out_for_delivery','delivered','completed','cancelled') DEFAULT 'pending',
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tip_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `handler_id` int(11) DEFAULT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `confirmed_at` datetime DEFAULT NULL,
  `preparing_at` datetime DEFAULT NULL,
  `ready_at` datetime DEFAULT NULL,
  `out_for_delivery_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `order_number`, `user_id`, `order_type`, `order_time`, `preferred_time`, `status`, `subtotal`, `delivery_fee`, `tip_amount`, `total_amount`, `handler_id`, `driver_id`, `confirmed_at`, `preparing_at`, `ready_at`, `out_for_delivery_at`, `delivered_at`, `cancelled_at`, `created_at`, `updated_at`) VALUES
(38, 'BSLH-1763272527-7ewC', 7, 'delivery', '2025-11-16 13:55:27', NULL, 'cancelled', 278.00, 0.00, 0.00, 278.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-18 11:42:04', '2025-11-16 13:55:27', '2025-11-18 11:42:04'),
(39, 'BSLH-1763300179-ekwg', 7, 'pickup', '2025-11-16 21:36:19', NULL, 'completed', 95.00, 0.00, 0.00, 95.00, 2, NULL, '2025-11-18 11:42:09', '2025-11-18 11:42:16', '2025-11-18 11:42:43', NULL, NULL, NULL, '2025-11-16 21:36:19', '2025-11-18 11:42:46'),
(40, 'BSLH-1763379616-XSK4', 7, 'pickup', '2025-11-17 19:40:16', NULL, 'completed', 275.00, 0.00, 0.00, 275.00, 2, NULL, '2025-11-18 11:43:06', '2025-11-18 11:44:58', '2025-11-18 11:45:30', NULL, NULL, NULL, '2025-11-17 19:40:16', '2025-11-18 11:51:14'),
(41, 'BSLH-1763387154-KHRa', 7, 'pickup', '2025-11-17 21:45:54', NULL, 'ready', 65.00, 0.00, 0.00, 65.00, 2, NULL, '2025-11-18 11:42:33', '2025-11-18 11:44:35', '2025-11-18 11:45:18', NULL, NULL, NULL, '2025-11-17 21:45:54', '2025-11-18 11:45:18'),
(42, 'BSLH-1763387241-GYvh', 7, 'pickup', '2025-11-17 21:47:21', NULL, 'preparing', 35.00, 0.00, 0.00, 35.00, 2, NULL, '2025-11-18 11:43:04', '2025-11-18 11:45:02', NULL, NULL, NULL, NULL, '2025-11-17 21:47:21', '2025-11-18 11:45:02'),
(43, 'BSLH-1763394091-2jbM', 7, 'pickup', '2025-11-17 23:41:31', NULL, 'preparing', 89.00, 0.00, 0.00, 89.00, 2, NULL, '2025-11-18 11:42:35', '2025-11-18 11:45:05', NULL, NULL, NULL, NULL, '2025-11-17 23:41:31', '2025-11-18 11:45:05'),
(44, 'BSLH-1763394128-eugs', 7, 'pickup', '2025-11-17 23:42:08', NULL, 'completed', 194.00, 0.00, 0.00, 194.00, 2, NULL, '2025-11-18 11:42:36', '2025-11-18 11:42:54', '2025-11-18 11:42:57', NULL, NULL, NULL, '2025-11-17 23:42:08', '2025-11-18 11:44:46'),
(49, 'BSLH-1763435671-gu2Q', 7, 'pickup', '2025-11-18 11:14:31', NULL, 'preparing', 25.00, 0.00, 0.00, 25.00, 2, NULL, '2025-11-18 11:43:00', '2025-11-18 11:44:13', NULL, NULL, NULL, NULL, '2025-11-18 11:14:31', '2025-11-18 11:44:13'),
(52, 'BSLH-1763437612-h2Ud', 7, 'delivery', '2025-11-18 11:46:52', NULL, 'delivered', 718.00, 0.00, 0.00, 718.00, 2, NULL, '2025-11-18 11:47:27', '2025-11-18 11:51:00', '2025-11-18 11:51:11', '2025-11-18 14:06:00', '2025-11-18 14:06:02', NULL, '2025-11-18 11:46:52', '2025-11-18 14:06:02'),
(55, 'BSLH-1763438464-9NHH', 7, 'pickup', '2025-11-18 12:01:04', '2025-11-18 10:00:00', 'pending', 393.00, 0.00, 0.00, 393.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-18 12:01:04', '2025-11-18 12:01:04'),
(58, 'BSLH-1763438821-VwiH', 7, 'delivery', '2025-11-18 12:07:01', NULL, 'pending', 95.00, 0.00, 0.00, 95.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-18 12:07:01', '2025-11-18 12:07:01'),
(66, 'BSLH-1763440020-eozt', 7, 'delivery', '2025-11-18 12:27:00', NULL, 'pending', 95.00, 0.00, 0.00, 95.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-18 12:27:00', '2025-11-18 12:27:00'),
(67, 'BSLH-1763440531-j27o', 7, 'delivery', '2025-11-18 12:35:31', NULL, 'pending', 149.00, 100.00, 0.00, 249.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-18 12:35:31', '2025-11-18 12:35:31'),
(68, 'BSLH-1763442273-veYf', 7, 'delivery', '2025-11-18 13:04:33', NULL, 'pending', 89.00, 100.00, 0.00, 189.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-18 13:04:33', '2025-11-18 13:04:33');

-- --------------------------------------------------------

--
-- Table structure for table `order_addresses`
--

CREATE TABLE `order_addresses` (
  `order_address_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `street` varchar(255) DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `floor_number` varchar(50) DEFAULT NULL,
  `apt_landmark` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_addresses`
--

INSERT INTO `order_addresses` (`order_address_id`, `order_id`, `street`, `barangay`, `city`, `province`, `floor_number`, `apt_landmark`) VALUES
(2, 38, 'Hjtgh', 'lumbangan', 'Nasugbu', NULL, '12', '5 star'),
(3, 52, 'Hjtgh', 'Papaya', 'Nasugbu', NULL, '12', '5 star'),
(4, 58, 'Hjtgh', 'Papaya', 'Nasugbu', 'Batangas', '12', '5 star'),
(5, 66, 'Hjtgh', 'lumbangan', 'Nasugbu', 'Batangas', '12', '5 star'),
(6, 67, 'Hjtgh', 'Papaya', 'Nasugbu', 'Batangas', '12', '5 star'),
(7, 68, 'Hjtgh', 'Papaya', 'Nasugbu', 'Batangas', '12', '5 star');

-- --------------------------------------------------------

--
-- Table structure for table `order_customer_details`
--

CREATE TABLE `order_customer_details` (
  `detail_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `customer_first_name` varchar(50) NOT NULL,
  `customer_last_name` varchar(50) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `order_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_customer_details`
--

INSERT INTO `order_customer_details` (`detail_id`, `order_id`, `customer_first_name`, `customer_last_name`, `customer_phone`, `customer_email`, `order_notes`) VALUES
(3, 38, 'Customer', 'Customer', '+639123456789', 'customer@gmail.com', ''),
(4, 39, 'Customer', 'Customer', '+639123456789', 'customer@gmail.com', ''),
(5, 40, 'Customer', 'Customer', '+639123456789', 'customer@gmail.com', ''),
(6, 41, 'Customer', 'Customer', '+639123456789', 'customer@gmail.com', ''),
(7, 42, 'Customer', 'Customer', '+639123456789', 'customer@gmail.com', ''),
(8, 43, 'Customer', 'Customer', '+639123456789', 'customer@gmail.com', ''),
(9, 44, 'Customer', 'Customer', '+639123456789', 'customer@gmail.com', ''),
(10, 49, 'Customer', 'Customer', '+639123456789', 'customer@gmail.com', ''),
(11, 52, 'Customer', 'Customer', '+639123456789', 'customer@gmail.com', ''),
(12, 55, 'Customer', 'Customer', '+639123456789', 'customer@gmail.com', ''),
(13, 58, 'Customer', 'Customer', '+639123456789', 'customer@gmail.com', ''),
(14, 66, 'Customer', 'Customer', '+639123456789', 'customer@gmail.com', ''),
(15, 67, 'Customer', 'Customer', '+639123456789', 'customer@gmail.com', ''),
(16, 68, 'Customer', 'Customer', '+639123456789', 'customer@gmail.com', '');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `total_price` decimal(10,2) NOT NULL,
  `special_instructions` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `product_name`, `unit_price`, `quantity`, `total_price`, `special_instructions`, `created_at`) VALUES
(3, 38, 4, 'Lomi Overload', 149.00, 1, 149.00, NULL, '2025-11-16 13:55:27'),
(4, 38, 2, 'Lomi with Lechon Kawali', 129.00, 1, 129.00, NULL, '2025-11-16 13:55:27'),
(5, 39, 3, 'Chicken Lomi', 95.00, 1, 95.00, NULL, '2025-11-16 21:36:19'),
(6, 40, 9, 'Hotsilog', 90.00, 2, 180.00, NULL, '2025-11-17 19:40:16'),
(7, 40, 3, 'Chicken Lomi', 95.00, 1, 95.00, NULL, '2025-11-17 19:40:16'),
(8, 41, 19, 'Leche Flan', 65.00, 1, 65.00, NULL, '2025-11-17 21:45:54'),
(9, 42, 11, 'Iced Gulaman', 35.00, 1, 35.00, NULL, '2025-11-17 21:47:21'),
(10, 43, 1, 'Original Bente Sais Lomi', 89.00, 1, 89.00, NULL, '2025-11-17 23:41:31'),
(11, 44, 19, 'Leche Flan', 65.00, 1, 65.00, NULL, '2025-11-17 23:42:08'),
(12, 44, 2, 'Lomi with Lechon Kawali', 129.00, 1, 129.00, NULL, '2025-11-17 23:42:08'),
(13, 49, 17, 'Garlic Rice (Cup)', 25.00, 1, 25.00, NULL, '2025-11-18 11:14:31'),
(14, 52, 4, 'Lomi Overload', 149.00, 1, 149.00, NULL, '2025-11-18 11:46:52'),
(15, 52, 10, 'Pancit Tray (Good for 6-8)', 480.00, 1, 480.00, NULL, '2025-11-18 11:46:52'),
(16, 52, 1, 'Original Bente Sais Lomi', 89.00, 1, 89.00, NULL, '2025-11-18 11:46:52'),
(17, 55, 4, 'Lomi Overload', 149.00, 2, 298.00, NULL, '2025-11-18 12:01:04'),
(18, 55, 3, 'Chicken Lomi', 95.00, 1, 95.00, NULL, '2025-11-18 12:01:04'),
(19, 58, 3, 'Chicken Lomi', 95.00, 1, 95.00, NULL, '2025-11-18 12:07:01'),
(20, 66, 3, 'Chicken Lomi', 95.00, 1, 95.00, NULL, '2025-11-18 12:27:00'),
(21, 67, 4, 'Lomi Overload', 149.00, 1, 149.00, NULL, '2025-11-18 12:35:31'),
(22, 68, 1, 'Original Bente Sais Lomi', 89.00, 1, 89.00, NULL, '2025-11-18 13:04:33');

-- --------------------------------------------------------

--
-- Table structure for table `order_payment_details`
--

CREATE TABLE `order_payment_details` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_method` enum('cash','gcash','card') NOT NULL,
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `gcash_reference` varchar(100) DEFAULT NULL,
  `gcash_amount` decimal(10,2) DEFAULT NULL,
  `gcash_sender_name` varchar(100) DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `change_amount` decimal(10,2) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_payment_details`
--

INSERT INTO `order_payment_details` (`payment_id`, `order_id`, `payment_method`, `payment_status`, `gcash_reference`, `gcash_amount`, `gcash_sender_name`, `amount_paid`, `change_amount`, `paid_at`) VALUES
(3, 38, 'gcash', 'paid', 'pay_V7AjidFm29XnQX8FxrK7hmBE', NULL, NULL, 278.00, NULL, '2025-11-16 13:55:27'),
(4, 39, 'gcash', 'paid', 'pay_3ZV6aw32Cu54DTJvth6BNuVL', NULL, NULL, 95.00, NULL, '2025-11-16 21:36:19'),
(5, 40, 'gcash', 'paid', 'pay_4rH1agnoGEkVYCZfWFFWPe22', NULL, NULL, 275.00, NULL, '2025-11-17 19:40:16'),
(6, 41, 'gcash', 'paid', 'pay_CheMtUhJBCLVA1hXeMN97PUf', NULL, NULL, 65.00, NULL, '2025-11-17 21:45:54'),
(7, 42, 'gcash', 'paid', 'pay_XyHzeA3o2wC2SmtCq1SzU6BQ', NULL, NULL, 35.00, NULL, '2025-11-17 21:47:21'),
(8, 43, 'gcash', 'paid', 'pay_xtZxXWGMB1FqXkqAStrcxvFF', NULL, NULL, 89.00, NULL, '2025-11-17 23:41:31'),
(9, 44, 'gcash', 'paid', 'pay_rs8yPBjqt3KDn6oCbqs1fVnq', NULL, NULL, 194.00, NULL, '2025-11-17 23:42:08'),
(10, 49, 'gcash', 'paid', 'pay_iGzsJSuyfF9qENFFaBPb3TWd', NULL, NULL, 25.00, NULL, '2025-11-18 11:14:31'),
(11, 52, 'gcash', 'paid', 'pay_tUHRQoYxNMLyCLD7B44KuFgh', NULL, NULL, 718.00, NULL, '2025-11-18 11:46:52'),
(12, 55, 'gcash', 'paid', 'pay_9KdJ3FriEndqqyQjQxjQxkwe', NULL, NULL, 393.00, NULL, '2025-11-18 12:01:04'),
(13, 58, 'gcash', 'paid', 'pay_s6kSdbDRjeAXkiEgN1zqLcQn', NULL, NULL, 95.00, NULL, '2025-11-18 12:07:01'),
(14, 66, 'gcash', 'paid', 'pay_yCNiNYifeAefUZTgrSo45XKT', NULL, NULL, 95.00, NULL, '2025-11-18 12:27:00'),
(15, 67, 'gcash', 'paid', 'pay_NoWAJUC2Jgnvfz3vFwLaBqsV', NULL, NULL, 249.00, NULL, '2025-11-18 12:35:31'),
(16, 68, 'gcash', 'paid', 'pay_rxNsQ65yYD4FWoCTAzkq9U34', NULL, NULL, 189.00, NULL, '2025-11-18 13:04:33');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `prep_time_minutes` int(11) DEFAULT 15,
  `is_available` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `name`, `description`, `base_price`, `image_url`, `prep_time_minutes`, `is_available`, `is_featured`, `created_at`, `updated_at`) VALUES
(1, 1, 'Original Bente Sais Lomi', 'Thick noodles, rich broth, egg, chicharon, and crispy toppings.', 89.00, 'uploads/products/ChickenLomi640-1.jpg', 15, 1, 1, '2025-11-14 12:54:35', '2025-11-14 21:04:49'),
(2, 1, 'Lomi with Lechon Kawali', 'Special lomi topped with crispy golden-brown lechon kawali.', 129.00, 'uploads/products/ChickenLomi640-1.jpg', 20, 1, 1, '2025-11-14 12:54:35', '2025-11-14 21:04:49'),
(3, 1, 'Chicken Lomi', 'Our classic lomi broth with tender chicken pieces and liver.', 95.00, 'uploads/products/ChickenLomi640-1.jpg', 15, 1, 0, '2025-11-14 12:54:35', '2025-11-14 21:04:49'),
(4, 1, 'Lomi Overload', 'The ultimate bowl: lomi with chicharon, lechon, liver, and extra toppings.', 149.00, 'uploads/products/ChickenLomi640-1.jpg', 25, 1, 1, '2025-11-14 12:54:35', '2025-11-14 21:04:49'),
(5, 2, 'Tapsilog', 'Garlic rice, fried egg, tapa. Classic, walang mintis.', 110.00, 'uploads/products/ChickenLomi640-1.jpg', 10, 1, 1, '2025-11-14 12:54:35', '2025-11-14 21:04:49'),
(6, 2, 'Lechon Kawali Silog', 'Crispy lechon kawali, garlic rice, and fried egg.', 125.00, 'uploads/products/ChickenLomi640-1.jpg', 12, 1, 1, '2025-11-14 12:54:35', '2025-11-14 21:04:49'),
(7, 2, 'Porkchop Silog', 'Breaded porkchop, garlic rice, and fried egg.', 115.00, 'uploads/products/ChickenLomi640-1.jpg', 10, 1, 0, '2025-11-14 12:54:35', '2025-11-14 21:04:49'),
(8, 2, 'Bangus Silog', 'Marinated boneless bangus (milkfish), garlic rice, and fried egg.', 120.00, 'uploads/products/ChickenLomi640-1.jpg', 12, 1, 0, '2025-11-14 12:54:35', '2025-11-14 21:04:49'),
(9, 2, 'Hotsilog', 'Classic red hotdog, garlic rice, and fried egg. Pambata favorite.', 90.00, 'uploads/products/ChickenLomi640-1.jpg', 8, 1, 0, '2025-11-14 12:54:35', '2025-11-14 21:04:49'),
(10, 3, 'Pancit Tray (Good for 6-8)', 'Perfect for handaan, barkada, inuman, overtime sa office.', 480.00, 'uploads/products/ChickenLomi640-1.jpg', 30, 1, 1, '2025-11-14 12:54:35', '2025-11-14 21:04:49'),
(11, 4, 'Iced Gulaman', 'Matamis, malamig, pambanlaw after lomi.', 35.00, 'uploads/products/ChickenLomi640-1.jpg', 2, 1, 0, '2025-11-14 12:54:35', '2025-11-14 21:04:49'),
(12, 4, 'Coke (Canned)', 'Coke Regular in can.', 40.00, 'uploads/products/ChickenLomi640-1.jpg', 1, 1, 0, '2025-11-14 12:54:35', '2025-11-14 21:04:49'),
(13, 4, 'Sprite (Canned)', 'Sprite in can.', 40.00, 'uploads/products/ChickenLomi640-1.jpg', 1, 1, 0, '2025-11-14 12:54:35', '2025-11-14 21:04:49'),
(14, 5, 'Tokwa\'t Baboy', 'Crispy tofu and pork belly with soy-vinegar sauce.', 70.00, 'uploads/products/ChickenLomi640-1.jpg', 10, 0, 0, '2025-11-14 12:54:35', '2025-11-14 21:04:49'),
(15, 5, 'Lumpiang Shanghai (10pcs)', 'Crispy fried spring rolls with pork filling. Perfect with lomi.', 80.00, 'uploads/products/ChickenLomi640-1.jpg', 15, 1, 1, '2025-11-14 12:54:35', '2025-11-14 21:04:49'),
(16, 5, 'Extra Chicharon', 'A side order of crispy pork rinds for your lomi.', 30.00, 'uploads/products/ChickenLomi640-1.jpg', 2, 1, 0, '2025-11-14 12:54:35', '2025-11-14 21:04:49'),
(17, 5, 'Garlic Rice (Cup)', 'Extra cup of sinangag.', 25.00, 'uploads/products/ChickenLomi640-1.jpg', 5, 1, 0, '2025-11-14 12:54:35', '2025-11-14 21:04:49'),
(18, 5, 'Fried Egg (1pc)', 'Extra fried egg, sunny side up or scrambled.', 15.00, 'uploads/products/ChickenLomi640-1.jpg', 5, 0, 0, '2025-11-14 12:54:35', '2025-11-14 21:04:49'),
(19, 6, 'Leche Flan', 'Creamy caramel custard. A classic Filipino dessert.', 65.00, 'uploads/products/ChickenLomi640-1.jpg', 2, 1, 1, '2025-11-14 12:54:35', '2025-11-14 21:04:49');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('string','number','boolean','json') DEFAULT 'string',
  `description` text DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_id`, `setting_key`, `setting_value`, `setting_type`, `description`, `updated_at`) VALUES
(1, 'store_name', 'Bente Sais Lomi House', 'string', 'Restaurant name', '2025-11-14 12:54:35'),
(2, 'store_phone', '+63 956 244 6616', 'string', 'Contact number', '2025-11-14 12:54:35'),
(3, 'store_email', 'info@bentesaislomi.com', 'string', 'Contact email', '2025-11-14 12:54:35'),
(4, 'delivery_lead_time', '15', 'number', 'Minimum delivery preparation time in minutes', '2025-11-14 12:54:35'),
(5, 'opening_time', '08:00', 'string', 'Store opening time', '2025-11-14 12:54:35'),
(6, 'closing_time', '22:00', 'string', 'Store closing time', '2025-11-14 12:54:35'),
(7, 'max_delivery_distance', '10', 'number', 'Maximum delivery distance in kilometers', '2025-11-14 12:54:35'),
(8, 'default_tip_percentages', '[10,15,20]', 'json', 'Default tip percentage options', '2025-11-14 12:54:35');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','staff','customer','driver') DEFAULT 'customer',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `email`, `phone`, `password`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'User', 'admin@gmail.com', '+639123456789', '$2y$10$pP/Sn1QnRJqcNEctb53NcO/Xo535av5gsWBGwuGLc1N40wZlIvjru', 'admin', 1, '2025-11-14 12:54:35', '2025-11-15 02:08:27'),
(2, 'Kitchen', 'Staff', 'staff@gmail.com', '+639123456788', '$2y$10$pP/Sn1QnRJqcNEctb53NcO/Xo535av5gsWBGwuGLc1N40wZlIvjru', 'staff', 1, '2025-11-14 12:54:35', '2025-11-15 02:08:47'),
(7, 'Customer', 'Customer', 'customer@gmail.com', '+639123456789', '$2y$10$pP/Sn1QnRJqcNEctb53NcO/Xo535av5gsWBGwuGLc1N40wZlIvjru', 'customer', 1, '2025-11-16 00:32:21', '2025-11-16 00:32:40');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `contact_submissions`
--
ALTER TABLE `contact_submissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `deliverable_barangays`
--
ALTER TABLE `deliverable_barangays`
  ADD PRIMARY KEY (`barangay_id`),
  ADD UNIQUE KEY `barangay_name` (`barangay_name`);

--
-- Indexes for table `delivery_zones`
--
ALTER TABLE `delivery_zones`
  ADD PRIMARY KEY (`zone_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `handler_id` (`handler_id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `order_addresses`
--
ALTER TABLE `order_addresses`
  ADD PRIMARY KEY (`order_address_id`),
  ADD UNIQUE KEY `order_id` (`order_id`);

--
-- Indexes for table `order_customer_details`
--
ALTER TABLE `order_customer_details`
  ADD PRIMARY KEY (`detail_id`),
  ADD UNIQUE KEY `order_id` (`order_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `order_payment_details`
--
ALTER TABLE `order_payment_details`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `contact_submissions`
--
ALTER TABLE `contact_submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `deliverable_barangays`
--
ALTER TABLE `deliverable_barangays`
  MODIFY `barangay_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `delivery_zones`
--
ALTER TABLE `delivery_zones`
  MODIFY `zone_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `order_addresses`
--
ALTER TABLE `order_addresses`
  MODIFY `order_address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `order_customer_details`
--
ALTER TABLE `order_customer_details`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `order_payment_details`
--
ALTER TABLE `order_payment_details`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_driver` FOREIGN KEY (`driver_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_orders_handler` FOREIGN KEY (`handler_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `order_addresses`
--
ALTER TABLE `order_addresses`
  ADD CONSTRAINT `fk_order_addresses_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_customer_details`
--
ALTER TABLE `order_customer_details`
  ADD CONSTRAINT `fk_order_customer_details_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `order_payment_details`
--
ALTER TABLE `order_payment_details`
  ADD CONSTRAINT `fk_order_payment_details_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
