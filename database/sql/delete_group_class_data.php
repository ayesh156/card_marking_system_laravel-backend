<?php
// Script to delete all Group class data

// Group tuition IDs
$groupTuitionIds = [3, 5, 36, 37, 38, 39, 40, 41, 42, 43, 44];

// Delete reports
$reportsDeleted = DB::table('student_reports')
    ->whereIn('tuition_id', $groupTuitionIds)
    ->delete();

// Delete student enrollments
$enrollmentsDeleted = DB::table('students_has_tuitions')
    ->whereIn('tuition_id', $groupTuitionIds)
    ->delete();

echo "Deleted $reportsDeleted reports and $enrollmentsDeleted student enrollments from Group classes.";
