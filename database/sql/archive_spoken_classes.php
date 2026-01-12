<?php
/**
 * Script: Archive and Delete Spoken Class Data
 * Date: 2026-01-12
 * 
 * Run this script using: php artisan tinker < database/sql/archive_spoken_classes.php
 * Or copy-paste the content into tinker
 * 
 * This script:
 * 1. Archives all spoken class data to archive tables
 * 2. Deletes the original spoken class data
 */

use Illuminate\Support\Facades\DB;

// Spoken category_id = 1
// Spoken tuition IDs: 2, 4, 8, 9, 35
$spokenTuitionIds = [2, 4, 8, 9, 35];
$archiveReason = 'Spoken category removal - 2026-01-12';

echo "=== Starting Spoken Class Data Archive ===\n\n";

// Step 1: Archive student reports
echo "Archiving student reports...\n";
$reportsToArchive = DB::table('student_reports')
    ->whereIn('tuition_id', $spokenTuitionIds)
    ->get();

$archivedReports = 0;
foreach ($reportsToArchive as $report) {
    DB::table('archived_student_reports')->insert([
        'original_report_id' => $report->id,
        'student_id' => $report->student_id,
        'tuition_id' => $report->tuition_id,
        'month_id' => $report->month_id,
        'year_id' => $report->year_id,
        'week1' => $report->week1,
        'week2' => $report->week2,
        'week3' => $report->week3,
        'week4' => $report->week4,
        'week5' => $report->week5,
        'paid' => $report->paid,
        'reminder_week3' => $report->reminder_week3,
        'reminder_week4' => $report->reminder_week4,
        'original_created_at' => $report->created_at,
        'original_updated_at' => $report->updated_at,
        'archive_reason' => $archiveReason,
    ]);
    $archivedReports++;
}
echo "Archived {$archivedReports} student reports.\n\n";

// Step 2: Archive student tuitions
echo "Archiving student tuitions...\n";
$tuitionsToArchive = DB::table('students_has_tuitions')
    ->whereIn('tuition_id', $spokenTuitionIds)
    ->get();

$archivedTuitions = 0;
foreach ($tuitionsToArchive as $tuition) {
    DB::table('archived_spoken_student_tuitions')->insert([
        'original_id' => $tuition->id,
        'student_id' => $tuition->student_id,
        'tuition_id' => $tuition->tuition_id,
        'status' => $tuition->status,
        'academic_year_id' => $tuition->academic_year_id,
        'original_created_at' => $tuition->created_at,
        'original_updated_at' => $tuition->updated_at,
        'archive_reason' => $archiveReason,
    ]);
    $archivedTuitions++;
}
echo "Archived {$archivedTuitions} student tuitions.\n\n";

// Step 3: Archive tuitions_has_grades
echo "Archiving tuition-grade links...\n";
$gradeLinksToArchive = DB::table('tuitions_has_grades')
    ->whereIn('tuition_id', $spokenTuitionIds)
    ->get();

$archivedGradeLinks = 0;
foreach ($gradeLinksToArchive as $link) {
    DB::table('archived_spoken_tuitions_has_grades')->insert([
        'original_id' => $link->id,
        'tuition_id' => $link->tuition_id,
        'grade_id' => $link->grade_id,
        'original_created_at' => $link->created_at,
        'original_updated_at' => $link->updated_at,
        'archive_reason' => $archiveReason,
    ]);
    $archivedGradeLinks++;
}
echo "Archived {$archivedGradeLinks} tuition-grade links.\n\n";

// Step 4: Archive tuitions
echo "Archiving tuitions...\n";
$tuitionsDataToArchive = DB::table('tuitions')
    ->where('category_id', 1) // Spoken category
    ->get();

$archivedTuitionsData = 0;
foreach ($tuitionsDataToArchive as $t) {
    DB::table('archived_spoken_tuitions')->insert([
        'original_tuition_id' => $t->id,
        'day_id' => $t->day_id,
        'category_id' => $t->category_id,
        'class_id' => $t->class_id,
        'original_created_at' => $t->created_at,
        'original_updated_at' => $t->updated_at,
        'archive_reason' => $archiveReason,
    ]);
    $archivedTuitionsData++;
}
echo "Archived {$archivedTuitionsData} tuitions.\n\n";

echo "=== Archive Complete ===\n";
echo "Summary:\n";
echo "- Student reports archived: {$archivedReports}\n";
echo "- Student tuitions archived: {$archivedTuitions}\n";
echo "- Grade links archived: {$archivedGradeLinks}\n";
echo "- Tuitions archived: {$archivedTuitionsData}\n\n";

// Step 5: Delete original data
echo "=== Starting Deletion ===\n\n";

// Delete in correct order due to foreign key constraints
$deletedReports = DB::table('student_reports')
    ->whereIn('tuition_id', $spokenTuitionIds)
    ->delete();
echo "Deleted {$deletedReports} student reports.\n";

$deletedStudentTuitions = DB::table('students_has_tuitions')
    ->whereIn('tuition_id', $spokenTuitionIds)
    ->delete();
echo "Deleted {$deletedStudentTuitions} student tuitions.\n";

$deletedGradeLinks = DB::table('tuitions_has_grades')
    ->whereIn('tuition_id', $spokenTuitionIds)
    ->delete();
echo "Deleted {$deletedGradeLinks} tuition-grade links.\n";

$deletedTuitions = DB::table('tuitions')
    ->where('category_id', 1)
    ->delete();
echo "Deleted {$deletedTuitions} spoken tuitions.\n\n";

echo "=== All Done! ===\n";
echo "Spoken class data has been archived and removed.\n";
echo "You can view archived data in:\n";
echo "- archived_student_reports\n";
echo "- archived_spoken_student_tuitions\n";
echo "- archived_spoken_tuitions\n";
echo "- archived_spoken_tuitions_has_grades\n";
