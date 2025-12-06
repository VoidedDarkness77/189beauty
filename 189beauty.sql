-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 06, 2025 at 04:45 AM
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
-- Database: `189beauty`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `service_type` varchar(100) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `special_requests` text DEFAULT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `service_type`, `full_name`, `email`, `phone`, `booking_date`, `booking_time`, `special_requests`, `status`, `created_at`) VALUES
(1, 1, 'makeup', 'Joshua Roberts', 'joshwilc.23@gmail.com', '5341531', '2025-12-08', '10:00:00', '', 'pending', '2025-12-05 02:15:53'),
(2, 1, 'makeup', 'Joshua Roberts', 'joshwilc.23@gmail.com', '5341531', '2025-12-06', '09:00:00', '', 'pending', '2025-12-05 02:19:17');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(20) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `shipping_address` text NOT NULL,
  `billing_address` text NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `total_amount`, `status`, `shipping_address`, `billing_address`, `payment_method`, `payment_status`, `created_at`, `updated_at`) VALUES
(1, 1, 'ORD-20231201-ABC123', 149.99, 'delivered', '123 Main St, New York, NY 10001', '123 Main St, New York, NY 10001', 'Credit Card', 'paid', '2025-11-29 17:31:10', '2025-11-29 17:31:10'),
(2, 1, 'ORD-20231215-DEF456', 89.50, 'shipped', '123 Main St, New York, NY 10001', '123 Main St, New York, NY 10001', 'PayPal', 'paid', '2025-11-29 17:31:10', '2025-11-29 17:31:10'),
(3, 1, 'ORD-20240105-GHI789', 210.75, 'processing', '123 Main St, New York, NY 10001', '123 Main St, New York, NY 10001', 'Credit Card', 'paid', '2025-11-29 17:31:10', '2025-11-29 17:31:10'),
(4, 4, 'ORD-20251129-692B4EB', 49.99, 'pending', 'Johnny Spot\n732 Narnia\nNew York, 10001\nPhone: 5341531\nEmail: johnnyforlife7@gmail.com', 'Johnny Spot\n732 Narnia\nNew York, 10001\nPhone: 5341531\nEmail: johnnyforlife7@gmail.com', 'credit_card', 'pending', '2025-11-29 19:51:12', '2025-11-29 19:51:12');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `product_price`, `quantity`, `subtotal`) VALUES
(1, 1, 1, 'Luxury Lipstick Set', 49.99, 2, 99.98),
(2, 1, 2, 'Anti-Aging Serum', 50.01, 1, 50.01),
(3, 2, 3, 'Hydrating Face Cream', 89.50, 1, 89.50),
(4, 3, 4, 'Premium Foundation', 75.25, 2, 150.50),
(5, 3, 5, 'Mascara Pro', 30.25, 2, 60.50),
(6, 4, 7, 'Makeup Brushes Set', 49.00, 1, 49.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_tracking`
--

CREATE TABLE `order_tracking` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_tracking`
--

INSERT INTO `order_tracking` (`id`, `order_id`, `status`, `description`, `created_at`) VALUES
(1, 1, 'pending', 'Order placed successfully', '2025-11-29 17:31:10'),
(2, 1, 'processing', 'Order is being processed', '2025-11-29 17:31:10'),
(3, 1, 'shipped', 'Order has been shipped', '2025-11-29 17:31:10'),
(4, 1, 'delivered', 'Order delivered successfully', '2025-11-29 17:31:10'),
(5, 2, 'pending', 'Order placed successfully', '2025-11-29 17:31:10'),
(6, 2, 'processing', 'Order is being processed', '2025-11-29 17:31:10'),
(7, 2, 'shipped', 'Order has been shipped', '2025-11-29 17:31:10'),
(8, 3, 'pending', 'Order placed successfully', '2025-11-29 17:31:10'),
(9, 3, 'processing', 'Order is being processed', '2025-11-29 17:31:10'),
(10, 4, 'pending', 'Order placed successfully', '2025-11-29 19:51:12');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `img` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `cart_data` text DEFAULT NULL,
  `wishlist_data` text DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `email_notifications` tinyint(1) DEFAULT 1,
  `order_updates` tinyint(1) DEFAULT 1,
  `promotional_emails` tinyint(1) DEFAULT 1,
  `notification_updated_at` timestamp NULL DEFAULT NULL,
  `verification_token` varchar(64) DEFAULT NULL,
  `verification_expires` datetime DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `cart_data`, `wishlist_data`, `first_name`, `last_name`, `phone`, `address`, `city`, `state`, `zip_code`, `date_of_birth`, `created_at`, `updated_at`, `email_notifications`, `order_updates`, `promotional_emails`, `notification_updated_at`, `verification_token`, `verification_expires`, `is_verified`, `reset_token`, `reset_expires`) VALUES
