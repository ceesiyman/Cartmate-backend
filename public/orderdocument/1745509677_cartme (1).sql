-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 23, 2025 at 07:15 PM
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
-- Database: `cartme`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(42, 2, 18, 20, '2025-04-20 07:16:16', '2025-04-22 18:14:52'),
(43, 2, 19, 1, '2025-04-20 07:16:16', '2025-04-20 07:16:16'),
(44, 2, 20, 2, '2025-04-20 07:16:16', '2025-04-20 07:16:16'),
(45, 2, 21, 4, '2025-04-20 07:16:16', '2025-04-20 07:16:16'),
(46, 2, 22, 3, '2025-04-20 07:16:16', '2025-04-20 07:16:16'),
(47, 2, 23, 2, '2025-04-20 07:16:16', '2025-04-20 07:16:16'),
(48, 2, 24, 1, '2025-04-20 07:16:16', '2025-04-20 07:16:16'),
(49, 2, 25, 3, '2025-04-20 07:16:16', '2025-04-20 07:16:16'),
(50, 2, 26, 2, '2025-04-20 07:16:16', '2025-04-20 07:16:16'),
(72, 1, 33, 10, '2025-04-22 12:58:33', '2025-04-22 12:58:46'),
(75, 1, 32, 4, '2025-04-22 13:27:42', '2025-04-22 13:32:33');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_04_18_055915_create_products_table', 1),
(5, '2019_12_14_000001_create_personal_access_tokens_table', 2);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `order_number` varchar(255) NOT NULL,
  `status` enum('pending','processing','completed','cancelled') DEFAULT 'pending',
  `total_amount` decimal(10,2) NOT NULL,
  `shipping_address` text DEFAULT NULL,
  `billing_address` text DEFAULT NULL,
  `phone_number` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `status`, `total_amount`, `shipping_address`, `billing_address`, `phone_number`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 'ORD-1YIGPXXJII', 'cancelled', 0.00, '123 Main St, City, Country', '123 Main St, City, Country', '+1234567890', 'Please deliver in the morning', '2025-04-20 05:00:11', '2025-04-20 05:13:17'),
(2, 1, 'ORD-D5I9MDNUNV', 'processing', 5772.58, '123 Main St, City, Country', '123 Main St, City, Country', '+1234567890', 'Please deliver in the morning', '2025-04-20 05:06:31', '2025-04-20 05:15:19'),
(3, 8, 'ORD-5SQAI0UXTH', 'cancelled', 92.48, 'Kilimahewa', 'dar es salaam', '+255744330332', 'make it clear', '2025-04-22 18:31:41', '2025-04-22 18:38:14');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `created_at`, `updated_at`) VALUES
(1, 2, 7, 2, 115.00, '2025-04-20 05:06:31', '2025-04-20 05:06:31'),
(2, 2, 8, 1, 220.00, '2025-04-20 05:06:31', '2025-04-20 05:06:31'),
(3, 2, 9, 4, 170.50, '2025-04-20 05:06:31', '2025-04-20 05:06:31'),
(4, 2, 10, 2, 88.50, '2025-04-20 05:06:31', '2025-04-20 05:06:31'),
(5, 2, 11, 1, 134.00, '2025-04-20 05:06:31', '2025-04-20 05:06:31'),
(6, 2, 12, 3, 329.00, '2025-04-20 05:06:31', '2025-04-20 05:06:31'),
(7, 2, 13, 1, 102.50, '2025-04-20 05:06:31', '2025-04-20 05:06:31'),
(8, 2, 14, 2, 247.80, '2025-04-20 05:06:31', '2025-04-20 05:06:31'),
(9, 2, 15, 5, 360.00, '2025-04-20 05:06:31', '2025-04-20 05:06:31'),
(10, 2, 16, 2, 441.00, '2025-04-20 05:06:31', '2025-04-20 05:06:31'),
(11, 2, 5, 1, 62.48, '2025-04-20 05:06:31', '2025-04-20 05:06:31'),
(12, 3, 34, 1, 92.48, '2025-04-22 18:31:41', '2025-04-22 18:31:41');

-- --------------------------------------------------------

--
-- Table structure for table `otps`
--

CREATE TABLE `otps` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `otp` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `otps`
--

