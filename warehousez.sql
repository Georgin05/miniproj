-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 01, 2025 at 06:19 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `warehousez`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `table_name` varchar(50) NOT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` text DEFAULT NULL,
  `new_values` text DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `description`) VALUES
(1, 'Electronics', 'Electronic components and devices'),
(2, 'Hardware', 'Construction and hardware items'),
(3, 'Electronics', 'Electronic components and devices'),
(4, 'Hardware', 'Construction and hardware items');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `payment_terms` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `inventory_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `batch_number` varchar(50) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `last_counted` datetime DEFAULT NULL,
  `last_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_adjustments`
--

CREATE TABLE `inventory_adjustments` (
  `adjustment_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `quantity_change` int(11) NOT NULL,
  `batch_number` varchar(50) DEFAULT NULL,
  `adjustment_type` enum('correction','damage','loss','theft','other') NOT NULL,
  `reason` text DEFAULT NULL,
  `adjusted_by` int(11) NOT NULL,
  `adjusted_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `location_id` int(11) NOT NULL,
  `location_code` varchar(50) NOT NULL,
  `location_name` varchar(100) NOT NULL,
  `zone` varchar(50) NOT NULL,
  `rack` varchar(20) DEFAULT NULL,
  `shelf` varchar(20) DEFAULT NULL,
  `bin_code` varchar(20) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `location_type` enum('storage','receiving','shipping','quarantine') DEFAULT 'storage',
  `status` enum('active','inactive','maintenance') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`location_id`, `location_code`, `location_name`, `zone`, `rack`, `shelf`, `bin_code`, `capacity`, `location_type`, `status`, `created_at`) VALUES
(1, 'A-1-2-01', 'Zone A Rack 1 Shelf 2 Bin 01', 'A', '1', '2', '01', 100, 'storage', 'active', '2025-08-01 21:38:18'),
(2, 'B-2-4-05', 'Zone B Rack 2 Shelf 4 Bin 05', 'B', '2', '4', '05', 50, 'storage', 'active', '2025-08-01 21:38:18');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `expected_ship_date` datetime DEFAULT NULL,
  `status` enum('draft','confirmed','processing','shipped','cancelled') DEFAULT 'draft',
  `priority` enum('low','medium','high','rush') DEFAULT 'medium',
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `requested_location_id` int(11) DEFAULT NULL,
  `picked_quantity` int(11) DEFAULT 0,
  `picked_location_id` int(11) DEFAULT NULL,
  `status` enum('pending','allocated','picked','packed','shipped') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `outbound`
--

CREATE TABLE `outbound` (
  `outbound_id` int(11) NOT NULL,
  `shipment_number` varchar(50) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `shipped_by` int(11) NOT NULL,
  `shipped_at` datetime DEFAULT current_timestamp(),
  `carrier` varchar(50) DEFAULT NULL,
  `tracking_number` varchar(50) DEFAULT NULL,
  `status` enum('pending','shipped','cancelled') DEFAULT 'shipped',
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `unit_of_measure` varchar(20) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `min_stock_level` int(11) DEFAULT 0,
  `max_stock_level` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_code`, `product_name`, `sku`, `description`, `category_id`, `unit_of_measure`, `supplier_id`, `min_stock_level`, `max_stock_level`, `created_at`, `updated_at`) VALUES
(1, 'PRD-001', 'Widget A', 'WDG-A-001', 'Standard widget model A', 1, 'pcs', 1, 0, NULL, '2025-08-01 21:38:18', NULL),
(2, 'PRD-002', 'Widget B', 'WDG-B-002', 'Premium widget model B', 1, 'pcs', 2, 0, NULL, '2025-08-01 21:38:18', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `receiving`
--

CREATE TABLE `receiving` (
  `receiving_id` int(11) NOT NULL,
  `reference_number` varchar(50) DEFAULT NULL,
  `supplier_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `batch_number` varchar(50) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `total_value` decimal(12,2) DEFAULT NULL,
  `received_by` int(11) NOT NULL,
  `received_at` datetime DEFAULT current_timestamp(),
  `status` enum('pending','received','partial','cancelled') DEFAULT 'received',
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL,
  `supplier_name` varchar(100) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `payment_terms` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `supplier_name`, `contact_person`, `phone`, `email`, `address`, `tax_id`, `payment_terms`, `created_at`, `updated_at`) VALUES
