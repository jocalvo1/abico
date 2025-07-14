-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
<<<<<<< HEAD
-- Generation Time: Jul 12, 2025 at 10:15 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4
=======
-- Generation Time: Jul 12, 2025 at 08:03 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12
>>>>>>> f675cbd9407bad57bf9e632e3b5a7b82eac05c8f

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
(23, 3, 6, 'update_debt', 'Updated customer debt', 15.00, 0.00, 'full', '2025-07-11 08:32:36'),
(24, 3, 4, 'update_debt', 'Updated customer debt', 100.00, 500.00, 'adjust', '2025-07-12 04:59:02'),
(25, 3, 4, 'update_debt', 'Updated customer debt', 500.00, 0.00, 'partial', '2025-07-12 04:59:16'),
(26, 3, 7, 'create_customer', 'Added new customer: Jack N Poy', NULL, NULL, NULL, '2025-07-12 05:22:58'),
(27, 3, 8, 'create_customer', 'Added new customer: Jo', NULL, NULL, NULL, '2025-07-12 16:07:38');

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
(4, 'joseph calvo', '09065187005', '0', '2025-07-10 13:11:02', '2025-07-12 04:59:16'),
(5, 'rodel', '09065187005', '0', '2025-07-10 15:27:11', '2025-07-10 15:27:11'),
(6, 'Jasper', '09065187005', '0', '2025-07-11 08:31:55', '2025-07-11 08:32:36'),
(7, 'Jack N Poy', '09611302308', '0', '2025-07-12 05:22:58', '2025-07-12 05:22:58'),
(8, 'Jo', '09611302308', '0', '2025-07-12 16:07:38', '2025-07-12 16:07:38');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_activity_log`
--

CREATE TABLE `inventory_activity_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'ID of the user who performed the action',
  `action` varchar(50) NOT NULL COMMENT 'Type of action (add, update, delete, etc.)',
  `description` text NOT NULL COMMENT 'Detailed description of the action',
  `product_id` int(11) DEFAULT NULL COMMENT 'ID of the affected product',
  `product_name` varchar(255) DEFAULT NULL COMMENT 'Name of the affected product',
  `old_values` text DEFAULT NULL COMMENT 'JSON string of old values (for updates)',
  `new_values` text DEFAULT NULL COMMENT 'JSON string of new values',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_activity_log`
--

INSERT INTO `inventory_activity_log` (`id`, `user_id`, `action`, `description`, `product_id`, `product_name`, `old_values`, `new_values`, `created_at`) VALUES
(25, 3, 'add', 'Added new product', 36, 'Fresh Whole Milk', NULL, '{\n    \"product_name\": \"Fresh Whole Milk\",\n    \"description\": \"\",\n    \"price\": null,\n    \"price_per_unit\": \"85\",\n    \"stock_quantity\": 120,\n    \"unit_type\": \"L\",\n    \"unit_value\": \"1\",\n    \"other_unit_type\": null,\n    \"pieces_per_pack\": null\n}', '2025-07-12 13:08:39'),
(26, 3, 'add', 'Added new product', 37, 'FreshScent Bath Soap', NULL, '{\n    \"product_name\": \"FreshScent Bath Soap\",\n    \"description\": \"\",\n    \"price\": null,\n    \"price_per_unit\": \"99\",\n    \"stock_quantity\": 77,\n    \"unit_type\": \"pack\",\n    \"unit_value\": \"1\",\n    \"other_unit_type\": null,\n    \"pieces_per_pack\": 5\n}', '2025-07-12 13:10:34'),
(27, 3, 'add', 'Added new product', 38, 'UltraBright LED Flashlight', NULL, '{\n    \"product_name\": \"UltraBright LED Flashlight\",\n    \"description\": \"\",\n    \"price\": null,\n    \"price_per_unit\": \"49\",\n    \"stock_quantity\": 50,\n    \"unit_type\": \"box\",\n    \"unit_value\": \"1\",\n    \"other_unit_type\": \"box\",\n    \"pieces_per_pack\": null\n}', '2025-07-12 13:11:10'),
(28, 3, 'add', 'Added new product', 39, 'UltraBright LED Flashlight', NULL, '{\n    \"product_name\": \"UltraBright LED Flashlight\",\n    \"description\": \"\",\n    \"price\": null,\n    \"price_per_unit\": \"49\",\n    \"stock_quantity\": 50,\n    \"unit_type\": \"g\",\n    \"unit_value\": \"20\",\n    \"other_unit_type\": null,\n    \"pieces_per_pack\": null\n}', '2025-07-12 15:57:10'),
(29, 3, 'add', 'Added new product', 40, 'UltraBright LED Flashlight', NULL, '{\n    \"product_name\": \"UltraBright LED Flashlight\",\n    \"description\": \"\",\n    \"price\": null,\n    \"price_per_unit\": \"49\",\n    \"stock_quantity\": 50,\n    \"unit_type\": \"g\",\n    \"unit_value\": \"50\",\n    \"other_unit_type\": null,\n    \"pieces_per_pack\": null\n}', '2025-07-12 15:57:29'),
(30, 3, 'add', 'Added new product', 41, 'UltraBright LED Flashlight', NULL, '{\n    \"product_name\": \"UltraBright LED Flashlight\",\n    \"description\": \"\",\n    \"price\": null,\n    \"price_per_unit\": \"49\",\n    \"stock_quantity\": 50,\n    \"unit_type\": \"g\",\n    \"unit_value\": \"50\",\n    \"other_unit_type\": null,\n    \"pieces_per_pack\": null\n}', '2025-07-12 15:57:45'),
(31, 3, 'add', 'Added new product', 42, 'Coke', NULL, '{\n    \"product_name\": \"Coke\",\n    \"description\": \"\",\n    \"price\": null,\n    \"price_per_unit\": \"20\",\n    \"stock_quantity\": 10,\n    \"unit_type\": \"pack\",\n    \"unit_value\": \"1\",\n    \"other_unit_type\": null,\n    \"pieces_per_pack\": 12\n}', '2025-07-12 15:58:25'),
(32, 3, 'update', 'Updated product details', 42, 'Coke', '{\n    \"pieces_per_pack\": 12\n}', '{\n    \"pieces_per_pack\": 16\n}', '2025-07-12 15:59:54');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `unit_value` int(11) NOT NULL DEFAULT 1,
  `unit_type` varchar(20) NOT NULL DEFAULT 'piece',
  `other_unit_type` varchar(50) DEFAULT NULL,
  `pieces_per_pack` int(11) DEFAULT NULL,
  `stock_quantity` int(11) NOT NULL,
  `price_per_unit` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Stores product inventory information';

--
-- Dumping data for table `products`
--

<<<<<<< HEAD
INSERT INTO `products` (`id`, `product_name`, `unit_value`, `unit_type`, `other_unit_type`, `pieces_per_pack`, `stock_quantity`, `price_per_unit`, `created_at`, `updated_at`) VALUES
(36, 'Fresh Whole Milk', 1, 'L', NULL, NULL, 120, 85.00, '2025-07-11 23:08:39', '2025-07-12 05:08:39'),
(37, 'FreshScent Bath Soap', 1, 'pack', NULL, 5, 77, 99.00, '2025-07-11 23:10:34', '2025-07-12 05:10:34'),
(38, 'UltraBright LED Flashlight', 1, 'box', 'box', NULL, 50, 49.00, '2025-07-11 23:11:10', '2025-07-12 05:11:10'),
(39, 'UltraBright LED Flashlight', 20, 'g', NULL, NULL, 50, 49.00, '2025-07-12 01:57:10', '2025-07-12 07:57:10'),
(40, 'UltraBright LED Flashlight', 50, 'g', NULL, NULL, 50, 49.00, '2025-07-12 01:57:28', '2025-07-12 07:57:28'),
(41, 'UltraBright LED Flashlight', 50, 'g', NULL, NULL, 50, 49.00, '2025-07-12 01:57:45', '2025-07-12 07:57:45'),
(42, 'Coke', 1, 'pack', NULL, 16, 10, 20.00, '2025-07-12 01:58:25', '2025-07-12 01:59:54');
=======
INSERT INTO `products` (`id`, `product_name`, `description`, `quantity`, `price`, `created_at`, `updated_at`) VALUES
(7, 'Patata', 'fresh from farm', 2000, 20.02, '2025-07-08 10:13:23', '2025-07-09 00:00:17'),
(11, 'Mang Juan ', '5 GRAM', 100, 15.00, '2025-07-09 04:56:53', '2025-07-10 07:10:32'),
(13, 'Mang Juan ', 'okay', 100, 15.00, '2025-07-10 04:41:39', '2025-07-10 10:41:39'),
(14, 'Mang Juan ', 'asda', 100, 20.00, '2025-07-10 04:46:01', '2025-07-10 08:31:47'),
(15, 'Patata', 'asda', 12, 12.00, '2025-07-10 04:46:09', '2025-07-10 08:31:36'),
(16, 'Chechs', 'asdf', 3, 0.02, '2025-07-10 04:46:21', '2025-07-10 08:58:18'),
(19, 'Mang Juan', 'asd', 3, 12.00, '2025-07-10 04:46:52', '2025-07-10 10:46:52'),
(25, 'BearBrand', 'asd', 20, 20.00, '2025-07-10 09:04:38', '2025-07-10 15:04:38'),
(27, 'BearBrand', 'asdas', 20, 20.00, '2025-07-10 09:06:54', '2025-07-10 15:06:54'),
(28, 'Milo', 'champion every day', 10, 10.00, '2025-07-10 09:08:54', '2025-07-10 15:08:54'),
(29, 'Milo', 'champion every days', 10, 10.00, '2025-07-10 09:09:47', '2025-07-10 09:12:46'),
(33, 'Patata', 'mn mn', 32, 502.00, '2025-07-10 09:26:26', '2025-07-10 15:26:26'),
(34, 'Patata', 'asfas', 32, 502.00, '2025-07-10 09:28:27', '2025-07-10 15:28:27'),
(35, 'Patata', 'sad', 32, 502.00, '2025-07-10 09:30:37', '2025-07-10 15:30:37'),
(37, 'Patat0', 'asxas', 32, 502.05, '2025-07-10 09:32:41', '2025-07-10 15:32:41'),
(39, 'CheezIt', 'Cheese, 60 grams', 100, 17.90, '2025-07-10 20:22:54', '2025-07-11 02:34:34'),
(41, 'skibiditoiletrizzahh', '', 32, 52.00, '2025-07-12 13:47:08', '2025-07-12 04:47:08');
>>>>>>> f675cbd9407bad57bf9e632e3b5a7b82eac05c8f

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
<<<<<<< HEAD
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
=======
>>>>>>> f675cbd9407bad57bf9e632e3b5a7b82eac05c8f
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
-- Indexes for table `inventory_activity_log`
--
ALTER TABLE `inventory_activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_name` (`product_name`),
  ADD KEY `idx_product_type` (`unit_type`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `inventory_activity_log`
--
ALTER TABLE `inventory_activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
<<<<<<< HEAD
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;
=======
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;
>>>>>>> f675cbd9407bad57bf9e632e3b5a7b82eac05c8f

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL;
<<<<<<< HEAD

--
-- Constraints for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD CONSTRAINT `fk_sale_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `sale_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE;
=======
>>>>>>> f675cbd9407bad57bf9e632e3b5a7b82eac05c8f
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