INSERT INTO `otps` (`id`, `user_id`, `otp`, `type`, `email`, `expires_at`, `used_at`, `created_at`, `updated_at`) VALUES
(1, 1, '899632', 'email_verification', 'john@example.com', '2025-04-18 14:52:29', NULL, '2025-04-18 14:42:29', '2025-04-18 14:42:29'),
(6, 6, '617658', 'verification', 'bomboclat7522@gmail.com', '2025-04-19 02:34:18', '2025-04-18 23:34:18', '2025-04-18 23:33:54', '2025-04-18 23:34:18'),
(7, 1, '078993', 'password_reset', 'john@example.com', '2025-04-18 23:47:20', NULL, '2025-04-18 23:37:20', '2025-04-18 23:37:20'),
(8, 6, '892210', 'password_reset', 'bomboclat7522@gmail.com', '2025-04-19 02:39:56', '2025-04-18 23:39:56', '2025-04-18 23:37:47', '2025-04-18 23:39:56'),
(9, 6, '053395', 'password_reset', 'bomboclat7522@gmail.com', '2025-04-19 02:42:03', '2025-04-18 23:42:03', '2025-04-18 23:39:56', '2025-04-18 23:42:03'),
(10, 6, '868745', 'password_reset', 'bomboclat7522@gmail.com', '2025-04-19 02:45:35', '2025-04-18 23:45:35', '2025-04-18 23:42:03', '2025-04-18 23:45:35'),
(11, 7, '281576', 'verification', 'chambuso@gmail.com', '2025-04-20 08:21:44', NULL, '2025-04-20 08:11:44', '2025-04-20 08:11:44'),
(12, 8, '815665', 'verification', 'ibrakombo@gmail.com', '2025-04-20 08:33:32', NULL, '2025-04-20 08:23:32', '2025-04-20 08:23:32'),
(13, 9, '725591', 'verification', 'ganja@gmail.com', '2025-04-20 08:35:53', NULL, '2025-04-20 08:25:53', '2025-04-20 08:25:53'),
(14, 10, '106667', 'verification', 'ibrakombo123@gmail.com', '2025-04-20 08:38:10', NULL, '2025-04-20 08:28:10', '2025-04-20 08:28:10'),
(15, 11, '808271', 'verification', 'ibrakombo456@gmail.com', '2025-04-20 08:38:36', NULL, '2025-04-20 08:28:36', '2025-04-20 08:28:36'),
(16, 12, '094850', 'verification', 'ibrakombo90@gmail.com', '2025-04-20 08:42:52', NULL, '2025-04-20 08:32:52', '2025-04-20 08:32:52'),
(17, 13, '156205', 'verification', 'ibrakombo901@gmail.com', '2025-04-20 08:53:15', NULL, '2025-04-20 08:43:15', '2025-04-20 08:43:15'),
(18, 14, '367161', 'verification', 'ibrakombo321@gmail.com', '2025-04-20 11:48:22', '2025-04-20 08:48:22', '2025-04-20 08:47:37', '2025-04-20 08:48:22'),
(19, 15, '762301', 'verification', 'ibrakombo00@gmail.com', '2025-04-20 12:02:25', '2025-04-20 09:02:25', '2025-04-20 08:52:50', '2025-04-20 09:02:25'),
(20, 15, '103476', 'verification', 'ibrakombo00@gmail.com', '2025-04-20 12:03:20', '2025-04-20 09:03:20', '2025-04-20 09:02:25', '2025-04-20 09:03:20'),
(21, 15, '636218', 'verification', 'ibrakombo00@gmail.com', '2025-04-20 12:05:19', '2025-04-20 09:05:19', '2025-04-20 09:03:41', '2025-04-20 09:05:19'),
(22, 15, '265039', 'verification', 'ibrakombo00@gmail.com', '2025-04-20 12:08:16', '2025-04-20 09:08:16', '2025-04-20 09:07:45', '2025-04-20 09:08:16'),
(23, 15, '288196', 'verification', 'ibrakombo00@gmail.com', '2025-04-20 12:10:26', '2025-04-20 09:10:26', '2025-04-20 09:10:11', '2025-04-20 09:10:26'),
(24, 16, '050479', 'verification', 'mwambino@gmail.com', '2025-04-22 09:01:03', NULL, '2025-04-22 08:51:03', '2025-04-22 08:51:03'),
(25, 17, '304111', 'verification', 'diola@gmail.com', '2025-04-22 09:14:52', NULL, '2025-04-22 09:04:52', '2025-04-22 09:04:52'),
(26, 18, '984866', 'verification', 'dinho@gmail.com', '2025-04-22 12:07:59', '2025-04-22 09:07:59', '2025-04-22 09:07:39', '2025-04-22 09:07:59'),
(27, 18, '051725', 'verification', 'dinho@gmail.com', '2025-04-22 09:17:59', NULL, '2025-04-22 09:07:59', '2025-04-22 09:07:59');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 6, 'auth_token', '19674471d49a5f2aaa7a9d241c8eebe0a97708ef41a016eb55d4d932284646be', '[\"*\"]', NULL, NULL, '2025-04-18 23:36:25', '2025-04-18 23:36:25'),
(4, 'App\\Models\\User', 17, 'auth_token', '6119d1c9c3dc3f064ce2b07a62f4123e7dcfeafeb549aea06363e8a9f175cd59', '[\"*\"]', '2025-04-22 09:14:30', NULL, '2025-04-22 09:13:30', '2025-04-22 09:14:30'),
(7, 'App\\Models\\User', 8, 'auth_token', '7b1e72ff1af53eb7e3fb66e6698160a78b9f236d78f03eaf2c3a7a1b6b698791', '[\"*\"]', '2025-04-23 13:54:43', NULL, '2025-04-23 12:59:33', '2025-04-23 13:54:43');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `original_price` decimal(10,2) NOT NULL,
  `discounted_price` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `store` varchar(255) NOT NULL,
  `original_url` varchar(255) NOT NULL,
  `shipping` decimal(10,2) DEFAULT 0.00,
  `customs` decimal(10,2) DEFAULT 0.00,
  `service_fee` decimal(10,2) DEFAULT 0.00,
  `vat` decimal(10,2) DEFAULT 0.00,
  `total_price` decimal(10,2) NOT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `similar_products` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`similar_products`)),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `specifications` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`specifications`)),
  `brand` varchar(255) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `rating` float DEFAULT NULL,
  `review_count` int(11) DEFAULT 0,
  `in_stock` tinyint(1) DEFAULT 1,
  `sku` varchar(255) DEFAULT NULL,
  `additional_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`additional_info`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `original_price`, `discounted_price`, `image`, `description`, `store`, `original_url`, `shipping`, `customs`, `service_fee`, `vat`, `total_price`, `images`, `similar_products`, `created_at`, `updated_at`, `features`, `specifications`, `brand`, `category`, `rating`, `review_count`, `in_stock`, `sku`, `additional_info`) VALUES
(5, 'Kitchen Shears Set - QtoiKce Kitchen Scissors 3 Pack All Purpose Poultry Shears,Stainless Steel Sharp Utility Cooking Scissors for Home', 4.99, NULL, 'https://m.media-amazon.com/images/I/51q7FQRC2HL.jpg', 'About this item 【KITCHEN SCISSORS SET】-Kitchen scissors have multiple uses at home and are perfect for daily tasks - they can be used as nutcrackers, cutting stickers, food packaging bags, unboxing, as well as cutting ingredients such as chicken, fish, vegetables, nuts, etc. 【SCISSORS SHARP BLADES】-The utility scissors are made of stainless steel blades, which is sharp. IThey can easily complete tasks related to daily cutting and can be used as household scissors, craft scissors, and fabric scissors. They are essential hand cutting tools for offices and homes. 【KITCHEN SCISSORS 3 PACK】-The package includes three different specifications of kitchen scissors. Each pair of kitchen scissors has a unique function. The utility scissors set can meet the basic needs of the kitchen, making the cutting task in the kitchen more convenient. 【USE EASILY】-The handle of kitchen scissors set are adopts ergonomic design, which is non-slip and comfortable to grip, effectively reducing finger fatigue and hand fatigue. Sharp and utility scissors can complete cutting tasks faster and more accurately, becoming a powerful assistant in your life. 【HOME ESSENTIALS】-The utility scissors is the ideal home assistant. The utility scissors set will make our kitchen cooking and daily use more convenient.Additionally, the scissors can be used as a gift for friends who enjoy cooking.(Thanksgiving, Christmas, Father\'s Day, Mother\'s Day, Halloween, weekend party, birthday, etc)  See more product details', 'Amazon', 'https://www.amazon.com/dp/B0CLCZD6QQ?ref=cm_sw_r_cp_ud_dp_9G2PMVNRANYCH6D9DGAV&ref_=cm_sw_r_cp_ud_dp_9G2PMVNRANYCH6D9DGAV&social_share=cm_sw_r_cp_ud_dp_9G2PMVNRANYCH6D9DGAV&previewDoh=1', 25.00, 18.50, 12.99, 1.00, 62.48, '[\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/41NA67WRk4L.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/41ob4B45k3L.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/41GWEKVMS4L.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/41Am1qsUaAL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/515CKssb7VL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51Kg7TBUeQL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/A1PR2gJPKLL.SS125_PKplay-button-mb-image-grid-small_.png\",\"https:\\/\\/images-na.ssl-images-amazon.com\\/images\\/G\\/01\\/x-locale\\/common\\/transparent-pixel.gif\"]', '[]', '2025-04-19 00:10:10', '2025-04-19 00:10:10', NULL, NULL, NULL, NULL, NULL, 0, 1, NULL, NULL),
(7, 'Product 1', 100.00, 90.00, 'image1.jpg', 'Description 1', 'Amazon', 'https://example.com/1', 10.00, 5.00, 2.00, 8.00, 115.00, '[\"image1a.jpg\",\"image1b.jpg\"]', '[\"Product 2\",\"Product 3\"]', '2025-04-20 07:06:39', '2025-04-20 07:06:39', NULL, NULL, NULL, NULL, NULL, 0, 1, NULL, NULL),
(8, 'Product 2', 200.00, 180.00, 'image2.jpg', 'Description 2', 'eBay', 'https://example.com/2', 15.00, 10.00, 3.00, 12.00, 220.00, '[\"image2a.jpg\",\"image2b.jpg\"]', '[\"Product 1\",\"Product 4\"]', '2025-04-20 07:06:39', '2025-04-20 07:06:39', NULL, NULL, NULL, NULL, NULL, 0, 1, NULL, NULL),
(9, 'Product 3', 150.00, 140.00, 'image3.jpg', 'Description 3', 'AliExpress', 'https://example.com/3', 12.00, 6.00, 2.50, 10.00, 170.50, '[\"image3a.jpg\",\"image3b.jpg\"]', '[\"Product 2\",\"Product 5\"]', '2025-04-20 07:06:39', '2025-04-20 07:06:39', NULL, NULL, NULL, NULL, NULL, 0, 1, NULL, NULL),
(10, 'Product 4', 80.00, 70.00, 'image4.jpg', 'Description 4', 'Walmart', 'https://example.com/4', 8.00, 3.00, 1.50, 6.00, 88.50, '[\"image4a.jpg\",\"image4b.jpg\"]', '[\"Product 3\",\"Product 6\"]', '2025-04-20 07:06:39', '2025-04-20 07:06:39', NULL, NULL, NULL, NULL, NULL, 0, 1, NULL, NULL),
(11, 'Product 5', 120.00, 110.00, 'image5.jpg', 'Description 5', 'Target', 'https://example.com/5', 9.00, 4.00, 2.00, 9.00, 134.00, '[\"image5a.jpg\",\"image5b.jpg\"]', '[\"Product 1\",\"Product 6\"]', '2025-04-20 07:06:39', '2025-04-20 07:06:39', NULL, NULL, NULL, NULL, NULL, 0, 1, NULL, NULL),
(12, 'Product 6', 300.00, 280.00, 'image6.jpg', 'Description 6', 'BestBuy', 'https://example.com/6', 20.00, 10.00, 4.00, 15.00, 329.00, '[\"image6a.jpg\",\"image6b.jpg\"]', '[\"Product 5\",\"Product 7\"]', '2025-04-20 07:06:39', '2025-04-20 07:06:39', NULL, NULL, NULL, NULL, NULL, 0, 1, NULL, NULL),
(13, 'Product 7', 95.00, 85.00, 'image7.jpg', 'Description 7', 'Amazon', 'https://example.com/7', 7.00, 3.00, 1.50, 6.00, 102.50, '[\"image7a.jpg\",\"image7b.jpg\"]', '[\"Product 6\",\"Product 8\"]', '2025-04-20 07:06:39', '2025-04-20 07:06:39', NULL, NULL, NULL, NULL, NULL, 0, 1, NULL, NULL),
(14, 'Product 8', 220.00, 210.00, 'image8.jpg', 'Description 8', 'eBay', 'https://example.com/8', 14.00, 8.00, 2.80, 13.00, 247.80, '[\"image8a.jpg\",\"image8b.jpg\"]', '[\"Product 7\",\"Product 9\"]', '2025-04-20 07:06:39', '2025-04-20 07:06:39', NULL, NULL, NULL, NULL, NULL, 0, 1, NULL, NULL),
(15, 'Product 9', 310.00, 300.00, 'image9.jpg', 'Description 9', 'AliExpress', 'https://example.com/9', 25.00, 12.00, 5.00, 18.00, 360.00, '[\"image9a.jpg\",\"image9b.jpg\"]', '[\"Product 8\",\"Product 10\"]', '2025-04-20 07:06:39', '2025-04-20 07:06:39', NULL, NULL, NULL, NULL, NULL, 0, 1, NULL, NULL),
(16, 'Product 10', 400.00, 370.00, 'image10.jpg', 'Description 10', 'Walmart', 'https://example.com/10', 30.00, 15.00, 6.00, 20.00, 441.00, '[\"image10a.jpg\",\"image10b.jpg\"]', '[\"Product 9\",\"Product 11\"]', '2025-04-20 07:06:39', '2025-04-20 07:06:39', NULL, NULL, NULL, NULL, NULL, 0, 1, NULL, NULL),
(17, 'Product 11', 180.00, 170.00, 'image11.jpg', 'Description 11', 'Target', 'https://example.com/11', 10.00, 5.00, 2.50, 11.00, 198.50, '[\"image11a.jpg\",\"image11b.jpg\"]', '[\"Product 10\",\"Product 12\"]', '2025-04-20 07:06:39', '2025-04-20 07:06:39', NULL, NULL, NULL, NULL, NULL, 0, 1, NULL, NULL),
(18, 'Product 12', 250.00, 230.00, 'image12.jpg', 'Description 12', 'BestBuy', 'https://example.com/12', 18.00, 9.00, 3.00, 14.00, 274.00, '[\"image12a.jpg\",\"image12b.jpg\"]', '[\"Product 11\",\"Product 13\"]', '2025-04-20 07:06:39', '2025-04-20 07:06:39', NULL, NULL, NULL, NULL, NULL, 0, 1, NULL, NULL),
(19, 'Product 13', 60.00, 55.00, 'image13.jpg', 'Description 13', 'Amazon', 'https://example.com/13', 6.00, 2.00, 1.00, 4.00, 68.00, '[\"image13a.jpg\",\"image13b.jpg\"]', '[\"Product 12\",\"Product 14\"]', '2025-04-20 07:06:39', '2025-04-20 07:06:39', NULL, NULL, NULL, NULL, NULL, 0, 1, NULL, NULL),
(20, 'Product 14', 320.00, 310.00, 'image14.jpg', 'Description 14', 'eBay', 'https://example.com/14', 22.00, 11.00, 3.50, 16.00, 362.50, '[\"image14a.jpg\",\"image14b.jpg\"]', '[\"Product 13\",\"Product 15\"]', '2025-04-20 07:06:39', '2025-04-20 07:06:39', NULL, NULL, NULL, NULL, NULL, 0, 1, NULL, NULL),
(21, 'Product 15', 280.00, 260.00, 'image15.jpg', 'Description 15', 'AliExpress', 'https://example.com/15', 16.00, 8.00, 3.00, 12.00, 299.00, '[\"image15a.jpg\",\"image15b.jpg\"]', '[\"Product 14\",\"Product 16\"]', '2025-04-20 07:06:39', '2025-04-20 07:06:39', NULL, NULL, NULL, NULL, NULL, 0, 1, NULL, NULL),
(22, 'Product 16', 130.00, 120.00, 'image16.jpg', 'Description 16', 'Walmart', 'https://example.com/16', 9.00, 4.00, 2.00, 7.00, 142.00, '[\"image16a.jpg\",\"image16b.jpg\"]', '[\"Product 15\",\"Product 17\"]', '2025-04-20 07:06:39', '2025-04-20 07:06:39', NULL, NULL, NULL, NULL, NULL, 0, 1, NULL, NULL),
(23, 'Product 17', 110.00, 100.00, 'image17.jpg', 'Description 17', 'Target', 'https://example.com/17', 7.00, 3.00, 1.80, 6.00, 117.80, '[\"image17a.jpg\",\"image17b.jpg\"]', '[\"Product 16\",\"Product 18\"]', '2025-04-20 07:06:39', '2025-04-20 07:06:39', NULL, NULL, NULL, NULL, NULL, 0, 1, NULL, NULL),
(24, 'Product 18', 90.00, 85.00, 'image18.jpg', 'Description 18', 'BestBuy', 'https://example.com/18', 6.00, 2.00, 1.50, 5.00, 100.50, '[\"image18a.jpg\",\"image18b.jpg\"]', '[\"Product 17\",\"Product 19\"]', '2025-04-20 07:06:39', '2025-04-20 07:06:39', NULL, NULL, NULL, NULL, NULL, 0, 1, NULL, NULL),
(25, 'Product 19', 75.00, 70.00, 'image19.jpg', 'Description 19', 'Amazon', 'https://example.com/19', 5.00, 2.00, 1.00, 4.00, 82.00, '[\"image19a.jpg\",\"image19b.jpg\"]', '[\"Product 18\",\"Product 20\"]', '2025-04-20 07:06:39', '2025-04-20 07:06:39', NULL, NULL, NULL, NULL, NULL, 0, 1, NULL, NULL),
(26, 'Product 20', 350.00, 340.00, 'image20.jpg', 'Description 20', 'eBay', 'https://example.com/20', 20.00, 10.00, 4.00, 17.00, 391.00, '[\"image20a.jpg\",\"image20b.jpg\"]', '[\"Product 19\",\"Product 1\"]', '2025-04-20 07:06:39', '2025-04-20 07:06:39', NULL, NULL, NULL, NULL, NULL, 0, 1, NULL, NULL),
(27, 'Acer Nitro KG241Y Sbiip 23.8” Full HD (1920 x 1080) VA Gaming Monitor | AMD FreeSync Premium Technology | 165Hz Refresh Rate | 1ms (VRB) | ZeroFrame Design | 1 x Display Port 1.2 & 2 x HDMI 2.0,Black', 119.99, NULL, 'https://m.media-amazon.com/images/I/71yo3bmyBnL.jpg', 'In competitive gaming, every frame matters. Introducing Acer\'s KG241Y gaming monitor - the Full HD (1920 x 1080) resolution monitor that can keep up with your game play. Through AMD FreeSync Premium technology, the game’s frame rate is determined by your graphics card, not the fixed refresh rate of the monitor, giving you a serious competitive edge. Plus, users can enjoy comfortable viewing experience while gaming via flicker-less, low dimming and ComfyView display. The design saves space on your desk and lets you place multiple monitors side by side to build a seamless big-screen display. (UM.QX1AA.S01).', 'Amazon', 'https://www.amazon.com/dp/B0B6DFG1FQ?ref=cm_sw_r_cp_ud_dp_M8TS4YWHQ37A4ES7JJ22&ref_=cm_sw_r_cp_ud_dp_M8TS4YWHQ37A4ES7JJ22&social_share=cm_sw_r_cp_ud_dp_M8TS4YWHQ37A4ES7JJ22&previewDoh=1', 25.00, 18.50, 12.99, 24.00, 200.48, '[\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/41t-fpXCUUL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51C6Rg4EGAL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51h1eIxoIIL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51QB2HqClAL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/61pSvcqjZuL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/41pWBW7N+GL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/61U1R4CnNKL.SS40_BG85,85,85_BR-120_PKdp-play-icon-overlay__.jpg\",\"https:\\/\\/images-na.ssl-images-amazon.com\\/images\\/G\\/01\\/x-locale\\/common\\/transparent-pixel.gif\"]', '[]', '2025-04-22 09:42:54', '2025-04-22 09:42:54', NULL, NULL, NULL, NULL, NULL, 0, 1, NULL, NULL),
(28, 'OLANLY Bathroom Rugs 30x20, Extra Soft Absorbent Chenille Bath Rugs, Rubber Backing Quick Dry, Machine Washable Bath Mats for Bathroom Floor, Tub and Shower, Home Decor Accessories, Grey', 9.97, NULL, 'https://m.media-amazon.com/images/I/91MTW21x7pL.jpg', 'About this item Soft and Plush Chenille - Wrap your feet in the softest, coziest chenille, creating a warm haven for you, your family, and even your furry friends. The plush pile not only pampers tired feet but also keeps toes delightfully toasty. Ultra Absorbent - Meet OLANLY\'s absorbent bath rug with dense chenille, keeping floors clean when you step out of the bath or shower. Instantly dry your feet on this shaggy rug, protecting your floor from water damage. Fade-resistant and Quick-Drying - The premium microfiber fabric in our rugs isn\'t just ultra-absorbent—it also dries quickly. These bathroom mats are a breeze to clean, and their fade-resistant quality ensures that you can machine wash and dry them as often as needed. Long-lasting and Sturdy - Our bath mat features a textured rubber backing for stability, surpassing the durability of other brands using PVC or hot glue. Unlike traditional materials, our TP Rubber Backing is engineered to withstand repeated washes without compromising its integrity. WARNING: Water under the bathroom rug can cause it to slip. Keep bottom of the bath rug dry! Multi-Purpose Comfort - Elevate your bathroom experience with OLANLY\'s soft and luxurious Chenille Bath Rug. Versatile enough for use in the bath, in front of the sink, or anywhere in your home where you desire toe-pampering comfort. Available in a variety of colors and sizes to complement your bathroom design. A perfect addition to your bathroom, vanities, holiday homes, master bathrooms, children\'s bathrooms, and guest suites.  See more product details', 'Amazon', 'https://www.amazon.com/dp/B0CFGYFCYL?ref=cm_sw_r_cp_ud_dp_K2EK15G1ZWP3R0JG2SZC_1&ref_=cm_sw_r_cp_ud_dp_K2EK15G1ZWP3R0JG2SZC_1&social_share=cm_sw_r_cp_ud_dp_K2EK15G1ZWP3R0JG2SZC_1&previewDoh=1', 25.00, 18.50, 12.99, 1.99, 68.45, '[\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51kr9wGrijL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51kJkh8QQmL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51h+9wn6CSL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51Soqq7rL2L.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51SuyCQ8JeL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/515D3RnVznL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/71xne4kTLuL.SS125_PKplay-button-mb-image-grid-small_.jpg\",\"https:\\/\\/images-na.ssl-images-amazon.com\\/images\\/G\\/01\\/x-locale\\/common\\/transparent-pixel.gif\"]', '[]', '2025-04-22 10:43:03', '2025-04-22 10:43:03', NULL, NULL, NULL, NULL, NULL, 0, 1, NULL, NULL),
(29, 'OLANLY Bathroom Rugs 30x20, Extra Soft Absorbent Chenille Bath Rugs, Rubber Backing Quick Dry, Machine Washable Bath Mats for Bathroom Floor, Tub and Shower, Home Decor Accessories, Grey', 9.97, NULL, 'https://m.media-amazon.com/images/I/91MTW21x7pL.jpg', 'About this item Soft and Plush Chenille - Wrap your feet in the softest, coziest chenille, creating a warm haven for you, your family, and even your furry friends. The plush pile not only pampers tired feet but also keeps toes delightfully toasty. Ultra Absorbent - Meet OLANLY\'s absorbent bath rug with dense chenille, keeping floors clean when you step out of the bath or shower. Instantly dry your feet on this shaggy rug, protecting your floor from water damage. Fade-resistant and Quick-Drying - The premium microfiber fabric in our rugs isn\'t just ultra-absorbent—it also dries quickly. These bathroom mats are a breeze to clean, and their fade-resistant quality ensures that you can machine wash and dry them as often as needed. Long-lasting and Sturdy - Our bath mat features a textured rubber backing for stability, surpassing the durability of other brands using PVC or hot glue. Unlike traditional materials, our TP Rubber Backing is engineered to withstand repeated washes without compromising its integrity. WARNING: Water under the bathroom rug can cause it to slip. Keep bottom of the bath rug dry! Multi-Purpose Comfort - Elevate your bathroom experience with OLANLY\'s soft and luxurious Chenille Bath Rug. Versatile enough for use in the bath, in front of the sink, or anywhere in your home where you desire toe-pampering comfort. Available in a variety of colors and sizes to complement your bathroom design. A perfect addition to your bathroom, vanities, holiday homes, master bathrooms, children\'s bathrooms, and guest suites.  See more product details', 'Amazon', 'https://www.amazon.com/dp/B0CFGYFCYL?ref=cm_sw_r_cp_ud_dp_K2EK15G1ZWP3R0JG2SZC_2&ref_=cm_sw_r_cp_ud_dp_K2EK15G1ZWP3R0JG2SZC_2&social_share=cm_sw_r_cp_ud_dp_K2EK15G1ZWP3R0JG2SZC_2&previewDoh=1', 25.00, 18.50, 12.99, 1.99, 68.45, '[\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51kr9wGrijL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51kJkh8QQmL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51h+9wn6CSL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51Soqq7rL2L.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51SuyCQ8JeL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/515D3RnVznL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/71xne4kTLuL.SS125_PKplay-button-mb-image-grid-small_.jpg\",\"https:\\/\\/images-na.ssl-images-amazon.com\\/images\\/G\\/01\\/x-locale\\/common\\/transparent-pixel.gif\"]', '[]', '2025-04-22 10:55:47', '2025-04-22 10:55:47', NULL, NULL, NULL, NULL, NULL, 0, 1, NULL, NULL),
(30, 'OLANLY Bathroom Rugs 30x20, Extra Soft Absorbent Chenille Bath Rugs, Rubber Backing Quick Dry, Machine Washable Bath Mats for Bathroom Floor, Tub and Shower, Home Decor Accessories, Grey', 9.97, NULL, 'https://m.media-amazon.com/images/I/91MTW21x7pL.jpg', 'About this item Soft and Plush Chenille - Wrap your feet in the softest, coziest chenille, creating a warm haven for you, your family, and even your furry friends. The plush pile not only pampers tired feet but also keeps toes delightfully toasty. Ultra Absorbent - Meet OLANLY\'s absorbent bath rug with dense chenille, keeping floors clean when you step out of the bath or shower. Instantly dry your feet on this shaggy rug, protecting your floor from water damage. Fade-resistant and Quick-Drying - The premium microfiber fabric in our rugs isn\'t just ultra-absorbent—it also dries quickly. These bathroom mats are a breeze to clean, and their fade-resistant quality ensures that you can machine wash and dry them as often as needed. Long-lasting and Sturdy - Our bath mat features a textured rubber backing for stability, surpassing the durability of other brands using PVC or hot glue. Unlike traditional materials, our TP Rubber Backing is engineered to withstand repeated washes without compromising its integrity. WARNING: Water under the bathroom rug can cause it to slip. Keep bottom of the bath rug dry! Multi-Purpose Comfort - Elevate your bathroom experience with OLANLY\'s soft and luxurious Chenille Bath Rug. Versatile enough for use in the bath, in front of the sink, or anywhere in your home where you desire toe-pampering comfort. Available in a variety of colors and sizes to complement your bathroom design. A perfect addition to your bathroom, vanities, holiday homes, master bathrooms, children\'s bathrooms, and guest suites.  See more product details', 'Amazon', 'https://www.amazon.com/dp/B0CFGYFCYL?ref=cm_sw_r_cp_ud_dp_K2EK15G1ZWP3R0JG2SZC_3&ref_=cm_sw_r_cp_ud_dp_K2EK15G1ZWP3R0JG2SZC_3&social_share=cm_sw_r_cp_ud_dp_K2EK15G1ZWP3R0JG2SZC_3&previewDoh=1', 25.00, 18.50, 12.99, 1.99, 68.45, '[\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51kr9wGrijL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51kJkh8QQmL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51h+9wn6CSL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51Soqq7rL2L.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51SuyCQ8JeL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/515D3RnVznL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/71xne4kTLuL.SS125_PKplay-button-mb-image-grid-small_.jpg\",\"https:\\/\\/images-na.ssl-images-amazon.com\\/images\\/G\\/01\\/x-locale\\/common\\/transparent-pixel.gif\"]', '[]', '2025-04-22 11:02:42', '2025-04-22 11:02:42', NULL, NULL, NULL, NULL, NULL, 0, 1, NULL, NULL),
(31, 'Redragon Mechanical Gaming Keyboard Wired, 11 Programmable Backlit Modes, Hot-Swappable Red Switch, Anti-Ghosting, Double-Shot PBT Keycaps, Light Up Keyboard for PC Mac', 29.99, NULL, 'https://m.media-amazon.com/images/I/71Bk2A2WmOL.jpg', 'About this item Brilliant Color Illumination- With 11 unique backlights, choose the perfect ambiance for any mood. Adjust light speed and brightness among 5 levels for a comfortable environment, day or night. The double injection ABS keycaps ensure clear backlight and precise typing. From late-night tasks to immersive gaming, our mechanical keyboard enhances every experience Support Macro Editing: The K671 Mechanical Gaming Keyboard can be macro editing, you can remap the keys function, set shortcuts, or combine multiple key functions in one key to get more efficient work and gaming. The LED Backlit Effects also can be adjusted by the software（note: the color can not be changed） Hot-swappable Linear Red Switch- Our K671 gaming keyboard features red switch, which requires less force to press down and the keys feel smoother and easier to use. It\'s best for rpgs and mmo, imo games. You will get 4 spare switches and two red keycaps as a gift to exchange the key switch when it does not work. Full keys Anti-ghosting- All keys can work simultaneously, easily complete any combining functions without conflicting keys. 12 multimedia key shortcuts allow you to quickly access to calculator/media/volume control/email Professional After-Sales Service- We provide every Redragon customer with 24-Month Warranty , Please feel free to contact us when you meet any problem. We will spare no effort to provide the best service to every customer within 24 hours to help you  See more product details', 'Amazon', 'https://www.amazon.com/dp/B0CF3VGQFL?ref=cm_sw_r_cp_ud_dp_8AKFMJ7Z9G7SDZ3GAZDE&ref_=cm_sw_r_cp_ud_dp_8AKFMJ7Z9G7SDZ3GAZDE&social_share=cm_sw_r_cp_ud_dp_8AKFMJ7Z9G7SDZ3GAZDE&previewDoh=1', 25.00, 18.50, 12.99, 6.00, 92.48, '[\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/41khzfsV4mL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51yJgZBQ7ZL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51rpmLxwDyL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51S6-9VG-tL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51gY2ZKhDfL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51uYAu7fVRL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51m2tgNi5-L.SS40_BG85,85,85_BR-120_PKdp-play-icon-overlay__.jpg\",\"https:\\/\\/images-na.ssl-images-amazon.com\\/images\\/G\\/01\\/x-locale\\/common\\/transparent-pixel.gif\"]', '[]', '2025-04-22 11:48:24', '2025-04-22 11:48:24', NULL, NULL, NULL, NULL, NULL, 0, 1, NULL, NULL),
(32, 'Redragon Mechanical Gaming Keyboard Wired, 11 Programmable Backlit Modes, Hot-Swappable Red Switch, Anti-Ghosting, Double-Shot PBT Keycaps, Light Up Keyboard for PC Mac', 29.99, NULL, 'https://m.media-amazon.com/images/I/71Bk2A2WmOL.jpg', 'About this item Brilliant Color Illumination- With 11 unique backlights, choose the perfect ambiance for any mood. Adjust light speed and brightness among 5 levels for a comfortable environment, day or night. The double injection ABS keycaps ensure clear backlight and precise typing. From late-night tasks to immersive gaming, our mechanical keyboard enhances every experience Support Macro Editing: The K671 Mechanical Gaming Keyboard can be macro editing, you can remap the keys function, set shortcuts, or combine multiple key functions in one key to get more efficient work and gaming. The LED Backlit Effects also can be adjusted by the software（note: the color can not be changed） Hot-swappable Linear Red Switch- Our K671 gaming keyboard features red switch, which requires less force to press down and the keys feel smoother and easier to use. It\'s best for rpgs and mmo, imo games. You will get 4 spare switches and two red keycaps as a gift to exchange the key switch when it does not work. Full keys Anti-ghosting- All keys can work simultaneously, easily complete any combining functions without conflicting keys. 12 multimedia key shortcuts allow you to quickly access to calculator/media/volume control/email Professional After-Sales Service- We provide every Redragon customer with 24-Month Warranty , Please feel free to contact us when you meet any problem. We will spare no effort to provide the best service to every customer within 24 hours to help you  See more product details', 'Amazon', 'https://www.amazon.com/dp/B0CF3VGQFL?ref=cm_sw_r_cp_ud_dp_PZPQ1CQYG69NZGKFBRDC&ref_=cm_sw_r_cp_ud_dp_PZPQ1CQYG69NZGKFBRDC&social_share=cm_sw_r_cp_ud_dp_PZPQ1CQYG69NZGKFBRDC&previewDoh=1', 25.00, 18.50, 12.99, 6.00, 92.48, '[\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/41khzfsV4mL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51yJgZBQ7ZL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51rpmLxwDyL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51S6-9VG-tL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51gY2ZKhDfL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51uYAu7fVRL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51m2tgNi5-L.SS40_BG85,85,85_BR-120_PKdp-play-icon-overlay__.jpg\",\"https:\\/\\/images-na.ssl-images-amazon.com\\/images\\/G\\/01\\/x-locale\\/common\\/transparent-pixel.gif\"]', '[]', '2025-04-22 12:24:47', '2025-04-22 12:24:47', '[\"Brilliant Color Illumination- With 11 unique backlights, choose the perfect ambiance for any mood. Adjust light speed and brightness among 5 levels for a comfortable environment, day or night. The double injection ABS keycaps ensure clear backlight and precise typing. From late-night tasks to immersive gaming, our mechanical keyboard enhances every experience\",\"Support Macro Editing: The K671 Mechanical Gaming Keyboard can be macro editing, you can remap the keys function, set shortcuts, or combine multiple key functions in one key to get more efficient work and gaming. The LED Backlit Effects also can be adjusted by the software\\uff08note: the color can not be changed\\uff09\",\"Hot-swappable Linear Red Switch- Our K671 gaming keyboard features red switch, which requires less force to press down and the keys feel smoother and easier to use. It\'s best for rpgs and mmo, imo games. You will get 4 spare switches and two red keycaps as a gift to exchange the key switch when it does not work.\",\"Full keys Anti-ghosting- All keys can work simultaneously, easily complete any combining functions without conflicting keys. 12 multimedia key shortcuts allow you to quickly access to calculator\\/media\\/volume control\\/email\",\"Professional After-Sales Service- We provide every Redragon customer with 24-Month Warranty , Please feel free to contact us when you meet any problem. We will spare no effort to provide the best service to every customer within 24 hours to help you\"]', '{\"Brand\":\"\\u200eRedragon\",\"Series\":\"\\u200eK671\",\"Item model number\":\"\\u200eK671\",\"Hardware Platform\":\"\\u200eLaptop, PC\",\"Operating System\":\"\\u200eWindows 8.1, Windows Vista, Windows 8, Windows 7, Windows 10\",\"Item Weight\":\"\\u200e2.09 pounds\",\"Package Dimensions\":\"\\u200e17.95 x 6.1 x 1.69 inches\",\"Color\":\"\\u200eRGB LED\",\"Power Source\":\"\\u200eWired\",\"Manufacturer\":\"\\u200eRedragon\",\"ASIN\":\"\\u200eB0CF3VGQFL\",\"Country of Origin\":\"\\u200eChina\",\"Date First Available\":\"\\u200eAugust 9, 2023\",\"Customer Reviews\":\"4.3 4.3 out of 5 stars 2,669 ratings var dpAcrHasRegisteredArcLinkClickAction; P.when(\'A\', \'ready\').execute(function(A) { if (dpAcrHasRegisteredArcLinkClickAction !== true) { dpAcrHasRegisteredArcLinkClickAction = true; A.declarative( \'acrLink-click-metrics\', \'click\', { \\\"allowLinkDefault\\\": true }, function (event) { if (window.ue) { ue.count(\\\"acrLinkClickCount\\\", (ue.count(\\\"acrLinkClickCount\\\") || 0) + 1); } } ); } }); P.when(\'A\', \'cf\').execute(function(A) { A.declarative(\'acrStarsLink-click-metrics\', \'click\', { \\\"allowLinkDefault\\\" : true }, function(event){ if(window.ue) { ue.count(\\\"acrStarsLinkWithPopoverClickCount\\\", (ue.count(\\\"acrStarsLinkWithPopoverClickCount\\\") || 0) + 1); } }); }); 4.3 out of 5 stars\",\"Best Sellers Rank\":\"#67 in Video Games (See Top 100 in Video Games) #1 in PC Gaming Keyboards\"}', 'Visit the Redragon Store', 'Video Games > PC > Accessories > Gaming Keyboards', 4.3, 2669, 1, '', '[]'),
(33, 'Redragon Mechanical Gaming Keyboard Wired, 11 Programmable Backlit Modes, Hot-Swappable Red Switch, Anti-Ghosting, Double-Shot PBT Keycaps, Light Up Keyboard for PC Mac', 29.99, NULL, 'https://m.media-amazon.com/images/I/71Bk2A2WmOL.jpg', 'About this item Brilliant Color Illumination- With 11 unique backlights, choose the perfect ambiance for any mood. Adjust light speed and brightness among 5 levels for a comfortable environment, day or night. The double injection ABS keycaps ensure clear backlight and precise typing. From late-night tasks to immersive gaming, our mechanical keyboard enhances every experience Support Macro Editing: The K671 Mechanical Gaming Keyboard can be macro editing, you can remap the keys function, set shortcuts, or combine multiple key functions in one key to get more efficient work and gaming. The LED Backlit Effects also can be adjusted by the software（note: the color can not be changed） Hot-swappable Linear Red Switch- Our K671 gaming keyboard features red switch, which requires less force to press down and the keys feel smoother and easier to use. It\'s best for rpgs and mmo, imo games. You will get 4 spare switches and two red keycaps as a gift to exchange the key switch when it does not work. Full keys Anti-ghosting- All keys can work simultaneously, easily complete any combining functions without conflicting keys. 12 multimedia key shortcuts allow you to quickly access to calculator/media/volume control/email Professional After-Sales Service- We provide every Redragon customer with 24-Month Warranty , Please feel free to contact us when you meet any problem. We will spare no effort to provide the best service to every customer within 24 hours to help you  See more product details', 'Amazon', 'https://www.amazon.com/dp/B0CF3VGQFL?ref=cm_sw_r_cp_ud_dp_PZPQ1CQYG69NZGKFBRDC_1&ref_=cm_sw_r_cp_ud_dp_PZPQ1CQYG69NZGKFBRDC_1&social_share=cm_sw_r_cp_ud_dp_PZPQ1CQYG69NZGKFBRDC_1&previewDoh=1', 25.00, 18.50, 12.99, 6.00, 92.48, '[\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/41khzfsV4mL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51yJgZBQ7ZL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51rpmLxwDyL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51S6-9VG-tL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51gY2ZKhDfL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51uYAu7fVRL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51m2tgNi5-L.SS40_BG85,85,85_BR-120_PKdp-play-icon-overlay__.jpg\",\"https:\\/\\/images-na.ssl-images-amazon.com\\/images\\/G\\/01\\/x-locale\\/common\\/transparent-pixel.gif\"]', '[]', '2025-04-22 12:58:29', '2025-04-22 12:58:29', '[\"Brilliant Color Illumination- With 11 unique backlights, choose the perfect ambiance for any mood. Adjust light speed and brightness among 5 levels for a comfortable environment, day or night. The double injection ABS keycaps ensure clear backlight and precise typing. From late-night tasks to immersive gaming, our mechanical keyboard enhances every experience\",\"Support Macro Editing: The K671 Mechanical Gaming Keyboard can be macro editing, you can remap the keys function, set shortcuts, or combine multiple key functions in one key to get more efficient work and gaming. The LED Backlit Effects also can be adjusted by the software\\uff08note: the color can not be changed\\uff09\",\"Hot-swappable Linear Red Switch- Our K671 gaming keyboard features red switch, which requires less force to press down and the keys feel smoother and easier to use. It\'s best for rpgs and mmo, imo games. You will get 4 spare switches and two red keycaps as a gift to exchange the key switch when it does not work.\",\"Full keys Anti-ghosting- All keys can work simultaneously, easily complete any combining functions without conflicting keys. 12 multimedia key shortcuts allow you to quickly access to calculator\\/media\\/volume control\\/email\",\"Professional After-Sales Service- We provide every Redragon customer with 24-Month Warranty , Please feel free to contact us when you meet any problem. We will spare no effort to provide the best service to every customer within 24 hours to help you\"]', '{\"Brand\":\"\\u200eRedragon\",\"Series\":\"\\u200eK671\",\"Item model number\":\"\\u200eK671\",\"Hardware Platform\":\"\\u200eLaptop, PC\",\"Operating System\":\"\\u200eWindows 8.1, Windows Vista, Windows 8, Windows 7, Windows 10\",\"Item Weight\":\"\\u200e2.09 pounds\",\"Package Dimensions\":\"\\u200e17.95 x 6.1 x 1.69 inches\",\"Color\":\"\\u200eRGB LED\",\"Power Source\":\"\\u200eWired\",\"Manufacturer\":\"\\u200eRedragon\",\"ASIN\":\"\\u200eB0CF3VGQFL\",\"Country of Origin\":\"\\u200eChina\",\"Date First Available\":\"\\u200eAugust 9, 2023\",\"Customer Reviews\":\"4.3 4.3 out of 5 stars 2,669 ratings var dpAcrHasRegisteredArcLinkClickAction; P.when(\'A\', \'ready\').execute(function(A) { if (dpAcrHasRegisteredArcLinkClickAction !== true) { dpAcrHasRegisteredArcLinkClickAction = true; A.declarative( \'acrLink-click-metrics\', \'click\', { \\\"allowLinkDefault\\\": true }, function (event) { if (window.ue) { ue.count(\\\"acrLinkClickCount\\\", (ue.count(\\\"acrLinkClickCount\\\") || 0) + 1); } } ); } }); P.when(\'A\', \'cf\').execute(function(A) { A.declarative(\'acrStarsLink-click-metrics\', \'click\', { \\\"allowLinkDefault\\\" : true }, function(event){ if(window.ue) { ue.count(\\\"acrStarsLinkWithPopoverClickCount\\\", (ue.count(\\\"acrStarsLinkWithPopoverClickCount\\\") || 0) + 1); } }); }); 4.3 out of 5 stars\",\"Best Sellers Rank\":\"#67 in Video Games (See Top 100 in Video Games) #1 in PC Gaming Keyboards\"}', 'Visit the Redragon Store', 'Video Games > PC > Accessories > Gaming Keyboards', 4.3, 2669, 1, '', '[]'),
(34, 'Redragon Mechanical Gaming Keyboard Wired, 11 Programmable Backlit Modes, Hot-Swappable Red Switch, Anti-Ghosting, Double-Shot PBT Keycaps, Light Up Keyboard for PC Mac', 29.99, NULL, 'https://m.media-amazon.com/images/I/71Bk2A2WmOL.jpg', 'About this item Brilliant Color Illumination- With 11 unique backlights, choose the perfect ambiance for any mood. Adjust light speed and brightness among 5 levels for a comfortable environment, day or night. The double injection ABS keycaps ensure clear backlight and precise typing. From late-night tasks to immersive gaming, our mechanical keyboard enhances every experience Support Macro Editing: The K671 Mechanical Gaming Keyboard can be macro editing, you can remap the keys function, set shortcuts, or combine multiple key functions in one key to get more efficient work and gaming. The LED Backlit Effects also can be adjusted by the software（note: the color can not be changed） Hot-swappable Linear Red Switch- Our K671 gaming keyboard features red switch, which requires less force to press down and the keys feel smoother and easier to use. It\'s best for rpgs and mmo, imo games. You will get 4 spare switches and two red keycaps as a gift to exchange the key switch when it does not work. Full keys Anti-ghosting- All keys can work simultaneously, easily complete any combining functions without conflicting keys. 12 multimedia key shortcuts allow you to quickly access to calculator/media/volume control/email Professional After-Sales Service- We provide every Redragon customer with 24-Month Warranty , Please feel free to contact us when you meet any problem. We will spare no effort to provide the best service to every customer within 24 hours to help you  See more product details', 'Amazon', 'https://www.amazon.com/dp/B0CF3VGQFL?ref=cm_sw_r_cp_ud_dp_PZPQ1CQYG69NZGKFBRDC_2&ref_=cm_sw_r_cp_ud_dp_PZPQ1CQYG69NZGKFBRDC_2&social_share=cm_sw_r_cp_ud_dp_PZPQ1CQYG69NZGKFBRDC_2&previewDoh=1', 25.00, 18.50, 12.99, 6.00, 92.48, '[\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/41khzfsV4mL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51yJgZBQ7ZL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51rpmLxwDyL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51S6-9VG-tL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51gY2ZKhDfL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51uYAu7fVRL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51m2tgNi5-L.SS40_BG85,85,85_BR-120_PKdp-play-icon-overlay__.jpg\",\"https:\\/\\/images-na.ssl-images-amazon.com\\/images\\/G\\/01\\/x-locale\\/common\\/transparent-pixel.gif\"]', '[]', '2025-04-22 13:07:48', '2025-04-22 13:07:48', '[\"Brilliant Color Illumination- With 11 unique backlights, choose the perfect ambiance for any mood. Adjust light speed and brightness among 5 levels for a comfortable environment, day or night. The double injection ABS keycaps ensure clear backlight and precise typing. From late-night tasks to immersive gaming, our mechanical keyboard enhances every experience\",\"Support Macro Editing: The K671 Mechanical Gaming Keyboard can be macro editing, you can remap the keys function, set shortcuts, or combine multiple key functions in one key to get more efficient work and gaming. The LED Backlit Effects also can be adjusted by the software\\uff08note: the color can not be changed\\uff09\",\"Hot-swappable Linear Red Switch- Our K671 gaming keyboard features red switch, which requires less force to press down and the keys feel smoother and easier to use. It\'s best for rpgs and mmo, imo games. You will get 4 spare switches and two red keycaps as a gift to exchange the key switch when it does not work.\",\"Full keys Anti-ghosting- All keys can work simultaneously, easily complete any combining functions without conflicting keys. 12 multimedia key shortcuts allow you to quickly access to calculator\\/media\\/volume control\\/email\",\"Professional After-Sales Service- We provide every Redragon customer with 24-Month Warranty , Please feel free to contact us when you meet any problem. We will spare no effort to provide the best service to every customer within 24 hours to help you\"]', '{\"Brand\":\"\\u200eRedragon\",\"Series\":\"\\u200eK671\",\"Item model number\":\"\\u200eK671\",\"Hardware Platform\":\"\\u200eLaptop, PC\",\"Operating System\":\"\\u200eWindows 8.1, Windows Vista, Windows 8, Windows 7, Windows 10\",\"Item Weight\":\"\\u200e2.09 pounds\",\"Package Dimensions\":\"\\u200e17.95 x 6.1 x 1.69 inches\",\"Color\":\"\\u200eRGB LED\",\"Power Source\":\"\\u200eWired\",\"Manufacturer\":\"\\u200eRedragon\",\"ASIN\":\"\\u200eB0CF3VGQFL\",\"Country of Origin\":\"\\u200eChina\",\"Date First Available\":\"\\u200eAugust 9, 2023\",\"Customer Reviews\":\"4.3 4.3 out of 5 stars 2,669 ratings var dpAcrHasRegisteredArcLinkClickAction; P.when(\'A\', \'ready\').execute(function(A) { if (dpAcrHasRegisteredArcLinkClickAction !== true) { dpAcrHasRegisteredArcLinkClickAction = true; A.declarative( \'acrLink-click-metrics\', \'click\', { \\\"allowLinkDefault\\\": true }, function (event) { if (window.ue) { ue.count(\\\"acrLinkClickCount\\\", (ue.count(\\\"acrLinkClickCount\\\") || 0) + 1); } } ); } }); P.when(\'A\', \'cf\').execute(function(A) { A.declarative(\'acrStarsLink-click-metrics\', \'click\', { \\\"allowLinkDefault\\\" : true }, function(event){ if(window.ue) { ue.count(\\\"acrStarsLinkWithPopoverClickCount\\\", (ue.count(\\\"acrStarsLinkWithPopoverClickCount\\\") || 0) + 1); } }); }); 4.3 out of 5 stars\",\"Best Sellers Rank\":\"#67 in Video Games (See Top 100 in Video Games) #1 in PC Gaming Keyboards\"}', 'Visit the Redragon Store', 'Video Games > PC > Accessories > Gaming Keyboards', 4.3, 2669, 1, '', '[]'),
(35, 'Redragon Mechanical Gaming Keyboard Wired, 11 Programmable Backlit Modes, Hot-Swappable Red Switch, Anti-Ghosting, Double-Shot PBT Keycaps, Light Up Keyboard for PC Mac', 29.99, NULL, 'https://m.media-amazon.com/images/I/71Bk2A2WmOL.jpg', 'About this item Brilliant Color Illumination- With 11 unique backlights, choose the perfect ambiance for any mood. Adjust light speed and brightness among 5 levels for a comfortable environment, day or night. The double injection ABS keycaps ensure clear backlight and precise typing. From late-night tasks to immersive gaming, our mechanical keyboard enhances every experience Support Macro Editing: The K671 Mechanical Gaming Keyboard can be macro editing, you can remap the keys function, set shortcuts, or combine multiple key functions in one key to get more efficient work and gaming. The LED Backlit Effects also can be adjusted by the software（note: the color can not be changed） Hot-swappable Linear Red Switch- Our K671 gaming keyboard features red switch, which requires less force to press down and the keys feel smoother and easier to use. It\'s best for rpgs and mmo, imo games. You will get 4 spare switches and two red keycaps as a gift to exchange the key switch when it does not work. Full keys Anti-ghosting- All keys can work simultaneously, easily complete any combining functions without conflicting keys. 12 multimedia key shortcuts allow you to quickly access to calculator/media/volume control/email Professional After-Sales Service- We provide every Redragon customer with 24-Month Warranty , Please feel free to contact us when you meet any problem. We will spare no effort to provide the best service to every customer within 24 hours to help you  See more product details', 'Amazon', 'https://www.amazon.com/dp/B0CF3VGQFL?ref=cm_sw_r_cp_ud_dp_PZPQ1CQYG69NZGKFBRDC_3&ref_=cm_sw_r_cp_ud_dp_PZPQ1CQYG69NZGKFBRDC_3&social_share=cm_sw_r_cp_ud_dp_PZPQ1CQYG69NZGKFBRDC_3&previewDoh=1', 25.00, 18.50, 12.99, 6.00, 92.48, '[\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/41khzfsV4mL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51yJgZBQ7ZL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51rpmLxwDyL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51S6-9VG-tL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51gY2ZKhDfL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51uYAu7fVRL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51m2tgNi5-L.SS40_BG85,85,85_BR-120_PKdp-play-icon-overlay__.jpg\",\"https:\\/\\/images-na.ssl-images-amazon.com\\/images\\/G\\/01\\/x-locale\\/common\\/transparent-pixel.gif\"]', '[]', '2025-04-22 13:26:46', '2025-04-22 13:26:46', '[\"Brilliant Color Illumination- With 11 unique backlights, choose the perfect ambiance for any mood. Adjust light speed and brightness among 5 levels for a comfortable environment, day or night. The double injection ABS keycaps ensure clear backlight and precise typing. From late-night tasks to immersive gaming, our mechanical keyboard enhances every experience\",\"Support Macro Editing: The K671 Mechanical Gaming Keyboard can be macro editing, you can remap the keys function, set shortcuts, or combine multiple key functions in one key to get more efficient work and gaming. The LED Backlit Effects also can be adjusted by the software\\uff08note: the color can not be changed\\uff09\",\"Hot-swappable Linear Red Switch- Our K671 gaming keyboard features red switch, which requires less force to press down and the keys feel smoother and easier to use. It\'s best for rpgs and mmo, imo games. You will get 4 spare switches and two red keycaps as a gift to exchange the key switch when it does not work.\",\"Full keys Anti-ghosting- All keys can work simultaneously, easily complete any combining functions without conflicting keys. 12 multimedia key shortcuts allow you to quickly access to calculator\\/media\\/volume control\\/email\",\"Professional After-Sales Service- We provide every Redragon customer with 24-Month Warranty , Please feel free to contact us when you meet any problem. We will spare no effort to provide the best service to every customer within 24 hours to help you\"]', '{\"Brand\":\"\\u200eRedragon\",\"Series\":\"\\u200eK671\",\"Item model number\":\"\\u200eK671\",\"Hardware Platform\":\"\\u200eLaptop, PC\",\"Operating System\":\"\\u200eWindows 8.1, Windows Vista, Windows 8, Windows 7, Windows 10\",\"Item Weight\":\"\\u200e2.09 pounds\",\"Package Dimensions\":\"\\u200e17.95 x 6.1 x 1.69 inches\",\"Color\":\"\\u200eRGB LED\",\"Power Source\":\"\\u200eWired\",\"Manufacturer\":\"\\u200eRedragon\",\"ASIN\":\"\\u200eB0CF3VGQFL\",\"Country of Origin\":\"\\u200eChina\",\"Date First Available\":\"\\u200eAugust 9, 2023\",\"Customer Reviews\":\"4.3 4.3 out of 5 stars 2,669 ratings var dpAcrHasRegisteredArcLinkClickAction; P.when(\'A\', \'ready\').execute(function(A) { if (dpAcrHasRegisteredArcLinkClickAction !== true) { dpAcrHasRegisteredArcLinkClickAction = true; A.declarative( \'acrLink-click-metrics\', \'click\', { \\\"allowLinkDefault\\\": true }, function (event) { if (window.ue) { ue.count(\\\"acrLinkClickCount\\\", (ue.count(\\\"acrLinkClickCount\\\") || 0) + 1); } } ); } }); P.when(\'A\', \'cf\').execute(function(A) { A.declarative(\'acrStarsLink-click-metrics\', \'click\', { \\\"allowLinkDefault\\\" : true }, function(event){ if(window.ue) { ue.count(\\\"acrStarsLinkWithPopoverClickCount\\\", (ue.count(\\\"acrStarsLinkWithPopoverClickCount\\\") || 0) + 1); } }); }); 4.3 out of 5 stars\",\"Best Sellers Rank\":\"#67 in Video Games (See Top 100 in Video Games) #1 in PC Gaming Keyboards\"}', 'Visit the Redragon Store', 'Video Games > PC > Accessories > Gaming Keyboards', 4.3, 2669, 1, '', '[]');
INSERT INTO `products` (`id`, `name`, `original_price`, `discounted_price`, `image`, `description`, `store`, `original_url`, `shipping`, `customs`, `service_fee`, `vat`, `total_price`, `images`, `similar_products`, `created_at`, `updated_at`, `features`, `specifications`, `brand`, `category`, `rating`, `review_count`, `in_stock`, `sku`, `additional_info`) VALUES
(36, 'Redragon Mechanical Gaming Keyboard Wired, 11 Programmable Backlit Modes, Hot-Swappable Red Switch, Anti-Ghosting, Double-Shot PBT Keycaps, Light Up Keyboard for PC Mac', 29.99, NULL, 'https://m.media-amazon.com/images/I/71Bk2A2WmOL.jpg', 'About this item Brilliant Color Illumination- With 11 unique backlights, choose the perfect ambiance for any mood. Adjust light speed and brightness among 5 levels for a comfortable environment, day or night. The double injection ABS keycaps ensure clear backlight and precise typing. From late-night tasks to immersive gaming, our mechanical keyboard enhances every experience Support Macro Editing: The K671 Mechanical Gaming Keyboard can be macro editing, you can remap the keys function, set shortcuts, or combine multiple key functions in one key to get more efficient work and gaming. The LED Backlit Effects also can be adjusted by the software（note: the color can not be changed） Hot-swappable Linear Red Switch- Our K671 gaming keyboard features red switch, which requires less force to press down and the keys feel smoother and easier to use. It\'s best for rpgs and mmo, imo games. You will get 4 spare switches and two red keycaps as a gift to exchange the key switch when it does not work. Full keys Anti-ghosting- All keys can work simultaneously, easily complete any combining functions without conflicting keys. 12 multimedia key shortcuts allow you to quickly access to calculator/media/volume control/email Professional After-Sales Service- We provide every Redragon customer with 24-Month Warranty , Please feel free to contact us when you meet any problem. We will spare no effort to provide the best service to every customer within 24 hours to help you  See more product details', 'Amazon', 'https://www.amazon.com/dp/B0CF3VGQFL?ref=cm_sw_r_cp_ud_dp_PZPQ1CQYG69NZGKFBRDC_5&ref_=cm_sw_r_cp_ud_dp_PZPQ1CQYG69NZGKFBRDC_5&social_share=cm_sw_r_cp_ud_dp_PZPQ1CQYG69NZGKFBRDC_5&previewDoh=1', 25.00, 18.50, 12.99, 6.00, 92.48, '[\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/41khzfsV4mL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51yJgZBQ7ZL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51rpmLxwDyL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51S6-9VG-tL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51gY2ZKhDfL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51uYAu7fVRL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51m2tgNi5-L.SS40_BG85,85,85_BR-120_PKdp-play-icon-overlay__.jpg\",\"https:\\/\\/images-na.ssl-images-amazon.com\\/images\\/G\\/01\\/x-locale\\/common\\/transparent-pixel.gif\"]', '[]', '2025-04-22 15:27:12', '2025-04-22 15:27:12', '[\"Brilliant Color Illumination- With 11 unique backlights, choose the perfect ambiance for any mood. Adjust light speed and brightness among 5 levels for a comfortable environment, day or night. The double injection ABS keycaps ensure clear backlight and precise typing. From late-night tasks to immersive gaming, our mechanical keyboard enhances every experience\",\"Support Macro Editing: The K671 Mechanical Gaming Keyboard can be macro editing, you can remap the keys function, set shortcuts, or combine multiple key functions in one key to get more efficient work and gaming. The LED Backlit Effects also can be adjusted by the software\\uff08note: the color can not be changed\\uff09\",\"Hot-swappable Linear Red Switch- Our K671 gaming keyboard features red switch, which requires less force to press down and the keys feel smoother and easier to use. It\'s best for rpgs and mmo, imo games. You will get 4 spare switches and two red keycaps as a gift to exchange the key switch when it does not work.\",\"Full keys Anti-ghosting- All keys can work simultaneously, easily complete any combining functions without conflicting keys. 12 multimedia key shortcuts allow you to quickly access to calculator\\/media\\/volume control\\/email\",\"Professional After-Sales Service- We provide every Redragon customer with 24-Month Warranty , Please feel free to contact us when you meet any problem. We will spare no effort to provide the best service to every customer within 24 hours to help you\"]', '{\"Brand\":\"\\u200eRedragon\",\"Series\":\"\\u200eK671\",\"Item model number\":\"\\u200eK671\",\"Hardware Platform\":\"\\u200eLaptop, PC\",\"Operating System\":\"\\u200eWindows 8.1, Windows Vista, Windows 8, Windows 7, Windows 10\",\"Item Weight\":\"\\u200e2.09 pounds\",\"Package Dimensions\":\"\\u200e17.95 x 6.1 x 1.69 inches\",\"Color\":\"\\u200eRGB LED\",\"Power Source\":\"\\u200eWired\",\"Manufacturer\":\"\\u200eRedragon\",\"ASIN\":\"\\u200eB0CF3VGQFL\",\"Country of Origin\":\"\\u200eChina\",\"Date First Available\":\"\\u200eAugust 9, 2023\",\"Customer Reviews\":\"4.3 4.3 out of 5 stars 2,669 ratings var dpAcrHasRegisteredArcLinkClickAction; P.when(\'A\', \'ready\').execute(function(A) { if (dpAcrHasRegisteredArcLinkClickAction !== true) { dpAcrHasRegisteredArcLinkClickAction = true; A.declarative( \'acrLink-click-metrics\', \'click\', { \\\"allowLinkDefault\\\": true }, function (event) { if (window.ue) { ue.count(\\\"acrLinkClickCount\\\", (ue.count(\\\"acrLinkClickCount\\\") || 0) + 1); } } ); } }); P.when(\'A\', \'cf\').execute(function(A) { A.declarative(\'acrStarsLink-click-metrics\', \'click\', { \\\"allowLinkDefault\\\" : true }, function(event){ if(window.ue) { ue.count(\\\"acrStarsLinkWithPopoverClickCount\\\", (ue.count(\\\"acrStarsLinkWithPopoverClickCount\\\") || 0) + 1); } }); }); 4.3 out of 5 stars\",\"Best Sellers Rank\":\"#67 in Video Games (See Top 100 in Video Games) #1 in PC Gaming Keyboards\"}', 'Visit the Redragon Store', 'Video Games > PC > Accessories > Gaming Keyboards', 4.3, 2669, 1, '', '[]'),
(37, 'Redragon Gaming Mouse, Wireless Mouse Gaming with 8000 DPI, PC Gaming Mice with Fire Button, RGB Backlit Programmable Ergonomic Mouse Gamer, Rechargeable, 70Hrs for Windows, Mac Gamer, Black', 22.49, NULL, 'https://m.media-amazon.com/images/I/61QY3V6A-NL.jpg', 'About this item 【Fully Programmable Gaming Mouse】- Redragon Wireless Gaming Mouse All buttons can be programmed with the driver and support macro editing. You can remap the buttons, assignment of complex macro functions, change RGB backlit effects, and adjust DPI (250-8000) to fit your different needs. 【High-Precision Gaming Mouse】-The wireless mouse features adjustable DPI(250-8000) and 4 adjustable polling rates ( 125Hz/250Hz/500Hz/1000Hz), you can easily adjust the moving speed, and experience a smooth, fast response and accurate tracking gaming experience. The fire button（✈1 click = 3 clicks) gives you the edge you need during those intense FPS battles. 【Enhance Your Gaming Immersion】UP to 9 RGB light effects can be chosen, you also can adjust backlit effects with 16.8 million color combinations by drivers to create your fancy gaming environment, and match your game style and desktop layout. 【Powerful Battery Life】The rechargeable mouse has a long battery life between 35 hours (RGB on) and 70 hours (RGB off) on a single charge, providing you with nonstopping use. It will auto-sleep after 1 minute of inactivity for power saving. The wireless mouse gaming also can be used while charging. 【Extreme Ergonomics】The mouse gaming with an ergonomic design and Skin-friendly material will provide you with a comfortable grip and soft touch, Effectively relieving fatigue during long-time gaming.  See more product details', 'Amazon', 'https://www.amazon.com/dp/B0B66RHD7B?_encoding=UTF8&psc=1&ref=cm_sw_r_cp_ud_dp_HTPHX074RKX4PNMAH1AR_1&ref_=cm_sw_r_cp_ud_dp_HTPHX074RKX4PNMAH1AR_1&social_share=cm_sw_r_cp_ud_dp_HTPHX074RKX4PNMAH1AR_1&previewDoh=1', 25.00, 18.50, 12.99, 4.50, 83.48, '[\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/31PB5xEjLiL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51Fw9vgOynL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51dbvhjnJJL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51Q+6EvMPqL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/512QCJs0VrL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51s1Mv8bX2L.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/41LymohiRiL.SS40_BG85,85,85_BR-120_PKdp-play-icon-overlay__.jpg\",\"https:\\/\\/images-na.ssl-images-amazon.com\\/images\\/G\\/01\\/x-locale\\/common\\/transparent-pixel.gif\"]', '[]', '2025-04-22 15:35:46', '2025-04-22 15:35:46', '[\"\\u3010Fully Programmable Gaming Mouse\\u3011- Redragon Wireless Gaming Mouse All buttons can be programmed with the driver and support macro editing. You can remap the buttons, assignment of complex macro functions, change RGB backlit effects, and adjust DPI (250-8000) to fit your different needs.\",\"\\u3010High-Precision Gaming Mouse\\u3011-The wireless mouse features adjustable DPI(250-8000) and 4 adjustable polling rates ( 125Hz\\/250Hz\\/500Hz\\/1000Hz), you can easily adjust the moving speed, and experience a smooth, fast response and accurate tracking gaming experience. The fire button\\uff08\\u27081 click = 3 clicks) gives you the edge you need during those intense FPS battles.\",\"\\u3010Enhance Your Gaming Immersion\\u3011UP to 9 RGB light effects can be chosen, you also can adjust backlit effects with 16.8 million color combinations by drivers to create your fancy gaming environment, and match your game style and desktop layout.\",\"\\u3010Powerful Battery Life\\u3011The rechargeable mouse has a long battery life between 35 hours (RGB on) and 70 hours (RGB off) on a single charge, providing you with nonstopping use. It will auto-sleep after 1 minute of inactivity for power saving. The wireless mouse gaming also can be used while charging.\",\"\\u3010Extreme Ergonomics\\u3011The mouse gaming with an ergonomic design and Skin-friendly material will provide you with a comfortable grip and soft touch, Effectively relieving fatigue during long-time gaming.\"]', '{\"Product Dimensions\":\"5.12 x 2.76 x 0.04 inches\",\"Item Weight\":\"4.8 ounces\",\"ASIN\":\"B0B66RHD7B\",\"Item model number\":\"M910-KS\",\"Batteries\":\"1 Lithium Polymer batteries required. (included)\",\"Customer Reviews\":\"4.5 4.5 out of 5 stars 4,244 ratings var dpAcrHasRegisteredArcLinkClickAction; P.when(\'A\', \'ready\').execute(function(A) { if (dpAcrHasRegisteredArcLinkClickAction !== true) { dpAcrHasRegisteredArcLinkClickAction = true; A.declarative( \'acrLink-click-metrics\', \'click\', { \\\"allowLinkDefault\\\": true }, function (event) { if (window.ue) { ue.count(\\\"acrLinkClickCount\\\", (ue.count(\\\"acrLinkClickCount\\\") || 0) + 1); } } ); } }); P.when(\'A\', \'cf\').execute(function(A) { A.declarative(\'acrStarsLink-click-metrics\', \'click\', { \\\"allowLinkDefault\\\" : true }, function(event){ if(window.ue) { ue.count(\\\"acrStarsLinkWithPopoverClickCount\\\", (ue.count(\\\"acrStarsLinkWithPopoverClickCount\\\") || 0) + 1); } }); }); 4.5 out of 5 stars\",\"Best Sellers Rank\":\"#104 in Video Games (See Top 100 in Video Games) #8 in PC Gaming Mice\",\"Date First Available\":\"July 20, 2022\",\"Manufacturer\":\"Redraogon\"}', 'Visit the Redragon Store', 'Video Games > PC > Accessories > Gaming Mice', 4.5, 4244, 1, 'B0B66RHD7B', '[]'),
(38, 'POEDAGAR Luxury High Quality Watches for Men Sport Quartz Leather Man Watch Waterproof Luminous Date Week Men\'s Watch Male Reloj - AliExpress 1511', 0.00, NULL, 'https://ae01.alicdn.com/kf/S552209413d8f41adb54391022df6dc371.jpg', 'Smarter Shopping, Better Living! Aliexpress.com', 'AliExpress', 'https://www.aliexpress.com/item/1005006770573211.html?channel=twinner', 25.00, 18.50, 12.99, 0.00, 56.49, '[]', '[]', '2025-04-22 18:11:16', '2025-04-22 18:11:16', '[]', '[]', NULL, NULL, NULL, 0, 1, NULL, '[]'),
(39, 'Redragon Gaming Mouse, Wireless Mouse Gaming with 8000 DPI, PC Gaming Mice with Fire Button, RGB Backlit Programmable Ergonomic Mouse Gamer, Rechargeable, 70Hrs for Windows, Mac Gamer, Black', 22.49, NULL, 'https://m.media-amazon.com/images/I/61QY3V6A-NL.jpg', 'About this item 【Fully Programmable Gaming Mouse】- Redragon Wireless Gaming Mouse All buttons can be programmed with the driver and support macro editing. You can remap the buttons, assignment of complex macro functions, change RGB backlit effects, and adjust DPI (250-8000) to fit your different needs. 【High-Precision Gaming Mouse】-The wireless mouse features adjustable DPI(250-8000) and 4 adjustable polling rates ( 125Hz/250Hz/500Hz/1000Hz), you can easily adjust the moving speed, and experience a smooth, fast response and accurate tracking gaming experience. The fire button（✈1 click = 3 clicks) gives you the edge you need during those intense FPS battles. 【Enhance Your Gaming Immersion】UP to 9 RGB light effects can be chosen, you also can adjust backlit effects with 16.8 million color combinations by drivers to create your fancy gaming environment, and match your game style and desktop layout. 【Powerful Battery Life】The rechargeable mouse has a long battery life between 35 hours (RGB on) and 70 hours (RGB off) on a single charge, providing you with nonstopping use. It will auto-sleep after 1 minute of inactivity for power saving. The wireless mouse gaming also can be used while charging. 【Extreme Ergonomics】The mouse gaming with an ergonomic design and Skin-friendly material will provide you with a comfortable grip and soft touch, Effectively relieving fatigue during long-time gaming.  See more product details', 'Amazon', 'https://www.amazon.com/dp/B0B66RHD7B?_encoding=UTF8&psc=1&ref=cm_sw_r_cp_ud_dp_HTPHX074RKX4PNMAH1AR_2&ref_=cm_sw_r_cp_ud_dp_HTPHX074RKX4PNMAH1AR_2&social_share=cm_sw_r_cp_ud_dp_HTPHX074RKX4PNMAH1AR_2&previewDoh=1', 25.00, 18.50, 12.99, 4.50, 83.48, '[\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/31PB5xEjLiL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51Fw9vgOynL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51dbvhjnJJL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51Q+6EvMPqL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/512QCJs0VrL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51s1Mv8bX2L.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/41LymohiRiL.SS40_BG85,85,85_BR-120_PKdp-play-icon-overlay__.jpg\",\"https:\\/\\/images-na.ssl-images-amazon.com\\/images\\/G\\/01\\/x-locale\\/common\\/transparent-pixel.gif\"]', '[]', '2025-04-22 18:16:04', '2025-04-22 18:16:04', '[\"\\u3010Fully Programmable Gaming Mouse\\u3011- Redragon Wireless Gaming Mouse All buttons can be programmed with the driver and support macro editing. You can remap the buttons, assignment of complex macro functions, change RGB backlit effects, and adjust DPI (250-8000) to fit your different needs.\",\"\\u3010High-Precision Gaming Mouse\\u3011-The wireless mouse features adjustable DPI(250-8000) and 4 adjustable polling rates ( 125Hz\\/250Hz\\/500Hz\\/1000Hz), you can easily adjust the moving speed, and experience a smooth, fast response and accurate tracking gaming experience. The fire button\\uff08\\u27081 click = 3 clicks) gives you the edge you need during those intense FPS battles.\",\"\\u3010Enhance Your Gaming Immersion\\u3011UP to 9 RGB light effects can be chosen, you also can adjust backlit effects with 16.8 million color combinations by drivers to create your fancy gaming environment, and match your game style and desktop layout.\",\"\\u3010Powerful Battery Life\\u3011The rechargeable mouse has a long battery life between 35 hours (RGB on) and 70 hours (RGB off) on a single charge, providing you with nonstopping use. It will auto-sleep after 1 minute of inactivity for power saving. The wireless mouse gaming also can be used while charging.\",\"\\u3010Extreme Ergonomics\\u3011The mouse gaming with an ergonomic design and Skin-friendly material will provide you with a comfortable grip and soft touch, Effectively relieving fatigue during long-time gaming.\"]', '{\"Product Dimensions\":\"5.12 x 2.76 x 0.04 inches\",\"Item Weight\":\"4.8 ounces\",\"ASIN\":\"B0B66RHD7B\",\"Item model number\":\"M910-KS\",\"Batteries\":\"1 Lithium Polymer batteries required. (included)\",\"Customer Reviews\":\"4.5 4.5 out of 5 stars 4,245 ratings var dpAcrHasRegisteredArcLinkClickAction; P.when(\'A\', \'ready\').execute(function(A) { if (dpAcrHasRegisteredArcLinkClickAction !== true) { dpAcrHasRegisteredArcLinkClickAction = true; A.declarative( \'acrLink-click-metrics\', \'click\', { \\\"allowLinkDefault\\\": true }, function (event) { if (window.ue) { ue.count(\\\"acrLinkClickCount\\\", (ue.count(\\\"acrLinkClickCount\\\") || 0) + 1); } } ); } }); P.when(\'A\', \'cf\').execute(function(A) { A.declarative(\'acrStarsLink-click-metrics\', \'click\', { \\\"allowLinkDefault\\\" : true }, function(event){ if(window.ue) { ue.count(\\\"acrStarsLinkWithPopoverClickCount\\\", (ue.count(\\\"acrStarsLinkWithPopoverClickCount\\\") || 0) + 1); } }); }); 4.5 out of 5 stars\",\"Best Sellers Rank\":\"#106 in Video Games (See Top 100 in Video Games) #9 in PC Gaming Mice\",\"Date First Available\":\"July 20, 2022\",\"Manufacturer\":\"Redraogon\"}', 'Visit the Redragon Store', 'Video Games > PC > Accessories > Gaming Mice', 4.5, 4245, 1, 'B0B66RHD7B', '[]'),
(40, 'Redragon Gaming Mouse, Wireless Mouse Gaming with 8000 DPI, PC Gaming Mice with Fire Button, RGB Backlit Programmable Ergonomic Mouse Gamer, Rechargeable, 70Hrs for Windows, Mac Gamer, Black', 22.49, NULL, 'https://m.media-amazon.com/images/I/61QY3V6A-NL.jpg', 'About this item 【Fully Programmable Gaming Mouse】- Redragon Wireless Gaming Mouse All buttons can be programmed with the driver and support macro editing. You can remap the buttons, assignment of complex macro functions, change RGB backlit effects, and adjust DPI (250-8000) to fit your different needs. 【High-Precision Gaming Mouse】-The wireless mouse features adjustable DPI(250-8000) and 4 adjustable polling rates ( 125Hz/250Hz/500Hz/1000Hz), you can easily adjust the moving speed, and experience a smooth, fast response and accurate tracking gaming experience. The fire button（✈1 click = 3 clicks) gives you the edge you need during those intense FPS battles. 【Enhance Your Gaming Immersion】UP to 9 RGB light effects can be chosen, you also can adjust backlit effects with 16.8 million color combinations by drivers to create your fancy gaming environment, and match your game style and desktop layout. 【Powerful Battery Life】The rechargeable mouse has a long battery life between 35 hours (RGB on) and 70 hours (RGB off) on a single charge, providing you with nonstopping use. It will auto-sleep after 1 minute of inactivity for power saving. The wireless mouse gaming also can be used while charging. 【Extreme Ergonomics】The mouse gaming with an ergonomic design and Skin-friendly material will provide you with a comfortable grip and soft touch, Effectively relieving fatigue during long-time gaming.  See more product details', 'Amazon', 'https://www.amazon.com/dp/B0B66RHD7B?_encoding=UTF8&psc=1&ref=cm_sw_r_cp_ud_dp_HTPHX074RKX4PNMAH1AR_3&ref_=cm_sw_r_cp_ud_dp_HTPHX074RKX4PNMAH1AR_3&social_share=cm_sw_r_cp_ud_dp_HTPHX074RKX4PNMAH1AR_3&previewDoh=1', 25.00, 18.50, 12.99, 4.50, 83.48, '[\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/31PB5xEjLiL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51Fw9vgOynL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51dbvhjnJJL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51Q+6EvMPqL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/512QCJs0VrL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51s1Mv8bX2L.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/41LymohiRiL.SS40_BG85,85,85_BR-120_PKdp-play-icon-overlay__.jpg\",\"https:\\/\\/images-na.ssl-images-amazon.com\\/images\\/G\\/01\\/x-locale\\/common\\/transparent-pixel.gif\"]', '[]', '2025-04-22 18:27:52', '2025-04-22 18:27:52', '[\"\\u3010Fully Programmable Gaming Mouse\\u3011- Redragon Wireless Gaming Mouse All buttons can be programmed with the driver and support macro editing. You can remap the buttons, assignment of complex macro functions, change RGB backlit effects, and adjust DPI (250-8000) to fit your different needs.\",\"\\u3010High-Precision Gaming Mouse\\u3011-The wireless mouse features adjustable DPI(250-8000) and 4 adjustable polling rates ( 125Hz\\/250Hz\\/500Hz\\/1000Hz), you can easily adjust the moving speed, and experience a smooth, fast response and accurate tracking gaming experience. The fire button\\uff08\\u27081 click = 3 clicks) gives you the edge you need during those intense FPS battles.\",\"\\u3010Enhance Your Gaming Immersion\\u3011UP to 9 RGB light effects can be chosen, you also can adjust backlit effects with 16.8 million color combinations by drivers to create your fancy gaming environment, and match your game style and desktop layout.\",\"\\u3010Powerful Battery Life\\u3011The rechargeable mouse has a long battery life between 35 hours (RGB on) and 70 hours (RGB off) on a single charge, providing you with nonstopping use. It will auto-sleep after 1 minute of inactivity for power saving. The wireless mouse gaming also can be used while charging.\",\"\\u3010Extreme Ergonomics\\u3011The mouse gaming with an ergonomic design and Skin-friendly material will provide you with a comfortable grip and soft touch, Effectively relieving fatigue during long-time gaming.\"]', '{\"Product Dimensions\":\"5.12 x 2.76 x 0.04 inches\",\"Item Weight\":\"4.8 ounces\",\"ASIN\":\"B0B66RHD7B\",\"Item model number\":\"M910-KS\",\"Batteries\":\"1 Lithium Polymer batteries required. (included)\",\"Customer Reviews\":\"4.5 4.5 out of 5 stars 4,245 ratings var dpAcrHasRegisteredArcLinkClickAction; P.when(\'A\', \'ready\').execute(function(A) { if (dpAcrHasRegisteredArcLinkClickAction !== true) { dpAcrHasRegisteredArcLinkClickAction = true; A.declarative( \'acrLink-click-metrics\', \'click\', { \\\"allowLinkDefault\\\": true }, function (event) { if (window.ue) { ue.count(\\\"acrLinkClickCount\\\", (ue.count(\\\"acrLinkClickCount\\\") || 0) + 1); } } ); } }); P.when(\'A\', \'cf\').execute(function(A) { A.declarative(\'acrStarsLink-click-metrics\', \'click\', { \\\"allowLinkDefault\\\" : true }, function(event){ if(window.ue) { ue.count(\\\"acrStarsLinkWithPopoverClickCount\\\", (ue.count(\\\"acrStarsLinkWithPopoverClickCount\\\") || 0) + 1); } }); }); 4.5 out of 5 stars\",\"Best Sellers Rank\":\"#106 in Video Games (See Top 100 in Video Games) #9 in PC Gaming Mice\",\"Date First Available\":\"July 20, 2022\",\"Manufacturer\":\"Redraogon\"}', 'Visit the Redragon Store', 'Video Games > PC > Accessories > Gaming Mice', 4.5, 4245, 1, 'B0B66RHD7B', '[]'),
(41, 'Redragon Gaming Mouse, Wireless Mouse Gaming with 8000 DPI, PC Gaming Mice with Fire Button, RGB Backlit Programmable Ergonomic Mouse Gamer, Rechargeable, 70Hrs for Windows, Mac Gamer, Black', 22.49, NULL, 'https://m.media-amazon.com/images/I/61QY3V6A-NL.jpg', 'About this item 【Fully Programmable Gaming Mouse】- Redragon Wireless Gaming Mouse All buttons can be programmed with the driver and support macro editing. You can remap the buttons, assignment of complex macro functions, change RGB backlit effects, and adjust DPI (250-8000) to fit your different needs. 【High-Precision Gaming Mouse】-The wireless mouse features adjustable DPI(250-8000) and 4 adjustable polling rates ( 125Hz/250Hz/500Hz/1000Hz), you can easily adjust the moving speed, and experience a smooth, fast response and accurate tracking gaming experience. The fire button（✈1 click = 3 clicks) gives you the edge you need during those intense FPS battles. 【Enhance Your Gaming Immersion】UP to 9 RGB light effects can be chosen, you also can adjust backlit effects with 16.8 million color combinations by drivers to create your fancy gaming environment, and match your game style and desktop layout. 【Powerful Battery Life】The rechargeable mouse has a long battery life between 35 hours (RGB on) and 70 hours (RGB off) on a single charge, providing you with nonstopping use. It will auto-sleep after 1 minute of inactivity for power saving. The wireless mouse gaming also can be used while charging. 【Extreme Ergonomics】The mouse gaming with an ergonomic design and Skin-friendly material will provide you with a comfortable grip and soft touch, Effectively relieving fatigue during long-time gaming.  See more product details', 'Amazon', 'https://www.amazon.com/dp/B0B66RHD7B?_encoding=UTF8&psc=1&ref=cm_sw_r_cp_ud_dp_HTPHX074RKX4PNMAH1AR_4&ref_=cm_sw_r_cp_ud_dp_HTPHX074RKX4PNMAH1AR_4&social_share=cm_sw_r_cp_ud_dp_HTPHX074RKX4PNMAH1AR_4&previewDoh=1', 25.00, 18.50, 12.99, 4.50, 83.48, '[\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/31PB5xEjLiL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51Fw9vgOynL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51dbvhjnJJL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51Q+6EvMPqL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/512QCJs0VrL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51s1Mv8bX2L.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/41LymohiRiL.SS40_BG85,85,85_BR-120_PKdp-play-icon-overlay__.jpg\",\"https:\\/\\/images-na.ssl-images-amazon.com\\/images\\/G\\/01\\/x-locale\\/common\\/transparent-pixel.gif\"]', '[]', '2025-04-22 18:30:53', '2025-04-22 18:30:53', '[\"\\u3010Fully Programmable Gaming Mouse\\u3011- Redragon Wireless Gaming Mouse All buttons can be programmed with the driver and support macro editing. You can remap the buttons, assignment of complex macro functions, change RGB backlit effects, and adjust DPI (250-8000) to fit your different needs.\",\"\\u3010High-Precision Gaming Mouse\\u3011-The wireless mouse features adjustable DPI(250-8000) and 4 adjustable polling rates ( 125Hz\\/250Hz\\/500Hz\\/1000Hz), you can easily adjust the moving speed, and experience a smooth, fast response and accurate tracking gaming experience. The fire button\\uff08\\u27081 click = 3 clicks) gives you the edge you need during those intense FPS battles.\",\"\\u3010Enhance Your Gaming Immersion\\u3011UP to 9 RGB light effects can be chosen, you also can adjust backlit effects with 16.8 million color combinations by drivers to create your fancy gaming environment, and match your game style and desktop layout.\",\"\\u3010Powerful Battery Life\\u3011The rechargeable mouse has a long battery life between 35 hours (RGB on) and 70 hours (RGB off) on a single charge, providing you with nonstopping use. It will auto-sleep after 1 minute of inactivity for power saving. The wireless mouse gaming also can be used while charging.\",\"\\u3010Extreme Ergonomics\\u3011The mouse gaming with an ergonomic design and Skin-friendly material will provide you with a comfortable grip and soft touch, Effectively relieving fatigue during long-time gaming.\"]', '{\"Product Dimensions\":\"5.12 x 2.76 x 0.04 inches\",\"Item Weight\":\"4.8 ounces\",\"ASIN\":\"B0B66RHD7B\",\"Item model number\":\"M910-KS\",\"Batteries\":\"1 Lithium Polymer batteries required. (included)\",\"Customer Reviews\":\"4.5 4.5 out of 5 stars 4,245 ratings var dpAcrHasRegisteredArcLinkClickAction; P.when(\'A\', \'ready\').execute(function(A) { if (dpAcrHasRegisteredArcLinkClickAction !== true) { dpAcrHasRegisteredArcLinkClickAction = true; A.declarative( \'acrLink-click-metrics\', \'click\', { \\\"allowLinkDefault\\\": true }, function (event) { if (window.ue) { ue.count(\\\"acrLinkClickCount\\\", (ue.count(\\\"acrLinkClickCount\\\") || 0) + 1); } } ); } }); P.when(\'A\', \'cf\').execute(function(A) { A.declarative(\'acrStarsLink-click-metrics\', \'click\', { \\\"allowLinkDefault\\\" : true }, function(event){ if(window.ue) { ue.count(\\\"acrStarsLinkWithPopoverClickCount\\\", (ue.count(\\\"acrStarsLinkWithPopoverClickCount\\\") || 0) + 1); } }); }); 4.5 out of 5 stars\",\"Best Sellers Rank\":\"#106 in Video Games (See Top 100 in Video Games) #9 in PC Gaming Mice\",\"Date First Available\":\"July 20, 2022\",\"Manufacturer\":\"Redraogon\"}', 'Visit the Redragon Store', 'Video Games > PC > Accessories > Gaming Mice', 4.5, 4245, 1, 'B0B66RHD7B', '[]'),
(42, 'Redragon Gaming Mouse, Wireless Mouse Gaming with 8000 DPI, PC Gaming Mice with Fire Button, RGB Backlit Programmable Ergonomic Mouse Gamer, Rechargeable, 70Hrs for Windows, Mac Gamer, Black', 22.49, NULL, 'https://m.media-amazon.com/images/I/61QY3V6A-NL.jpg', 'About this item 【Fully Programmable Gaming Mouse】- Redragon Wireless Gaming Mouse All buttons can be programmed with the driver and support macro editing. You can remap the buttons, assignment of complex macro functions, change RGB backlit effects, and adjust DPI (250-8000) to fit your different needs. 【High-Precision Gaming Mouse】-The wireless mouse features adjustable DPI(250-8000) and 4 adjustable polling rates ( 125Hz/250Hz/500Hz/1000Hz), you can easily adjust the moving speed, and experience a smooth, fast response and accurate tracking gaming experience. The fire button（✈1 click = 3 clicks) gives you the edge you need during those intense FPS battles. 【Enhance Your Gaming Immersion】UP to 9 RGB light effects can be chosen, you also can adjust backlit effects with 16.8 million color combinations by drivers to create your fancy gaming environment, and match your game style and desktop layout. 【Powerful Battery Life】The rechargeable mouse has a long battery life between 35 hours (RGB on) and 70 hours (RGB off) on a single charge, providing you with nonstopping use. It will auto-sleep after 1 minute of inactivity for power saving. The wireless mouse gaming also can be used while charging. 【Extreme Ergonomics】The mouse gaming with an ergonomic design and Skin-friendly material will provide you with a comfortable grip and soft touch, Effectively relieving fatigue during long-time gaming.  See more product details', 'Amazon', 'https://www.amazon.com/dp/B0B66RHD7B?_encoding=UTF8&psc=1&ref=cm_sw_r_cp_ud_dp_HTPHX074RKX4PNMAH1AR_5&ref_=cm_sw_r_cp_ud_dp_HTPHX074RKX4PNMAH1AR_5&social_share=cm_sw_r_cp_ud_dp_HTPHX074RKX4PNMAH1AR_5&previewDoh=1', 25.00, 18.50, 12.99, 4.50, 83.48, '[\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/31PB5xEjLiL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51Fw9vgOynL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51dbvhjnJJL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51Q+6EvMPqL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/512QCJs0VrL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51s1Mv8bX2L.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/41LymohiRiL.SS40_BG85,85,85_BR-120_PKdp-play-icon-overlay__.jpg\",\"https:\\/\\/images-na.ssl-images-amazon.com\\/images\\/G\\/01\\/x-locale\\/common\\/transparent-pixel.gif\"]', '[]', '2025-04-23 12:28:02', '2025-04-23 12:28:02', '[\"\\u3010Fully Programmable Gaming Mouse\\u3011- Redragon Wireless Gaming Mouse All buttons can be programmed with the driver and support macro editing. You can remap the buttons, assignment of complex macro functions, change RGB backlit effects, and adjust DPI (250-8000) to fit your different needs.\",\"\\u3010High-Precision Gaming Mouse\\u3011-The wireless mouse features adjustable DPI(250-8000) and 4 adjustable polling rates ( 125Hz\\/250Hz\\/500Hz\\/1000Hz), you can easily adjust the moving speed, and experience a smooth, fast response and accurate tracking gaming experience. The fire button\\uff08\\u27081 click = 3 clicks) gives you the edge you need during those intense FPS battles.\",\"\\u3010Enhance Your Gaming Immersion\\u3011UP to 9 RGB light effects can be chosen, you also can adjust backlit effects with 16.8 million color combinations by drivers to create your fancy gaming environment, and match your game style and desktop layout.\",\"\\u3010Powerful Battery Life\\u3011The rechargeable mouse has a long battery life between 35 hours (RGB on) and 70 hours (RGB off) on a single charge, providing you with nonstopping use. It will auto-sleep after 1 minute of inactivity for power saving. The wireless mouse gaming also can be used while charging.\",\"\\u3010Extreme Ergonomics\\u3011The mouse gaming with an ergonomic design and Skin-friendly material will provide you with a comfortable grip and soft touch, Effectively relieving fatigue during long-time gaming.\"]', '{\"Product Dimensions\":\"5.12 x 2.76 x 0.04 inches\",\"Item Weight\":\"4.8 ounces\",\"ASIN\":\"B0B66RHD7B\",\"Item model number\":\"M910-KS\",\"Batteries\":\"1 Lithium Polymer batteries required. (included)\",\"Customer Reviews\":\"4.5 4.5 out of 5 stars 4,252 ratings var dpAcrHasRegisteredArcLinkClickAction; P.when(\'A\', \'ready\').execute(function(A) { if (dpAcrHasRegisteredArcLinkClickAction !== true) { dpAcrHasRegisteredArcLinkClickAction = true; A.declarative( \'acrLink-click-metrics\', \'click\', { \\\"allowLinkDefault\\\": true }, function (event) { if (window.ue) { ue.count(\\\"acrLinkClickCount\\\", (ue.count(\\\"acrLinkClickCount\\\") || 0) + 1); } } ); } }); P.when(\'A\', \'cf\').execute(function(A) { A.declarative(\'acrStarsLink-click-metrics\', \'click\', { \\\"allowLinkDefault\\\" : true }, function(event){ if(window.ue) { ue.count(\\\"acrStarsLinkWithPopoverClickCount\\\", (ue.count(\\\"acrStarsLinkWithPopoverClickCount\\\") || 0) + 1); } }); }); 4.5 out of 5 stars\",\"Best Sellers Rank\":\"#85 in Video Games (See Top 100 in Video Games) #7 in PC Gaming Mice\",\"Date First Available\":\"July 20, 2022\",\"Manufacturer\":\"Redraogon\"}', 'Visit the Redragon Store', 'Video Games > PC > Accessories > Gaming Mice', 4.5, 4252, 1, 'B0B66RHD7B', '[]'),
(43, 'Redragon Gaming Mouse, Wireless Mouse Gaming with 8000 DPI, PC Gaming Mice with Fire Button, RGB Backlit Programmable Ergonomic Mouse Gamer, Rechargeable, 70Hrs for Windows, Mac Gamer, Black', 22.49, NULL, 'https://m.media-amazon.com/images/I/61QY3V6A-NL.jpg', 'About this item 【Fully Programmable Gaming Mouse】- Redragon Wireless Gaming Mouse All buttons can be programmed with the driver and support macro editing. You can remap the buttons, assignment of complex macro functions, change RGB backlit effects, and adjust DPI (250-8000) to fit your different needs. 【High-Precision Gaming Mouse】-The wireless mouse features adjustable DPI(250-8000) and 4 adjustable polling rates ( 125Hz/250Hz/500Hz/1000Hz), you can easily adjust the moving speed, and experience a smooth, fast response and accurate tracking gaming experience. The fire button（✈1 click = 3 clicks) gives you the edge you need during those intense FPS battles. 【Enhance Your Gaming Immersion】UP to 9 RGB light effects can be chosen, you also can adjust backlit effects with 16.8 million color combinations by drivers to create your fancy gaming environment, and match your game style and desktop layout. 【Powerful Battery Life】The rechargeable mouse has a long battery life between 35 hours (RGB on) and 70 hours (RGB off) on a single charge, providing you with nonstopping use. It will auto-sleep after 1 minute of inactivity for power saving. The wireless mouse gaming also can be used while charging. 【Extreme Ergonomics】The mouse gaming with an ergonomic design and Skin-friendly material will provide you with a comfortable grip and soft touch, Effectively relieving fatigue during long-time gaming.  See more product details', 'Amazon', 'https://www.amazon.com/dp/B0B66RHD7B?_encoding=UTF8&psc=1&ref=cm_sw_r_cp_ud_dp_HTPHX074RKX4PNMAH1AR_6&ref_=cm_sw_r_cp_ud_dp_HTPHX074RKX4PNMAH1AR_6&social_share=cm_sw_r_cp_ud_dp_HTPHX074RKX4PNMAH1AR_6&previewDoh=1', 25.00, 18.50, 12.99, 4.50, 83.48, '[\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/31PB5xEjLiL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51Fw9vgOynL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51dbvhjnJJL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51Q+6EvMPqL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/512QCJs0VrL.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/51s1Mv8bX2L.jpg\",\"https:\\/\\/m.media-amazon.com\\/images\\/I\\/41LymohiRiL.SS40_BG85,85,85_BR-120_PKdp-play-icon-overlay__.jpg\",\"https:\\/\\/images-na.ssl-images-amazon.com\\/images\\/G\\/01\\/x-locale\\/common\\/transparent-pixel.gif\"]', '[]', '2025-04-23 12:31:55', '2025-04-23 12:31:55', '[\"\\u3010Fully Programmable Gaming Mouse\\u3011- Redragon Wireless Gaming Mouse All buttons can be programmed with the driver and support macro editing. You can remap the buttons, assignment of complex macro functions, change RGB backlit effects, and adjust DPI (250-8000) to fit your different needs.\",\"\\u3010High-Precision Gaming Mouse\\u3011-The wireless mouse features adjustable DPI(250-8000) and 4 adjustable polling rates ( 125Hz\\/250Hz\\/500Hz\\/1000Hz), you can easily adjust the moving speed, and experience a smooth, fast response and accurate tracking gaming experience. The fire button\\uff08\\u27081 click = 3 clicks) gives you the edge you need during those intense FPS battles.\",\"\\u3010Enhance Your Gaming Immersion\\u3011UP to 9 RGB light effects can be chosen, you also can adjust backlit effects with 16.8 million color combinations by drivers to create your fancy gaming environment, and match your game style and desktop layout.\",\"\\u3010Powerful Battery Life\\u3011The rechargeable mouse has a long battery life between 35 hours (RGB on) and 70 hours (RGB off) on a single charge, providing you with nonstopping use. It will auto-sleep after 1 minute of inactivity for power saving. The wireless mouse gaming also can be used while charging.\",\"\\u3010Extreme Ergonomics\\u3011The mouse gaming with an ergonomic design and Skin-friendly material will provide you with a comfortable grip and soft touch, Effectively relieving fatigue during long-time gaming.\"]', '{\"Product Dimensions\":\"5.12 x 2.76 x 0.04 inches\",\"Item Weight\":\"4.8 ounces\",\"ASIN\":\"B0B66RHD7B\",\"Item model number\":\"M910-KS\",\"Batteries\":\"1 Lithium Polymer batteries required. (included)\",\"Customer Reviews\":\"4.5 4.5 out of 5 stars 4,252 ratings var dpAcrHasRegisteredArcLinkClickAction; P.when(\'A\', \'ready\').execute(function(A) { if (dpAcrHasRegisteredArcLinkClickAction !== true) { dpAcrHasRegisteredArcLinkClickAction = true; A.declarative( \'acrLink-click-metrics\', \'click\', { \\\"allowLinkDefault\\\": true }, function (event) { if (window.ue) { ue.count(\\\"acrLinkClickCount\\\", (ue.count(\\\"acrLinkClickCount\\\") || 0) + 1); } } ); } }); P.when(\'A\', \'cf\').execute(function(A) { A.declarative(\'acrStarsLink-click-metrics\', \'click\', { \\\"allowLinkDefault\\\" : true }, function(event){ if(window.ue) { ue.count(\\\"acrStarsLinkWithPopoverClickCount\\\", (ue.count(\\\"acrStarsLinkWithPopoverClickCount\\\") || 0) + 1); } }); }); 4.5 out of 5 stars\",\"Best Sellers Rank\":\"#85 in Video Games (See Top 100 in Video Games) #7 in PC Gaming Mice\",\"Date First Available\":\"July 20, 2022\",\"Manufacturer\":\"Redraogon\"}', 'Visit the Redragon Store', 'Video Games > PC > Accessories > Gaming Mice', 4.5, 4252, 1, 'B0B66RHD7B', '[]');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('kNl9P8mfTLKUw4pfmVnmVmP5ZZYYOAbklF0sihUB', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMWkzS0VzMTFWNURSNXpIQUxZNkZkUmExUVlscmZiVDdBQ1RmbG02ayI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1745132562),
('v3mHDROrNsNSuI04x8GxZdtcT43Lfy7Fgafb7wHy', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoicm1zVGhkbzJ6QmUzeWl5cDNSdlJPcDBtbDNEQ256V1FLSFMwTVZPdiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1744988914);

-- --------------------------------------------------------

--
-- Table structure for table `stores`
--

CREATE TABLE `stores` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stores`
--

