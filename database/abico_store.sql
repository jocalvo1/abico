-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 11, 2025 at 02:39 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `abico_store`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL COMMENT 'e.g., create_customer, update_debt, etc.',
  `details` text DEFAULT NULL,
  `old_value` decimal(10,2) DEFAULT NULL,
  `new_value` decimal(10,2) DEFAULT NULL,
  `update_type` varchar(20) DEFAULT NULL COMMENT 'partial, full, adjust',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `customer_id`, `action`, `details`, `old_value`, `new_value`, `update_type`, `created_at`) VALUES
(1, 3, 4, 'update_debt', 'Updated customer debt', 500.00, 0.00, 'full', '2025-07-10 14:15:51'),
(2, 3, 2, 'update_debt', 'Updated customer debt', 800.00, 600.00, 'adjust', '2025-07-10 14:16:45'),
(3, 3, 2, 'update_debt', 'Updated customer debt', 600.00, 400.00, 'partial', '2025-07-10 14:17:27'),
(4, 3, 1, 'update_debt', 'Updated customer debt', 15.00, 0.00, 'partial', '2025-07-10 14:22:27'),
(5, 3, 2, 'update_debt', 'Updated customer debt', 400.00, 350.00, 'partial', '2025-07-10 14:22:34'),
(6, 3, 4, 'update_debt', 'Updated customer debt', 0.00, 10.00, 'adjust', '2025-07-10 14:28:22'),
(7, 3, 4, 'update_debt', 'Updated customer debt', 10.00, 0.00, 'full', '2025-07-10 14:28:26'),
(8, 3, 4, 'update_debt', 'Updated customer debt', 0.00, 20.00, 'adjust', '2025-07-10 14:28:34'),
(9, 3, 4, 'update_debt', 'Updated customer debt', 20.00, 19.00, 'partial', '2025-07-10 14:28:39'),
(10, 3, 4, 'update_debt', 'Updated customer debt', 19.00, 2000.00, 'adjust', '2025-07-10 14:28:57'),
(11, 3, 4, 'update_debt', 'Updated customer debt', 2000.00, 500.00, 'partial', '2025-07-10 14:29:19'),
(12, 3, 4, 'update_debt', 'Updated customer debt', 500.00, 50000.00, 'adjust', '2025-07-10 14:29:28'),
(13, 3, 4, 'update_debt', 'Updated customer debt', 50000.00, 0.00, 'full', '2025-07-10 15:26:41'),
(14, 3, 4, 'update_debt', 'Updated customer debt', 0.00, 30.00, 'adjust', '2025-07-10 15:26:51'),
(15, 3, 4, 'update_debt', 'Updated customer debt', 30.00, 25.00, 'partial', '2025-07-10 15:26:57'),
(16, 3, 5, 'create_customer', 'Added new customer: rodel', NULL, NULL, NULL, '2025-07-10 15:27:11'),
(17, 3, 3, 'update_debt', 'Updated customer debt', 80.00, 75.00, 'partial', '2025-07-11 02:18:42'),
(18, 3, 4, 'update_debt', 'Updated customer debt', 25.00, 100.00, 'adjust', '2025-07-11 02:18:52'),
(19, 3, 3, 'update_debt', 'Updated customer debt', 75.00, 0.00, 'full', '2025-07-11 02:19:00'),
(20, 3, 6, 'create_customer', 'Added new customer: Jasper', NULL, NULL, NULL, '2025-07-11 08:31:55'),
(21, 3, 6, 'update_debt', 'Updated customer debt', 0.00, 20.00, 'adjust', '2025-07-11 08:32:20'),
(22, 3, 6, 'update_debt', 'Updated customer debt', 20.00, 15.00, 'partial', '2025-07-11 08:32:31'),
(23, 3, 6, 'update_debt', 'Updated customer debt', 15.00, 0.00, 'full', '2025-07-11 08:32:36');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `contact` varchar(100) DEFAULT NULL,
  `debt` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `customer_name`, `contact`, `debt`, `created_at`, `updated_at`) VALUES
