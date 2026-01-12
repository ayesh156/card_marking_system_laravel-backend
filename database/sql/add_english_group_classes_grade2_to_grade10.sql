-- ============================================================================
-- SQL Script: Add English Group Classes for Grade 2 to Grade 10
-- Date: 2026-01-12
-- Description: Creates tuitions for English Group classes and links them to
--              grades 2 through 10. These group classes are held on Saturday.
-- ============================================================================

-- NOTE: Before running this script, verify the following IDs in your database:
-- day_id = 6 (Saturday)
-- category_id = 4 (Group)  
-- class_id = 1 (English)
-- grade_ids: Grade 2=1, Grade 3=2, Grade 4=3, Grade 5=4, Grade 6=5, Grade 7=6, Grade 8=7, Grade 9=8, Grade 10=9

-- ============================================================================
-- Step 1: Create new tuitions for English Group classes (Saturday Group English)
-- Each grade will have its own tuition entry
-- ============================================================================

-- Note: The existing tuition_id=3 is already Saturday Group English for Grade 9
-- We need to create new tuitions for other grades

-- Insert tuitions for English Group classes (Grade 2-8 and Grade 10)
-- Grade 9 already has tuition_id=3 linked

INSERT INTO `tuitions` (`day_id`, `category_id`, `class_id`, `created_at`, `updated_at`) VALUES
(6, 4, 1, NOW(), NOW()),  -- For Grade 2 (English Group Saturday)
(6, 4, 1, NOW(), NOW()),  -- For Grade 3 (English Group Saturday)
(6, 4, 1, NOW(), NOW()),  -- For Grade 4 (English Group Saturday)
(6, 4, 1, NOW(), NOW()),  -- For Grade 5 (English Group Saturday)
(6, 4, 1, NOW(), NOW()),  -- For Grade 6 (English Group Saturday)
(6, 4, 1, NOW(), NOW()),  -- For Grade 7 (English Group Saturday)
(6, 4, 1, NOW(), NOW()),  -- For Grade 8 (English Group Saturday)
(6, 4, 1, NOW(), NOW());  -- For Grade 10 (English Group Saturday)

-- ============================================================================
-- Step 2: Link the new tuitions to their respective grades
-- We need to get the IDs of the tuitions we just created
-- ============================================================================

-- Get the last inserted ID and link tuitions to grades
-- Assuming tuitions are inserted with auto-increment IDs starting from the last ID

SET @last_id = LAST_INSERT_ID();

INSERT INTO `tuitions_has_grades` (`tuition_id`, `grade_id`, `created_at`, `updated_at`) VALUES
(@last_id, 1, NOW(), NOW()),       -- Tuition for Grade 2 (grade_id=1)
(@last_id + 1, 2, NOW(), NOW()),   -- Tuition for Grade 3 (grade_id=2)
(@last_id + 2, 3, NOW(), NOW()),   -- Tuition for Grade 4 (grade_id=3)
(@last_id + 3, 4, NOW(), NOW()),   -- Tuition for Grade 5 (grade_id=4)
(@last_id + 4, 5, NOW(), NOW()),   -- Tuition for Grade 6 (grade_id=5)
(@last_id + 5, 6, NOW(), NOW()),   -- Tuition for Grade 7 (grade_id=6)
(@last_id + 6, 7, NOW(), NOW()),   -- Tuition for Grade 8 (grade_id=7)
(@last_id + 7, 9, NOW(), NOW());   -- Tuition for Grade 10 (grade_id=9)

-- Note: Grade 9 (grade_id=8) already has Saturday Group English via tuition_id=3 (tuitions_has_grades id=11)

-- ============================================================================
-- Verification Queries (run after insertion to verify)
-- ============================================================================

-- Check all English Group tuitions
-- SELECT t.id as tuition_id, d.day_name, c.category_name, cl.class_name, g.grade_name
-- FROM tuitions t
-- JOIN days d ON t.day_id = d.id
-- JOIN categories c ON t.category_id = c.id
-- JOIN classes cl ON t.class_id = cl.id
-- JOIN tuitions_has_grades thg ON t.id = thg.tuition_id
-- JOIN grades g ON thg.grade_id = g.id
-- WHERE t.category_id = 4 AND t.class_id = 1
-- ORDER BY g.id;

-- ============================================================================
-- Summary of changes:
-- - 8 new tuition entries created for Saturday Group English
-- - 8 new tuitions_has_grades entries linking tuitions to Grade 2-8 and Grade 10
-- - Grade 9 already has existing Saturday Group English (tuition_id=3)
-- ============================================================================