INSERT INTO `stores` (`id`, `name`, `image`, `link`, `created_at`, `updated_at`) VALUES
(1, 'Amazon', 'https://via.placeholder.com/150x150?text=Amazon', 'https://www.amazon.com', '2025-04-23 15:49:47', '2025-04-23 15:49:47'),
(2, 'eBay', 'https://via.placeholder.com/150x150?text=eBay', 'https://www.ebay.com', '2025-04-23 15:49:47', '2025-04-23 15:49:47'),
(3, 'AliExpress', 'https://via.placeholder.com/150x150?text=AliExpress', 'https://www.aliexpress.com', '2025-04-23 15:49:47', '2025-04-23 15:49:47'),
(4, 'Walmart', 'https://via.placeholder.com/150x150?text=Walmart', 'https://www.walmart.com', '2025-04-23 15:49:47', '2025-04-23 15:49:47'),
(5, 'Target', 'https://via.placeholder.com/150x150?text=Target', 'https://www.target.com', '2025-04-23 15:49:47', '2025-04-23 15:49:47'),
(6, 'Best Buy', 'https://via.placeholder.com/150x150?text=Best+Buy', 'https://www.bestbuy.com', '2025-04-23 15:49:47', '2025-04-23 15:49:47'),
(7, 'Newegg', 'https://via.placeholder.com/150x150?text=Newegg', 'https://www.newegg.com', '2025-04-23 15:49:47', '2025-04-23 15:49:47'),
(8, 'Etsy', 'https://via.placeholder.com/150x150?text=Etsy', 'https://www.etsy.com', '2025-04-23 15:49:47', '2025-04-23 15:49:47'),
(9, 'Shein', 'https://via.placeholder.com/150x150?text=Shein', 'https://www.shein.com', '2025-04-23 15:49:47', '2025-04-23 15:49:47'),
(10, 'Zalando', 'https://via.placeholder.com/150x150?text=Zalando', 'https://www.zalando.com', '2025-04-23 15:49:47', '2025-04-23 15:49:47');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `image` varchar(255) DEFAULT 'userimage/default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `image`) VALUES
(1, 'John Doe', 'john@example.com', NULL, '$2y$12$63rHUIGDnpMJRIdY2w/yR.rghS4RR2pTsAal8pUzn2iFYwrPbHHUm', NULL, '2025-04-18 14:42:29', '2025-04-18 14:42:29', 'userimage/default.png'),
(2, 'Ashraf Issa', 'iymanashraf2002@gmail.com', NULL, '$2y$12$ByCQlh/ejTL3kPyavETuK.4.Z3JULy/Q2z4eg.lbeDVxrq9RpI.Cu', NULL, '2025-04-18 23:00:28', '2025-04-18 23:00:28', 'userimage/default.png'),
(6, 'Ashraf Issa', 'bomboclat7522@gmail.com', '2025-04-18 23:34:18', '$2y$12$LCh1QwxIKneA1V3i.sdmuOlTVAgMfyfk1q0ZZSLIWEqDGm2FHyauG', NULL, '2025-04-18 23:33:54', '2025-04-18 23:45:35', 'userimage/default.png'),
(7, 'David Chambuso', 'chambuso@gmail.com', NULL, '$2y$12$I8C6IqnmfMrdhloiUikr7uvLE6OOdJVgh135PSlEwChGEZpS.rgda', NULL, '2025-04-20 08:11:44', '2025-04-20 08:11:44', 'userimage/default.png'),
(8, 'John Doe', 'ibrakombo@gmail.com', NULL, '$2y$12$CSotxerF9VNQcWAiwQVy6.cfyvtlOzVuuAOkr6Q9YEfoLxjl3VP3K', NULL, '2025-04-20 08:23:32', '2025-04-23 13:53:49', 'userimage/1745427229_8.jpeg'),
(9, 'ganja', 'ganja@gmail.com', NULL, '$2y$12$bhkeuEqcpgbo/fqWZLcLy.7i0alpELhaUGIFS6WygeJ2geCJWUmOq', NULL, '2025-04-20 08:25:53', '2025-04-20 08:25:53', 'userimage/default.png'),
(10, 'ganja', 'ibrakombo123@gmail.com', NULL, '$2y$12$2cIbxnrLdf8tcgdERHbS1Obpdfh91AsUkUAl8KSumx0eyxI2glg4C', NULL, '2025-04-20 08:28:10', '2025-04-20 08:28:10', 'userimage/default.png'),
(11, 'asdfg', 'ibrakombo456@gmail.com', NULL, '$2y$12$/JeRu/IKXiP0ALYD20dshuhBx4LBll7j71kSdKHjGa.jHuqbz3b6m', NULL, '2025-04-20 08:28:36', '2025-04-20 08:28:36', 'userimage/default.png'),
(12, 'ganja', 'ibrakombo90@gmail.com', NULL, '$2y$12$6oKVNjA/kwKXytAEm0OAHOlJGuMvkDjGnLVCUGdWtZXxyGnNUlD12', NULL, '2025-04-20 08:32:52', '2025-04-20 08:32:52', 'userimage/default.png'),
(13, 'bunju', 'ibrakombo901@gmail.com', NULL, '$2y$12$em0MP4LcvHj8zm8gLi9iGucsa.1MEP7N/uxP68JlWOVeHBtt0Fq4C', NULL, '2025-04-20 08:43:15', '2025-04-20 08:43:15', 'userimage/default.png'),
(14, 'kongo', 'ibrakombo321@gmail.com', '2025-04-20 08:48:22', '$2y$12$iAdWcSfm0EYIiRLrc2md0uejbp9uystZd1cZSPX7Dravu29f.EKPW', NULL, '2025-04-20 08:47:37', '2025-04-20 08:48:22', 'userimage/default.png'),
(15, 'ganja', 'ibrakombo00@gmail.com', '2025-04-20 09:10:26', '$2y$12$8sBN23JAlUhogswc2TaTO.uzONk2nKYdAfup36AW/.vm1JjdE/iz2', NULL, '2025-04-20 08:52:50', '2025-04-20 09:10:26', 'userimage/default.png'),
(16, 'mwambino', 'mwambino@gmail.com', NULL, '$2y$12$/U0CKFaRBlAB4fYdwiGg4.F6M820w/zYBYRgYuyvB/RbeeU9Lm1/W', NULL, '2025-04-22 08:51:03', '2025-04-22 08:51:03', 'userimage/default.png'),
(17, 'guardiola', 'diola@gmail.com', NULL, '$2y$12$1/ooAkrDzitxbcVQAGhg3OvxbhbIuHb3A8K7U02LWo18yeV7o2PLC', NULL, '2025-04-22 09:04:52', '2025-04-22 09:04:52', 'userimage/default.png'),
(18, 'dinho', 'dinho@gmail.com', NULL, '$2y$12$kr9.sOB6odSN7Bda5soB5uaT8hftJwm9rPgEcuFGCI2.uOzIET5su', NULL, '2025-04-22 09:07:39', '2025-04-22 09:07:39', 'userimage/default.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_product_unique` (`user_id`,`product_id`),
  ADD KEY `carts_product_id_foreign` (`product_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `orders_user_id_foreign` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_order_id_foreign` (`order_id`),
  ADD KEY `order_items_product_id_foreign` (`product_id`);

--
-- Indexes for table `otps`
--
ALTER TABLE `otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `original_url` (`original_url`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `stores`
--
ALTER TABLE `stores`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `otps`
--
ALTER TABLE `otps`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `stores`
--
ALTER TABLE `stores`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `carts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `otps`
--
ALTER TABLE `otps`
  ADD CONSTRAINT `otps_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
