-- =============================================================================
-- Script: Archive and Delete Spoken Class Data
-- Date: 2026-01-12
-- Description: Archives all Spoken category class data before deletion
-- Category ID = 1 (Spoken)
-- Spoken Tuition IDs: 2, 4, 8, 9, 35
-- =============================================================================

-- Step 1: Create archive table for student reports if not exists
CREATE TABLE IF NOT EXISTS `archived_student_reports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `original_report_id` bigint unsigned NOT NULL,
  `student_id` bigint unsigned NOT NULL,
  `tuition_id` bigint unsigned NOT NULL,
  `month_id` bigint unsigned NOT NULL,
  `year_id` bigint unsigned NOT NULL,
  `week1` tinyint DEFAULT '0',
  `week2` tinyint DEFAULT '0',
  `week3` tinyint DEFAULT '0',
  `week4` tinyint DEFAULT '0',
  `week5` tinyint DEFAULT '0',
  `paid` tinyint DEFAULT '0',
  `reminder_week3` tinyint DEFAULT '0',
  `reminder_week4` tinyint DEFAULT '0',
  `original_created_at` timestamp NULL DEFAULT NULL,
  `original_updated_at` timestamp NULL DEFAULT NULL,
  `archive_reason` varchar(100) DEFAULT NULL,
  `archived_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `archived_student_reports_student_id_idx` (`student_id`),
  KEY `archived_student_reports_tuition_id_idx` (`tuition_id`),
  KEY `archived_student_reports_year_id_idx` (`year_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 2: Create archive table for spoken student tuitions if not exists
CREATE TABLE IF NOT EXISTS `archived_spoken_student_tuitions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `original_id` bigint unsigned NOT NULL,
  `student_id` bigint unsigned NOT NULL,
  `tuition_id` bigint unsigned NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `academic_year_id` bigint unsigned DEFAULT NULL,
  `original_created_at` timestamp NULL DEFAULT NULL,
  `original_updated_at` timestamp NULL DEFAULT NULL,
  `archive_reason` varchar(100) DEFAULT NULL,
  `archived_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `archived_spoken_student_tuitions_student_id_idx` (`student_id`),
  KEY `archived_spoken_student_tuitions_tuition_id_idx` (`tuition_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 3: Create archive table for spoken tuitions if not exists
CREATE TABLE IF NOT EXISTS `archived_spoken_tuitions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `original_tuition_id` bigint unsigned NOT NULL,
  `day_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  `class_id` bigint unsigned NOT NULL,
  `original_created_at` timestamp NULL DEFAULT NULL,
  `original_updated_at` timestamp NULL DEFAULT NULL,
  `archive_reason` varchar(100) DEFAULT NULL,
  `archived_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 4: Create archive table for spoken tuitions_has_grades if not exists
CREATE TABLE IF NOT EXISTS `archived_spoken_tuitions_has_grades` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `original_id` bigint unsigned NOT NULL,
  `tuition_id` bigint unsigned NOT NULL,
  `grade_id` bigint unsigned NOT NULL,
  `original_created_at` timestamp NULL DEFAULT NULL,
  `original_updated_at` timestamp NULL DEFAULT NULL,
  `archive_reason` varchar(100) DEFAULT NULL,
  `archived_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- ARCHIVE DATA (Run these first to preserve historical data)
-- =============================================================================

-- Define Spoken tuition IDs (category_id = 1)
-- Tuition IDs: 2, 4, 8, 9, 35

-- Archive student reports for spoken classes
INSERT INTO `archived_student_reports` (
  `original_report_id`, `student_id`, `tuition_id`, `month_id`, `year_id`,
  `week1`, `week2`, `week3`, `week4`, `week5`, `paid`, 
  `reminder_week3`, `reminder_week4`, 
  `original_created_at`, `original_updated_at`, `archive_reason`
)
SELECT 
  sr.id, sr.student_id, sr.tuition_id, sr.month_id, sr.year_id,
  sr.week1, sr.week2, sr.week3, sr.week4, sr.week5, sr.paid,
  sr.reminder_week3, sr.reminder_week4,
  sr.created_at, sr.updated_at, 'Spoken category removal - 2026-01-12'
FROM student_reports sr
WHERE sr.tuition_id IN (2, 4, 8, 9, 35);

-- Archive student tuitions for spoken classes
INSERT INTO `archived_spoken_student_tuitions` (
  `original_id`, `student_id`, `tuition_id`, `status`, `academic_year_id`,
  `original_created_at`, `original_updated_at`, `archive_reason`
)
SELECT 
  sht.id, sht.student_id, sht.tuition_id, sht.status, sht.academic_year_id,
  sht.created_at, sht.updated_at, 'Spoken category removal - 2026-01-12'
FROM students_has_tuitions sht
WHERE sht.tuition_id IN (2, 4, 8, 9, 35);

-- Archive tuitions_has_grades for spoken tuitions
INSERT INTO `archived_spoken_tuitions_has_grades` (
  `original_id`, `tuition_id`, `grade_id`, 
  `original_created_at`, `original_updated_at`, `archive_reason`
)
SELECT 
  thg.id, thg.tuition_id, thg.grade_id,
  thg.created_at, thg.updated_at, 'Spoken category removal - 2026-01-12'
FROM tuitions_has_grades thg
WHERE thg.tuition_id IN (2, 4, 8, 9, 35);

-- Archive spoken tuitions
INSERT INTO `archived_spoken_tuitions` (
  `original_tuition_id`, `day_id`, `category_id`, `class_id`,
  `original_created_at`, `original_updated_at`, `archive_reason`
)
SELECT 
  t.id, t.day_id, t.category_id, t.class_id,
  t.created_at, t.updated_at, 'Spoken category removal - 2026-01-12'
FROM tuitions t
WHERE t.category_id = 1;

-- =============================================================================
-- DELETE DATA (Run these AFTER archiving is complete)
-- =============================================================================

-- Delete student reports for spoken classes
DELETE FROM student_reports WHERE tuition_id IN (2, 4, 8, 9, 35);

-- Delete student tuitions for spoken classes
DELETE FROM students_has_tuitions WHERE tuition_id IN (2, 4, 8, 9, 35);

-- Delete tuitions_has_grades for spoken tuitions
DELETE FROM tuitions_has_grades WHERE tuition_id IN (2, 4, 8, 9, 35);

-- Delete spoken tuitions (category_id = 1)
DELETE FROM tuitions WHERE category_id = 1;

-- =============================================================================
-- VERIFICATION QUERIES (Optional - run to verify)
-- =============================================================================

-- Check archived reports count
-- SELECT COUNT(*) as archived_reports FROM archived_student_reports WHERE archive_reason = 'Spoken category removal - 2026-01-12';

-- Check archived student tuitions count
-- SELECT COUNT(*) as archived_tuitions FROM archived_spoken_student_tuitions WHERE archive_reason = 'Spoken category removal - 2026-01-12';

-- Verify no spoken tuitions remain
-- SELECT * FROM tuitions WHERE category_id = 1;

-- View archived data summary
-- SELECT 
--   (SELECT COUNT(*) FROM archived_student_reports WHERE archive_reason LIKE '%Spoken%') as archived_reports,
--   (SELECT COUNT(*) FROM archived_spoken_student_tuitions WHERE archive_reason LIKE '%Spoken%') as archived_enrollments,
--   (SELECT COUNT(*) FROM archived_spoken_tuitions WHERE archive_reason LIKE '%Spoken%') as archived_tuitions,
--   (SELECT COUNT(*) FROM archived_spoken_tuitions_has_grades WHERE archive_reason LIKE '%Spoken%') as archived_grade_links;
