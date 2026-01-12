<?php

namespace App\Console\Commands;

use App\Models\Grade;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MergeGrades extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grades:merge 
                            {gradeA : The first grade to merge (e.g., "Grade 2a" or grade ID)}
                            {gradeB : The second grade to merge into the first (e.g., "Grade 2b" or grade ID)}
                            {--target= : The target grade name (default: removes suffix from gradeA)}
                            {--dry-run : Show what would be changed without making changes}
                            {--performed-by= : Name/email of person performing the merge}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manually merge two grade groups (e.g., Grade 2a + Grade 2b → Grade 2). 
                              Grade B students are migrated to Grade A tuitions, then Grade B is archived/deleted.';

    private string $batchId;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $gradeAInput = $this->argument('gradeA');
        $gradeBInput = $this->argument('gradeB');
        $targetName = $this->option('target');
        $dryRun = $this->option('dry-run');
        $performedBy = $this->option('performed-by') ?? 'system';

        // Generate unique batch ID for this merge
        $this->batchId = Str::uuid()->toString();

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        $this->info('🔀 Starting Grade Merge Process...');
        $this->info("📋 Batch ID: {$this->batchId}");
        $this->newLine();

        // Find Grade A
        $gradeA = $this->findGrade($gradeAInput);
        if (!$gradeA) {
            $this->error("Grade A not found: {$gradeAInput}");
            return 1;
        }

        // Find Grade B
        $gradeB = $this->findGrade($gradeBInput);
        if (!$gradeB) {
            $this->error("Grade B not found: {$gradeBInput}");
            return 1;
        }

        // Determine target grade name
        if (!$targetName) {
            // Extract base name by removing suffix (e.g., "Grade 2a" → "Grade 2")
            if (preg_match('/^(Grade\s*\d+)\s*[a-z]$/i', $gradeA->grade_name, $matches)) {
                $targetName = $matches[1];
            } else {
                $targetName = $gradeA->grade_name;
            }
        }

        $this->info('📊 Merge Plan:');
        $this->table(
            ['Role', 'ID', 'Current Name', 'Action'],
            [
                ['Grade A (Primary)', $gradeA->id, $gradeA->grade_name, "Rename to '{$targetName}'"],
                ['Grade B (Merge)', $gradeB->id, $gradeB->grade_name, 'Archive & Delete'],
            ]
        );
        $this->newLine();

        // Count students in each group
        $gradeAStudents = $this->countStudents($gradeA->id);
        $gradeBStudents = $this->countStudents($gradeB->id);

        $this->info('👥 Student Statistics:');
        $this->line("  • {$gradeA->grade_name} students: {$gradeAStudents}");
        $this->line("  • {$gradeB->grade_name} students: {$gradeBStudents}");
        $this->line("  • Total after merge: " . ($gradeAStudents + $gradeBStudents));
        $this->newLine();

        // Get tuition counts
        $gradeATuitionIds = DB::table('tuitions_has_grades')
            ->where('grade_id', $gradeA->id)
            ->pluck('tuition_id')
            ->toArray();
            
        $gradeBTuitionIds = DB::table('tuitions_has_grades')
            ->where('grade_id', $gradeB->id)
            ->pluck('tuition_id')
            ->toArray();

        $this->info('📚 Tuition Statistics:');
        $this->line("  • {$gradeA->grade_name} tuitions: " . count($gradeATuitionIds));
        $this->line("  • {$gradeB->grade_name} tuitions: " . count($gradeBTuitionIds));
        $this->newLine();

        $this->info('📈 Summary:');
        $this->line("  • Batch ID: {$this->batchId}");
        $this->line("  • {$gradeA->grade_name} → {$targetName} (renamed, keeps tuitions)");
        $this->line("  • {$gradeB->grade_name} students → migrated to {$targetName}");
        $this->line("  • {$gradeB->grade_name} → ARCHIVED & DELETED");
        $this->line("  • Performed by: {$performedBy}");
        $this->newLine();

        if ($dryRun) {
            $this->info('✅ Dry run completed. Run without --dry-run to apply changes.');
            return 0;
        }

        if (!$this->confirm('Do you want to proceed with this merge?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        DB::beginTransaction();

        try {
            $this->executeMerge(
                $gradeA,
                $gradeB,
                $targetName,
                $gradeATuitionIds,
                $gradeBTuitionIds,
                $gradeBStudents,
                $performedBy
            );

            DB::commit();

            $this->newLine();
            $this->info('🎉 Merge completed successfully!');
            $this->info("📝 Batch ID for reference: {$this->batchId}");

            // Show final state
            $this->newLine();
            $this->info('📊 Updated Grades:');
            $updatedGrades = Grade::orderBy('id')->get();
            $this->table(
                ['ID', 'Grade Name'],
                $updatedGrades->map(fn($g) => [$g->id, $g->grade_name])->toArray()
            );

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error during merge: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }

        return 0;
    }

    /**
     * Find a grade by ID or name.
     */
    private function findGrade($input): ?Grade
    {
        // Try as ID first
        if (is_numeric($input)) {
            return Grade::find($input);
        }

        // Try as exact name
        $grade = Grade::where('grade_name', $input)->first();
        if ($grade) {
            return $grade;
        }

        // Try case-insensitive match
        return Grade::whereRaw('LOWER(grade_name) = ?', [strtolower($input)])->first();
    }

    /**
     * Count students in a grade.
     */
    private function countStudents($gradeId): int
    {
        return DB::table('students_has_tuitions as sht')
            ->join('tuitions_has_grades as thg', 'sht.tuition_id', '=', 'thg.tuition_id')
            ->where('thg.grade_id', $gradeId)
            ->where('sht.status', 1)
            ->distinct('sht.student_id')
            ->count('sht.student_id');
    }

    /**
     * Execute the merge operation.
     */
    private function executeMerge(
        $gradeA,
        $gradeB,
        $targetName,
        array $gradeATuitionIds,
        array $gradeBTuitionIds,
        int $gradeBStudents,
        string $performedBy
    ) {
        $this->info('🔄 Executing merge...');

        // Get current year ID
        $currentYearId = DB::table('years')->orderBy('year', 'desc')->value('id') ?? 1;

        // Step 1: Archive Grade B
        $this->line('  📦 Archiving Grade B...');
        DB::table('archived_grades')->insert([
            'original_grade_id' => $gradeB->id,
            'grade_name' => $gradeB->grade_name,
            'academic_year_id' => $currentYearId,
            'batch_id' => $this->batchId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->line("    ✅ Archived: {$gradeB->grade_name}");

        // Step 2: Migrate Grade B students to Grade A tuitions
        $this->line('  👥 Migrating students...');
        $migratedStudents = 0;
        $primaryGradeATuitionId = !empty($gradeATuitionIds) ? $gradeATuitionIds[0] : null;

        if ($primaryGradeATuitionId && !empty($gradeBTuitionIds)) {
            // Build tuition mapping
            $tuitionMapping = $this->buildTuitionMapping($gradeATuitionIds, $gradeBTuitionIds);

            foreach ($gradeBTuitionIds as $gradeBTuitionId) {
                $targetTuitionId = $tuitionMapping[$gradeBTuitionId] ?? $primaryGradeATuitionId;

                // Get students enrolled in this Grade B tuition
                $studentEnrollments = DB::table('students_has_tuitions')
                    ->where('tuition_id', $gradeBTuitionId)
                    ->get();

                foreach ($studentEnrollments as $enrollment) {
                    // Archive the original enrollment
                    DB::table('archived_student_tuitions')->insert([
                        'original_id' => $enrollment->id,
                        'student_id' => $enrollment->student_id,
                        'tuition_id' => $enrollment->tuition_id,
                        'status' => $enrollment->status,
                        'academic_year_id' => $currentYearId,
                        'batch_id' => $this->batchId,
                        'original_created_at' => $enrollment->created_at,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Check if student already enrolled in target tuition
                    $existingEnrollment = DB::table('students_has_tuitions')
                        ->where('student_id', $enrollment->student_id)
                        ->where('tuition_id', $targetTuitionId)
                        ->first();

                    if (!$existingEnrollment) {
                        // Create new enrollment in target tuition
                        DB::table('students_has_tuitions')->insert([
                            'student_id' => $enrollment->student_id,
                            'tuition_id' => $targetTuitionId,
                            'status' => $enrollment->status,
                            'academic_year_id' => $enrollment->academic_year_id ?? $currentYearId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    $migratedStudents++;
                }

                // Migrate student reports
                DB::table('student_reports')
                    ->where('tuition_id', $gradeBTuitionId)
                    ->update(['tuition_id' => $targetTuitionId, 'updated_at' => now()]);
            }
        }
        $this->line("    ✅ Migrated {$migratedStudents} student enrollments");

        // Step 3: Delete Grade B associations
        $this->line('  🗑️  Cleaning up Grade B...');
        
        if (!empty($gradeBTuitionIds)) {
            // Delete students_has_tuitions for Grade B tuitions
            DB::table('students_has_tuitions')
                ->whereIn('tuition_id', $gradeBTuitionIds)
                ->delete();
            $this->line('    ✅ Deleted old enrollment records');

            // Delete tuitions_has_grades for Grade B
            DB::table('tuitions_has_grades')
                ->where('grade_id', $gradeB->id)
                ->delete();
            $this->line('    ✅ Removed tuition associations');

            // Delete the tuitions themselves
            DB::table('tuitions')
                ->whereIn('id', $gradeBTuitionIds)
                ->delete();
            $this->line('    ✅ Deleted tuitions');
        }

        // Step 4: Log the merge to promotion history
        DB::table('grade_promotion_history')->insert([
            'batch_id' => $this->batchId,
            'from_year_id' => $currentYearId,
            'to_year_id' => $currentYearId,
            'grade_id' => $gradeB->id,
            'old_grade_name' => $gradeB->grade_name,
            'new_grade_name' => "[MERGED INTO {$targetName}]",
            'action' => 'merged',
            'affected_students' => $gradeBStudents,
            'performed_by' => $performedBy,
            'notes' => "Manual merge: {$gradeB->grade_name} merged into {$targetName}",
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Step 5: Delete Grade B record
        DB::table('grades')->where('id', $gradeB->id)->delete();
        $this->line("    ✅ Deleted Grade B record (ID: {$gradeB->id})");

        // Step 6: Rename Grade A to target name
        $this->line('  ✏️  Renaming Grade A...');
        $oldName = $gradeA->grade_name;
        $gradeA->grade_name = $targetName;
        $gradeA->updated_at = now();
        $gradeA->save();
        $this->line("    ✅ Renamed: {$oldName} → {$targetName}");

        // Log the rename
        DB::table('grade_promotion_history')->insert([
            'batch_id' => $this->batchId,
            'from_year_id' => $currentYearId,
            'to_year_id' => $currentYearId,
            'grade_id' => $gradeA->id,
            'old_grade_name' => $oldName,
            'new_grade_name' => $targetName,
            'action' => 'renamed',
            'affected_students' => $this->countStudents($gradeA->id),
            'performed_by' => $performedBy,
            'notes' => "Manual merge: {$oldName} renamed to {$targetName} as merge target",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Build mapping between Grade B tuitions and Grade A tuitions.
     */
    private function buildTuitionMapping(array $gradeATuitionIds, array $gradeBTuitionIds): array
    {
        $mapping = [];

        $gradeATuitions = DB::table('tuitions')
            ->whereIn('id', $gradeATuitionIds)
            ->get()
            ->keyBy('id');

        $gradeBTuitions = DB::table('tuitions')
            ->whereIn('id', $gradeBTuitionIds)
            ->get();

        foreach ($gradeBTuitions as $gradeBTuition) {
            $bestMatch = null;

            // Try to find exact match (same day, category, class)
            foreach ($gradeATuitions as $gradeATuition) {
                if ($gradeBTuition->day_id == $gradeATuition->day_id &&
                    $gradeBTuition->category_id == $gradeATuition->category_id &&
                    $gradeBTuition->class_id == $gradeATuition->class_id) {
                    $bestMatch = $gradeATuition->id;
                    break;
                }
            }

            // If no exact match, try matching by day and category
            if (!$bestMatch) {
                foreach ($gradeATuitions as $gradeATuition) {
                    if ($gradeBTuition->day_id == $gradeATuition->day_id &&
                        $gradeBTuition->category_id == $gradeATuition->category_id) {
                        $bestMatch = $gradeATuition->id;
                        break;
                    }
                }
            }

            // If still no match, try matching by day only
            if (!$bestMatch) {
                foreach ($gradeATuitions as $gradeATuition) {
                    if ($gradeBTuition->day_id == $gradeATuition->day_id) {
                        $bestMatch = $gradeATuition->id;
                        break;
                    }
                }
            }

            $mapping[$gradeBTuition->id] = $bestMatch;
        }

        return $mapping;
    }
}
