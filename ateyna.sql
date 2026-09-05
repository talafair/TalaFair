-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 05, 2026 at 01:09 PM
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
-- Database: `ateyna`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_event` tinyint(1) NOT NULL DEFAULT 0,
  `allow_guest_scanning` tinyint(1) NOT NULL DEFAULT 0,
  `raffle_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `event_start_at` datetime DEFAULT NULL,
  `event_end_at` datetime DEFAULT NULL,
  `rsvp_due_at` datetime DEFAULT NULL,
  `audiences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`audiences`)),
  `base_points` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `weight_points` decimal(8,2) NOT NULL DEFAULT 1.00,
  `participation_points` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `qr_token` varchar(64) DEFAULT NULL,
  `qr_expires_at` datetime DEFAULT NULL,
  `venue_name` varchar(255) DEFAULT NULL,
  `venue_lat` decimal(10,7) DEFAULT NULL,
  `venue_lng` decimal(10,7) DEFAULT NULL,
  `geofence_radius` int(10) UNSIGNED NOT NULL DEFAULT 300,
  `banner_path` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `category`, `body`, `is_featured`, `is_event`, `allow_guest_scanning`, `raffle_enabled`, `event_start_at`, `event_end_at`, `rsvp_due_at`, `audiences`, `base_points`, `weight_points`, `participation_points`, `qr_token`, `qr_expires_at`, `venue_name`, `venue_lat`, `venue_lng`, `geofence_radius`, `banner_path`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(5, 'Scheduled Maintenance Notice', 'maintenance', 'We perform routine maintenance to keep TalaFair fast and reliable. Any downtime will be announced here in advance ??? your points are always safe.', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, 1.00, 0, NULL, NULL, NULL, NULL, NULL, 300, NULL, NULL, NULL, '2026-08-05 23:07:55', '2026-08-05 23:07:55'),
(8, 'rene death anniversarry', 'updates', 'august 10,2026 1pm @nabua cathedral church', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, 1.00, 0, NULL, NULL, NULL, NULL, NULL, 300, NULL, NULL, NULL, '2026-08-05 23:30:58', '2026-08-05 23:30:58'),
(10, 'Winners', 'rewards', 'totoy brown won 1k or rice', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, 1.00, 0, NULL, NULL, NULL, NULL, NULL, 300, NULL, NULL, NULL, '2026-08-05 23:34:34', '2026-08-05 23:34:34'),
(11, 'meeting', 'maintenance', 'fixing the system', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, 1.00, 0, NULL, NULL, NULL, NULL, NULL, 300, NULL, NULL, NULL, '2026-08-06 03:35:38', '2026-08-06 03:35:38'),
(12, 'Testing', 'events', 'hi', 1, 1, 0, 0, '2026-08-25 18:14:00', '2026-08-25 19:14:00', '2026-08-25 17:35:00', '[\"public\"]', 100, 0.00, 0, 'efd1aeea-6ee1-47a3-9151-b279139e8221', '2026-08-25 19:14:00', NULL, 14.2700000, 121.0500000, 300, 'banners/dkgJ8tzzoRZb013Q6pqZAVPlRTBsxObsBYRKBpDJ.png', NULL, 16, '2026-08-13 22:59:39', '2026-08-25 01:17:19'),
(13, 'Meet and Greet', 'events', 'Meet and Greet with your new Youth Officers of Barangay San Jose.', 1, 1, 0, 1, '2026-08-18 21:30:00', '2026-08-18 22:00:00', '2026-08-18 21:00:00', '[\"youth\"]', 100, 0.00, 0, 'd76d5266-df1b-4e2f-8e0b-1155c94016c6', '2026-08-18 22:00:00', NULL, 13.4262080, 123.3987476, 300, 'banners/4eDYkHyEyEEhiQkGoIk5fpovSqHuvm9ZUvxwk7qm.png', 16, 16, '2026-08-18 04:23:50', '2026-08-26 07:32:11');

-- --------------------------------------------------------

--
-- Table structure for table `announcement_participations`
--

CREATE TABLE `announcement_participations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `announcement_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `scanned_by` bigint(20) UNSIGNED NOT NULL,
  `scanned_at` datetime NOT NULL,
  `points_awarded` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendances`
--

CREATE TABLE `attendances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `announcement_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `scanned_at` datetime NOT NULL,
  `is_early` tinyint(1) NOT NULL DEFAULT 0,
  `pre_registered` tinyint(1) NOT NULL DEFAULT 0,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `distance_m` int(10) UNSIGNED DEFAULT NULL,
  `points_awarded` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `actor_unique_id` varchar(24) DEFAULT NULL,
  `action` varchar(20) NOT NULL,
  `auditable_type` varchar(255) NOT NULL,
  `auditable_id` bigint(20) UNSIGNED NOT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `duration_ms` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `actor_unique_id`, `action`, `auditable_type`, `auditable_id`, `old_values`, `new_values`, `ip_address`, `duration_ms`, `created_at`, `updated_at`) VALUES
