-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 16, 2025 at 07:30 PM
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
-- Database: `abico_store`
--

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
(1, 'Joseph Emmanuel N. Calvo', '09611302308 / 09157361882', '0', '2025-07-15 05:58:39', '2025-07-15 05:58:39'),
(2, 'Zhedrick J. Villavecencio', '09123456789', '0', '2025-07-15 06:09:07', '2025-07-15 06:09:07'),
(3, 'Rodel A. Tayo', '09192626010', '0', '2025-07-16 03:29:22', '2025-07-16 03:29:22');

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
(1, 5, 'add', 'Added new product', NULL, 'Mang Juan', NULL, '{\n    \"product_name\": \"Mang Juan\",\n    \"description\": \"\",\n    \"price\": null,\n    \"price_per_unit\": \"14\",\n    \"stock_quantity\": 50,\n    \"unit_type\": \"pc\",\n    \"unit_value\": \"1\",\n    \"other_unit_type\": null,\n    \"pieces_per_pack\": null\n}', '2025-07-14 22:57:00'),
(2, 5, 'add', 'Added new product', NULL, 'Jasmine Rice (1 Sack)', NULL, '{\n    \"product_name\": \"Jasmine Rice (1 Sack)\",\n    \"description\": \"\",\n    \"price\": null,\n    \"price_per_unit\": \"2550\",\n    \"stock_quantity\": 30,\n    \"unit_type\": \"kg\",\n    \"unit_value\": \"50\",\n    \"other_unit_type\": null,\n    \"pieces_per_pack\": null\n}', '2025-07-14 23:02:25'),
(3, 5, 'add', 'Added new product', NULL, 'Zest-O Juice', NULL, '{\n    \"product_name\": \"Zest-O Juice\",\n    \"description\": \"\",\n    \"price\": null,\n    \"price_per_unit\": \"100\",\n    \"stock_quantity\": 100,\n    \"unit_type\": \"Carton\",\n    \"unit_value\": \"25\",\n    \"other_unit_type\": \"Carton\",\n    \"pieces_per_pack\": null\n}', '2025-07-14 23:03:15'),
(4, 5, 'add', 'Added new product', NULL, 'Milo', NULL, '{\n    \"product_name\": \"Milo\",\n    \"description\": \"\",\n    \"price\": null,\n    \"price_per_unit\": \"120\",\n    \"stock_quantity\": 20,\n    \"unit_type\": \"pack\",\n    \"unit_value\": \"1\",\n    \"other_unit_type\": null,\n    \"pieces_per_pack\": 12\n}', '2025-07-14 23:04:26'),
(5, 3, 'add', 'Added new product', 5, 'Kalamay nga pula', NULL, '{\n    \"product_name\": \"Kalamay nga pula\",\n    \"description\": \"\",\n    \"price\": null,\n    \"price_per_unit\": \"50\",\n    \"stock_quantity\": 40,\n    \"unit_type\": \"g\",\n    \"unit_value\": \"500\",\n    \"other_unit_type\": null,\n    \"pieces_per_pack\": null\n}', '2025-07-15 20:30:06'),
(6, 3, 'update', 'Updated product details', 4, 'Milo', '{\n    \"stock_quantity\": 19\n}', '{\n    \"stock_quantity\": 20\n}', '2025-07-15 20:33:55'),
(7, 3, 'add', 'Added new product', 6, 'Kalamay nga puti', NULL, '{\n    \"product_name\": \"Kalamay nga puti\",\n    \"description\": \"\",\n    \"price\": null,\n    \"price_per_unit\": \"50\",\n    \"stock_quantity\": 20,\n    \"unit_type\": \"g\",\n    \"unit_value\": \"500\",\n    \"other_unit_type\": null,\n    \"pieces_per_pack\": null\n}', '2025-07-15 20:34:23'),
(8, 3, 'add', 'Added new product', 7, 'Kalamay Hati', NULL, '{\n    \"product_name\": \"Kalamay Hati\",\n    \"description\": \"\",\n    \"price\": null,\n    \"price_per_unit\": \"12\",\n    \"stock_quantity\": 50,\n    \"unit_type\": \"pc\",\n    \"unit_value\": \"1\",\n    \"other_unit_type\": null,\n    \"pieces_per_pack\": null\n}', '2025-07-15 20:37:45');

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Cash', 'Cash payment', 1, '2025-07-13 02:14:25', '2025-07-13 02:14:25'),
(2, 'Online', 'Online payment or bank transfer', 1, '2025-07-13 02:14:25', '2025-07-13 02:14:25'),
(3, 'Cheque', 'Payment by check', 1, '2025-07-13 02:14:25', '2025-07-13 02:14:25');

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

INSERT INTO `products` (`id`, `product_name`, `unit_value`, `unit_type`, `other_unit_type`, `pieces_per_pack`, `stock_quantity`, `price_per_unit`, `created_at`, `updated_at`) VALUES
(1, 'Mang Juan', 1, 'pc', NULL, NULL, 46, 14.00, '2025-07-15 14:57:00', '2025-07-15 22:02:28'),
(2, 'Jasmine Rice (1 Sack)', 50, 'kg', NULL, NULL, 27, 2550.00, '2025-07-15 15:02:25', '2025-07-15 22:02:28'),
(3, 'Zest-O Juice', 25, 'Carton', 'Carton', NULL, 100, 100.00, '2025-07-15 15:03:15', '2025-07-15 06:03:15'),
(4, 'Milo', 1, 'pack', NULL, 12, 20, 120.00, '2025-07-15 15:04:26', '2025-07-16 12:33:55'),
(5, 'Kalamay nga pula', 500, 'g', NULL, NULL, 40, 50.00, '2025-07-16 12:30:06', '2025-07-16 03:30:06'),
(6, 'Kalamay nga puti', 500, 'g', NULL, NULL, 20, 50.00, '2025-07-16 12:34:23', '2025-07-16 03:34:23'),
(7, 'Kalamay Hati', 1, 'pc', NULL, NULL, 50, 12.00, '2025-07-16 12:37:45', '2025-07-16 03:37:45');

