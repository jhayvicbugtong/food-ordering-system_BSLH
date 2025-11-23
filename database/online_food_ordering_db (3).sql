-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 23, 2025 at 11:02 AM
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
(10, 'Pancit', '', 8, 1, '2025-11-19 20:42:04'),
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
(4, 'dgdg dfgdfs', '09067035958', 'aldriebaquiran15@gmail.com', 'nsdoskdskd', '2025-11-18 08:08:46', 0),
(5, 'dgdg dfgdfs', '09067035958', 'aldriebaquiran15@gmail.com', 'jaskajslaklskalskla', '2025-11-22 04:53:44', 0),
(6, 'Clarisse Cabral', '09067035958', 'clarisse15@gmail.com', 'Hi', '2025-11-22 07:58:37', 0),
(7, 'Clarisse Cabral', '09067035958', 'clarisse15@gmail.com', 'Hi', '2025-11-22 08:01:14', 0),
(8, 'Clarisse Cabral', '09067035958', 'aldriebaquiran15@gmail.com', 'kandksks', '2025-11-22 08:06:13', 0),
(9, 'Clarisse Cabral', '09067035958', 'aldriebaquiran15@gmail.com', 'nkadlaskapkspw', '2025-11-22 08:12:40', 0),
(10, 'Clarisse Cabral', '09067035958', 'aldriebaquiran15@gmail.com', 'nkadlaskapkspw', '2025-11-22 08:22:05', 0),
(11, 'Clarisse Cabral', '09067035958', 'aldriebaquiran15@gmail.com', 'nkadlaskapkspw', '2025-11-22 08:22:09', 0),
(12, 'John Aldrie Baquiran', '09067035958', 'johnaldriebaquiran51@gmail.com', 'sknskq', '2025-11-22 08:22:19', 0),
(13, 'John Aldrie Baquiran', '09067035958', 'johnaldriebaquiran51@gmail.com', 'sknskq', '2025-11-22 08:26:49', 0),
(14, 'John Aldrie Baquiran', '09067035958', 'johnaldriebaquiran51@gmail.com', 'sknskq', '2025-11-22 08:26:59', 0),
(15, 'John Aldrie Baquiran', '09067035958', 'johnaldriebaquiran51@gmail.com', 'hi', '2025-11-22 08:27:11', 0),
(16, 'John Aldrie Baquiran', '09067035958', 'johnaldriebaquiran51@gmail.com', ',,mmlm', '2025-11-22 13:49:56', 0);

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
(103, 'BSLH-1763892051-Gwb7', 27, 'delivery', '2025-11-23 18:00:51', NULL, 'pending', 110.00, 100.00, 0.00, 210.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-23 18:00:51', '2025-11-23 18:00:51'),
(104, 'BSLH-1763892085-3o5m', 27, 'pickup', '2025-11-23 18:01:25', NULL, 'pending', 340.00, 0.00, 0.00, 340.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-23 18:01:25', '2025-11-23 18:01:25');

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
(28, 103, 'Dagundong', 'Papaya', 'Nasugbu', 'Batangas', '', '');

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
(51, 103, 'John Aldrie', 'Baquiran', '09067035958', 'johnaldriebaquiran51@gmail.com', ''),
(52, 104, 'John Aldrie', 'Baquiran', '09067035958', 'johnaldriebaquiran51@gmail.com', '');

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
(57, 103, 21, 'Chicken Lomi', 110.00, 1, 110.00, NULL, '2025-11-23 18:00:51'),
(58, 104, 20, 'Barkada Lomi', 230.00, 1, 230.00, NULL, '2025-11-23 18:01:25'),
(59, 104, 24, 'Liver Lomi', 110.00, 1, 110.00, NULL, '2025-11-23 18:01:25');

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
(51, 103, 'gcash', 'paid', 'pay_FAybsF6uMbAnUJ7BiyuLwbHR', NULL, NULL, 210.00, NULL, '2025-11-23 18:00:51'),
(52, 104, 'gcash', 'paid', 'pay_GcVbVEDVckWMfZgE8h4y4CQU', NULL, NULL, 340.00, NULL, '2025-11-23 18:01:25');

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
(25, 10, 'Bihon', 'Bihon: Classic thin rice noodles stir-fried with vegetables and savory seasonings.', 75.00, 'uploads/products/6922c3c1afb67_1763886017.jpg', 15, 1, 0, '2025-11-19 20:44:25', '2025-11-23 17:18:18'),
(26, 10, 'Canton Bihon', 'Canton Bihon: A mix of egg noodles and rice noodles sautéed together with vegetables and light seasoning.', 80.00, 'uploads/products/691dc266e6bbe_1763557990.jpg', 15, 1, 0, '2025-11-19 20:45:39', '2025-11-23 17:18:25'),
(27, 10, 'Mike Bihon', 'Mike Bihon: Crispy fried noodles served with soft bihon and a flavorful stir-fried topping.', 80.00, 'uploads/products/691dbca1ba6a9_1763556513.jpg', 15, 1, 0, '2025-11-19 20:48:33', '2025-11-23 17:18:33'),
(28, 10, 'Guisado Plain', 'Guisado Plain: Stir-fried noodles with vegetables in a simple, savory sauce.', 80.00, 'uploads/products/691dbe272cc67_1763556903.jpg', 15, 1, 0, '2025-11-19 20:55:03', '2025-11-23 17:18:53'),
(29, 10, 'Guisado (Tamis Anghang)', 'Guisado (Tamis Anghang): Stir-fried noodles cooked in a sweet-spicy sauce with vegetables.', 80.00, 'uploads/products/691dc203dece0_1763557891.jpg', 15, 1, 0, '2025-11-19 20:57:16', '2025-11-23 17:18:59'),
(30, 10, 'Canton Plain', 'Canton Plain: Soft egg noodles stir-fried with vegetables in a simple savory blend.', 80.00, 'uploads/products/691dbf91ad0fe_1763557265.jpg', 15, 1, 0, '2025-11-19 21:01:05', '2025-11-23 17:19:05'),
(31, 10, 'Canton (Tamis Anghang)', 'Canton (Tamis Anghang): Egg noodles cooked in a sweet-spicy stir-fry with vegetables.', 85.00, 'uploads/products/691dc315b054d_1763558165.jpg', 15, 1, 0, '2025-11-19 21:03:22', '2025-11-23 17:19:12'),
(32, 10, 'Tustado Plain', 'Tustado Plain: Toasted crispy noodles topped with a savory stir-fried mix.', 75.00, 'uploads/products/691dc3d0a4337_1763558352.jpg', 15, 1, 0, '2025-11-19 21:19:12', '2025-11-23 17:19:17'),
(33, 10, 'Tustado (Tamis Anghang)', 'Tustado (Tamis Anghang): Crispy toasted noodles served with a sweet-spicy stir-fried topping.', 80.00, 'uploads/products/691dc409820ae_1763558409.jpg', 15, 1, 0, '2025-11-19 21:20:09', '2025-11-23 17:19:25'),
(34, 11, 'Bilao Spaghetti Large', 'Large: A large party tray of sweet Filipino-style spaghetti topped with ground meat and cheese.', 900.00, 'uploads/products/691dc4e824159_1763558632.jpg', 15, 1, 0, '2025-11-19 21:23:52', '2025-11-23 17:19:34'),
(35, 11, 'Bilao Spaghetti Extra Large', 'Extra Large: A big celebration-sized bilao of Filipino-style spaghetti, perfect for big gatherings.', 1200.00, 'uploads/products/691dc50bf4084_1763558667.jpg', 15, 1, 0, '2025-11-19 21:24:28', '2025-11-23 17:19:41'),
(37, 12, 'Pork Chami', 'Pork Chami (Tamis Anghang): Stir-fried thick noodles with tender pork in a sweet-spicy sauce.', 135.00, 'uploads/products/6922ca0464147_1763887620.jpg', 15, 1, 0, '2025-11-23 16:36:17', '2025-11-23 17:14:30'),
(38, 12, 'Chicken Chami Plain', 'Chicken Chami Plain: Thick stir-fried noodles cooked with flavorful chicken, served without added spice.', 120.00, 'uploads/products/6922cfa41fd3b_1763889060.jpg', 15, 1, 0, '2025-11-23 16:37:07', '2025-11-23 17:14:38'),
(39, 12, 'Chicken Chami (Tamis anghang)', 'Chicken Chami (Tamis Anghang): Sweet-spicy stir-fried noodles with tender chicken pieces.', 125.00, 'uploads/products/6922cfb1d6b3f_1763889073.jpg', 15, 1, 0, '2025-11-23 16:37:37', '2025-11-23 17:14:47'),
(40, 12, 'Lechon Kawali Chami Plain', 'Lechon Kawali Chami Plain: Crispy fried pork belly mixed with thick noodles in a savory plain sauce.', 145.00, 'uploads/products/6922cb2393955_1763887907.jpg', 15, 1, 0, '2025-11-23 16:38:44', '2025-11-23 17:14:54'),
(41, 12, 'Lechon Kawali (Tamis Anghang)', 'Lechon Kawali Chami (Tamis Anghang): Crispy pork belly tossed with thick noodles in a sweet-spicy sauce.', 150.00, 'uploads/products/6922cb2bb0760_1763887915.jpg', 15, 1, 0, '2025-11-23 16:39:21', '2025-11-23 17:15:00'),
(42, 12, 'Liver Chami Plain', 'Liver Chami Plain: Savory stir-fried noodles with sautéed liver in a mild, plain sauce.', 105.00, 'uploads/products/6922ced8a1937_1763888856.png', 15, 1, 0, '2025-11-23 16:39:35', '2025-11-23 17:15:12'),
(43, 12, 'Liver Chami (Tamis Anghang)', 'Liver Chami (Tamis Anghang): Stir-fried noodles with tender liver in a sweet-spicy sauce.', 110.00, 'uploads/products/6922cf8b964d0_1763889035.jpg', 15, 1, 0, '2025-11-23 16:39:47', '2025-11-23 17:15:18');

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
(1, 'store_name', 'Bente Sais Lomi Houses', 'string', 'Restaurant name', '2025-11-23 14:56:28'),
(2, 'store_phone', '+63 956 244 6616', 'string', 'Contact number', '2025-11-23 14:56:28'),
(3, 'store_email', 'info@bentesaislomi.com', 'string', 'Contact email', '2025-11-23 14:56:28'),
(5, 'opening_time', '09:00', 'string', 'Store opening time', '2025-11-23 15:12:03'),
(6, 'closing_time', '11:00', 'string', 'Store closing time', '2025-11-23 15:12:21'),
(9, 'store_status', 'open', 'string', 'Manually open or close the store', '2025-11-23 16:05:35');

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
  `verification_code` varchar(6) DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `role` enum('admin','staff','customer','driver') DEFAULT 'customer',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `email`, `phone`, `password`, `verification_code`, `email_verified_at`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'Admin', 'admin@gmail.com', '9123456789', '$2y$10$pP/Sn1QnRJqcNEctb53NcO/Xo535av5gsWBGwuGLc1N40wZlIvjru', '480434', NULL, 'admin', 1, '2025-11-14 12:54:35', '2025-11-22 22:51:10'),
(2, 'Staff', 'Staff', 'staff@gmail.com', '9123456788', '$2y$10$pP/Sn1QnRJqcNEctb53NcO/Xo535av5gsWBGwuGLc1N40wZlIvjru', '978827', NULL, 'staff', 1, '2025-11-14 12:54:35', '2025-11-23 15:32:00'),
(27, 'John Aldrie', 'Baquiran', 'johnaldriebaquiran51@gmail.com', '09067035958', '$2y$10$i9qL/tfqjw6p4r/yHYHt4.Ui8P5qL76fU7sq7IncAS.dFz88Hfo7i', NULL, '2025-11-23 16:06:20', 'customer', 1, '2025-11-23 16:06:02', '2025-11-23 16:06:20');

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
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `contact_submissions`
--
ALTER TABLE `contact_submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `deliverable_barangays`
--
ALTER TABLE `deliverable_barangays`
  MODIFY `barangay_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT for table `order_addresses`
--
ALTER TABLE `order_addresses`
  MODIFY `order_address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `order_customer_details`
--
ALTER TABLE `order_customer_details`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `order_payment_details`
--
ALTER TABLE `order_payment_details`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

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