(1, NULL, NULL, 'created', 'App\\Models\\User', 18, NULL, '{\"first_name\":\"Ynah Marie\",\"middle_name\":\"Rodriguez\",\"last_name\":\"Calibara\",\"suffix\":null,\"name\":\"Ynah Marie Rodriguez Calibara\",\"gender\":\"female\",\"gender_other\":null,\"birthdate\":\"2002-09-14 00:00:00\",\"contact_number\":\"09054146272\",\"house_no\":\"242\",\"street\":\"Aventurina\",\"zone\":\"2\",\"barangay\":\"San Jose\",\"city\":\"Iriga City\",\"province\":\"Camarines Sur\",\"country\":\"Philippines\",\"postal_code\":\"4431\",\"is_head_of_family\":false,\"head_of_family_id\":null,\"head_of_family_name\":\"Rosita Apa Rodriguez\",\"username\":\"ynahng_eiram\",\"email\":\"yncalibara@my.cspc.edu.ph\",\"role\":\"resident\",\"updated_at\":\"2026-08-18 12:14:26\",\"created_at\":\"2026-08-18 12:14:26\",\"id\":18}', '127.0.0.1', NULL, '2026-08-18 04:14:26', '2026-08-18 04:14:26'),
(2, NULL, NULL, 'updated', 'App\\Models\\User', 18, NULL, '{\"unique_id\":\"Z2-26000000001\"}', '127.0.0.1', NULL, '2026-08-18 04:14:26', '2026-08-18 04:14:26'),
(3, 16, NULL, 'created', 'App\\Models\\Announcement', 13, NULL, '{\"title\":\"Meet and Greet\",\"category\":\"events\",\"body\":\"Meet and Greet with your new Youth Officers of Barangay San Jose.\",\"is_featured\":true,\"is_event\":true,\"event_start_at\":\"2026-08-18 21:30:00\",\"event_end_at\":\"2026-08-18 22:00:00\",\"rsvp_due_at\":\"2026-08-18 21:00:00\",\"base_points\":\"100\",\"weight_points\":\"10\",\"venue_name\":null,\"venue_lat\":\"13.426208\",\"venue_lng\":\"123.3987476\",\"geofence_radius\":\"300\",\"audiences\":\"[\\\"youth\\\"]\",\"banner_path\":\"banners\\/4eDYkHyEyEEhiQkGoIk5fpovSqHuvm9ZUvxwk7qm.png\",\"qr_token\":\"d76d5266-df1b-4e2f-8e0b-1155c94016c6\",\"qr_expires_at\":\"2026-08-18 22:00:00\",\"created_by\":16,\"updated_by\":16,\"updated_at\":\"2026-08-18 12:23:50\",\"created_at\":\"2026-08-18 12:23:50\",\"id\":13}', '127.0.0.1', NULL, '2026-08-18 04:23:50', '2026-08-18 04:23:50'),
(4, 18, 'Z2-26000000001', 'created', 'App\\Models\\EventRsvp', 1, NULL, '{\"announcement_id\":13,\"user_id\":18,\"status\":\"attending\",\"reason\":null,\"responded_at\":\"2026-08-18 12:26:31\",\"updated_by\":18,\"updated_at\":\"2026-08-18 12:26:31\",\"created_at\":\"2026-08-18 12:26:31\",\"id\":1}', '127.0.0.1', NULL, '2026-08-18 04:26:32', '2026-08-18 04:26:32'),
(5, 16, NULL, 'updated', 'App\\Models\\User', 16, '{\"name\":\"admin\",\"first_name\":null,\"middle_name\":null,\"last_name\":null,\"gender\":null,\"birthdate\":null,\"house_no\":null,\"street\":null,\"zone\":null}', '{\"name\":\"Admin Test Account\",\"first_name\":\"Admin\",\"middle_name\":\"Test\",\"last_name\":\"Account\",\"gender\":\"female\",\"birthdate\":\"2002-08-01 00:00:00\",\"house_no\":\"242\",\"street\":\"Aventurina\",\"zone\":\"2\"}', '127.0.0.1', NULL, '2026-08-19 02:10:17', '2026-08-19 02:10:17'),
(6, 16, NULL, 'updated', 'App\\Models\\User', 16, '{\"avatar_path\":null}', '{\"avatar_path\":\"avatars\\/p1KHt7gQQ3D7OqtLmIXINKVTx0mBN0f6kQEFd993.jpg\"}', '127.0.0.1', NULL, '2026-08-19 02:11:34', '2026-08-19 02:11:34'),
(7, 16, NULL, 'created', 'App\\Models\\Badge', 1, NULL, '{\"name\":\"Early Birdy Award\",\"description\":\"Be the first to scan on any event\",\"points_required\":\"0\",\"image_path\":\"badges\\/vfYHkhONZ1hJncLmfJH2HZvKAbeghtVuJHYL2IIr.webp\",\"created_by\":16,\"updated_by\":16,\"updated_at\":\"2026-08-19 10:27:42\",\"created_at\":\"2026-08-19 10:27:42\",\"id\":1}', '127.0.0.1', NULL, '2026-08-19 02:27:42', '2026-08-19 02:27:42'),
(8, 16, NULL, 'updated', 'App\\Models\\User', 16, NULL, NULL, '127.0.0.1', NULL, '2026-08-23 03:39:18', '2026-08-23 03:39:18'),
(9, 16, NULL, 'updated', 'App\\Models\\User', 16, '{\"avatar_path\":\"avatars\\/p1KHt7gQQ3D7OqtLmIXINKVTx0mBN0f6kQEFd993.jpg\"}', '{\"avatar_path\":\"avatars\\/Yukq0tAYsQ3IyXEAg7YbwR3R91RGUjEnl2YLfhZE.jpg\"}', '127.0.0.1', NULL, '2026-08-23 04:08:44', '2026-08-23 04:08:44'),
(10, 18, 'Z2-26000000001', 'updated', 'App\\Models\\User', 18, '{\"avatar_path\":null}', '{\"avatar_path\":\"avatars\\/gf8FTgV0IFuGeSu7ElBuFxokLd3GWgeSwElPgN07.jpg\"}', '127.0.0.1', NULL, '2026-08-23 04:19:28', '2026-08-23 04:19:28'),
(11, 16, NULL, 'updated', 'App\\Models\\User', 18, '{\"is_verified\":false}', '{\"is_verified\":true}', '127.0.0.1', NULL, '2026-08-23 16:37:32', '2026-08-23 16:37:32'),
(12, 16, NULL, 'updated', 'App\\Models\\Announcement', 12, '{\"is_event\":false,\"event_start_at\":null,\"event_end_at\":null,\"rsvp_due_at\":null,\"audiences\":null,\"base_points\":0,\"weight_points\":1,\"qr_token\":null,\"qr_expires_at\":null,\"venue_lat\":null,\"venue_lng\":null,\"updated_by\":null}', '{\"is_event\":true,\"event_start_at\":\"2026-08-25 18:14:00\",\"event_end_at\":\"2026-08-25 19:14:00\",\"rsvp_due_at\":\"2026-08-25 17:35:00\",\"audiences\":\"[\\\"public\\\"]\",\"base_points\":100,\"weight_points\":0,\"qr_token\":\"efd1aeea-6ee1-47a3-9151-b279139e8221\",\"qr_expires_at\":\"2026-08-25 19:14:00\",\"venue_lat\":\"14.2700000\",\"venue_lng\":\"121.0500000\",\"updated_by\":16}', '127.0.0.1', NULL, '2026-08-25 01:14:17', '2026-08-25 01:14:17'),
(13, 16, NULL, 'created', 'App\\Models\\EventRsvp', 2, NULL, '{\"announcement_id\":12,\"user_id\":16,\"status\":\"attending\",\"reason\":null,\"responded_at\":\"2026-08-25 09:14:40\",\"updated_by\":16,\"updated_at\":\"2026-08-25 09:14:40\",\"created_at\":\"2026-08-25 09:14:40\",\"id\":2}', '127.0.0.1', NULL, '2026-08-25 01:14:40', '2026-08-25 01:14:40'),
(14, 16, NULL, 'updated', 'App\\Models\\Announcement', 12, '{\"banner_path\":null}', '{\"banner_path\":\"banners\\/dkgJ8tzzoRZb013Q6pqZAVPlRTBsxObsBYRKBpDJ.png\"}', '127.0.0.1', NULL, '2026-08-25 01:17:19', '2026-08-25 01:17:19'),
(15, 18, 'Z2-26000000001', 'created', 'App\\Models\\EventRsvp', 3, NULL, '{\"announcement_id\":12,\"user_id\":18,\"status\":\"attending\",\"reason\":null,\"responded_at\":\"2026-08-25 09:23:07\",\"updated_by\":18,\"updated_at\":\"2026-08-25 09:23:07\",\"created_at\":\"2026-08-25 09:23:07\",\"id\":3}', '127.0.0.1', NULL, '2026-08-25 01:23:07', '2026-08-25 01:23:07'),
(16, 16, NULL, 'created', 'App\\Models\\TriviaTheme', 1, NULL, '{\"title\":\"Barangay Trivia\",\"base_points\":\"100\",\"due_at\":\"2026-08-26 15:00:00\",\"questions\":\"[{\\\"question\\\":\\\"Who is the Barangay Captain in our barangay?\\\",\\\"answer\\\":\\\"Kap. Darcy DV. Go\\\",\\\"choices\\\":[\\\"Kap. Darcy DV. Go\\\",\\\"Kapitan Tiago\\\",\\\"Kap. Che-Che De Jesus\\\"]},{\\\"question\\\":\\\"Who is the patron saint of our barangay?\\\",\\\"answer\\\":\\\"Saint Joseph\\\",\\\"choices\\\":[\\\"Saint Joseph\\\",\\\"Saint Anthony\\\",\\\"Saint Peter\\\"]}]\",\"created_by\":16,\"updated_at\":\"2026-08-26 06:53:27\",\"created_at\":\"2026-08-26 06:53:27\",\"id\":1}', '127.0.0.1', NULL, '2026-08-25 22:53:27', '2026-08-25 22:53:27'),
(17, 16, NULL, 'created', 'App\\Models\\TriviaTheme', 2, NULL, '{\"title\":\"Barangay History\",\"base_points\":\"100\",\"due_at\":\"2026-08-26 15:30:00\",\"questions\":\"[{\\\"question\\\":\\\"Who is the patron saint of our barangay?\\\",\\\"answer\\\":\\\"Saint Joseph\\\",\\\"choices\\\":[\\\"Saint Joseph\\\",\\\"Saint Anthony\\\",\\\"Saint Peter\\\"]}]\",\"created_by\":16,\"updated_at\":\"2026-08-26 06:58:19\",\"created_at\":\"2026-08-26 06:58:19\",\"id\":2}', '127.0.0.1', NULL, '2026-08-25 22:58:19', '2026-08-25 22:58:19'),
(18, 16, NULL, 'updated', 'App\\Models\\User', 16, '{\"points\":0}', '{\"points\":50}', '127.0.0.1', NULL, '2026-08-25 22:58:31', '2026-08-25 22:58:31'),
(19, 18, 'Z2-26000000001', 'updated', 'App\\Models\\User', 18, '{\"points\":0}', '{\"points\":30}', '127.0.0.1', NULL, '2026-08-25 23:25:08', '2026-08-25 23:25:08'),
(20, 18, 'Z2-26000000001', 'updated', 'App\\Models\\User', 18, '{\"points\":30}', '{\"points\":106}', '127.0.0.1', NULL, '2026-08-25 23:26:13', '2026-08-25 23:26:13'),
(21, 18, 'Z2-26000000001', 'updated', 'App\\Models\\User', 18, '{\"points\":106}', '{\"points\":126}', '127.0.0.1', NULL, '2026-08-25 23:27:25', '2026-08-25 23:27:25'),
(22, 16, NULL, 'updated', 'App\\Models\\Badge', 1, '{\"description\":\"Be the first to scan on any event\",\"rarity\":\"common\",\"condition_key\":\"points\",\"limited_total\":null}', '{\"description\":\"Be one of the first to scan on any event\",\"rarity\":\"rare\",\"condition_key\":\"early_arrival\",\"limited_total\":\"20\"}', '127.0.0.1', NULL, '2026-08-26 05:45:12', '2026-08-26 05:45:12'),
(23, 16, NULL, 'created', 'App\\Models\\Badge', 2, NULL, '{\"name\":\"Number One Girl\",\"description\":null,\"points_required\":\"0\",\"category\":\"participation\",\"rarity\":\"common\",\"award_method\":\"official\",\"condition_key\":\"manual\",\"limited_total\":\"1\",\"announcement_id\":null,\"image_path\":\"badges\\/HRkXhgdHWYd1KyBupU74H6PpSgAviV6nCSEQfcCc.png\",\"created_by\":16,\"updated_by\":16,\"updated_at\":\"2026-08-26 13:55:52\",\"created_at\":\"2026-08-26 13:55:52\",\"id\":2}', '127.0.0.1', NULL, '2026-08-26 05:55:52', '2026-08-26 05:55:52'),
(24, 16, NULL, 'updated', 'App\\Models\\Badge', 2, '{\"rarity\":\"common\"}', '{\"rarity\":\"legendary\"}', '127.0.0.1', NULL, '2026-08-26 05:56:04', '2026-08-26 05:56:04'),
(25, 16, NULL, 'updated', 'App\\Models\\Announcement', 13, '{\"raffle_enabled\":false,\"weight_points\":10}', '{\"raffle_enabled\":true,\"weight_points\":0}', '127.0.0.1', NULL, '2026-08-26 07:32:11', '2026-08-26 07:32:11'),
(26, 18, 'Z2-26000000001', 'updated', 'App\\Models\\User', 18, NULL, NULL, '127.0.0.1', NULL, '2026-08-26 20:42:40', '2026-08-26 20:42:40'),
(27, 18, 'Z2-26000000001', 'updated', 'App\\Models\\User', 18, '{\"points\":126}', '{\"points\":141}', '127.0.0.1', NULL, '2026-08-26 21:01:03', '2026-08-26 21:01:03'),
(28, 18, 'Z2-26000000001', 'updated', 'App\\Models\\User', 18, NULL, NULL, '127.0.0.1', NULL, '2026-08-26 21:42:10', '2026-08-26 21:42:10'),
(29, 16, NULL, 'updated', 'App\\Models\\User', 16, NULL, NULL, '127.0.0.1', NULL, '2026-08-27 17:02:00', '2026-08-27 17:02:00'),
(30, 16, NULL, 'login', 'App\\Models\\User', 16, NULL, NULL, '127.0.0.1', NULL, '2026-08-27 17:02:00', '2026-08-27 17:02:00'),
(31, 16, NULL, 'updated', 'App\\Models\\User', 16, '{\"points\":50}', '{\"points\":64}', '127.0.0.1', NULL, '2026-08-27 17:39:15', '2026-08-27 17:39:15'),
(32, 16, NULL, 'updated', 'App\\Models\\User', 16, '{\"points\":64}', '{\"points\":146}', '127.0.0.1', NULL, '2026-08-27 17:39:59', '2026-08-27 17:39:59'),
(33, 16, NULL, 'created', 'App\\Models\\TriviaTheme', 3, NULL, '{\"title\":\"Barangay Trivia\",\"base_points\":\"100\",\"due_at\":\"2026-08-28 11:00:00\",\"questions\":\"[{\\\"question\\\":\\\"Who is the current Barangay Captain of our barangay?\\\",\\\"answer\\\":\\\"Kap. Darcy DV. Go\\\",\\\"choices\\\":[\\\"Kap. Darcy DV. Go\\\",\\\"Kapitan Tiago\\\",\\\"Kap. Che-Che De Jesus\\\"]}]\",\"created_by\":16,\"updated_at\":\"2026-08-28 02:09:11\",\"created_at\":\"2026-08-28 02:09:11\",\"id\":3}', '127.0.0.1', NULL, '2026-08-27 18:09:11', '2026-08-27 18:09:11'),
(34, 16, NULL, 'created', 'App\\Models\\TriviaAnswer', 3, NULL, '{\"trivia_theme_id\":3,\"user_id\":16,\"question_index\":0,\"is_correct\":true,\"answered_at\":\"2026-08-28 02:09:22\",\"id\":3}', '127.0.0.1', NULL, '2026-08-27 18:09:22', '2026-08-27 18:09:22'),
(35, 16, NULL, 'updated', 'App\\Models\\TriviaAnswer', 3, NULL, '{\"rank\":1,\"points_awarded\":150}', '127.0.0.1', NULL, '2026-08-27 18:09:22', '2026-08-27 18:09:22'),
(36, 16, NULL, 'updated', 'App\\Models\\User', 16, '{\"points\":146}', '{\"points\":296}', '127.0.0.1', NULL, '2026-08-27 18:09:22', '2026-08-27 18:09:22'),
(37, 16, NULL, 'updated', 'App\\Models\\User', 16, NULL, NULL, '127.0.0.1', NULL, '2026-08-27 18:10:10', '2026-08-27 18:10:10'),
(38, 18, 'Z2-26000000001', 'login', 'App\\Models\\User', 18, NULL, NULL, '127.0.0.1', NULL, '2026-08-27 18:10:25', '2026-08-27 18:10:25'),
(39, 18, 'Z2-26000000001', 'created', 'App\\Models\\TriviaAnswer', 4, NULL, '{\"trivia_theme_id\":3,\"user_id\":18,\"question_index\":0,\"is_correct\":true,\"answered_at\":\"2026-08-28 02:10:38\",\"id\":4}', '127.0.0.1', NULL, '2026-08-27 18:10:38', '2026-08-27 18:10:38'),
(40, 18, 'Z2-26000000001', 'updated', 'App\\Models\\TriviaAnswer', 4, NULL, '{\"rank\":2,\"points_awarded\":130}', '127.0.0.1', NULL, '2026-08-27 18:10:38', '2026-08-27 18:10:38'),
(41, 18, 'Z2-26000000001', 'updated', 'App\\Models\\User', 18, '{\"points\":141}', '{\"points\":271}', '127.0.0.1', NULL, '2026-08-27 18:10:38', '2026-08-27 18:10:38'),
(42, 18, 'Z2-26000000001', 'created', 'App\\Models\\GameRun', 7, NULL, '{\"user_id\":18,\"game\":\"catch-star\",\"played_on\":\"2026-08-28 00:00:00\",\"stage_scores\":\"[15,4,0]\",\"total_score\":23,\"updated_at\":\"2026-08-28 02:12:24\",\"created_at\":\"2026-08-28 02:12:24\",\"id\":7}', '127.0.0.1', NULL, '2026-08-27 18:12:24', '2026-08-27 18:12:24'),
(43, 18, 'Z2-26000000001', 'updated', 'App\\Models\\User', 18, '{\"points\":271}', '{\"points\":294}', '127.0.0.1', NULL, '2026-08-27 18:12:24', '2026-08-27 18:12:24'),
(44, 18, 'Z2-26000000001', 'created', 'App\\Models\\GameRun', 8, NULL, '{\"user_id\":18,\"game\":\"memory-test\",\"played_on\":\"2026-08-28 00:00:00\",\"stage_scores\":\"[7,11,18]\",\"total_score\":83,\"updated_at\":\"2026-08-28 02:13:11\",\"created_at\":\"2026-08-28 02:13:11\",\"id\":8}', '127.0.0.1', NULL, '2026-08-27 18:13:11', '2026-08-27 18:13:11'),
(45, 18, 'Z2-26000000001', 'updated', 'App\\Models\\User', 18, '{\"points\":294}', '{\"points\":377}', '127.0.0.1', NULL, '2026-08-27 18:13:11', '2026-08-27 18:13:11'),
(46, 18, 'Z2-26000000001', 'updated', 'App\\Models\\User', 18, '{\"points\":377}', '{\"points\":402}', '127.0.0.1', NULL, '2026-08-27 18:19:40', '2026-08-27 18:19:40'),
(47, 18, 'Z2-26000000001', 'updated', 'App\\Models\\User', 18, NULL, NULL, '127.0.0.1', NULL, '2026-08-27 18:26:16', '2026-08-27 18:26:16'),
(48, 16, NULL, 'login', 'App\\Models\\User', 16, NULL, NULL, '127.0.0.1', NULL, '2026-08-27 18:26:24', '2026-08-27 18:26:24'),
(49, NULL, NULL, 'created', 'App\\Models\\Badge', 3, NULL, '{\"name\":\"First Step\",\"description\":\"Attended your first event.\",\"category\":\"milestones\",\"rarity\":\"common\",\"condition_key\":\"first_event\",\"points_required\":0,\"award_method\":\"automatic\",\"updated_at\":\"2026-08-28 03:35:58\",\"created_at\":\"2026-08-28 03:35:58\",\"id\":3}', '127.0.0.1', NULL, '2026-08-27 19:35:58', '2026-08-27 19:35:58'),
(50, NULL, NULL, 'created', 'App\\Models\\Badge', 4, NULL, '{\"name\":\"Event Attendee\",\"description\":\"Successfully attended an event.\",\"category\":\"attendance\",\"rarity\":\"common\",\"condition_key\":\"event_attendance\",\"points_required\":0,\"award_method\":\"automatic\",\"updated_at\":\"2026-08-28 03:35:58\",\"created_at\":\"2026-08-28 03:35:58\",\"id\":4}', '127.0.0.1', NULL, '2026-08-27 19:35:58', '2026-08-27 19:35:58'),
(51, NULL, NULL, 'created', 'App\\Models\\Badge', 5, NULL, '{\"name\":\"Early Bird\",\"description\":\"One of the first 20 verified attendees of an event.\",\"category\":\"attendance\",\"rarity\":\"rare\",\"condition_key\":\"early_bird\",\"limited_total\":20,\"points_required\":0,\"award_method\":\"automatic\",\"updated_at\":\"2026-08-28 03:35:58\",\"created_at\":\"2026-08-28 03:35:58\",\"id\":5}', '127.0.0.1', NULL, '2026-08-27 19:35:58', '2026-08-27 19:35:58'),
(52, NULL, NULL, 'created', 'App\\Models\\Badge', 6, NULL, '{\"name\":\"Regular\",\"description\":\"Attended 5 events.\",\"category\":\"milestones\",\"rarity\":\"uncommon\",\"condition_key\":\"attendance_count\",\"points_required\":5,\"award_method\":\"automatic\",\"updated_at\":\"2026-08-28 03:35:58\",\"created_at\":\"2026-08-28 03:35:58\",\"id\":6}', '127.0.0.1', NULL, '2026-08-27 19:35:58', '2026-08-27 19:35:58'),
(53, NULL, NULL, 'created', 'App\\Models\\Badge', 7, NULL, '{\"name\":\"Dedicated\",\"description\":\"Attended 10 events.\",\"category\":\"milestones\",\"rarity\":\"rare\",\"condition_key\":\"attendance_count\",\"points_required\":10,\"award_method\":\"automatic\",\"updated_at\":\"2026-08-28 03:35:58\",\"created_at\":\"2026-08-28 03:35:58\",\"id\":7}', '127.0.0.1', NULL, '2026-08-27 19:35:58', '2026-08-27 19:35:58'),
(54, NULL, NULL, 'created', 'App\\Models\\Badge', 8, NULL, '{\"name\":\"TalaFair Veteran\",\"description\":\"Attended 25 events.\",\"category\":\"milestones\",\"rarity\":\"epic\",\"condition_key\":\"attendance_count\",\"points_required\":25,\"award_method\":\"automatic\",\"updated_at\":\"2026-08-28 03:35:58\",\"created_at\":\"2026-08-28 03:35:58\",\"id\":8}', '127.0.0.1', NULL, '2026-08-27 19:35:58', '2026-08-27 19:35:58'),
(55, NULL, NULL, 'created', 'App\\Models\\Badge', 9, NULL, '{\"name\":\"TalaFair Legend\",\"description\":\"Attended 50 events.\",\"category\":\"milestones\",\"rarity\":\"legendary\",\"condition_key\":\"attendance_count\",\"points_required\":50,\"award_method\":\"automatic\",\"updated_at\":\"2026-08-28 03:35:58\",\"created_at\":\"2026-08-28 03:35:58\",\"id\":9}', '127.0.0.1', NULL, '2026-08-27 19:35:58', '2026-08-27 19:35:58'),
(56, NULL, NULL, 'created', 'App\\Models\\Badge', 10, NULL, '{\"name\":\"Getting Started\",\"description\":\"Attended 3 consecutive events.\",\"category\":\"streaks\",\"rarity\":\"uncommon\",\"condition_key\":\"streak\",\"points_required\":3,\"award_method\":\"automatic\",\"updated_at\":\"2026-08-28 03:35:58\",\"created_at\":\"2026-08-28 03:35:58\",\"id\":10}', '127.0.0.1', NULL, '2026-08-27 19:35:58', '2026-08-27 19:35:58'),
(57, NULL, NULL, 'created', 'App\\Models\\Badge', 11, NULL, '{\"name\":\"On a Roll\",\"description\":\"Attended 5 consecutive events.\",\"category\":\"streaks\",\"rarity\":\"rare\",\"condition_key\":\"streak\",\"points_required\":5,\"award_method\":\"automatic\",\"updated_at\":\"2026-08-28 03:35:58\",\"created_at\":\"2026-08-28 03:35:58\",\"id\":11}', '127.0.0.1', NULL, '2026-08-27 19:35:58', '2026-08-27 19:35:58'),
(58, NULL, NULL, 'created', 'App\\Models\\Badge', 12, NULL, '{\"name\":\"Unstoppable\",\"description\":\"Attended 10 consecutive events.\",\"category\":\"streaks\",\"rarity\":\"epic\",\"condition_key\":\"streak\",\"points_required\":10,\"award_method\":\"automatic\",\"updated_at\":\"2026-08-28 03:35:58\",\"created_at\":\"2026-08-28 03:35:58\",\"id\":12}', '127.0.0.1', NULL, '2026-08-27 19:35:58', '2026-08-27 19:35:58'),
(59, 16, NULL, 'updated', 'App\\Models\\Badge', 5, '{\"image_path\":null,\"updated_by\":null}', '{\"image_path\":\"badges\\/SsYymcbTRuuPRV7FPFLNLqiOx2HLxPUFDL8E0OYK.webp\",\"updated_by\":16}', '127.0.0.1', NULL, '2026-08-27 20:57:03', '2026-08-27 20:57:03'),
(60, 16, NULL, 'deleted', 'App\\Models\\Badge', 5, '{\"id\":5,\"name\":\"Early Bird\",\"description\":\"One of the first 20 verified attendees of an event.\",\"category\":\"attendance\",\"rarity\":\"rare\",\"award_method\":\"automatic\",\"condition_key\":\"early_bird\",\"limited_total\":20,\"announcement_id\":null,\"image_path\":\"badges\\/SsYymcbTRuuPRV7FPFLNLqiOx2HLxPUFDL8E0OYK.webp\",\"points_required\":0,\"created_by\":null,\"updated_by\":16,\"created_at\":\"2026-08-28T03:35:58.000000Z\",\"updated_at\":\"2026-08-28T04:57:03.000000Z\"}', NULL, '127.0.0.1', NULL, '2026-08-27 21:04:38', '2026-08-27 21:04:38'),
(61, 16, NULL, 'created', 'App\\Models\\TriviaTheme', 4, NULL, '{\"title\":\"General Knowledge\",\"base_points\":\"100\",\"due_at\":\"2026-08-28 13:15:00\",\"questions\":\"[{\\\"question\\\":\\\"What is the oldest City in the POhilippines?\\\",\\\"answer\\\":\\\"Cebu\\\",\\\"choices\\\":[\\\"Cebu\\\",\\\"Manila\\\",\\\"Iriga City\\\"]}]\",\"created_by\":16,\"updated_at\":\"2026-08-28 05:10:30\",\"created_at\":\"2026-08-28 05:10:30\",\"id\":4}', '127.0.0.1', NULL, '2026-08-27 21:10:30', '2026-08-27 21:10:30'),
(62, 16, NULL, 'created', 'App\\Models\\TriviaAnswer', 5, NULL, '{\"trivia_theme_id\":4,\"user_id\":16,\"question_index\":0,\"is_correct\":true,\"answered_at\":\"2026-08-28 05:15:19\",\"id\":5}', '127.0.0.1', NULL, '2026-08-27 21:15:19', '2026-08-27 21:15:19'),
(63, 16, NULL, 'updated', 'App\\Models\\TriviaAnswer', 5, NULL, '{\"rank\":1,\"points_awarded\":150}', '127.0.0.1', NULL, '2026-08-27 21:15:19', '2026-08-27 21:15:19'),
(64, 16, NULL, 'updated', 'App\\Models\\User', 16, '{\"points\":296}', '{\"points\":446}', '127.0.0.1', NULL, '2026-08-27 21:15:19', '2026-08-27 21:15:19'),
(65, 16, NULL, 'updated', 'App\\Models\\User', 16, NULL, NULL, '127.0.0.1', NULL, '2026-08-27 21:44:44', '2026-08-27 21:44:44'),
(66, 18, 'Z2-26000000001', 'login', 'App\\Models\\User', 18, NULL, NULL, '127.0.0.1', NULL, '2026-08-27 21:44:55', '2026-08-27 21:44:55'),
(67, 18, 'Z2-26000000001', 'created', 'App\\Models\\TriviaAnswer', 6, NULL, '{\"trivia_theme_id\":4,\"user_id\":18,\"question_index\":0,\"is_correct\":true,\"answered_at\":\"2026-08-28 05:45:29\",\"id\":6}', '127.0.0.1', NULL, '2026-08-27 21:45:29', '2026-08-27 21:45:29'),
(68, 18, 'Z2-26000000001', 'updated', 'App\\Models\\TriviaAnswer', 6, NULL, '{\"rank\":2,\"points_awarded\":130}', '127.0.0.1', NULL, '2026-08-27 21:45:29', '2026-08-27 21:45:29'),
(69, 18, 'Z2-26000000001', 'updated', 'App\\Models\\User', 18, '{\"points\":402}', '{\"points\":532}', '127.0.0.1', NULL, '2026-08-27 21:45:29', '2026-08-27 21:45:29'),
(70, 18, 'Z2-26000000001', 'updated', 'App\\Models\\User', 18, NULL, NULL, '127.0.0.1', NULL, '2026-08-27 21:45:50', '2026-08-27 21:45:50'),
(71, 15, NULL, 'login', 'App\\Models\\User', 15, NULL, NULL, '127.0.0.1', NULL, '2026-08-27 21:46:01', '2026-08-27 21:46:01'),
(72, 15, NULL, 'created', 'App\\Models\\TriviaAnswer', 7, NULL, '{\"trivia_theme_id\":4,\"user_id\":15,\"question_index\":0,\"is_correct\":true,\"answered_at\":\"2026-08-28 05:46:12\",\"id\":7}', '127.0.0.1', NULL, '2026-08-27 21:46:12', '2026-08-27 21:46:12'),
(73, 15, NULL, 'updated', 'App\\Models\\TriviaAnswer', 7, NULL, '{\"rank\":3,\"points_awarded\":120}', '127.0.0.1', NULL, '2026-08-27 21:46:12', '2026-08-27 21:46:12'),
(74, 15, NULL, 'updated', 'App\\Models\\User', 15, '{\"points\":0}', '{\"points\":120}', '127.0.0.1', NULL, '2026-08-27 21:46:12', '2026-08-27 21:46:12'),
(75, 15, NULL, 'created', 'App\\Models\\GameRun', 9, NULL, '{\"user_id\":15,\"game\":\"catch-star\",\"played_on\":\"2026-08-28 00:00:00\",\"stage_scores\":\"[9,3,0]\",\"total_score\":15,\"updated_at\":\"2026-08-28 05:48:04\",\"created_at\":\"2026-08-28 05:48:04\",\"id\":9}', '127.0.0.1', NULL, '2026-08-27 21:48:04', '2026-08-27 21:48:04'),
(76, 15, NULL, 'updated', 'App\\Models\\User', 15, '{\"points\":120}', '{\"points\":135}', '127.0.0.1', NULL, '2026-08-27 21:48:04', '2026-08-27 21:48:04'),
(77, 15, NULL, 'created', 'App\\Models\\GameRun', 10, NULL, '{\"user_id\":15,\"game\":\"memory-test\",\"played_on\":\"2026-08-28 00:00:00\",\"stage_scores\":\"[7,10,17]\",\"total_score\":78,\"updated_at\":\"2026-08-28 05:48:50\",\"created_at\":\"2026-08-28 05:48:50\",\"id\":10}', '127.0.0.1', NULL, '2026-08-27 21:48:50', '2026-08-27 21:48:50'),
(78, 15, NULL, 'updated', 'App\\Models\\User', 15, '{\"points\":135}', '{\"points\":213}', '127.0.0.1', NULL, '2026-08-27 21:48:50', '2026-08-27 21:48:50'),
(79, 15, NULL, 'updated', 'App\\Models\\User', 15, '{\"avatar_path\":null}', '{\"avatar_path\":\"avatars\\/GSEr4BEABPRExWI1jcDZ7W6DJx5GaJCa0n7829DX.png\"}', '127.0.0.1', 126, '2026-08-27 21:55:51', '2026-08-27 21:55:51'),
(80, 16, NULL, 'login', 'App\\Models\\User', 16, NULL, NULL, '127.0.0.1', 354, '2026-08-27 21:56:53', '2026-08-27 21:56:53'),
(81, 16, NULL, 'updated', 'App\\Models\\User', 16, NULL, NULL, '127.0.0.1', 57, '2026-08-27 22:22:54', '2026-08-27 22:22:54'),
(82, NULL, NULL, 'created', 'App\\Models\\User', 19, NULL, '{\"first_name\":\"Rosita\",\"middle_name\":\"Rodriguez\",\"last_name\":\"Apa\",\"suffix\":null,\"name\":\"Rosita Rodriguez Apa\",\"gender\":\"female\",\"gender_other\":null,\"birthdate\":\"1956-08-27 00:00:00\",\"contact_number\":null,\"house_no\":\"242\",\"street\":\"Aventurina\",\"zone\":\"2\",\"barangay\":\"San Jose\",\"city\":\"Iriga City\",\"province\":\"Camarines Sur\",\"country\":\"Philippines\",\"postal_code\":\"4431\",\"is_head_of_family\":true,\"head_of_family_id\":null,\"head_of_family_name\":null,\"username\":\"rosing70\",\"email\":\"rosita_rodriguez@gmail.com\",\"role\":\"resident\",\"official_group\":null,\"official_position\":null,\"updated_at\":\"2026-08-28 06:40:30\",\"created_at\":\"2026-08-28 06:40:30\",\"id\":19}', '127.0.0.1', 400, '2026-08-27 22:40:30', '2026-08-27 22:40:30'),
(83, NULL, NULL, 'updated', 'App\\Models\\User', 19, NULL, '{\"unique_id\":\"Z2-26000000002\"}', '127.0.0.1', 411, '2026-08-27 22:40:30', '2026-08-27 22:40:30'),
(84, 18, 'Z2-26000000001', 'login', 'App\\Models\\User', 18, NULL, NULL, '127.0.0.1', 364, '2026-08-27 22:42:10', '2026-08-27 22:42:10'),
(85, 18, 'Z2-26000000001', 'updated', 'App\\Models\\User', 18, NULL, NULL, '127.0.0.1', 59, '2026-08-27 22:43:52', '2026-08-27 22:43:52'),
(86, 16, NULL, 'login', 'App\\Models\\User', 16, NULL, NULL, '127.0.0.1', 382, '2026-08-27 22:44:00', '2026-08-27 22:44:00'),
(87, 16, NULL, 'updated', 'App\\Models\\User', 19, '{\"is_verified\":false}', '{\"is_verified\":true}', '127.0.0.1', 68, '2026-08-27 22:44:16', '2026-08-27 22:44:16'),
(88, 16, NULL, 'updated', 'App\\Models\\User', 16, NULL, NULL, '127.0.0.1', 74, '2026-08-27 22:44:46', '2026-08-27 22:44:46'),
(89, NULL, NULL, 'created', 'App\\Models\\User', 20, NULL, '{\"first_name\":\"Admin\",\"middle_name\":\"Test\",\"last_name\":\"Account\",\"suffix\":\"Jr.\",\"name\":\"Admin Test Account Jr.\",\"gender\":\"male\",\"gender_other\":null,\"birthdate\":\"2002-09-14 00:00:00\",\"contact_number\":\"09054146272\",\"house_no\":\"242\",\"street\":\"Aventurina\",\"zone\":\"2\",\"barangay\":\"San Jose\",\"city\":\"Iriga City\",\"province\":\"Camarines Sur\",\"country\":\"Philippines\",\"postal_code\":\"4431\",\"is_head_of_family\":false,\"head_of_family_id\":19,\"head_of_family_name\":\"Rosita Rodriguez Apa\",\"username\":\"adminjr\",\"email\":\"adminjr@gmail.com\",\"role\":\"official\",\"official_group\":\"personnel\",\"official_position\":\"other_personnel\",\"updated_at\":\"2026-08-28 06:53:32\",\"created_at\":\"2026-08-28 06:53:32\",\"id\":20}', '127.0.0.1', 387, '2026-08-27 22:53:32', '2026-08-27 22:53:32'),
(90, NULL, NULL, 'updated', 'App\\Models\\User', 20, NULL, '{\"unique_id\":\"Z2-26000000003\"}', '127.0.0.1', 396, '2026-08-27 22:53:32', '2026-08-27 22:53:32'),
(91, 18, 'Z2-26000000001', 'login', 'App\\Models\\User', 18, NULL, NULL, '127.0.0.1', 467, '2026-08-27 23:03:30', '2026-08-27 23:03:30'),
(92, 16, NULL, 'login', 'App\\Models\\User', 16, NULL, NULL, '127.0.0.1', 375, '2026-09-04 22:33:15', '2026-09-04 22:33:15'),
(93, 16, NULL, 'updated', 'App\\Models\\User', 16, NULL, NULL, '127.0.0.1', 53, '2026-09-04 22:34:19', '2026-09-04 22:34:19'),
(94, 18, 'Z2-26000000001', 'login', 'App\\Models\\User', 18, NULL, NULL, '127.0.0.1', 380, '2026-09-04 22:34:23', '2026-09-04 22:34:23'),
(95, 18, 'Z2-26000000001', 'updated', 'App\\Models\\User', 18, '{\"avatar_path\":\"avatars\\/gf8FTgV0IFuGeSu7ElBuFxokLd3GWgeSwElPgN07.jpg\"}', '{\"avatar_path\":\"avatars\\/WPePat85l8VkEb64XWJOaCxODztpvaSbT3r0WZEW.jpg\"}', '127.0.0.1', 123, '2026-09-04 22:35:02', '2026-09-04 22:35:02'),
(96, 18, 'Z2-26000000001', 'updated', 'App\\Models\\User', 18, NULL, NULL, '127.0.0.1', 82, '2026-09-04 22:44:29', '2026-09-04 22:44:29'),
(97, 16, NULL, 'login', 'App\\Models\\User', 16, NULL, NULL, '127.0.0.1', 369, '2026-09-04 22:44:33', '2026-09-04 22:44:33'),
(98, 16, NULL, 'updated', 'App\\Models\\User', 16, NULL, NULL, '127.0.0.1', 90, '2026-09-05 00:04:29', '2026-09-05 00:04:29'),
(99, 18, 'Z2-26000000001', 'login', 'App\\Models\\User', 18, NULL, NULL, '127.0.0.1', 662, '2026-09-05 00:04:33', '2026-09-05 00:04:33'),
(100, 18, 'Z2-26000000001', 'updated', 'App\\Models\\User', 18, NULL, NULL, '127.0.0.1', 84, '2026-09-05 00:12:12', '2026-09-05 00:12:12'),
(101, 16, NULL, 'login', 'App\\Models\\User', 16, NULL, NULL, '127.0.0.1', 387, '2026-09-05 00:12:16', '2026-09-05 00:12:16'),
(102, 16, NULL, 'updated', 'App\\Models\\User', 16, NULL, NULL, '127.0.0.1', 78, '2026-09-05 00:12:34', '2026-09-05 00:12:34'),
(103, 16, NULL, 'login', 'App\\Models\\User', 16, NULL, NULL, '127.0.0.1', 358, '2026-09-05 00:14:36', '2026-09-05 00:14:36'),
(104, 16, NULL, 'updated', 'App\\Models\\User', 18, '{\"is_student\":false,\"school\":null,\"head_of_family_name\":\"Rosita Apa Rodriguez\",\"head_of_family_id\":null}', '{\"is_student\":\"1\",\"school\":\"Camarines Sur Polytechnic Colleges\",\"head_of_family_name\":\"Rosita Rodriguez Apa\",\"head_of_family_id\":\"19\"}', '127.0.0.1', 743, '2026-09-05 00:17:34', '2026-09-05 00:17:34'),
(105, 16, NULL, 'created', 'App\\Models\\Survey', 2, NULL, '{\"title\":\"Survey Testing\",\"description\":\"Testing if survey function well.\",\"due_at\":\"2026-09-05 18:30:00\",\"questions\":\"[\\\"Smooth operation?\\\",\\\"The event was executed without delay\\\",\\\"The system makes attendance convenient\\\"]\",\"suggestion_enabled\":true,\"points\":\"100\",\"audience\":\"public\",\"event_id\":null,\"created_by\":16,\"updated_at\":\"2026-09-05 09:11:03\",\"created_at\":\"2026-09-05 09:11:03\",\"id\":2}', '127.0.0.1', 166, '2026-09-05 01:11:03', '2026-09-05 01:11:03'),
(106, 16, NULL, 'updated', 'App\\Models\\User', 16, NULL, NULL, '127.0.0.1', 67, '2026-09-05 01:11:36', '2026-09-05 01:11:36'),
(107, 18, 'Z2-26000000001', 'login', 'App\\Models\\User', 18, NULL, NULL, '127.0.0.1', 366, '2026-09-05 01:11:40', '2026-09-05 01:11:40'),
(108, 18, 'Z2-26000000001', 'updated', 'App\\Models\\User', 18, '{\"points\":532}', '{\"points\":632}', '127.0.0.1', 91, '2026-09-05 01:11:57', '2026-09-05 01:11:57'),
(109, 18, 'Z2-26000000001', 'created', 'App\\Models\\GameRun', 11, NULL, '{\"user_id\":18,\"game\":\"catch-star\",\"played_on\":\"2026-09-05 00:00:00\",\"stage_scores\":\"[16,3,1]\",\"total_score\":25,\"updated_at\":\"2026-09-05 09:13:12\",\"created_at\":\"2026-09-05 09:13:12\",\"id\":11}', '127.0.0.1', 67, '2026-09-05 01:13:12', '2026-09-05 01:13:12'),
(110, 18, 'Z2-26000000001', 'updated', 'App\\Models\\User', 18, '{\"points\":632}', '{\"points\":657}', '127.0.0.1', 77, '2026-09-05 01:13:12', '2026-09-05 01:13:12'),
(111, 18, 'Z2-26000000001', 'created', 'App\\Models\\GameRun', 12, NULL, '{\"user_id\":18,\"game\":\"memory-test\",\"played_on\":\"2026-09-05 00:00:00\",\"stage_scores\":\"[8,13,16]\",\"total_score\":82,\"updated_at\":\"2026-09-05 09:14:00\",\"created_at\":\"2026-09-05 09:14:00\",\"id\":12}', '127.0.0.1', 67, '2026-09-05 01:14:00', '2026-09-05 01:14:00'),
(112, 18, 'Z2-26000000001', 'updated', 'App\\Models\\User', 18, '{\"points\":657}', '{\"points\":739}', '127.0.0.1', 74, '2026-09-05 01:14:00', '2026-09-05 01:14:00'),
(113, 18, 'Z2-26000000001', 'updated', 'App\\Models\\User', 18, NULL, NULL, '127.0.0.1', 90, '2026-09-05 01:21:41', '2026-09-05 01:21:41'),
(114, 16, NULL, 'login', 'App\\Models\\User', 16, NULL, NULL, '127.0.0.1', 350, '2026-09-05 01:21:45', '2026-09-05 01:21:45'),
(115, 16, NULL, 'updated', 'App\\Models\\User', 16, '{\"points\":446}', '{\"points\":546}', '127.0.0.1', 102, '2026-09-05 01:21:58', '2026-09-05 01:21:58'),
(116, 16, NULL, 'updated', 'App\\Models\\User', 16, NULL, NULL, '127.0.0.1', 76, '2026-09-05 01:22:01', '2026-09-05 01:22:01'),
(117, 16, NULL, 'login', 'App\\Models\\User', 16, NULL, NULL, '127.0.0.1', 362, '2026-09-05 01:22:04', '2026-09-05 01:22:04'),
(118, 16, NULL, 'created', 'App\\Models\\User', 21, NULL, '{\"first_name\":\"Marie\",\"middle_name\":\"Rodriguez\",\"last_name\":\"Calibara\",\"suffix\":null,\"gender\":\"female\",\"gender_other\":null,\"birthdate\":\"2022-09-05 00:00:00\",\"contact_number\":null,\"is_student\":true,\"school\":\"San Jose Elementary School\",\"occupation\":null,\"house_no\":\"242\",\"street\":\"Aventurina\",\"zone\":\"2\",\"email\":\"marie@gmail.com\",\"head_of_family_id\":\"19\",\"head_of_family_name\":\"Rosita Rodriguez Apa\",\"is_head_of_family\":\"0\",\"username\":\"marie\",\"name\":\"Marie Rodriguez Calibara\",\"role\":\"resident\",\"is_verified\":true,\"points\":0,\"barangay\":\"San Jose\",\"city\":\"Iriga City\",\"province\":\"Camarines Sur\",\"country\":\"Philippines\",\"postal_code\":\"4431\",\"updated_at\":\"2026-09-05 10:00:49\",\"created_at\":\"2026-09-05 10:00:49\",\"id\":21}', '127.0.0.1', 364, '2026-09-05 02:00:49', '2026-09-05 02:00:49'),
(119, 16, NULL, 'updated', 'App\\Models\\User', 21, NULL, '{\"unique_id\":\"Z2-26000000004\"}', '127.0.0.1', 444, '2026-09-05 02:00:49', '2026-09-05 02:00:49'),
(120, 16, NULL, 'updated', 'App\\Models\\User', 16, NULL, NULL, '127.0.0.1', 75, '2026-09-05 02:01:08', '2026-09-05 02:01:08'),
(121, 21, 'Z2-26000000004', 'login', 'App\\Models\\User', 21, NULL, NULL, '127.0.0.1', 353, '2026-09-05 02:01:11', '2026-09-05 02:01:11'),
(122, 21, 'Z2-26000000004', 'updated', 'App\\Models\\User', 21, NULL, NULL, '127.0.0.1', 647, '2026-09-05 02:03:15', '2026-09-05 02:03:15'),
(123, 21, 'Z2-26000000004', 'updated', 'App\\Models\\User', 21, '{\"points\":0}', '{\"points\":10}', '127.0.0.1', 86, '2026-09-05 02:03:42', '2026-09-05 02:03:42'),
(124, 21, 'Z2-26000000004', 'updated', 'App\\Models\\User', 21, '{\"points\":10}', '{\"points\":110}', '127.0.0.1', 118, '2026-09-05 02:05:02', '2026-09-05 02:05:02'),
(125, 16, NULL, 'login', 'App\\Models\\User', 16, NULL, NULL, '127.0.0.1', 343, '2026-09-05 02:05:13', '2026-09-05 02:05:13'),
(126, 16, NULL, 'login', 'App\\Models\\User', 16, NULL, NULL, '192.168.1.4', 343, '2026-09-05 02:27:52', '2026-09-05 02:27:52');

