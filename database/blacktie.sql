-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 28, 2025 at 12:42 PM
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
-- Database: `blacktie`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `phone`, `email`, `password`, `address`) VALUES
(1, 'Admin', '999', 'admin@gmail.com', '321', 'admin'),
(2, 'Admin User', '', 'admin@blacktie.com', 'admin123', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_size_id` int(11) DEFAULT NULL,
  `size` varchar(10) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `selected` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `customer_id`, `product_id`, `product_size_id`, `size`, `quantity`, `created_at`, `selected`) VALUES
(21, 1, 7, 13, '', 1, '2025-06-27 17:04:17', 0);

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `phone`, `email`, `password`, `address`) VALUES
(1, 'zakwan', '016', 'zakwan@gmail.com', '123', 'a'),
(2, 'nadzmi', '014', 'nadzmi2324@gmail.com', '1234', 'lanang'),
(3, 'aki', '019', 'aki@gmail.com', '123', 'a');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `shipping_address` text NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_id`, `total_amount`, `shipping_address`, `payment_method`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1927.00, 'a', 'online_banking', 'pending', '2025-06-27 09:47:32', '2025-06-27 09:47:32'),
(2, 3, 1157.00, 'a', 'online_banking', 'shipped', '2025-06-27 12:57:27', '2025-06-27 14:21:57'),
(3, 1, 129.00, 'a', 'credit_card', 'pending', '2025-06-27 14:27:33', '2025-06-27 14:27:33'),
(4, 1, 1298.00, 'a', 'cash_on_delivery', 'pending', '2025-06-27 15:39:14', '2025-06-27 15:39:14'),
(5, 1, 129.00, 'a', 'credit_card', 'pending', '2025-06-27 15:40:30', '2025-06-27 15:40:30'),
(6, 1, 299.00, 'a', 'cash_on_delivery', 'pending', '2025-06-27 16:00:05', '2025-06-27 16:00:05'),
(7, 1, 749.00, 'a', 'cash_on_delivery', 'pending', '2025-06-27 16:04:08', '2025-06-27 16:04:08'),
(8, 1, 299.00, 'a', 'cash_on_delivery', 'pending', '2025-06-27 16:12:13', '2025-06-27 16:12:13'),
(9, 1, 899.00, 'a', 'credit_card', 'pending', '2025-06-27 16:12:44', '2025-06-27 16:12:44'),
(10, 1, 899.00, 'a', 'cash_on_delivery', 'pending', '2025-06-27 16:32:25', '2025-06-27 16:32:25'),
(11, 1, 399.00, 'a', 'cash_on_delivery', 'pending', '2025-06-27 16:51:52', '2025-06-27 16:51:52'),
(12, 1, 749.00, 'a', 'cash_on_delivery', 'shipped', '2025-06-27 17:05:03', '2025-06-27 17:06:08');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_size_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `size` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_size_id`, `quantity`, `price`, `size`) VALUES
(1, 1, 1, NULL, 2, 899.00, 'M'),
(2, 1, 8, NULL, 1, 129.00, 'M'),
(3, 2, 8, NULL, 2, 129.00, 'L'),
(4, 2, 1, NULL, 1, 899.00, 'M'),
(5, 3, 8, NULL, 1, 129.00, 'L'),
(6, 4, 5, 5, 1, 399.00, ''),
(7, 4, 1, 8, 1, 899.00, ''),
(8, 5, 8, 28, 1, 129.00, ''),
(9, 6, 7, 13, 1, 299.00, ''),
(10, 7, 3, 3, 1, 749.00, ''),
(11, 8, 7, 13, 1, 299.00, ''),
(12, 9, 1, 1, 1, 899.00, ''),
(13, 10, 1, 8, 1, 899.00, ''),
(14, 11, 5, 12, 1, 399.00, ''),
(15, 12, 3, 17, 1, 749.00, '');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `category` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category`, `price`, `stock`, `description`, `image`, `created_at`, `updated_at`) VALUES
(1, 'Classic Black Tuxedo', 'Tuxedos', 899.00, 12, 'Elegant black tuxedo perfect for formal events and weddings.', 'https://images.unsplash.com/photo-1594938298603-c8148c4dae35?w=400', '2025-06-27 09:38:26', '2025-06-27 12:57:27'),
(2, 'Navy Business Suit', 'Business Suits', 599.00, 25, 'Professional navy blue suit ideal for business meetings.', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400', '2025-06-27 09:38:26', '2025-06-27 09:38:26'),
(3, 'Charcoal Formal Suit', 'Formal Suits', 749.00, 20, 'Sophisticated charcoal suit for formal occasions.', 'https://images.unsplash.com/photo-1617137984095-74e4e5e3613f?w=400', '2025-06-27 09:38:26', '2025-06-27 09:38:26'),
(4, 'Light Grey Wedding Suit', 'Wedding Suits', 829.00, 12, 'Stylish light grey suit perfect for weddings and special events.', 'https://images.unsplash.com/photo-1594938298603-c8148c4dae35?w=400', '2025-06-27 09:38:26', '2025-06-27 09:38:26'),
(5, 'Casual Blazer', 'Casual Suits', 399.00, 30, 'Comfortable casual blazer for everyday professional wear.', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400', '2025-06-27 09:38:26', '2025-06-27 09:38:26'),
(7, 'Leather Dress Shoes', 'Accessories', 299.00, 40, 'Genuine leather dress shoes to complete your formal look.', 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=400', '2025-06-27 09:38:26', '2025-06-27 09:38:26'),
(8, 'Cufflinks Set', 'Accessories', 129.00, 19, 'Elegant silver cufflinks for formal shirts.', 'https://images.unsplash.com/photo-1611652022419-a9419f74343d?w=400', '2025-06-27 09:38:26', '2025-06-27 14:27:33');

-- --------------------------------------------------------

--
-- Table structure for table `product_sizes`
--

CREATE TABLE `product_sizes` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size` varchar(10) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_sizes`
--

INSERT INTO `product_sizes` (`id`, `product_id`, `size`, `stock`, `created_at`, `updated_at`) VALUES
(1, 1, 'S', 1, '2025-06-27 14:44:28', '2025-06-27 16:12:44'),
(2, 2, 'S', 5, '2025-06-27 14:44:28', '2025-06-27 14:44:28'),
(3, 3, 'S', 3, '2025-06-27 14:44:28', '2025-06-27 16:04:08'),
(4, 4, 'S', 2, '2025-06-27 14:44:28', '2025-06-27 14:44:28'),
(5, 5, 'S', 5, '2025-06-27 14:44:28', '2025-06-27 15:39:14'),
(6, 7, 'S', 8, '2025-06-27 14:44:28', '2025-06-27 14:44:28'),
(7, 8, 'S', 3, '2025-06-27 14:44:28', '2025-06-27 14:44:28'),
(8, 1, 'M', 1, '2025-06-27 14:44:28', '2025-06-27 16:32:25'),
(9, 2, 'M', 7, '2025-06-27 14:44:28', '2025-06-27 14:44:28'),
(10, 3, 'M', 6, '2025-06-27 14:44:28', '2025-06-27 14:44:28'),
(11, 4, 'M', 3, '2025-06-27 14:44:28', '2025-06-27 14:44:28'),
(12, 5, 'M', 8, '2025-06-27 14:44:28', '2025-06-27 16:51:52'),
(13, 7, 'M', 10, '2025-06-27 14:44:28', '2025-06-27 16:12:13'),
(14, 8, 'M', 5, '2025-06-27 14:44:28', '2025-06-27 14:44:28'),
(15, 1, 'L', 3, '2025-06-27 14:44:28', '2025-06-27 14:44:28'),
(16, 2, 'L', 7, '2025-06-27 14:44:28', '2025-06-27 14:44:28'),
(17, 3, 'L', 5, '2025-06-27 14:44:28', '2025-06-27 17:05:03'),
(18, 4, 'L', 3, '2025-06-27 14:44:28', '2025-06-27 14:44:28'),
(19, 5, 'L', 9, '2025-06-27 14:44:28', '2025-06-27 14:44:28'),
(20, 7, 'L', 12, '2025-06-27 14:44:28', '2025-06-27 14:44:28'),
(21, 8, 'L', 5, '2025-06-27 14:44:28', '2025-06-27 14:44:28'),
(22, 1, 'XL', 2, '2025-06-27 14:44:28', '2025-06-27 14:44:28'),
(23, 2, 'XL', 5, '2025-06-27 14:44:28', '2025-06-27 14:44:28'),
(24, 3, 'XL', 4, '2025-06-27 14:44:28', '2025-06-27 14:44:28'),
(25, 4, 'XL', 2, '2025-06-27 14:44:28', '2025-06-27 14:44:28'),
(26, 5, 'XL', 6, '2025-06-27 14:44:28', '2025-06-27 14:44:28'),
(27, 7, 'XL', 8, '2025-06-27 14:44:28', '2025-06-27 14:44:28'),
(28, 8, 'XL', 2, '2025-06-27 14:44:28', '2025-06-27 15:40:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cart_item` (`customer_id`,`product_id`,`size`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `product_size_id` (`product_size_id`),
  ADD KEY `idx_cart_customer_id` (`customer_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orders_customer_id` (`customer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `product_size_id` (`product_size_id`),
  ADD KEY `idx_order_items_order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_sizes`
--
ALTER TABLE `product_sizes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_product_size` (`product_id`,`size`),
  ADD KEY `idx_product_sizes_product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `product_sizes`
--
ALTER TABLE `product_sizes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_size_id`) REFERENCES `product_sizes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`product_size_id`) REFERENCES `product_sizes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_sizes`
--
ALTER TABLE `product_sizes`
  ADD CONSTRAINT `product_sizes_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