(1, 'ACME Supplies', 'John Smith', '555-1234', 'john@acme.com', '123 Main St, Anytown', NULL, NULL, '2025-08-01 21:38:18', NULL),
(2, 'WMS Direct', 'Sarah Johnson', '555-5678', 'sarah@wmsdirect.com', '456 Warehouse Rd, Industrial Park', NULL, NULL, '2025-08-01 21:38:18', NULL),
(3, 'ACME Supplies', 'John Smith', '555-1234', 'john@acme.com', '123 Main St, Anytown', NULL, NULL, '2025-08-01 21:38:35', NULL),
(4, 'WMS Direct', 'Sarah Johnson', '555-5678', 'sarah@wmsdirect.com', '456 Warehouse Rd, Industrial Park', NULL, NULL, '2025-08-01 21:38:35', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `transfers`
--

CREATE TABLE `transfers` (
  `transfer_id` int(11) NOT NULL,
  `reference_number` varchar(50) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `from_location_id` int(11) NOT NULL,
  `to_location_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `transferred_by` int(11) NOT NULL,
  `transferred_at` datetime DEFAULT current_timestamp(),
  `status` enum('pending','completed','cancelled') DEFAULT 'completed',
  `reason` enum('replenishment','consolidation','quality','other') DEFAULT 'replenishment',
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `user_type` enum('admin','staff') NOT NULL,
  `department` enum('inventory','shipping','quality','logistics','admin') NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `phone`, `password_hash`, `user_type`, `department`, `created_at`) VALUES
(1, 'amal', 'amal@gmail.com', '3243124', '$2y$10$mfnjhyQx9qUPwYgOQv1z5ue.hjdUEAtVyZRnNN9Sr1TnPI/ffgY02', 'admin', 'admin', '2025-08-01 21:37:27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

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
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`inventory_id`),
  ADD UNIQUE KEY `product_id` (`product_id`,`location_id`,`batch_number`),
  ADD KEY `location_id` (`location_id`);

--
-- Indexes for table `inventory_adjustments`
--
ALTER TABLE `inventory_adjustments`
  ADD PRIMARY KEY (`adjustment_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `location_id` (`location_id`),
  ADD KEY `adjusted_by` (`adjusted_by`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`location_id`),
  ADD UNIQUE KEY `location_code` (`location_code`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `requested_location_id` (`requested_location_id`),
  ADD KEY `picked_location_id` (`picked_location_id`);

--
-- Indexes for table `outbound`
--
ALTER TABLE `outbound`
  ADD PRIMARY KEY (`outbound_id`),
  ADD UNIQUE KEY `shipment_number` (`shipment_number`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `location_id` (`location_id`),
  ADD KEY `shipped_by` (`shipped_by`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD UNIQUE KEY `product_code` (`product_code`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `receiving`
--
ALTER TABLE `receiving`
  ADD PRIMARY KEY (`receiving_id`),
  ADD UNIQUE KEY `reference_number` (`reference_number`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `location_id` (`location_id`),
  ADD KEY `received_by` (`received_by`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`);

--
-- Indexes for table `transfers`
--
ALTER TABLE `transfers`
  ADD PRIMARY KEY (`transfer_id`),
  ADD UNIQUE KEY `reference_number` (`reference_number`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `from_location_id` (`from_location_id`),
  ADD KEY `to_location_id` (`to_location_id`),
  ADD KEY `transferred_by` (`transferred_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`full_name`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_adjustments`
--
ALTER TABLE `inventory_adjustments`
  MODIFY `adjustment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `outbound`
--
ALTER TABLE `outbound`
  MODIFY `outbound_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `receiving`
--
ALTER TABLE `receiving`
  MODIFY `receiving_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `transfers`
--
ALTER TABLE `transfers`
  MODIFY `transfer_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `inventory_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `locations` (`location_id`);

--
-- Constraints for table `inventory_adjustments`
--
ALTER TABLE `inventory_adjustments`
  ADD CONSTRAINT `inventory_adjustments_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `inventory_adjustments_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `locations` (`location_id`),
  ADD CONSTRAINT `inventory_adjustments_ibfk_3` FOREIGN KEY (`adjusted_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`requested_location_id`) REFERENCES `locations` (`location_id`),
  ADD CONSTRAINT `order_items_ibfk_4` FOREIGN KEY (`picked_location_id`) REFERENCES `locations` (`location_id`);

--
-- Constraints for table `outbound`
--
ALTER TABLE `outbound`
  ADD CONSTRAINT `outbound_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `outbound_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `outbound_ibfk_3` FOREIGN KEY (`location_id`) REFERENCES `locations` (`location_id`),
  ADD CONSTRAINT `outbound_ibfk_4` FOREIGN KEY (`shipped_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`);

--
-- Constraints for table `receiving`
--
ALTER TABLE `receiving`
  ADD CONSTRAINT `receiving_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`),
  ADD CONSTRAINT `receiving_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `receiving_ibfk_3` FOREIGN KEY (`location_id`) REFERENCES `locations` (`location_id`),
  ADD CONSTRAINT `receiving_ibfk_4` FOREIGN KEY (`received_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `transfers`
--
ALTER TABLE `transfers`
  ADD CONSTRAINT `transfers_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `transfers_ibfk_2` FOREIGN KEY (`from_location_id`) REFERENCES `locations` (`location_id`),
  ADD CONSTRAINT `transfers_ibfk_3` FOREIGN KEY (`to_location_id`) REFERENCES `locations` (`location_id`),
  ADD CONSTRAINT `transfers_ibfk_4` FOREIGN KEY (`transferred_by`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