-- --------------------------------------------------------

--
-- Table structure for table `badges`
--

CREATE TABLE `badges` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `category` varchar(255) NOT NULL DEFAULT 'attendance',
  `rarity` varchar(255) NOT NULL DEFAULT 'common',
  `award_method` varchar(255) NOT NULL DEFAULT 'automatic',
  `condition_key` varchar(255) NOT NULL DEFAULT 'points',
  `limited_total` int(10) UNSIGNED DEFAULT NULL,
  `announcement_id` bigint(20) UNSIGNED DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `points_required` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `badges`
--

INSERT INTO `badges` (`id`, `name`, `description`, `category`, `rarity`, `award_method`, `condition_key`, `limited_total`, `announcement_id`, `image_path`, `points_required`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'Early Birdy Award', 'Be one of the first to scan on any event', 'attendance', 'rare', 'automatic', 'early_arrival', 20, NULL, 'badges/vfYHkhONZ1hJncLmfJH2HZvKAbeghtVuJHYL2IIr.webp', 0, 16, 16, '2026-08-19 02:27:42', '2026-08-26 05:45:12'),
(2, 'Number One Girl', NULL, 'participation', 'legendary', 'official', 'manual', 1, NULL, 'badges/HRkXhgdHWYd1KyBupU74H6PpSgAviV6nCSEQfcCc.png', 0, 16, 16, '2026-08-26 05:55:52', '2026-08-26 05:56:04'),
(3, 'First Step', 'Attended your first event.', 'milestones', 'common', 'automatic', 'first_event', NULL, NULL, NULL, 0, NULL, NULL, '2026-08-27 19:35:58', '2026-08-27 19:35:58'),
(4, 'Event Attendee', 'Successfully attended an event.', 'attendance', 'common', 'automatic', 'event_attendance', NULL, NULL, NULL, 0, NULL, NULL, '2026-08-27 19:35:58', '2026-08-27 19:35:58'),
(6, 'Regular', 'Attended 5 events.', 'milestones', 'uncommon', 'automatic', 'attendance_count', NULL, NULL, NULL, 5, NULL, NULL, '2026-08-27 19:35:58', '2026-08-27 19:35:58'),
(7, 'Dedicated', 'Attended 10 events.', 'milestones', 'rare', 'automatic', 'attendance_count', NULL, NULL, NULL, 10, NULL, NULL, '2026-08-27 19:35:58', '2026-08-27 19:35:58'),
(8, 'TalaFair Veteran', 'Attended 25 events.', 'milestones', 'epic', 'automatic', 'attendance_count', NULL, NULL, NULL, 25, NULL, NULL, '2026-08-27 19:35:58', '2026-08-27 19:35:58'),
(9, 'TalaFair Legend', 'Attended 50 events.', 'milestones', 'legendary', 'automatic', 'attendance_count', NULL, NULL, NULL, 50, NULL, NULL, '2026-08-27 19:35:58', '2026-08-27 19:35:58'),
(10, 'Getting Started', 'Attended 3 consecutive events.', 'streaks', 'uncommon', 'automatic', 'streak', NULL, NULL, NULL, 3, NULL, NULL, '2026-08-27 19:35:58', '2026-08-27 19:35:58'),
(11, 'On a Roll', 'Attended 5 consecutive events.', 'streaks', 'rare', 'automatic', 'streak', NULL, NULL, NULL, 5, NULL, NULL, '2026-08-27 19:35:58', '2026-08-27 19:35:58'),
(12, 'Unstoppable', 'Attended 10 consecutive events.', 'streaks', 'epic', 'automatic', 'streak', NULL, NULL, NULL, 10, NULL, NULL, '2026-08-27 19:35:58', '2026-08-27 19:35:58');