(1, 'Josh', 'joshwilc.23@gmail.com', 'Revelation11:18', '[]', '[]', 'Joshua', 'Roberts', '5341531', '732 Narnia', 'Bronx', 'New York', '10001', '1998-12-18', '2025-11-29 20:06:39', '2025-12-05 01:48:42', 1, 1, 1, '2025-12-05 01:27:12', NULL, NULL, 0, NULL, NULL),
(4, 'demo_user', 'johnnyforlife7@gmail.com', '$2y$10$afq.n.2Dz7/I9FAWe.F5Z.37a362Tc1/XNO32lJ4j1KqQLn3oJbfm', '[]', '{\"9\":{\"name\":\"Vitamin C Cream\",\"price\":41.99,\"img\":\"images\\/vitamin-c-cream.jpg\",\"added_at\":\"2025-11-29 18:02:35\"}}', 'Johnny', 'Spot', '5341531', '732 Narnia', 'Bronx', 'New York', '10001', '2025-12-18', '2025-11-29 20:06:39', '2025-11-29 20:12:53', 1, 1, 1, NULL, NULL, NULL, 0, NULL, NULL),
(7, 'Johnny', 'joshuar5748@tamcc.edu.gd', '$2y$10$o4wkZdgobczhcs3X5l8X5O6ZhHMfRpgdGWIUS71y5gL9OamzrfYny', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-05 10:50:29', '2025-12-05 10:50:29', 1, 1, 1, NULL, 'd61da5a91ae67079d9d2a45e49dd2fa2927c315245e88fbcca2725476299b764', '2025-12-06 11:50:29', 0, NULL, NULL),
(9, 'John', 'joshwilc.23@hotmail.com', '$2y$10$Z5ZrGekKqPlv.PKHbMLDf.kRfjZnZ9mJj3wSwzzkDh2jBNlvoqB9q', '[]', '[]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-05 11:27:11', '2025-12-05 12:39:23', 1, 1, 1, NULL, '86ce2a44293d17c7d503aead65301a525b7af8730a2016a61e743b30f9ac9411', '2025-12-06 12:27:11', 0, NULL, NULL),
(10, 'Jai', 'jaiwater@gmail.com', '$2y$10$pdpKuHyexMJtyolenzER0OPn8rOM0xHGtnh9ZpShDnXq/T0LplzXm', '[]', '[]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-05 13:19:48', '2025-12-05 13:21:05', 1, 1, 1, NULL, 'f87b4e552c094a2e5c022c1443efd34a04baac09c220917cd0ebe4e20054910e', '2025-12-06 14:19:48', 0, NULL, NULL),
(11, 'Kaeden', 'kaedengeorge1324@gmail.com', '$2y$10$VbRTEAHoSrw88S63qzNr/utpvKnokBtMJqSpj.8GyQGsvNr/i0gLC', '[]', '[]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-05 13:45:40', '2025-12-05 13:51:02', 1, 1, 1, NULL, '0042eaf0808a6a085c3d5925a09ba50bc5e65f066222b657cfd1f5550cacbae7', '2025-12-06 14:45:40', 0, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `order_tracking`
--
ALTER TABLE `order_tracking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `order_tracking`
--
ALTER TABLE `order_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_tracking`
--
ALTER TABLE `order_tracking`
  ADD CONSTRAINT `order_tracking_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
