-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.29 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.0.0.6468
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for todo_project
CREATE DATABASE IF NOT EXISTS `todo_project` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `todo_project`;

-- Dumping structure for table todo_project.cache
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table todo_project.cache: ~2 rows (approximately)
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
	('laravel_cache_81b329ed6678cce7b87a8efa97b38767', 'i:1;', 1749719256),
	('laravel_cache_81b329ed6678cce7b87a8efa97b38767:timer', 'i:1749719256;', 1749719256);

-- Dumping structure for table todo_project.cache_locks
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table todo_project.cache_locks: ~0 rows (approximately)

-- Dumping structure for table todo_project.failed_jobs
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table todo_project.failed_jobs: ~0 rows (approximately)

-- Dumping structure for table todo_project.items
CREATE TABLE IF NOT EXISTS `items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `items_user_id_index` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table todo_project.items: ~0 rows (approximately)
INSERT INTO `items` (`id`, `user_id`, `title`, `description`, `status`, `created_at`, `updated_at`) VALUES
	(1, 1, 'suscipit', 'Array', 0, '2025-06-12 04:33:49', '2025-06-12 04:33:49'),
	(2, 1, 'quo', 'Array', 0, '2025-06-12 04:33:49', '2025-06-12 04:33:49'),
	(3, 1, 'expedita', 'Array', 0, '2025-06-12 04:33:49', '2025-06-12 04:33:49'),
	(4, 1, 'voluptas', 'Array', 1, '2025-06-12 04:33:49', '2025-06-12 04:33:49'),
	(5, 1, 'ut', 'Array', 1, '2025-06-12 04:33:49', '2025-06-12 04:33:49'),
	(6, 1, 'assumenda', 'Array', 1, '2025-06-12 04:33:49', '2025-06-12 04:33:49'),
	(7, 1, 'est', 'Array', 0, '2025-06-12 04:33:49', '2025-06-12 04:33:49'),
	(8, 1, 'explicabo', 'Array', 1, '2025-06-12 04:33:49', '2025-06-12 04:33:49'),
	(9, 1, 'repudiandae', 'Array', 0, '2025-06-12 04:33:49', '2025-06-12 04:33:49'),
	(10, 1, 'qui', 'Array', 0, '2025-06-12 04:33:49', '2025-06-12 04:33:49'),
	(11, 1, 'distinctio', 'Array', 1, '2025-06-12 04:33:49', '2025-06-12 04:33:49'),
	(12, 1, 'ratione', 'Array', 0, '2025-06-12 04:33:49', '2025-06-12 04:33:49'),
	(13, 1, 'consectetur', 'Array', 1, '2025-06-12 04:33:49', '2025-06-12 04:33:49'),
	(14, 1, 'pariatur', 'Array', 1, '2025-06-12 04:33:49', '2025-06-12 04:33:49'),
	(15, 1, 'tempora', 'Array', 1, '2025-06-12 04:33:49', '2025-06-12 04:33:49'),
	(16, 1, 'aperiam', 'Array', 1, '2025-06-12 04:33:49', '2025-06-12 04:33:49'),
	(17, 1, 'distinctio', 'Array', 1, '2025-06-12 04:33:49', '2025-06-12 04:33:49'),
	(18, 1, 'ut', 'Array', 1, '2025-06-12 04:33:49', '2025-06-12 04:33:49'),
	(19, 1, 'qui', 'Array', 0, '2025-06-12 04:33:49', '2025-06-12 04:33:49'),
	(20, 1, 'quam', 'Array', 0, '2025-06-12 04:33:49', '2025-06-12 04:33:49'),
	(21, 1, 'voluptatem', 'Array', 1, '2025-06-12 04:33:49', '2025-06-12 04:33:49'),
	(22, 1, 'tempore', 'Array', 0, '2025-06-12 04:33:49', '2025-06-12 04:33:49'),
	(23, 1, 'aut', 'Array', 0, '2025-06-12 04:33:49', '2025-06-12 04:33:49'),
	(24, 1, 'aut', 'Array', 0, '2025-06-12 04:33:49', '2025-06-12 04:33:49'),
	(25, 1, 'maiores', 'Array', 0, '2025-06-12 04:33:49', '2025-06-12 04:33:49');

-- Dumping structure for table todo_project.jobs
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table todo_project.jobs: ~0 rows (approximately)

-- Dumping structure for table todo_project.job_batches
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table todo_project.job_batches: ~0 rows (approximately)

-- Dumping structure for table todo_project.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table todo_project.migrations: ~5 rows (approximately)
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '0001_01_01_000000_create_users_table', 1),
	(2, '0001_01_01_000001_create_cache_table', 1),
	(3, '0001_01_01_000002_create_jobs_table', 1),
	(4, '2025_06_12_062409_add_two_factor_columns_to_users_table', 1),
	(5, '2025_06_12_062448_create_personal_access_tokens_table', 1),
	(7, '2025_06_12_065131_create_items_table', 2);

-- Dumping structure for table todo_project.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table todo_project.password_reset_tokens: ~0 rows (approximately)

-- Dumping structure for table todo_project.personal_access_tokens
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table todo_project.personal_access_tokens: ~0 rows (approximately)

-- Dumping structure for table todo_project.sessions
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table todo_project.sessions: ~1 rows (approximately)
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
	('4Dl6pxf1HWWd5MuZoiVMLLrgZM3001sP4K39qgOI', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiVHVpWERTNFRUNjVYWFRhQ0pPRVZDdzExbGJMUldrY0lVcUlxTDZPMCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9pdGVtcz9hY3RpdmU9ZmFsc2UmcGFnZT0xJnNvcnRBc2M9ZmFsc2UiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6MjE6InBhc3N3b3JkX2hhc2hfc2FuY3R1bSI7czo2MDoiJDJ5JDEyJDNCRzZoTllsbnNyVFF6alV4RTJ5dy51SmpGNWpJSG1QZVJiTEtkSUVMUUQvdm81Q1FHOU9xIjt9', 1749725350);

-- Dumping structure for table todo_project.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `two_factor_secret` text COLLATE utf8mb4_unicode_ci,
  `two_factor_recovery_codes` text COLLATE utf8mb4_unicode_ci,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_team_id` bigint unsigned DEFAULT NULL,
  `profile_photo_path` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table todo_project.users: ~1 rows (approximately)
INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `remember_token`, `current_team_id`, `profile_photo_path`, `created_at`, `updated_at`) VALUES
	(1, 'Ayesh', 'sdachathuranga@gmail.com', NULL, '$2y$12$3BG6hNYlnsrTQzjUxE2yw.uJjF5jIHmPeRbLKdIELQD/vo5CQG9Oq', NULL, NULL, NULL, 'rgj9Js9n5Jw189B5HpGEJG5CF6tv0GQat5f1fqQH5S3NRBfP806qXwtvb9WG', NULL, NULL, '2025-06-12 00:59:23', '2025-06-12 00:59:23');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