-- --------------------------------------------------------

--
-- Table structure for table `badge_user`
--

CREATE TABLE `badge_user` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `badge_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `awarded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `award_rank` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `badge_user`
--

INSERT INTO `badge_user` (`id`, `badge_id`, `user_id`, `awarded_by`, `award_rank`, `created_at`, `updated_at`) VALUES
(1, 2, 18, 16, NULL, '2026-08-26 05:55:52', '2026-08-26 05:55:52');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('talafair-cache-setting:home_background', 'N;', 2103966109);

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
-- Table structure for table `event_raffle_entries`
--

CREATE TABLE `event_raffle_entries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `announcement_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `is_early` tinyint(1) NOT NULL DEFAULT 0,
  `weight` decimal(5,2) NOT NULL DEFAULT 1.00,
  `selected_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_reminders`
--

CREATE TABLE `event_reminders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `announcement_id` bigint(20) UNSIGNED NOT NULL,
  `sent_by` bigint(20) UNSIGNED NOT NULL,
  `kind` enum('event_reminder','survey_closing') NOT NULL,
  `message` text DEFAULT NULL,
  `recipients_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_rsvps`
--

CREATE TABLE `event_rsvps` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `announcement_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('attending','not_attending') NOT NULL,
  `reason` text DEFAULT NULL,
  `responded_at` datetime NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `event_rsvps`