-- --------------------------------------------------------

--
-- Table structure for table `sales_activity_log`
--

CREATE TABLE `sales_activity_log` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_activity_log`
--

INSERT INTO `sales_activity_log` (`id`, `transaction_id`, `customer_name`, `description`, `created_at`) VALUES
(10, 16, 'Joseph Emmanuel N. Calvo', 'Customer Joseph Emmanuel N. Calvo bought 2 items for ₱2,578.00', '2025-07-15 20:31:04'),
(11, 17, 'Walk-in Customer', 'Customer Walk-in Customer bought 2 items for ₱150.00', '2025-07-15 20:35:07'),
(12, 18, 'Zhedrick J. Villavecencio', 'Customer Zhedrick J. Villavecencio bought 1 items for ₱5,100.00', '2025-07-15 20:35:26'),
(13, 19, 'Zhedrick J. Villavecencio', 'Customer Zhedrick J. Villavecencio bought 1 items for ₱5,100.00', '2025-07-15 20:37:13');

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sale_items`
--

INSERT INTO `sale_items` (`id`, `transaction_id`, `product_id`, `product_name`, `quantity`, `price`, `subtotal`, `created_at`) VALUES
(28, 16, 2, 'Jasmine Rice (1 Sack)', 1, 2550.00, 2550.00, '2025-07-16 03:31:04'),
(29, 16, 1, 'Mang Juan', 2, 14.00, 28.00, '2025-07-16 03:31:04'),
(30, 17, 5, 'Kalamay nga pula', 1, 50.00, 50.00, '2025-07-16 03:35:07'),
(31, 17, 6, 'Kalamay nga puti', 2, 50.00, 100.00, '2025-07-16 03:35:07'),
(32, 18, 2, 'Jasmine Rice (1 Sack)', 2, 2550.00, 5100.00, '2025-07-16 03:35:26'),
(33, 19, 2, 'Jasmine Rice (1 Sack)', 2, 2550.00, 5100.00, '2025-07-16 03:37:13');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `payment_method_id` int(11) DEFAULT NULL,
  `amount_received` decimal(10,2) NOT NULL,
  `change_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `customer_id`, `customer_name`, `total_amount`, `payment_method`, `payment_method_id`, `amount_received`, `change_amount`, `created_at`) VALUES
(16, 1, 'Joseph Emmanuel N. Calvo', 2578.00, 'Cheque', 3, 5000.00, 2422.00, '2025-07-16 03:31:04'),
(17, 0, 'Walk-in Customer', 150.00, 'Cash', 1, 200.00, 50.00, '2025-07-16 03:35:07'),
(18, 2, 'Zhedrick J. Villavecencio', 5100.00, 'Cheque', 3, 10000.00, 4900.00, '2025-07-16 03:35:26'),
(19, 2, 'Zhedrick J. Villavecencio', 5100.00, 'Cheque', 3, 7500.00, 2400.00, '2025-07-16 03:37:13');

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
(3, 'admin', '$2y$10$1MPaVjz.EUNGgzwKdF1iMeFtHd0P9ukfj5gfniZED2umL/LzJwdK2', 'Administrator', '2025-07-08 21:38:20'),
(4, 'abico', '$2y$10$1MPaVjz.EUNGgzwKdF1iMeFtHd0P9ukfj5gfniZED2umL/LzJwdK2', 'Administrator', '2025-07-08 21:49:56'),
(5, 'user', '$2y$10$gjlaLigXjZ/Bl70n5YkmF.fU/B5eqGUHmt4u6RmdFxPkeUJ9ubfDK', 'Administrator', '2025-07-13 06:17:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_activity_log`
--
ALTER TABLE `inventory_activity_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_name` (`product_name`),
  ADD KEY `idx_product_type` (`unit_type`);

--
-- Indexes for table `sales_activity_log`
--
ALTER TABLE `sales_activity_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `idx_transaction_payment_method` (`payment_method_id`);

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
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `inventory_activity_log`
--
ALTER TABLE `inventory_activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Table structure for table `ledger_activity_log`
--

CREATE TABLE IF NOT EXISTS `ledger_activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL COMMENT 'e.g., create_customer, update_debt, etc.',
  `details` text DEFAULT NULL,
  `old_value` decimal(10,2) DEFAULT NULL,
  `new_value` decimal(10,2) DEFAULT NULL,
  `update_type` varchar(20) DEFAULT NULL COMMENT 'partial, full, adjust',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ledger_activity_log`
--

INSERT INTO `ledger_activity_log` (`id`, `user_id`, `customer_id`, `action`, `details`, `old_value`, `new_value`, `update_type`, `created_at`) VALUES
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
(24, 3, 6, 'update_debt', 'Updated customer debt', 0.00, 500.00, 'adjust', '2025-07-13 06:23:28'),
(25, 3, 6, 'update_debt', 'Updated customer debt', 500.00, 400.00, 'partial', '2025-07-14 15:37:35'),
(26, 3, 6, 'update_debt', 'Updated customer debt', 400.00, 0.00, 'full', '2025-07-14 15:37:49'),
(27, 3, 4, 'update_debt', 'Updated customer debt', 100.00, 0.00, 'full', '2025-07-14 15:47:29');

--
-- AUTO_INCREMENT for table `ledger_activity_log`
--
ALTER TABLE `ledger_activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `sales_activity_log`
--
ALTER TABLE `sales_activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
