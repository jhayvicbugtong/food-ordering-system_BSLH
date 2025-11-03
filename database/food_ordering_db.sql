-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 03, 2025 at 05:31 PM
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
-- Database: `food_ordering_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `address_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `street` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `state_province` varchar(100) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `floor_number` varchar(50) DEFAULT NULL,
  `apt_landmark` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(6, 'Lomi Bowls'),
(7, 'Silog Meals'),
(8, 'Party Trays'),
(9, 'Drinks'),
(10, 'Sides'),
(11, 'Panghimagas (Desserts)');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `customer_name`, `email`) VALUES
(1, 'Jane Cruz', 'jane@example.com'),
(2, 'Leo Santos', 'leo@example.com'),
(3, 'Ava Lim', 'ava@example.com'),
(4, 'Chris Dela Cruz', 'chrisdc@example.com'),
(5, 'Ana Cruz', 'ana@example.com'),
(6, 'Walk-in POS', 'walkin@example.com'),
(7, 'Online Delivery', 'delivery@example.com');

-- --------------------------------------------------------

--
-- Table structure for table `deliveries`
--

CREATE TABLE `deliveries` (
  `delivery_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `instructions` varchar(255) DEFAULT NULL,
  `eta` datetime DEFAULT NULL,
  `delivery_status` enum('On time','Delayed') DEFAULT 'On time'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deliveries`
--

INSERT INTO `deliveries` (`delivery_id`, `order_id`, `driver_id`, `address`, `instructions`, `eta`, `delivery_status`) VALUES
(1, 2, 2, '14 Palm Drive, Phase 2', 'Leave at door if no answer', '2025-10-30 11:45:00', 'On time'),
(2, 4, 4, '9 Horizon Blk 3, Lot 7', 'Call on arrival', '2025-10-30 11:28:00', 'Delayed');

-- --------------------------------------------------------

--
-- Table structure for table `discounts_coupons`
--

CREATE TABLE `discounts_coupons` (
  `discount_id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `type` enum('Percentage','Fixed Amount') NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `valid_from` date NOT NULL,
  `valid_until` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `order_time` datetime DEFAULT NULL,
  `source` varchar(50) DEFAULT 'Pickup ASAP',
  `payment_method` varchar(20) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `order_type` enum('Pickup','Delivery') DEFAULT NULL,
  `address_id` int(11) DEFAULT NULL,
  `status` enum('Preparing','Ready','Out for delivery','Driver issue','Completed','Cancelled') DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT 0.00,
  `delivery_fee` decimal(10,2) DEFAULT 0.00,
  `tip_amount` decimal(10,2) DEFAULT 0.00,
  `discount_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `time_ready` time DEFAULT NULL,
  `handler_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `order_time`, `source`, `payment_method`, `customer_id`, `order_type`, `address_id`, `status`, `subtotal`, `delivery_fee`, `tip_amount`, `discount_id`, `total_amount`, `time_ready`, `handler_id`) VALUES
(1, '2025-10-30 10:12:00', 'Online Delivery', 'GCash', 1, 'Pickup', NULL, 'Preparing', 0.00, 0.00, 0.00, NULL, 0.00, '11:30:00', 1),
(2, '2025-10-30 09:58:00', 'Delivery', 'Card', 2, 'Delivery', NULL, 'Out for delivery', 0.00, 0.00, 0.00, NULL, 0.00, '10:45:00', 2),
(3, '2025-10-30 09:31:00', 'Pickup ASAP', 'Cash', 3, 'Pickup', NULL, 'Ready', 0.00, 0.00, 0.00, NULL, 0.00, '10:45:00', 3),
(4, '2025-10-30 09:05:00', 'Delivery', 'Card', 4, 'Delivery', NULL, 'Driver issue', 0.00, 0.00, 0.00, NULL, 0.00, '10:55:00', 4),
(5, '2025-10-30 11:00:00', 'Online Delivery', 'GCash', 3, 'Delivery', NULL, 'Preparing', 0.00, 0.00, 0.00, NULL, 0.00, '10:55:00', 1),
(6, '2025-10-30 11:10:00', 'Pickup ASAP', 'Cash', 1, 'Pickup', NULL, 'Ready', 0.00, 0.00, 0.00, NULL, 0.00, '11:42:00', 1),
(7, '2025-10-30 10:55:00', 'Walk-in POS', 'Cash', 2, 'Pickup', NULL, 'Completed', 0.00, 0.00, 0.00, NULL, 0.00, '11:15:00', 3),
(8, '2025-10-30 10:50:00', 'Delivery', 'Card', 3, 'Delivery', NULL, 'Driver issue', 0.00, 0.00, 0.00, NULL, 0.00, '10:45:00', 2),
(9, '2025-10-31 10:45:00', 'Online Delivery', 'GCash', 1, 'Delivery', NULL, 'Preparing', 0.00, 0.00, 0.00, NULL, 0.00, '10:55:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `item_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`item_id`, `order_id`, `product_id`, `quantity`) VALUES
(1, 1, NULL, 1),
(2, 1, NULL, 1),
(3, 2, NULL, 2),
(4, 3, NULL, 1),
(5, 4, NULL, 1),
(6, 4, NULL, 1),
(7, 1, NULL, 2),
(8, 1, NULL, 1),
(9, 2, NULL, 1),
(10, 3, NULL, 1),
(11, 3, NULL, 1),
(12, 4, NULL, 1),
(13, 4, NULL, 2),
(14, 9, NULL, 1),
(15, 9, NULL, 1),
(16, 5, NULL, 2),
(17, 5, NULL, 1),
(18, 6, NULL, 1),
(19, 7, NULL, 1),
(20, 7, NULL, 1),
(21, 8, NULL, 1),
(22, 8, NULL, 2);

-- --------------------------------------------------------

--
-- Table structure for table `order_tips`
--

CREATE TABLE `order_tips` (
  `tip_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `tip_amount` decimal(10,2) NOT NULL,
  `tip_percentage` decimal(5,2) DEFAULT NULL,
  `date_recorded` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `payment_method` enum('Cash','GCash','Card') DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `order_id`, `payment_method`, `amount`) VALUES
(1, 1, 'GCash', 350.00),
(2, 2, 'Cash', 120.00),
(3, 3, 'Card', 150.00),
(4, 4, 'GCash', 250.00),
(8, 7, 'Cash', 200.00),
(9, 8, 'Card', 250.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `name`, `description`, `base_price`, `image_url`, `is_available`) VALUES
(15, 6, 'Original Bente Sais Lomi', 'Thick noodles, rich broth, egg, chicharon, and crispy toppings.', 89.00, 'uploads/products/ChickenLomi640-1.jpg', 1),
(16, 6, 'Lomi with Lechon Kawali', 'Special lomi topped with crispy golden-brown lechon kawali.', 129.00, 'uploads/products/ChickenLomi640-1.jpg', 1),
(17, 6, 'Chicken Lomi', 'Our classic lomi broth with tender chicken pieces and liver.', 95.00, 'uploads/products/ChickenLomi640-1.jpg', 1),
(18, 6, 'Lomi Overload', 'The ultimate bowl: lomi with chicharon, lechon, liver, and extra toppings.', 149.00, 'uploads/products/ChickenLomi640-1.jpg', 1),
(19, 7, 'Tapsilog', 'Garlic rice, fried egg, tapa. Classic, walang mintis.', 110.00, 'uploads/products/ChickenLomi640-1.jpg', 1),
(20, 7, 'Lechon Kawali Silog', 'Crispy lechon kawali, garlic rice, and fried egg.', 125.00, 'uploads/products/ChickenLomi640-1.jpg', 1),
(21, 7, 'Porkchop Silog', 'Breaded porkchop, garlic rice, and fried egg.', 115.00, 'uploads/products/ChickenLomi640-1.jpg', 1),
(22, 7, 'Bangus Silog', 'Marinated boneless bangus (milkfish), garlic rice, and fried egg.', 120.00, 'uploads/products/ChickenLomi640-1.jpg', 1),
(23, 7, 'Hotsilog', 'Classic red hotdog, garlic rice, and fried egg. Pambata favorite.', 90.00, 'uploads/products/ChickenLomi640-1.jpg', 1),
(24, 8, 'Pancit Tray (Good for 6-8)', 'Perfect for handaan, barkada, inuman, overtime sa office.', 480.00, 'uploads/products/ChickenLomi640-1.jpg', 1),
(25, 9, 'Iced Gulaman', 'Matamis, malamig, pambanlaw after lomi.', 35.00, 'uploads/products/ChickenLomi640-1.jpg', 1),
(26, 9, 'Coke (Canned)', 'Coke Regular in can.', 40.00, 'uploads/products/ChickenLomi640-1.jpg', 1),
(27, 9, 'Sprite (Canned)', 'Sprite in can.', 40.00, 'uploads/products/ChickenLomi640-1.jpg', 1),
(28, 10, 'Tokwa\'t Baboy', 'Crispy tofu and pork belly with soy-vinegar sauce.', 70.00, 'uploads/products/ChickenLomi640-1.jpg', 0),
(29, 10, 'Lumpiang Shanghai (10pcs)', 'Crispy fried spring rolls with pork filling. Perfect with lomi.', 80.00, 'uploads/products/ChickenLomi640-1.jpg', 1),
(30, 10, 'Extra Chicharon', 'A side order of crispy pork rinds for your lomi.', 30.00, 'uploads/products/ChickenLomi640-1.jpg', 1),
(31, 10, 'Garlic Rice (Cup)', 'Extra cup of sinangag.', 25.00, 'uploads/products/ChickenLomi640-1.jpg', 1),
(32, 10, 'Fried Egg (1pc)', 'Extra fried egg, sunny side up or scrambled.', 15.00, 'uploads/products/ChickenLomi640-1.jpg', 0),
(33, 11, 'Leche Flan', 'Creamy caramel custard. A classic Filipino dessert.', 65.00, 'uploads/products/ChickenLomi640-1.jpg', 1);

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staff_id` int(11) NOT NULL,
  `staff_name` varchar(100) DEFAULT NULL,
  `role` enum('Kitchen','Front Desk','Rider','Dispatch') DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staff_id`, `staff_name`, `role`, `phone`) VALUES
(1, 'Staff', 'Kitchen', '+63 998 765 4321'),
(2, 'Rex P.', 'Rider', '+63 912 345 6789'),
(3, 'Janelle R.', 'Front Desk', '+63 977 111 2233'),
(4, 'Staff', 'Dispatch', '+63 998 765 4321'),
(5, 'Kitchen Staff', 'Kitchen', '+63 998 765 4321'),
(6, 'Rider A', 'Rider', '+63 917 654 8899'),
(7, 'Cashier', 'Front Desk', '+63 926 334 5566');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','staff') DEFAULT 'staff'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`) VALUES
(1, 'Admin', 'admin@example.com', '1234', 'admin'),
(2, 'Staff', 'staff@example.com', '1234', 'staff');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`);

--
-- Indexes for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD PRIMARY KEY (`delivery_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `discounts_coupons`
--
ALTER TABLE `discounts_coupons`
  ADD PRIMARY KEY (`discount_id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `handler_id` (`handler_id`),
  ADD KEY `fk_order_address` (`address_id`),
  ADD KEY `fk_order_discount` (`discount_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `fk_orderitem_product` (`product_id`);

--
-- Indexes for table `order_tips`
--
ALTER TABLE `order_tips`
  ADD PRIMARY KEY (`tip_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staff_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `delivery_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `discounts_coupons`
--
ALTER TABLE `discounts_coupons`
  MODIFY `discount_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `order_tips`
--
ALTER TABLE `order_tips`
  MODIFY `tip_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`);

--
-- Constraints for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD CONSTRAINT `deliveries_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `deliveries_ibfk_2` FOREIGN KEY (`driver_id`) REFERENCES `staff` (`staff_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_order_address` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`address_id`),
  ADD CONSTRAINT `fk_order_discount` FOREIGN KEY (`discount_id`) REFERENCES `discounts_coupons` (`discount_id`),
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`handler_id`) REFERENCES `staff` (`staff_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_orderitem_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Constraints for table `order_tips`
--
ALTER TABLE `order_tips`
  ADD CONSTRAINT `order_tips_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