--

INSERT INTO `event_rsvps` (`id`, `announcement_id`, `user_id`, `status`, `reason`, `responded_at`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 13, 18, 'attending', NULL, '2026-08-18 12:26:31', 18, '2026-08-18 04:26:31', '2026-08-18 04:26:31'),
(2, 12, 16, 'attending', NULL, '2026-08-25 09:14:40', 16, '2026-08-25 01:14:40', '2026-08-25 01:14:40'),
(3, 12, 18, 'attending', NULL, '2026-08-25 09:23:07', 18, '2026-08-25 01:23:07', '2026-08-25 01:23:07');

-- --------------------------------------------------------

--
-- Table structure for table `event_substitutions`
--

CREATE TABLE `event_substitutions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `announcement_id` bigint(20) UNSIGNED NOT NULL,
  `family_head_id` bigint(20) UNSIGNED NOT NULL,
  `substitute_user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Table structure for table `game_runs`
--

CREATE TABLE `game_runs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `game` varchar(30) NOT NULL,
  `played_on` date NOT NULL,
  `stage_scores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`stage_scores`)),
  `total_score` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `game_runs`
--

INSERT INTO `game_runs` (`id`, `user_id`, `game`, `played_on`, `stage_scores`, `total_score`, `created_at`, `updated_at`) VALUES
(1, 16, 'memory-test', '2026-08-26', '[12,20,28]', 136, '2026-08-25 22:29:04', '2026-08-25 22:29:04'),
(2, 16, 'catch-star', '2026-08-26', '[17,0,0]', 17, '2026-08-25 23:20:52', '2026-08-25 23:20:52'),
(3, 18, 'memory-test', '2026-08-26', '[8,10,16]', 76, '2026-08-25 23:26:13', '2026-08-25 23:26:13'),
(4, 18, 'catch-star', '2026-08-26', '[16,2,0]', 20, '2026-08-25 23:27:25', '2026-08-25 23:27:25'),
(5, 16, 'catch-star', '2026-08-28', '[14,0,0]', 14, '2026-08-27 17:39:14', '2026-08-27 17:39:14'),
(6, 16, 'memory-test', '2026-08-28', '[7,12,17]', 82, '2026-08-27 17:39:59', '2026-08-27 17:39:59'),
(7, 18, 'catch-star', '2026-08-28', '[15,4,0]', 23, '2026-08-27 18:12:24', '2026-08-27 18:12:24'),
(8, 18, 'memory-test', '2026-08-28', '[7,11,18]', 83, '2026-08-27 18:13:11', '2026-08-27 18:13:11'),
(9, 15, 'catch-star', '2026-08-28', '[9,3,0]', 15, '2026-08-27 21:48:04', '2026-08-27 21:48:04'),
(10, 15, 'memory-test', '2026-08-28', '[7,10,17]', 78, '2026-08-27 21:48:50', '2026-08-27 21:48:50'),
(11, 18, 'catch-star', '2026-09-05', '[16,3,1]', 25, '2026-09-05 01:13:12', '2026-09-05 01:13:12'),
(12, 18, 'memory-test', '2026-09-05', '[8,13,16]', 82, '2026-09-05 01:14:00', '2026-09-05 01:14:00');

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
(4, '2026_08_06_000001_create_spin_history_table', 1),
(5, '2026_08_06_000002_create_announcements_table', 1),
(6, '2026_08_06_000003_create_prizes_table', 1),
(7, '2026_08_17_142152_add_fields_to_users_table', 1),
(8, '2026_08_17_142444_add_event_fields_to_announcements_table', 1),
(9, '2026_08_17_142646_create_event_rsvp_table', 1),
(10, '2026_08_17_142816_create_attendances_table', 1),
(11, '2026_08_17_142936_create_audit_logs_table', 1),
(12, '2026_08_17_143035_create_badges_table', 1),
(13, '2026_08_17_143153_create_settings_table', 1),
(14, '2026_08_17_143248_create_event_reminders_table', 1),
(15, '2026_08_24_000001_add_official_positions_to_users_table', 1),
(16, '2026_08_24_000002_add_verification_status_to_users_table', 1),
(17, '2026_08_24_000003_create_raffle_entries_table', 1),
(18, '2026_08_25_000001_create_announcement_participations_table', 1),
(19, '2026_08_25_000002_add_participation_points_to_announcements_table', 1),
(20, '2026_08_26_000001_add_badge_mechanics', 1),
(21, '2026_08_26_000001_create_trivia_themes_table', 1),
(22, '2026_08_26_000002_add_guest_access', 1),
(23, '2026_08_26_000002_add_trivia_points_and_answers_table', 1),
(24, '2026_08_26_000003_create_game_runs_table', 1),
(25, '2026_08_26_000004_add_due_at_to_trivia_themes', 1),
(26, '2026_08_26_000005_create_event_raffle_entries_table', 1),
(27, '2026_08_28_000001_add_badge_award_metadata', 1),
(28, '2026_08_28_000001_add_student_and_occupation_to_users_table', 1),
(29, '2026_08_28_000002_add_duration_to_audit_logs_table', 1),
(30, '2026_08_28_000003_create_event_substitutions_table', 1),
(31, '2026_08_29_000001_create_surveys_tables', 1),
(32, '2026_08_29_000002_add_survey_id_to_user_notifications', 1),
(33, '2026_08_29_000003_add_due_at_and_points_to_surveys', 1);

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
-- Table structure for table `prizes`
--