(1, 'Zhedrick Villavecencio', '09065187005', '0', '2025-07-10 12:34:29', '2025-07-10 14:22:27'),
(2, 'Zhedrick Villavecencio', '09065187005', '350', '2025-07-10 12:34:43', '2025-07-10 14:22:34'),
(3, 'Zhean B. Villavecencio', '09065187005', '0', '2025-07-10 12:37:07', '2025-07-11 02:19:00'),
(4, 'joseph calvo', '09065187005', '100', '2025-07-10 13:11:02', '2025-07-11 02:18:52'),
(5, 'rodel', '09065187005', '0', '2025-07-10 15:27:11', '2025-07-10 15:27:11'),
(6, 'Jasper', '09065187005', '0', '2025-07-11 08:31:55', '2025-07-11 08:32:36');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_activity_logs`
--

CREATE TABLE `inventory_activity_logs` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL COMMENT 'e.g., create_product, update_product, delete_product, update_stock',
  `details` text DEFAULT NULL,
  `old_quantity` int(11) DEFAULT NULL,
  `new_quantity` int(11) DEFAULT NULL,
  `old_price` decimal(10,2) DEFAULT NULL,
  `new_price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_activity_logs`
--

INSERT INTO `inventory_activity_logs` (`id`, `product_id`, `action`, `details`, `old_quantity`, `new_quantity`, `old_price`, `new_price`, `created_at`) VALUES
(25, 35, 'create_product', 'Added new product: Patata', NULL, 32, NULL, 502.00, '2025-07-10 09:30:37'),
(26, 36, 'create_product', 'Added new product: Patata', NULL, 32, NULL, 502.05, '2025-07-10 09:32:12'),
(27, 37, 'create_product', 'Added new product: Patat0', NULL, 32, NULL, 502.05, '2025-07-10 09:32:41'),
(28, 38, 'create_product', 'Added new product: Patat01', NULL, 32, NULL, 502.05, '2025-07-10 09:33:33'),
(29, 36, 'delete_product', 'Deleted product: Patata', 32, NULL, 502.05, NULL, '2025-07-10 15:36:47'),
(30, 38, 'delete_product', 'Deleted product: Patat01', 32, NULL, 502.05, NULL, '2025-07-10 15:36:53'),
(31, 24, 'delete_product', 'Deleted product: Toothpaste car', 12, NULL, 10.01, NULL, '2025-07-10 15:37:55'),
(32, 23, 'delete_product', 'Deleted product: Toothpaste', 10, NULL, 10.00, NULL, '2025-07-10 15:37:59'),
(33, 39, 'create_product', 'Added new product: CheezIt', NULL, 100, NULL, 17.90, '2025-07-10 20:22:54'),
(34, 40, 'create_product', 'Added new product: CheezItasdas', NULL, 100, NULL, 17.90, '2025-07-11 02:34:52');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_method` enum('cash','credit_card','bank_transfer','other') NOT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `description`, `quantity`, `price`, `created_at`, `updated_at`) VALUES
(7, 'Patata', 'fresh from farm', 2000, 20.02, '2025-07-08 10:13:23', '2025-07-09 00:00:17'),
(11, 'Mang Juan ', '5 GRAM', 100, 15.00, '2025-07-09 04:56:53', '2025-07-10 07:10:32'),
(13, 'Mang Juan ', 'okay', 100, 15.00, '2025-07-10 04:41:39', '2025-07-10 10:41:39'),
(14, 'Mang Juan ', 'asda', 100, 20.00, '2025-07-10 04:46:01', '2025-07-10 08:31:47'),
(15, 'Patata', 'asda', 12, 12.00, '2025-07-10 04:46:09', '2025-07-10 08:31:36'),
(16, 'Chechs', 'asdf', 3, 0.02, '2025-07-10 04:46:21', '2025-07-10 08:58:18'),
(19, 'Mang Juan', 'asd', 3, 12.00, '2025-07-10 04:46:52', '2025-07-10 10:46:52'),
(20, 'Patata', 'asdfasdfsdafadsf/aksdmvlkjasdnfkjadsnkajdsfnkadsjfnadskjfnasdkjfnadskjnadskjnfjdjsajnfkjadsnfkjdsnfjksdnfjsdnfjkasdnfkjadsnfjkasdnfjkasdnfjasdnfjadsnfjkasdnfjasdnfjksadnfjkadsnfjsdnfjkadsnfjkdsnfjknasdjfknsdkjfnsdkjfnadsljkfnasdljkfnasdlkjfnasdlkjfnadslkjfnadslkjfnaldskjf', 12, 12.00, '2025-07-10 04:47:00', '2025-07-10 08:38:04'),
(25, 'BearBrand', 'asd', 20, 20.00, '2025-07-10 09:04:38', '2025-07-10 15:04:38'),
(26, 'BearBrand', 'lkjbkh', 20, 20.00, '2025-07-10 09:05:56', '2025-07-10 15:05:56'),
(27, 'BearBrand', 'asdas', 20, 20.00, '2025-07-10 09:06:54', '2025-07-10 15:06:54'),
(28, 'Milo', 'champion every day', 10, 10.00, '2025-07-10 09:08:54', '2025-07-10 15:08:54'),
(29, 'Milo', 'champion every days', 10, 10.00, '2025-07-10 09:09:47', '2025-07-10 09:12:46'),
(33, 'Patata', 'mn mn', 32, 502.00, '2025-07-10 09:26:26', '2025-07-10 15:26:26'),
(34, 'Patata', 'asfas', 32, 502.00, '2025-07-10 09:28:27', '2025-07-10 15:28:27'),
(35, 'Patata', 'sad', 32, 502.00, '2025-07-10 09:30:37', '2025-07-10 15:30:37'),
(37, 'Patat0', 'asxas', 32, 502.05, '2025-07-10 09:32:41', '2025-07-10 15:32:41'),
(39, 'CheezIt', 'Cheese, 60 grams', 100, 17.90, '2025-07-10 20:22:54', '2025-07-11 02:34:34');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_status` enum('pending','partial','paid') NOT NULL DEFAULT 'pending',
  `sale_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `created_at`) VALUES
(3, 'admin', '$2y$10$1MPaVjz.EUNGgzwKdF1iMeFtHd0P9ukfj5gfniZED2umL/LzJwdK2', 'Administrator', '2025-07-08 14:38:20'),
(4, 'abico', '$2y$10$1MPaVjz.EUNGgzwKdF1iMeFtHd0P9ukfj5gfniZED2umL/LzJwdK2', 'Administrator', '2025-07-08 14:49:56');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_activity_logs`
--
ALTER TABLE `inventory_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `inventory_activity_logs`
--
ALTER TABLE `inventory_activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD CONSTRAINT `sale_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sale_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
