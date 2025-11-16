-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 15, 2025 at 05:52 PM
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
  `floor_number` varchar(50) DEFAULT NULL,
  `apt_landmark` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- AUTO_INCREMENT for table `delivery_zones`
--
ALTER TABLE `delivery_zones`
  MODIFY `zone_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `order_addresses`
--
ALTER TABLE `order_addresses`
  MODIFY `order_address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_customer_details`
--
ALTER TABLE `order_customer_details`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_payment_details`
--
ALTER TABLE `order_payment_details`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