CREATE TABLE `prizes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(50) NOT NULL,
  `prize_type` enum('points','foods','electronics','cash','essentials','none') NOT NULL,
  `amount` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `color` varchar(7) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `prizes`
--

INSERT INTO `prizes` (`id`, `label`, `prize_type`, `amount`, `color`, `created_at`, `updated_at`) VALUES
(9, '100 points', 'points', 100, '#f5ef42', '2026-08-06 04:07:48', '2026-08-26 07:12:45'),
(14, '25 Points', 'points', 25, '#9acd32', '2026-08-06 18:23:05', '2026-08-26 07:11:57'),
(15, '10 points', 'points', 10, '#edf028', '2026-08-06 18:23:26', '2026-08-26 07:12:57'),
(16, 'Sorry, please try again', 'none', 0, '#9acd32', '2026-08-13 03:27:13', '2026-08-13 03:27:13'),
(17, '15 points', 'points', 15, '#f0ea2d', '2026-08-26 07:13:40', '2026-08-26 07:13:40');

-- --------------------------------------------------------

--
-- Table structure for table `raffle_entries`
--

CREATE TABLE `raffle_entries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `extra_chances` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `raffle_entries`
--

INSERT INTO `raffle_entries` (`id`, `user_id`, `extra_chances`, `created_at`, `updated_at`) VALUES
(1, 4, 1, '2026-08-25 21:54:16', '2026-08-25 21:54:16'),
(2, 18, 0, '2026-08-25 21:54:56', '2026-08-26 21:03:39'),
(3, 19, 0, '2026-08-27 22:40:30', '2026-08-27 22:40:30'),
(4, 21, 0, '2026-09-05 02:00:49', '2026-09-05 02:00:49');

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
('ipjoudPkWWv1Lm0ZDaaymP3z214ZYVErZ1VdGaTp', 16, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoidk9LVmIwZ0FKcVVtcDBuZFplbzRaelR3N1hMT3NDMDBiVEFHWDlPbCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTY7fQ==', 1788605800),
('qTCsXOD33H6tcQHKmb3h90gHeEjeFnat5atzzJA0', 21, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiM2JmWXZPd3l6NDRJcUN1UmVwSDJ5UlZKV2ZRcFREQ1VWQjZXb29PNyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjk6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hY2NvdW50IjtzOjU6InJvdXRlIjtzOjc6ImFjY291bnQiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToyMTt9', 1788602552),
('xaMpBZnFHCklkKvEFDtDSwWrB11ng0vIMToCYfns', 16, '192.168.1.4', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7_8 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/131.0.6778.154 Mobile/15E148 Safari/604.1', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiVERtb2paSlJ1czl3RDFwR1V0VTQxa1BNTUoyT2NVNzhlajZrd2poZCI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjI0OiJodHRwOi8vMTkyLjE2OC4xLjE0OjgwMDAiO3M6NToicm91dGUiO3M6NDoiaG9tZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE2O30=', 1788606109);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `spin_history`
--

CREATE TABLE `spin_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `prize_label` varchar(255) NOT NULL,
  `prize_type` enum('points','foods','electronics','cash','essentials','none') NOT NULL,
  `amount` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `spin_history`
--

INSERT INTO `spin_history` (`id`, `user_id`, `prize_label`, `prize_type`, `amount`, `created_at`, `updated_at`) VALUES
(2, 3, '100 XP', 'points', 100, '2026-08-05 23:33:34', '2026-08-05 23:33:34'),
(7, 16, '500 Cash', 'cash', 0, '2026-08-13 03:26:00', '2026-08-13 03:26:00'),
(8, 18, '500 Cash', 'cash', 0, '2026-08-26 07:06:13', '2026-08-26 07:06:13'),
(9, 18, '15 points', 'points', 15, '2026-08-26 21:01:03', '2026-08-26 21:01:03'),
(10, 18, 'Sorry, please try again', 'none', 0, '2026-08-26 21:03:39', '2026-08-26 21:03:39'),
(11, 18, '25 Points', 'points', 25, '2026-08-27 18:19:40', '2026-08-27 18:19:40'),
(12, 21, '10 points', 'points', 10, '2026-09-05 02:03:42', '2026-09-05 02:03:42');

-- --------------------------------------------------------

--
-- Table structure for table `surveys`
--

CREATE TABLE `surveys` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `due_at` datetime DEFAULT NULL,
  `questions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`questions`)),
  `suggestion_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `points` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `audience` varchar(255) NOT NULL DEFAULT 'event_attendees',
  `event_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `surveys`
--

INSERT INTO `surveys` (`id`, `title`, `description`, `due_at`, `questions`, `suggestion_enabled`, `points`, `audience`, `event_id`, `created_by`, `created_at`, `updated_at`) VALUES
(2, 'Survey Testing', 'Testing if survey function well.', '2026-09-05 18:30:00', '[\"Smooth operation?\",\"The event was executed without delay\",\"The system makes attendance convenient\"]', 1, 100, 'public', NULL, 16, '2026-09-05 01:11:03', '2026-09-05 01:11:03');

-- --------------------------------------------------------

--
-- Table structure for table `survey_responses`
--

