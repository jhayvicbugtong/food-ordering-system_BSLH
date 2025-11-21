-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 21, 2025 at 11:56 AM
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
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `description`, `display_order`, `is_active`, `created_at`) VALUES
(1, 'Lomi Bowls', 'Hearty lomi soups with various toppings', 1, 1, '2025-11-14 12:54:35'),
(2, 'Silog Meals', 'Classic Filipino rice meals with egg', 2, 1, '2025-11-14 12:54:35'),
(3, 'Party Trays', 'Shareable trays for gatherings', 3, 1, '2025-11-14 12:54:35'),
(4, 'Drinks', 'Beverages and refreshments', 4, 1, '2025-11-14 12:54:35'),
(5, 'Sides', 'Side dishes and appetizers', 5, 1, '2025-11-14 12:54:35'),
(6, 'Panghimagas (Desserts)', 'Sweet treats and desserts', 6, 1, '2025-11-14 12:54:35'),
(7, 'Special Order', '', 7, 1, '0000-00-00 00:00:00'),
(10, 'Pancit (Short Order)', NULL, 8, 1, '2025-11-19 20:42:04'),
(11, 'Bilao Spaghetti', NULL, 9, 1, '2025-11-19 21:21:44'),
(12, 'Chami', NULL, 10, 1, '2025-11-19 21:26:04');

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
(69, 'BSLH-1763650367-5UmD', 7, 'delivery', '2025-11-20 22:52:47', NULL, 'delivered', 230.00, 100.00, 23.00, 353.00, 2, NULL, '2025-11-21 11:29:14', '2025-11-21 11:29:17', '2025-11-21 11:29:18', '2025-11-21 11:36:10', '2025-11-21 11:36:12', '2025-11-20 22:55:48', '2025-11-20 22:52:47', '2025-11-21 11:36:12'),
(70, 'BSLH-1763650393-gUhz', 7, 'delivery', '2025-11-20 22:53:13', NULL, 'delivered', 230.00, 100.00, 0.00, 330.00, 2, NULL, '2025-11-21 11:04:13', '2025-11-21 11:04:22', '2025-11-21 11:04:31', '2025-11-21 11:07:46', '2025-11-21 11:07:48', NULL, '2025-11-20 22:53:13', '2025-11-21 11:07:48'),
(71, 'BSLH-1763650414-TzG1', 7, 'pickup', '2025-11-20 22:53:34', NULL, 'completed', 90.00, 0.00, 0.00, 90.00, 2, NULL, '2025-11-21 11:04:20', '2025-11-21 11:24:11', '2025-11-21 11:24:12', NULL, NULL, NULL, '2025-11-20 22:53:34', '2025-11-21 11:24:13'),
(72, 'BSLH-1763650446', 7, 'delivery', '2025-11-20 22:54:06', NULL, 'delivered', 130.00, 100.00, 0.00, 230.00, 2, NULL, '2025-11-21 10:59:47', '2025-11-21 10:59:48', '2025-11-21 10:59:51', '2025-11-21 10:59:57', '2025-11-21 11:24:15', NULL, '2025-11-20 22:54:06', '2025-11-21 11:24:15'),
(73, 'BSLH-1763650492', 7, 'pickup', '2025-11-20 22:54:52', NULL, 'completed', 110.00, 0.00, 0.00, 110.00, 2, NULL, '2025-11-20 22:57:23', '2025-11-20 22:57:27', '2025-11-20 22:57:30', NULL, NULL, NULL, '2025-11-20 22:54:52', '2025-11-20 22:57:51'),
(74, 'BSLH-1763650502-Y6oz', 7, 'delivery', '2025-11-20 22:55:02', NULL, 'delivered', 180.00, 100.00, 0.00, 280.00, 2, NULL, '2025-11-21 10:23:36', NULL, NULL, '2025-11-21 10:45:17', '2025-11-21 10:45:19', NULL, '2025-11-20 22:55:02', '2025-11-21 10:45:19'),
(75, 'BSLH-1763696357-qtDh', 7, 'delivery', '2025-11-21 11:39:17', NULL, 'delivered', 230.00, 100.00, 23.00, 353.00, 2, NULL, '2025-11-21 11:55:19', '2025-11-21 11:58:04', '2025-11-21 11:58:07', '2025-11-21 11:58:19', '2025-11-21 11:58:22', NULL, '2025-11-21 11:39:17', '2025-11-21 11:58:22'),
(76, 'BSLH-1763696390-qEQ6', 7, 'delivery', '2025-11-21 11:39:50', NULL, 'delivered', 70.00, 100.00, 7.00, 177.00, 2, NULL, '2025-11-21 11:59:30', '2025-11-21 11:59:32', '2025-11-21 11:59:34', '2025-11-21 11:59:45', '2025-11-21 12:01:31', NULL, '2025-11-21 11:39:50', '2025-11-21 12:01:31'),
(77, 'BSLH-1763696474', 7, 'delivery', '2025-11-21 11:41:14', NULL, 'delivered', 110.00, 100.00, 0.00, 210.00, 2, NULL, '2025-11-21 11:43:10', '2025-11-21 11:43:12', '2025-11-21 11:43:14', '2025-11-21 11:43:19', '2025-11-21 11:43:21', NULL, '2025-11-21 11:41:14', '2025-11-21 11:43:21'),
(78, 'BSLH-1763696683', 7, 'pickup', '2025-11-21 11:44:43', NULL, 'completed', 70.00, 0.00, 0.00, 70.00, 2, NULL, '2025-11-21 11:45:22', '2025-11-21 11:45:24', '2025-11-21 11:45:25', NULL, NULL, NULL, '2025-11-21 11:44:43', '2025-11-21 11:48:42'),
(79, 'BSLH-1763696955', 7, 'delivery', '2025-11-21 11:49:15', NULL, 'delivered', 90.00, 100.00, 0.00, 190.00, 2, NULL, '2025-11-21 11:49:36', '2025-11-21 11:49:38', '2025-11-21 11:49:39', '2025-11-21 11:49:50', '2025-11-21 11:49:51', NULL, '2025-11-21 11:49:15', '2025-11-21 11:49:51'),
(80, 'BSLH-1763697280', 7, 'pickup', '2025-11-21 11:54:40', NULL, 'completed', 130.00, 0.00, 0.00, 130.00, 2, NULL, '2025-11-21 11:55:01', '2025-11-21 11:55:02', '2025-11-21 11:55:04', NULL, NULL, NULL, '2025-11-21 11:54:40', '2025-11-21 12:23:15'),
(81, 'BSLH-1763697462', 7, 'delivery', '2025-11-21 11:57:42', NULL, 'delivered', 110.00, 100.00, 0.00, 210.00, 2, NULL, '2025-11-21 11:58:03', '2025-11-21 11:58:05', '2025-11-21 11:58:11', '2025-11-21 11:59:11', '2025-11-21 11:59:13', NULL, '2025-11-21 11:57:42', '2025-11-21 11:59:13'),
(82, 'BSLH-1763697747', 7, 'delivery', '2025-11-21 12:02:27', NULL, 'ready', 110.00, 100.00, 0.00, 210.00, 2, NULL, '2025-11-21 12:02:40', '2025-11-21 12:02:42', '2025-11-21 12:03:55', NULL, NULL, NULL, '2025-11-21 12:02:27', '2025-11-21 12:03:55'),
(83, 'BSLH-1763699094', 7, 'delivery', '2025-11-21 12:24:54', NULL, 'preparing', 150.00, 100.00, 0.00, 250.00, 2, NULL, '2025-11-21 12:25:53', '2025-11-21 12:29:32', NULL, NULL, NULL, NULL, '2025-11-21 12:24:54', '2025-11-21 12:29:32'),
(84, 'BSLH-1763699128-us6g', 7, 'delivery', '2025-11-21 12:25:28', NULL, 'confirmed', 90.00, 100.00, 0.00, 190.00, 2, NULL, '2025-11-21 12:29:02', NULL, NULL, NULL, NULL, NULL, '2025-11-21 12:25:28', '2025-11-21 12:29:02');

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
(8, 69, 'Dagundong', 'Papaya', 'Nasugbu', 'Batangas', '', ''),
(9, 70, 'Dagundong', 'Papaya', 'Nasugbu', 'Batangas', '', ''),
(10, 72, 'Dagundong', 'Papaya', 'Nasugbu', 'Batangas', '', ''),
(11, 74, 'Dagundong', 'Papaya', 'Nasugbu', 'Batangas', '', ''),
(12, 75, 'Dagundong', 'Papaya', 'Nasugbu', 'Batangas', '', 'Pico De Loro'),
(13, 76, 'Dagundong', 'Papaya', 'Nasugbu', 'Batangas', '', 'Pico De Loro'),
(14, 77, 'Dagundong', 'Papaya', 'Nasugbu', 'Batangas', '', 'Pico De Loro'),
(15, 79, 'Dagundong', 'Papaya', 'Nasugbu', 'Batangas', '', 'Pico De Loro'),
(16, 81, 'Dagundong', 'Papaya', 'Nasugbu', 'Batangas', '', 'Pico De Loro'),
(17, 82, 'Dagundong', 'Papaya', 'Nasugbu', 'Batangas', '', 'Pico De Loro'),
(18, 83, 'Dagundong', 'Papaya', 'Nasugbu', 'Batangas', '', 'Pico De Loro'),
(19, 84, 'Dagundong', 'Papaya', 'Nasugbu', 'Batangas', '', 'Pico De Loro');

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
(17, 69, 'Customer', 'Customer', '+639123456789', 'customer@gmail.com', ''),
(18, 70, 'Customer', 'Customer', '+639123456789', 'customer@gmail.com', ''),
(19, 71, 'Customer', 'Customer', '+639123456789', 'customer@gmail.com', ''),
(20, 72, 'Customer', 'Customer', '+639123456789', 'customer@gmail.com', ''),
(21, 73, 'Customer', 'Customer', '+639123456789', 'customer@gmail.com', ''),
(22, 74, 'Customer', 'Customer', '+639123456789', 'customer@gmail.com', ''),
(23, 75, 'Customer', 'Customer', '+639123456789', 'customer@gmail.com', 'no chilli'),
(24, 76, 'Customer', 'Customer', '+639123456789', 'customer@gmail.com', 'no chilli'),
(25, 77, 'Customer', 'Customer', '+639123456789', 'customer@gmail.com', ''),
(26, 78, 'Customer', 'Customer', '+639123456789', 'customer@gmail.com', ''),
(27, 79, 'Customer', 'Customer', '+639123456789', 'customer@gmail.com', ''),
(28, 80, 'Customer', 'Customer', '+639123456789', 'customer@gmail.com', ''),
(29, 81, 'Customer', 'Customer', '+639123456789', 'customer@gmail.com', ''),
(30, 82, 'Customer', 'Customer', '+639123456789', 'customer@gmail.com', ''),
(31, 83, 'Customer', 'Customer', '+639123456789', 'customer@gmail.com', ''),
(32, 84, 'Customer', 'Customer', '+639123456789', 'customer@gmail.com', '');

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
(23, 69, 20, 'Barkada Lomi', 230.00, 1, 230.00, NULL, '2025-11-20 22:52:47'),
(24, 70, 20, 'Barkada Lomi', 230.00, 1, 230.00, NULL, '2025-11-20 22:53:13'),
(25, 71, 19, 'Jumbo Lomi', 90.00, 1, 90.00, NULL, '2025-11-20 22:53:34'),
(26, 72, 23, 'Lechon Lomi', 130.00, 1, 130.00, NULL, '2025-11-20 22:54:06'),
(27, 73, 21, 'Chicken Lomi', 110.00, 1, 110.00, NULL, '2025-11-20 22:54:52'),
(28, 74, 19, 'Jumbo Lomi', 90.00, 2, 180.00, NULL, '2025-11-20 22:55:02'),
(29, 75, 20, 'Barkada Lomi', 230.00, 1, 230.00, NULL, '2025-11-21 11:39:17'),
(30, 76, 6, 'Malingsilog', 70.00, 1, 70.00, NULL, '2025-11-21 11:39:50'),
(31, 77, 21, 'Chicken Lomi', 110.00, 1, 110.00, NULL, '2025-11-21 11:41:14'),
(32, 78, 18, 'Special Lomi', 70.00, 1, 70.00, NULL, '2025-11-21 11:44:43'),
(33, 79, 19, 'Jumbo Lomi', 90.00, 1, 90.00, NULL, '2025-11-21 11:49:15'),
(34, 80, 23, 'Lechon Lomi', 130.00, 1, 130.00, NULL, '2025-11-21 11:54:40'),
(35, 81, 21, 'Chicken Lomi', 110.00, 1, 110.00, NULL, '2025-11-21 11:57:42'),
(36, 82, 21, 'Chicken Lomi', 110.00, 1, 110.00, NULL, '2025-11-21 12:02:27'),
(37, 83, 14, 'Crispy Dinakdakan only', 150.00, 1, 150.00, NULL, '2025-11-21 12:24:54'),
(38, 84, 19, 'Jumbo Lomi', 90.00, 1, 90.00, NULL, '2025-11-21 12:25:28');

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
(17, 69, 'gcash', 'paid', 'pay_HsYnyhSkweKn4hmmgHESbYhf', NULL, NULL, 353.00, NULL, '2025-11-20 22:52:47'),
(18, 70, 'gcash', 'paid', 'pay_spnMVEHTTKYWB4RkhP9ppUoQ', NULL, NULL, 330.00, NULL, '2025-11-20 22:53:13'),
(19, 71, 'gcash', 'paid', 'pay_VyixdVK5tpzoiTy4JNT6bUyr', NULL, NULL, 90.00, NULL, '2025-11-20 22:53:34'),
(20, 72, 'cash', 'pending', NULL, NULL, NULL, 230.00, NULL, NULL),
(21, 73, 'cash', 'paid', '', NULL, '', 1000.00, 890.00, '2025-11-20 22:57:51'),
(22, 74, 'gcash', 'paid', 'pay_Cgfkva2PEZR7YMw4ZuH2Yt3u', NULL, NULL, 280.00, NULL, '2025-11-20 22:55:02'),
(23, 75, 'gcash', 'paid', 'pay_vBpykZN3BFeT54zgLN7AVbdC', NULL, NULL, 353.00, NULL, '2025-11-21 11:39:17'),
(24, 76, 'gcash', 'paid', 'pay_Xe1BKkaQMeyJtbPHuj2vHhmZ', NULL, NULL, 177.00, NULL, '2025-11-21 11:39:50'),
(25, 77, 'cash', 'pending', NULL, NULL, NULL, 210.00, NULL, NULL),
(26, 78, 'cash', 'paid', '', NULL, '', 100.00, 30.00, '2025-11-21 11:48:42'),
(27, 79, 'cash', 'pending', NULL, NULL, NULL, 190.00, NULL, NULL),
(28, 80, 'cash', 'paid', '', NULL, '', 1000.00, 870.00, '2025-11-21 12:23:15'),
(29, 81, 'cash', 'paid', NULL, NULL, NULL, 210.00, NULL, '2025-11-21 11:59:13'),
(30, 82, 'cash', 'pending', NULL, NULL, NULL, 210.00, NULL, NULL),
(31, 83, 'cash', 'pending', NULL, NULL, NULL, 250.00, NULL, NULL),
(32, 84, 'gcash', 'paid', 'pay_8Eui6BmnMtVG7qLnKhU8v6Rm', NULL, NULL, 190.00, NULL, '2025-11-21 12:25:28');

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
(1, 2, 'Tapsilog', 'Tapsilog: Savory marinated beef tapa with garlic fried rice and a sunny-side-up egg.', 90.00, 'uploads/products/691d451751109_1763525911.jpg', 15, 1, 1, '2025-11-14 12:54:35', '2025-11-19 14:51:12'),
(2, 2, 'Tocilog', 'Tocilog: Sweet pork tocino paired with garlic rice and a fried egg.', 90.00, 'uploads/products/691d478b20887_1763526539.jpg', 20, 1, 1, '2025-11-14 12:54:35', '2025-11-19 14:51:22'),
(3, 2, 'Porksilog', 'Porksilog: Juicy pork slices served with garlic fried rice and a fried egg.', 90.00, 'uploads/products/691d4a3813466_1763527224.jpg', 15, 1, 0, '2025-11-14 12:54:35', '2025-11-19 14:51:38'),
(4, 2, 'Chicksilog', 'Chicksilog: Tender chicken fillet with garlic rice and a fried egg.', 90.00, 'uploads/products/691d4aed7c55d_1763527405.jpg', 25, 1, 1, '2025-11-14 12:54:35', '2025-11-19 14:51:54'),
(5, 2, 'Bulaklaksilog', 'Bulaklaksilog: Flavorful grilled pork shoulder (bulaklak) with garlic fried rice and a fried egg', 100.00, 'uploads/products/691d4b90a4be5_1763527568.jpg', 10, 1, 1, '2025-11-14 12:54:35', '2025-11-19 14:52:04'),
(6, 2, 'Malingsilog', 'Malingsilog: Savory Filipino-style luncheon meat with garlic fried rice and a fried egg.', 70.00, 'uploads/products/691d4ce349e95_1763527907.jpg', 12, 1, 1, '2025-11-14 12:54:35', '2025-11-19 14:52:21'),
(7, 2, 'Cornsilog', 'Cornsilog: Classic corned beef served with garlic rice and a fried egg.', 75.00, 'uploads/products/691d5090ceeb6_1763528848.jpg', 10, 1, 0, '2025-11-14 12:54:35', '2025-11-19 14:52:35'),
(10, 2, 'Hungarian Silog', 'Hungariansilog: Spicy Hungarian sausage with garlic rice and a fried egg.', 100.00, 'uploads/products/691d52ff0085b_1763529471.jpg', 30, 1, 1, '2025-11-14 12:54:35', '2025-11-19 14:55:53'),
(11, 2, 'Lechon Silog', 'Lechonsilog: Crispy roasted pork (lechon) with garlic fried rice and a fried egg.', 100.00, 'uploads/products/691d55303ecc8_1763530032.jpg', 2, 1, 0, '2025-11-14 12:54:35', '2025-11-19 14:56:05'),
(12, 2, 'Shanghai Silog', 'Shanghaisilog: Sweet-style pork (Shanghai) served with garlic fried rice and a fried egg.', 75.00, 'uploads/products/691d55e497d47_1763530212.jpg', 1, 1, 0, '2025-11-14 12:54:35', '2025-11-19 14:56:14'),
(13, 7, 'Crispy Dinakdakan with Rice', 'Crispy Dinakdakan with Rice: A rich and crispy Ilocano pork dish served with warm steamed rice.', 150.00, 'uploads/products/691d5861ecdd8_1763530849.jpg', 1, 1, 0, '2025-11-14 12:54:35', '2025-11-19 14:57:40'),
(14, 7, 'Crispy Dinakdakan only', 'Crispy Dinakdakan Only: Crunchy Ilocano-style pork doused in creamy, tangy dressing—served on its own.', 150.00, 'uploads/products/691d59e44b161_1763531236.jpg', 10, 1, 0, '2025-11-14 12:54:35', '2025-11-19 14:57:54'),
(15, 7, 'Tokwa\'t Baboy with Rice', 'Tokwat Baboy w/ Rice: Classic tofu and pork combo tossed in a savory vinegar-soy sauce, paired with steamed rice.', 80.00, 'uploads/products/691d5cbfa3cf3_1763531967.jpg', 15, 1, 1, '2025-11-14 12:54:35', '2025-11-19 14:58:04'),
(16, 7, 'Tokwa\'t Baboy only', 'Tokwat Baboy Only: A hearty mix of pork and tofu in a tangy, savory sauce—perfect as a standalone dish.', 130.00, 'uploads/products/691d5cf527b73_1763532021.jpg', 2, 1, 0, '2025-11-14 12:54:35', '2025-11-19 14:58:14'),
(17, 7, 'Lechon Kawali only', 'Lechon Kawali Only: Crispy deep-fried pork belly with golden crackling and tender meat.', 150.00, 'uploads/products/691d5d826c016_1763532162.jpg', 5, 1, 0, '2025-11-14 12:54:35', '2025-11-19 14:58:24'),
(18, 1, 'Special Lomi', 'Special Lomi: A hearty lomi bowl loaded with noodles, veggies, and a mix of flavorful toppings.', 70.00, 'uploads/products/691d6cefdb5c6_1763536111.jpg', 5, 1, 0, '2025-11-14 12:54:35', '2025-11-19 15:32:25'),
(19, 1, 'Jumbo Lomi', 'Jumbo Lomi: Extra-large serving of rich, savory lomi perfect for big appetites', 90.00, 'uploads/products/691d6d0f08a30_1763536143.jpg', 2, 1, 1, '2025-11-14 12:54:35', '2025-11-19 15:32:38'),
(20, 1, 'Barkada Lomi', 'Barkada Lomi: A giant lomi portion made for sharing packed with toppings and bold flavor.', 230.00, 'uploads/products/691d6e6933c78_1763536489.jpg', 15, 1, 0, '2025-11-19 15:14:49', '2025-11-19 15:32:50'),
(21, 1, 'Chicken Lomi', 'Chicken Lomi: Warm, comforting lomi noodles served with tender chicken pieces in a thick, savory broth.', 110.00, 'uploads/products/691d6f6dd8f7d_1763536749.jpg', 15, 1, 0, '2025-11-19 15:19:09', '2025-11-19 20:39:04'),
(22, 1, 'Pork Lomi', 'Pork Lomi: Classic lomi with juicy pork slices simmered in a rich, flavorful soup.', 120.00, '', 15, 1, 0, '2025-11-19 15:26:34', '2025-11-19 15:33:11'),
(23, 1, 'Lechon Lomi', 'Lechon Lomi: Crispy lechon paired with thick lomi noodles for a deliciously indulgent bowl.', 130.00, 'uploads/products/691d718ecfb17_1763537294.jpg', 15, 1, 0, '2025-11-19 15:28:14', '2025-11-19 15:33:28'),
(24, 1, 'Liver Lomi', 'Liver Lomi: Traditional lomi with savory, tender liver pieces for a bold and hearty taste.', 110.00, 'uploads/products/691d725b148b4_1763537499.JPG', 15, 1, 0, '2025-11-19 15:31:39', '2025-11-19 15:33:39'),
(25, 10, 'Bihon', '', 75.00, 'uploads/products/691dbba9b8da8_1763556265.jpg', 15, 1, 0, '2025-11-19 20:44:25', '2025-11-19 20:44:25'),
(26, 10, 'Canton Bihon', '', 80.00, 'uploads/products/691dc266e6bbe_1763557990.jpg', 15, 1, 0, '2025-11-19 20:45:39', '2025-11-19 21:13:10'),
(27, 10, 'Mike Bihon', '', 80.00, 'uploads/products/691dbca1ba6a9_1763556513.jpg', 15, 1, 0, '2025-11-19 20:48:33', '2025-11-19 20:48:33'),
(28, 10, 'Guisado Plain', '', 80.00, 'uploads/products/691dbe272cc67_1763556903.jpg', 15, 1, 0, '2025-11-19 20:55:03', '2025-11-19 20:55:03'),
(29, 10, 'Guisado (Tamis Anghang)', '', 80.00, 'uploads/products/691dc203dece0_1763557891.jpg', 15, 1, 0, '2025-11-19 20:57:16', '2025-11-19 21:11:31'),
(30, 10, 'Canton Plain', '', 80.00, 'uploads/products/691dbf91ad0fe_1763557265.jpg', 15, 1, 0, '2025-11-19 21:01:05', '2025-11-19 21:01:05'),
(31, 10, 'Canton (Tamis Anghang)', '', 85.00, 'uploads/products/691dc315b054d_1763558165.jpg', 15, 1, 0, '2025-11-19 21:03:22', '2025-11-19 21:16:05'),
(32, 10, 'Tustado Plain', '', 75.00, 'uploads/products/691dc3d0a4337_1763558352.jpg', 15, 1, 0, '2025-11-19 21:19:12', '2025-11-19 21:19:12'),
(33, 10, 'Tustado (Tamis Anghang)', '', 80.00, 'uploads/products/691dc409820ae_1763558409.jpg', 15, 1, 0, '2025-11-19 21:20:09', '2025-11-19 21:20:09'),
(34, 11, 'Bilao Spaghetti Large', '', 900.00, 'uploads/products/691dc4e824159_1763558632.jpg', 15, 1, 0, '2025-11-19 21:23:52', '2025-11-19 21:23:52'),
(35, 11, 'Bilao Spaghetti Extra Large', '', 1200.00, 'uploads/products/691dc50bf4084_1763558667.jpg', 15, 1, 0, '2025-11-19 21:24:28', '2025-11-19 21:24:28');

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
(7, 'Customer', 'Customer', 'customer@gmail.com', '+639123456789', '$2y$10$pP/Sn1QnRJqcNEctb53NcO/Xo535av5gsWBGwuGLc1N40wZlIvjru', 'customer', 1, '2025-11-16 00:32:21', '2025-11-16 00:32:40'),
(9, 'John Aldrie', 'Baquiran', 'aldriebaquiran15@gmail.com', '+639067035958', '$2y$10$uiO7P0CwDqGN24Xlv/HqbeSqD/tKWjpkUzLmujPMd2UWuXQ0FOgKu', 'admin', 1, '2025-11-21 17:47:42', '2025-11-21 17:47:42');

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
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

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
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `order_addresses`
--
ALTER TABLE `order_addresses`
  MODIFY `order_address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `order_customer_details`
--
ALTER TABLE `order_customer_details`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `order_payment_details`
--
ALTER TABLE `order_payment_details`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