CREATE TABLE `survey_responses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `survey_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `answers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`answers`)),
  `suggestion` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `survey_responses`
--

INSERT INTO `survey_responses` (`id`, `survey_id`, `user_id`, `answers`, `suggestion`, `submitted_at`) VALUES
(2, 2, 18, '[\"5\",\"4\",\"5\"]', NULL, '2026-09-05 01:11:57'),
(3, 2, 16, '[\"5\",\"5\",\"5\"]', NULL, '2026-09-05 01:21:58'),
(4, 2, 21, '[\"4\",\"4\",\"4\"]', NULL, '2026-09-05 02:05:02');

-- --------------------------------------------------------

--
-- Table structure for table `trivia_answers`
--

CREATE TABLE `trivia_answers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `trivia_theme_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `question_index` int(10) UNSIGNED NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  `rank` int(10) UNSIGNED DEFAULT NULL,
  `points_awarded` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `answered_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trivia_answers`
--

INSERT INTO `trivia_answers` (`id`, `trivia_theme_id`, `user_id`, `question_index`, `is_correct`, `rank`, `points_awarded`, `answered_at`) VALUES
(1, 2, 16, 0, 1, 1, 50, '2026-08-26 06:58:31'),
(2, 2, 18, 0, 1, 2, 30, '2026-08-26 07:25:08'),
(3, 3, 16, 0, 1, 1, 150, '2026-08-28 02:09:22'),
(4, 3, 18, 0, 1, 2, 130, '2026-08-28 02:10:38'),
(5, 4, 16, 0, 1, 1, 150, '2026-08-28 05:15:19'),
(6, 4, 18, 0, 1, 2, 130, '2026-08-28 05:45:29'),
(7, 4, 15, 0, 1, 3, 120, '2026-08-28 05:46:12');

-- --------------------------------------------------------

--
-- Table structure for table `trivia_themes`
--

CREATE TABLE `trivia_themes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `base_points` int(10) UNSIGNED NOT NULL DEFAULT 100,
  `due_at` datetime DEFAULT NULL,
  `questions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`questions`)),
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trivia_themes`
--

INSERT INTO `trivia_themes` (`id`, `title`, `base_points`, `due_at`, `questions`, `created_by`, `created_at`, `updated_at`) VALUES
(2, 'Barangay History', 100, '2026-08-26 15:30:00', '[{\"question\":\"Who is the patron saint of our barangay?\",\"answer\":\"Saint Joseph\",\"choices\":[\"Saint Joseph\",\"Saint Anthony\",\"Saint Peter\"]}]', 16, '2026-08-25 22:58:19', '2026-08-25 22:58:19'),
(3, 'Barangay Trivia', 100, '2026-08-28 11:00:00', '[{\"question\":\"Who is the current Barangay Captain of our barangay?\",\"answer\":\"Kap. Darcy DV. Go\",\"choices\":[\"Kap. Darcy DV. Go\",\"Kapitan Tiago\",\"Kap. Che-Che De Jesus\"]}]', 16, '2026-08-27 18:09:11', '2026-08-27 18:09:11'),
(4, 'General Knowledge', 100, '2026-08-28 13:15:00', '[{\"question\":\"What is the oldest City in the POhilippines?\",\"answer\":\"Cebu\",\"choices\":[\"Cebu\",\"Manila\",\"Iriga City\"]}]', 16, '2026-08-27 21:10:30', '2026-08-27 21:10:30');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `suffix` varchar(20) DEFAULT NULL,
  `gender` enum('female','male','others') DEFAULT NULL,
  `gender_other` varchar(255) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `is_student` tinyint(1) NOT NULL DEFAULT 0,
  `school` varchar(255) DEFAULT NULL,
  `occupation` varchar(255) DEFAULT NULL,
  `house_no` varchar(255) DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `zone` varchar(10) DEFAULT NULL,
  `barangay` varchar(255) NOT NULL DEFAULT 'San Jose',
  `city` varchar(255) NOT NULL DEFAULT 'Iriga City',
  `province` varchar(255) NOT NULL DEFAULT 'Camarines Sur',
  `country` varchar(255) NOT NULL DEFAULT 'Philippines',
  `postal_code` varchar(10) NOT NULL DEFAULT '4431',
  `is_head_of_family` tinyint(1) NOT NULL DEFAULT 0,
  `head_of_family_name` varchar(255) DEFAULT NULL,
  `head_of_family_id` bigint(20) UNSIGNED DEFAULT NULL,
  `unique_id` varchar(24) DEFAULT NULL,
  `avatar_path` varchar(255) DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('resident','guest','official') NOT NULL DEFAULT 'resident',
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `official_group` varchar(255) DEFAULT NULL,
  `official_position` varchar(255) DEFAULT NULL,
  `points` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `first_name`, `middle_name`, `last_name`, `suffix`, `gender`, `gender_other`, `birthdate`, `contact_number`, `is_student`, `school`, `occupation`, `house_no`, `street`, `zone`, `barangay`, `city`, `province`, `country`, `postal_code`, `is_head_of_family`, `head_of_family_name`, `head_of_family_id`, `unique_id`, `avatar_path`, `username`, `email`, `email_verified_at`, `password`, `role`, `is_verified`, `official_group`, `official_position`, `points`, `remember_token`, `created_at`, `updated_at`) VALUES
(3, 'renebaterbonia', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 'San Jose', 'Iriga City', 'Camarines Sur', 'Philippines', '4431', 0, NULL, NULL, NULL, NULL, 'renebutterbonia', 'renebaterbonia@gmail.com', NULL, '$2y$12$3vauoniJcAx753tDk/Eooe8adCHmlCqjbNKUcJL3icv9m6dS9CvUa', 'official', 0, NULL, NULL, 100, NULL, '2026-08-05 23:12:09', '2026-08-05 23:33:34'),
(4, 'totoybrown', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 'San Jose', 'Iriga City', 'Camarines Sur', 'Philippines', '4431', 0, NULL, NULL, NULL, NULL, 'totoybutterbonia', 'totoybutterbonia@gmailc.com', NULL, '$2y$12$g3sC66jlbFellHkwII03VOJLiPUmvwFy3IN9j2rwMdZ5AJIdOHG1m', 'resident', 0, NULL, NULL, 0, NULL, '2026-08-05 23:13:36', '2026-08-05 23:13:36'),
(15, 'Test Account', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 'San Jose', 'Iriga City', 'Camarines Sur', 'Philippines', '4431', 0, NULL, NULL, NULL, 'avatars/GSEr4BEABPRExWI1jcDZ7W6DJx5GaJCa0n7829DX.png', 'testaccount', 'test123@gmail.com', NULL, '$2y$12$SlE5ZekknLXRJh8WYyvO5.9sEQKNKoz5ZDphjVjRvBjEgep5dwvJ.', 'resident', 0, NULL, NULL, 213, NULL, '2026-08-11 03:37:56', '2026-08-27 21:55:51'),
(16, 'Admin Test Account', 'Admin', 'Test', 'Account', NULL, 'female', NULL, '2002-08-01', NULL, 0, NULL, NULL, '242', 'Aventurina', '2', 'San Jose', 'Iriga City', 'Camarines Sur', 'Philippines', '4431', 0, NULL, NULL, NULL, 'avatars/Yukq0tAYsQ3IyXEAg7YbwR3R91RGUjEnl2YLfhZE.jpg', 'admin', 'admin@test.com', NULL, '$2y$12$MRkrRYvIEbeA/8dzi1Fce.kvQkQRinMDWdlC8i/CPZBHxhCywoIMW', 'official', 0, NULL, NULL, 546, 'ktPRhNIkJtBgD5JKJ9palZAT4mSH3i64Jmxc7UAl0EFWPWPGDXA7uHc0k0Jt', '2026-08-11 03:53:08', '2026-09-05 01:21:58'),
(17, 'ena marie', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 'San Jose', 'Iriga City', 'Camarines Sur', 'Philippines', '4431', 0, NULL, NULL, NULL, NULL, 'enahmarie', 'ynahcalibara02@gmail.com', NULL, '$2y$12$x.Ve5H0Otu/ZZG99X7KFteiCklRoweTWeNeIVxXJRKk0cutwdN6G6', 'resident', 0, NULL, NULL, 0, NULL, '2026-08-13 21:53:49', '2026-08-13 21:53:49'),
(18, 'Ynah Marie Rodriguez Calibara', 'Ynah Marie', 'Rodriguez', 'Calibara', NULL, 'female', NULL, '2002-09-14', '09054146272', 1, 'Camarines Sur Polytechnic Colleges', NULL, '242', 'Aventurina', '2', 'San Jose', 'Iriga City', 'Camarines Sur', 'Philippines', '4431', 0, 'Rosita Rodriguez Apa', 19, 'Z2-26000000001', 'avatars/WPePat85l8VkEb64XWJOaCxODztpvaSbT3r0WZEW.jpg', 'ynahng_eiram', 'yncalibara@my.cspc.edu.ph', NULL, '$2y$12$teZsaXdorBSDAtf2dt5/4.tNEjeNcjtGym7sBNyhJ1mVK7jj0Ymh.', 'resident', 1, NULL, NULL, 739, 'RUblnbIMUzGpqRRTC8ssZ7u1Klf6FzkpkOE1CQVyftiAJYinwweRoIHHWpPE', '2026-08-18 04:14:26', '2026-09-05 01:14:00'),
(19, 'Rosita Rodriguez Apa', 'Rosita', 'Rodriguez', 'Apa', NULL, 'female', NULL, '1956-08-27', NULL, 0, NULL, NULL, '242', 'Aventurina', '2', 'San Jose', 'Iriga City', 'Camarines Sur', 'Philippines', '4431', 1, NULL, NULL, 'Z2-26000000002', NULL, 'rosing70', 'rosita_rodriguez@gmail.com', NULL, '$2y$12$be/43lARw2LzbkWc4xDUfOLLOLlqDOjDhdOPpuN9F4qCrH8IZP336', 'resident', 1, NULL, NULL, 0, NULL, '2026-08-27 22:40:30', '2026-08-27 22:44:16'),
(20, 'Admin Test Account Jr.', 'Admin', 'Test', 'Account', 'Jr.', 'male', NULL, '2002-09-14', '09054146272', 0, NULL, NULL, '242', 'Aventurina', '2', 'San Jose', 'Iriga City', 'Camarines Sur', 'Philippines', '4431', 0, 'Rosita Rodriguez Apa', 19, 'Z2-26000000003', NULL, 'adminjr', 'adminjr@gmail.com', NULL, '$2y$12$YNOfz7gkkHD2N7hN7IeHBejejs6Ul2OAFwhGo9LrQ2pkZjgp0rR96', 'official', 0, 'personnel', 'other_personnel', 0, NULL, '2026-08-27 22:53:32', '2026-08-27 22:53:32'),
(21, 'Marie Rodriguez Calibara', 'Marie', 'Rodriguez', 'Calibara', NULL, 'female', NULL, '2022-09-05', NULL, 1, 'San Jose Elementary School', NULL, '242', 'Aventurina', '2', 'San Jose', 'Iriga City', 'Camarines Sur', 'Philippines', '4431', 0, 'Rosita Rodriguez Apa', 19, 'Z2-26000000004', NULL, 'marie', 'marie@gmail.com', NULL, '$2y$12$xhvtt.izabsDQIHasSiXSufwEQ0Upjuoqsc5INEBR3x9ii6EggYBC', 'resident', 1, NULL, NULL, 110, NULL, '2026-09-05 02:00:49', '2026-09-05 02:05:02');

-- --------------------------------------------------------

--
-- Table structure for table `user_notifications`
--

CREATE TABLE `user_notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `announcement_id` bigint(20) UNSIGNED DEFAULT NULL,
  `survey_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `body` text DEFAULT NULL,
  `read_at` datetime DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_notifications`
--

INSERT INTO `user_notifications` (`id`, `user_id`, `announcement_id`, `survey_id`, `title`, `body`, `read_at`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 18, 13, NULL, 'New event: Meet and Greet', 'You are invited. Please confirm your attendance before Aug 18, 2026 9:00 PM.', '2026-08-28 05:45:14', 16, '2026-08-18 04:23:50', '2026-08-27 21:45:14'),
(2, 3, NULL, 2, 'New survey: Survey Testing', 'Share your feedback in the new community survey.', NULL, 16, '2026-09-05 01:11:03', '2026-09-05 01:11:03'),
(3, 4, NULL, 2, 'New survey: Survey Testing', 'Share your feedback in the new community survey.', NULL, 16, '2026-09-05 01:11:03', '2026-09-05 01:11:03'),
(4, 15, NULL, 2, 'New survey: Survey Testing', 'Share your feedback in the new community survey.', NULL, 16, '2026-09-05 01:11:03', '2026-09-05 01:11:03'),
(5, 16, NULL, 2, 'New survey: Survey Testing', 'Share your feedback in the new community survey.', '2026-09-05 09:21:48', 16, '2026-09-05 01:11:03', '2026-09-05 01:21:48'),
(6, 17, NULL, 2, 'New survey: Survey Testing', 'Share your feedback in the new community survey.', NULL, 16, '2026-09-05 01:11:03', '2026-09-05 01:11:03'),
(7, 18, NULL, 2, 'New survey: Survey Testing', 'Share your feedback in the new community survey.', '2026-09-05 09:12:01', 16, '2026-09-05 01:11:03', '2026-09-05 01:12:01'),
(8, 19, NULL, 2, 'New survey: Survey Testing', 'Share your feedback in the new community survey.', NULL, 16, '2026-09-05 01:11:03', '2026-09-05 01:11:03'),
(9, 20, NULL, 2, 'New survey: Survey Testing', 'Share your feedback in the new community survey.', NULL, 16, '2026-09-05 01:11:03', '2026-09-05 01:11:03'),
(10, 21, NULL, NULL, 'Account created by an official', 'An official created your resident account. You can now sign in using the credentials provided to you.', '2026-09-05 10:01:28', 16, '2026-09-05 02:00:49', '2026-09-05 02:01:28'),
(11, 21, NULL, NULL, 'Password changed', 'Your account password was changed successfully.', '2026-09-05 10:03:26', 21, '2026-09-05 02:03:15', '2026-09-05 02:03:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `announcements_qr_token_unique` (`qr_token`),
  ADD KEY `announcements_created_by_foreign` (`created_by`),
  ADD KEY `announcements_updated_by_foreign` (`updated_by`),
  ADD KEY `announcements_is_event_event_start_at_index` (`is_event`,`event_start_at`);

--
-- Indexes for table `announcement_participations`
--
ALTER TABLE `announcement_participations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `announcement_participations_announcement_id_user_id_unique` (`announcement_id`,`user_id`),
  ADD KEY `announcement_participations_user_id_foreign` (`user_id`),
  ADD KEY `announcement_participations_scanned_by_foreign` (`scanned_by`);

--
-- Indexes for table `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `attendances_announcement_id_user_id_unique` (`announcement_id`,`user_id`),
  ADD KEY `attendances_user_id_foreign` (`user_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audit_logs_user_id_foreign` (`user_id`),
  ADD KEY `audit_logs_auditable_type_auditable_id_index` (`auditable_type`,`auditable_id`);

--
-- Indexes for table `badges`
--
ALTER TABLE `badges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `badges_created_by_foreign` (`created_by`),
  ADD KEY `badges_updated_by_foreign` (`updated_by`),
  ADD KEY `badges_announcement_id_foreign` (`announcement_id`);

--
-- Indexes for table `badge_user`
--
ALTER TABLE `badge_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `badge_user_badge_id_user_id_unique` (`badge_id`,`user_id`),
  ADD KEY `badge_user_user_id_foreign` (`user_id`),
  ADD KEY `badge_user_awarded_by_foreign` (`awarded_by`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `event_raffle_entries`
--
ALTER TABLE `event_raffle_entries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `event_raffle_entries_announcement_id_user_id_unique` (`announcement_id`,`user_id`),
  ADD KEY `event_raffle_entries_user_id_foreign` (`user_id`);

--
-- Indexes for table `event_reminders`
--
ALTER TABLE `event_reminders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_reminders_announcement_id_foreign` (`announcement_id`),
  ADD KEY `event_reminders_sent_by_foreign` (`sent_by`);

--
-- Indexes for table `event_rsvps`
--
ALTER TABLE `event_rsvps`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `event_rsvps_announcement_id_user_id_unique` (`announcement_id`,`user_id`),
  ADD KEY `event_rsvps_user_id_foreign` (`user_id`),
  ADD KEY `event_rsvps_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `event_substitutions`
--
ALTER TABLE `event_substitutions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `event_substitutions_announcement_id_family_head_id_unique` (`announcement_id`,`family_head_id`),
  ADD KEY `event_substitutions_family_head_id_foreign` (`family_head_id`),
  ADD KEY `event_substitutions_substitute_user_id_foreign` (`substitute_user_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `game_runs`
--
ALTER TABLE `game_runs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `game_runs_user_id_game_played_on_unique` (`user_id`,`game`,`played_on`);

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
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `prizes`
--
ALTER TABLE `prizes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `raffle_entries`
--
ALTER TABLE `raffle_entries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `raffle_entries_user_id_unique` (`user_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`key`),
  ADD KEY `settings_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `spin_history`
--
ALTER TABLE `spin_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `spin_history_user_id_foreign` (`user_id`);

--
-- Indexes for table `surveys`
--
ALTER TABLE `surveys`
  ADD PRIMARY KEY (`id`),
  ADD KEY `surveys_event_id_foreign` (`event_id`),
  ADD KEY `surveys_created_by_foreign` (`created_by`);

--
-- Indexes for table `survey_responses`
--
ALTER TABLE `survey_responses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `survey_responses_survey_id_user_id_unique` (`survey_id`,`user_id`),
  ADD KEY `survey_responses_user_id_foreign` (`user_id`);

--
-- Indexes for table `trivia_answers`
--
ALTER TABLE `trivia_answers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `trivia_answers_trivia_theme_id_user_id_question_index_unique` (`trivia_theme_id`,`user_id`,`question_index`),
  ADD KEY `trivia_answers_user_id_foreign` (`user_id`),
  ADD KEY `trivia_answers_rank_idx` (`trivia_theme_id`,`question_index`,`is_correct`,`answered_at`);

--
-- Indexes for table `trivia_themes`
--
ALTER TABLE `trivia_themes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trivia_themes_created_by_foreign` (`created_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_unique_id_unique` (`unique_id`),
  ADD KEY `users_head_of_family_id_foreign` (`head_of_family_id`),
  ADD KEY `users_birthdate_index` (`birthdate`),
  ADD KEY `users_is_head_of_family_index` (`is_head_of_family`),
  ADD KEY `users_is_verified_index` (`is_verified`);

--
-- Indexes for table `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_notifications_announcement_id_foreign` (`announcement_id`),
  ADD KEY `user_notifications_created_by_foreign` (`created_by`),
  ADD KEY `user_notifications_user_id_read_at_index` (`user_id`,`read_at`),
  ADD KEY `user_notifications_survey_id_foreign` (`survey_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `announcement_participations`
--
ALTER TABLE `announcement_participations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=127;

--
-- AUTO_INCREMENT for table `badges`
--
ALTER TABLE `badges`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `badge_user`
--
ALTER TABLE `badge_user`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `event_raffle_entries`
--
ALTER TABLE `event_raffle_entries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_reminders`
--
ALTER TABLE `event_reminders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_rsvps`
--
ALTER TABLE `event_rsvps`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `event_substitutions`
--
ALTER TABLE `event_substitutions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `game_runs`
--
ALTER TABLE `game_runs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `prizes`
--
ALTER TABLE `prizes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `raffle_entries`
--
ALTER TABLE `raffle_entries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `spin_history`
--
ALTER TABLE `spin_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `surveys`
--
ALTER TABLE `surveys`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `survey_responses`
--
ALTER TABLE `survey_responses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `trivia_answers`
--
ALTER TABLE `trivia_answers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `trivia_themes`
--
ALTER TABLE `trivia_themes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `user_notifications`
--
ALTER TABLE `user_notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `announcements_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `announcement_participations`
--
ALTER TABLE `announcement_participations`
  ADD CONSTRAINT `announcement_participations_announcement_id_foreign` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `announcement_participations_scanned_by_foreign` FOREIGN KEY (`scanned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `announcement_participations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attendances`
--
ALTER TABLE `attendances`
  ADD CONSTRAINT `attendances_announcement_id_foreign` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendances_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `badges`
--
ALTER TABLE `badges`
  ADD CONSTRAINT `badges_announcement_id_foreign` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `badges_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `badges_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `badge_user`
--
ALTER TABLE `badge_user`
  ADD CONSTRAINT `badge_user_awarded_by_foreign` FOREIGN KEY (`awarded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `badge_user_badge_id_foreign` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `badge_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_raffle_entries`
--
ALTER TABLE `event_raffle_entries`
  ADD CONSTRAINT `event_raffle_entries_announcement_id_foreign` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_raffle_entries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_reminders`
--
ALTER TABLE `event_reminders`
  ADD CONSTRAINT `event_reminders_announcement_id_foreign` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_reminders_sent_by_foreign` FOREIGN KEY (`sent_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_rsvps`
--
ALTER TABLE `event_rsvps`
  ADD CONSTRAINT `event_rsvps_announcement_id_foreign` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_rsvps_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `event_rsvps_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_substitutions`
--
ALTER TABLE `event_substitutions`
  ADD CONSTRAINT `event_substitutions_announcement_id_foreign` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_substitutions_family_head_id_foreign` FOREIGN KEY (`family_head_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_substitutions_substitute_user_id_foreign` FOREIGN KEY (`substitute_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `game_runs`
--
ALTER TABLE `game_runs`
  ADD CONSTRAINT `game_runs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `raffle_entries`
--
ALTER TABLE `raffle_entries`
  ADD CONSTRAINT `raffle_entries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `settings`
--
ALTER TABLE `settings`
  ADD CONSTRAINT `settings_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `spin_history`
--
ALTER TABLE `spin_history`
  ADD CONSTRAINT `spin_history_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `surveys`
--
ALTER TABLE `surveys`
  ADD CONSTRAINT `surveys_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `surveys_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `announcements` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `survey_responses`
--
ALTER TABLE `survey_responses`
  ADD CONSTRAINT `survey_responses_survey_id_foreign` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `survey_responses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `trivia_answers`
--
ALTER TABLE `trivia_answers`
  ADD CONSTRAINT `trivia_answers_trivia_theme_id_foreign` FOREIGN KEY (`trivia_theme_id`) REFERENCES `trivia_themes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `trivia_answers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `trivia_themes`
--
ALTER TABLE `trivia_themes`
  ADD CONSTRAINT `trivia_themes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_head_of_family_id_foreign` FOREIGN KEY (`head_of_family_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD CONSTRAINT `user_notifications_announcement_id_foreign` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_notifications_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `user_notifications_survey_id_foreign` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `user_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
