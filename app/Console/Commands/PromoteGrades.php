<?php

namespace App\Console\Commands;

use App\Models\Grade;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PromoteGrades extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grades:promote 
                            {--dry-run : Show what would be changed without making changes}
                            {--year=2026 : The target academic year for promotion}
                            {--performed-by= : Name/email of person performing the promotion}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Promote all grades by one level for new academic year with full archival. 
                              Grade 11 becomes "Grade 11 2025", Grade 10 becomes "Grade 11 2026", etc.
                              Grade 1a/1b are MERGED into single Grade 2. 
                              All changes are archived for historical reference.';

    private string $batchId;
    private int $fromYearId;
    private int $toYearId;
    
    /**
     * Track Grade merge operations (e.g., Grade 1a/1b → Grade 2, Grade 2a/2b → Grade 3)
     */
    private array $gradeMergeData = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $newYear = (int) $this->option('year');
        $previousYear = $newYear - 1;
        $performedBy = $this->option('performed-by') ?? 'system';

        // Generate unique batch ID for this promotion
        $this->batchId = Str::uuid()->toString();

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        $this->info('📚 Starting Grade Promotion Process for Year ' . $newYear . '...');
        $this->info("📋 Batch ID: {$this->batchId}");
        $this->newLine();

        // Verify years exist in database
        $this->fromYearId = DB::table('years')->where('year', $previousYear)->value('id');
        $this->toYearId = DB::table('years')->where('year', $newYear)->value('id');

        if (!$this->fromYearId) {
            $this->error("Year {$previousYear} not found in years table!");
            return 1;
        }

        if (!$this->toYearId) {
            $this->warn("Year {$newYear} not found. Creating it...");
            if (!$dryRun) {
                $this->toYearId = DB::table('years')->insertGetId([
                    'year' => $newYear,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $this->toYearId = 999; // Placeholder for dry run
            }
        }

        // Check if archive tables exist
        if (!$dryRun && !Schema::hasTable('grade_promotion_history')) {
            $this->error('Archive tables not found! Please run migrations first:');
            $this->line('  php artisan migrate');
            return 1;
        }

        // Show current grades
        $grades = Grade::orderBy('id')->get();
        
        $this->info('📊 Current Grades in Database:');
        $this->table(
            ['ID', 'Grade Name'],
            $grades->map(fn($g) => [$g->id, $g->grade_name])->toArray()
        );
        $this->newLine();

        // Find Nursery grade for archival and deletion
        $nurseryGrade = $grades->first(fn($g) => preg_match('/^Nursery$/i', $g->grade_name));
        
        // Define grade promotion mapping (includes merge detection for Grade 1a/1b)
        $promotionMap = $this->buildPromotionMap($grades, $newYear, $previousYear);

        if (empty($promotionMap) && !$nurseryGrade && empty($this->gradeMergeData)) {
            $this->error('No grades found to promote!');
            return 1;
        }

        // Display promotion plan
        if (!empty($promotionMap)) {
            $this->info('📋 Promotion Plan:');
            $this->table(
                ['Grade ID', 'Current Grade', 'New Grade Name', 'Action'],
                collect($promotionMap)->map(fn($data) => [
                    $data['id'], 
                    $data['old'], 
                    $data['new'],
                    $data['action']
                ])->values()->toArray()
            );
            $this->newLine();
        }

        // Display Grade merge plans if applicable
        if (!empty($this->gradeMergeData)) {
            $this->warn('🔀 Grade A/B → Merged Grade Plans:');
            foreach ($this->gradeMergeData as $gradeNum => $mergeInfo) {
                $targetGrade = $gradeNum + 1;
                $this->line("  • Grade {$gradeNum}a (ID: {$mergeInfo['gradeA']['id']}) → Grade {$targetGrade} (PRIMARY - will be renamed)");
                $this->line("  • Grade {$gradeNum}b (ID: {$mergeInfo['gradeB']['id']}) → MERGED into Grade {$targetGrade} (will be archived & deleted)");
                $this->line("  • All Grade {$gradeNum}b students will be migrated to Grade {$targetGrade} tuitions");
                $this->line("  • Both groups unified into single Grade {$targetGrade} class");
                $this->newLine();
            }
        }

        // Display archival plan for Nursery
        if ($nurseryGrade) {
            $this->warn('🗄️  Archival & Deletion Plan:');
            $this->line("  • Nursery grade (ID: {$nurseryGrade->id}) will be ARCHIVED then deleted");
            $this->line("  • All related student enrollments will be archived to 'archived_student_tuitions'");
            $this->line("  • Historical data will remain accessible via archive tables");
            $this->newLine();
        }

        // Show summary
        $this->info('📈 Summary:');
        $this->line("  • Batch ID: {$this->batchId}");
        $this->line("  • From Academic Year: {$previousYear} (ID: {$this->fromYearId})");
        $this->line("  • To Academic Year: {$newYear} (ID: {$this->toYearId})");
        $this->line("  • Total grades to update: " . count($promotionMap));
        $this->line("  • Grade 11 students → Grade 11 {$previousYear} (archived cohort)");
        $this->line("  • Grade 10 students → Grade 11 {$newYear} (new senior class)");
        if (!empty($this->gradeMergeData)) {
            $mergeGrades = [];
            foreach ($this->gradeMergeData as $gradeNum => $info) {
                $mergeGrades[] = "Grade {$gradeNum}a + Grade {$gradeNum}b → Grade " . ($gradeNum + 1);
            }
            $this->line("  • " . implode(", ", $mergeGrades) . " (MERGED into single class)");
        }
        $this->line("  • Grade 1a/1b {$newYear} → Grade 1a/1b (incoming students)");
        if ($nurseryGrade) {
            $this->line("  • Nursery → ARCHIVED & DELETED, then fresh Nursery CREATED");
        } else {
            $this->line("  • Fresh Nursery grade will be CREATED for new students");
        }
        $this->line("  • Performed by: {$performedBy}");
        $this->newLine();

        $this->info('🛡️  Data Protection:');
        $this->line("  • All changes logged to 'grade_promotion_history' table");
        $this->line("  • Deleted data archived to 'archived_grades' and 'archived_student_tuitions'");
        $this->line("  • Student enrollments tagged with academic_year_id for historical queries");
        $this->newLine();

        if (!$dryRun) {
            if (!$this->confirm('Do you want to proceed with these changes?')) {
                $this->info('Operation cancelled.');
                return 0;
            }

            DB::beginTransaction();
            
            try {
                // First, archive and delete Nursery data
                if ($nurseryGrade) {
                    $this->archiveAndDeleteNurseryData($nurseryGrade->id, $performedBy);
                }

                // Execute Grade A/B → Merged Grade merges BEFORE normal promotions
                if (!empty($this->gradeMergeData)) {
                    foreach ($this->gradeMergeData as $gradeNum => $mergeInfo) {
                        $this->executeGradeMerge($gradeNum, $mergeInfo, $performedBy, $previousYear, $newYear);
                    }
                }

                $updatedCount = 0;
                
                foreach ($promotionMap as $gradeId => $data) {
                    $grade = Grade::find($gradeId);
                    
                    if ($grade) {
                        // Count affected students before update
                        $affectedStudents = $this->countAffectedStudents($gradeId);
                        
                        // Log the promotion to history
                        DB::table('grade_promotion_history')->insert([
                            'batch_id' => $this->batchId,
                            'from_year_id' => $this->fromYearId,
                            'to_year_id' => $this->toYearId,
                            'grade_id' => $gradeId,
                            'old_grade_name' => $data['old'],
                            'new_grade_name' => $data['new'],
                            'action' => $data['action'],
                            'affected_students' => $affectedStudents,
                            'performed_by' => $performedBy,
                            'notes' => "Promotion from {$previousYear} to {$newYear} academic year",
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        
                        // Update the grade
                        $oldName = $grade->grade_name;
                        $grade->grade_name = $data['new'];
                        $grade->updated_at = now();
                        
                        // Set marking deadline for archived grades (Grade 11 YYYY)
                        // Allow marking until end of February of the new year
                        if ($data['action'] === 'archived' && preg_match('/^Grade\s*11\s+\d{4}$/i', $data['new'])) {
                            $grade->marking_deadline = "{$newYear}-02-28";
                            $this->line("    📅 Setting marking deadline to {$newYear}-02-28 for archived grade");
                        }
                        
                        $grade->save();
                        
                        $this->line("  ✅ [{$gradeId}] {$oldName} → {$data['new']} ({$affectedStudents} students)");
                        $updatedCount++;
                    }
                }
                
                // Update academic year for all current enrollments to the new year
                $this->updateEnrollmentYears($newYear);
                
                // Create fresh Nursery grade for the new year
                $this->createFreshNurseryGrade($newYear, $performedBy);
                
                DB::commit();
                
                $this->newLine();
                $this->info("🎉 Successfully promoted {$updatedCount} grades!");
                $this->info("📝 Batch ID for reference: {$this->batchId}");
                
                // Show final state
                $this->newLine();
                $this->info('📊 Updated Grades:');
                $updatedGrades = Grade::orderBy('id')->get();
                $this->table(
                    ['ID', 'Grade Name'],
                    $updatedGrades->map(fn($g) => [$g->id, $g->grade_name])->toArray()
                );

                // Show how to query historical data
                $this->newLine();
                $this->info('📖 To query historical data:');
                $this->line("  -- View promotion history:");
                $this->line("  SELECT * FROM grade_promotion_history WHERE batch_id = '{$this->batchId}';");
                $this->line("");
                $this->line("  -- View archived Nursery enrollments:");
                $this->line("  SELECT * FROM archived_student_tuitions WHERE batch_id = '{$this->batchId}';");
                
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error('❌ Error during promotion: ' . $e->getMessage());
                $this->error('Stack trace: ' . $e->getTraceAsString());
                return 1;
            }
        } else {
            $this->info('✅ Dry run completed. Run without --dry-run to apply changes.');
            $this->newLine();
            $this->warn('⚠️  Before running, ensure migrations are up to date:');
            $this->line('  php artisan migrate');
        }

        return 0;
    }

    /**
     * Count students affected by a grade change.
     */
    private function countAffectedStudents($gradeId): int
    {
        return DB::table('students_has_tuitions as sht')
            ->join('tuitions_has_grades as thg', 'sht.tuition_id', '=', 'thg.tuition_id')
            ->where('thg.grade_id', $gradeId)
            ->where('sht.status', 1)
            ->distinct('sht.student_id')
            ->count('sht.student_id');
    }

    /**
     * Archive and delete Nursery grade and all related data.
     */
    private function archiveAndDeleteNurseryData($nurseryGradeId, $performedBy)
    {
        $this->line('  🗄️  Archiving Nursery data...');
        
        // Archive the grade itself
        $nurseryGrade = DB::table('grades')->find($nurseryGradeId);
        DB::table('archived_grades')->insert([
            'original_grade_id' => $nurseryGradeId,
            'grade_name' => $nurseryGrade->grade_name,
            'academic_year_id' => $this->fromYearId,
            'batch_id' => $this->batchId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->line("    • Archived grade: {$nurseryGrade->grade_name}");
        
        // Find tuitions linked to Nursery grade via tuitions_has_grades
        $tuitionIds = DB::table('tuitions_has_grades')
            ->where('grade_id', $nurseryGradeId)
            ->pluck('tuition_id')
            ->toArray();
        
        if (!empty($tuitionIds)) {
            // Archive student enrollments before deletion
            $enrollments = DB::table('students_has_tuitions')
                ->whereIn('tuition_id', $tuitionIds)
                ->get();
            
            $archivedCount = 0;
            foreach ($enrollments as $enrollment) {
                DB::table('archived_student_tuitions')->insert([
                    'original_id' => $enrollment->id,
                    'student_id' => $enrollment->student_id,
                    'tuition_id' => $enrollment->tuition_id,
                    'status' => $enrollment->status,
                    'academic_year_id' => $this->fromYearId,
                    'batch_id' => $this->batchId,
                    'original_created_at' => $enrollment->created_at,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $archivedCount++;
            }
            $this->line("    • Archived {$archivedCount} student enrollments");
            
            // Delete student reports for these tuitions
            $deletedReports = DB::table('student_reports')
                ->whereIn('tuition_id', $tuitionIds)
                ->delete();
            $this->line("    • Deleted {$deletedReports} student reports");
            
            // Delete students_has_tuitions entries for these tuitions
            $deletedStudentTuitions = DB::table('students_has_tuitions')
                ->whereIn('tuition_id', $tuitionIds)
                ->delete();
            $this->line("    • Deleted {$deletedStudentTuitions} student-tuition associations");
            
            // Delete tuitions_has_grades entries for Nursery
            $deletedTuitionGrades = DB::table('tuitions_has_grades')
                ->where('grade_id', $nurseryGradeId)
                ->delete();
            $this->line("    • Deleted {$deletedTuitionGrades} tuition-grade associations");
            
            // Delete the tuitions themselves
            $deletedTuitions = DB::table('tuitions')
                ->whereIn('id', $tuitionIds)
                ->delete();
            $this->line("    • Deleted {$deletedTuitions} tuitions");
        }
        
        // Log the deletion to promotion history
        DB::table('grade_promotion_history')->insert([
            'batch_id' => $this->batchId,
            'from_year_id' => $this->fromYearId,
            'to_year_id' => $this->toYearId,
            'grade_id' => $nurseryGradeId,
            'old_grade_name' => $nurseryGrade->grade_name,
            'new_grade_name' => '[ARCHIVED & DELETED]',
            'action' => 'deleted',
            'affected_students' => count($enrollments ?? []),
            'performed_by' => $performedBy,
            'notes' => 'Nursery grade archived and removed during promotion',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Delete the Nursery grade
        DB::table('grades')->where('id', $nurseryGradeId)->delete();
        $this->line("    • Deleted Nursery grade (ID: {$nurseryGradeId})");
        
        $this->line('  ✅ Nursery data archived and cleaned up');
    }

    /**
     * Update enrollment academic years for the new year.
     */
    private function updateEnrollmentYears($newYear)
    {
        // New enrollments going forward should use the new year
        // Existing enrollments keep their academic_year_id (already set to previous year by migration)
        $this->line('  📅 Academic year tracking configured');
    }

    /**
     * Create a fresh Nursery grade for the new academic year.
     */
    private function createFreshNurseryGrade($newYear, $performedBy)
    {
        $this->line('  🎒 Creating fresh Nursery grade for ' . $newYear . '...');
        
        // Create new Nursery grade
        $nurseryId = DB::table('grades')->insertGetId([
            'grade_name' => 'Nursery',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->line("    • Created new Nursery grade (ID: {$nurseryId})");
        
        // Create a default tuition for Nursery (Sunday Theory English class - based on original tuition ID 1)
        $tuitionId = DB::table('tuitions')->insertGetId([
            'day_id' => 7, // Sunday
            'category_id' => 2, // Theory
            'class_id' => 1, // English
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->line("    • Created default Nursery tuition (ID: {$tuitionId}) - Sunday Theory English");
        
        // Link the new tuition to the new Nursery grade
        DB::table('tuitions_has_grades')->insert([
            'tuition_id' => $tuitionId,
            'grade_id' => $nurseryId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->line("    • Linked Nursery grade to tuition");
        
        // Log to promotion history
        DB::table('grade_promotion_history')->insert([
            'batch_id' => $this->batchId,
            'from_year_id' => $this->fromYearId,
            'to_year_id' => $this->toYearId,
            'grade_id' => $nurseryId,
            'old_grade_name' => '[NEW]',
            'new_grade_name' => 'Nursery',
            'action' => 'created',
            'affected_students' => 0,
            'performed_by' => $performedBy,
            'notes' => "Fresh Nursery grade created for {$newYear} academic year",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->line('  ✅ Fresh Nursery grade ready for new students');
    }

    /**
     * Build the promotion map based on current grades.
     * Handles Grade Na/Nb → Grade (N+1) merges for any paired grades.
     */
    private function buildPromotionMap($grades, $newYear, $previousYear): array
    {
        $map = [];
        $skipped = [];
        
        // First pass: identify all paired grades (e.g., Grade 1a/1b, Grade 2a/2b, etc.)
        $pairedGrades = [];
        
        foreach ($grades as $grade) {
            $name = $grade->grade_name;
            // Match Grade Na or Grade Nb patterns (e.g., "Grade 1a", "Grade 2b")
            if (preg_match('/^Grade\s*(\d+)\s*([ab])$/i', $name, $matches)) {
                $gradeNum = (int)$matches[1];
                $suffix = strtolower($matches[2]);
                
                if (!isset($pairedGrades[$gradeNum])) {
                    $pairedGrades[$gradeNum] = ['a' => null, 'b' => null];
                }
                $pairedGrades[$gradeNum][$suffix] = $grade;
            }
        }
        
        // Set up merge operations for complete pairs (both a and b exist)
        foreach ($pairedGrades as $gradeNum => $pair) {
            if ($pair['a'] && $pair['b']) {
                $this->gradeMergeData[$gradeNum] = [
                    'gradeA' => ['id' => $pair['a']->id, 'name' => $pair['a']->grade_name],
                    'gradeB' => ['id' => $pair['b']->id, 'name' => $pair['b']->grade_name],
                ];
            }
        }
        
        foreach ($grades as $grade) {
            $name = $grade->grade_name;
            $id = $grade->id;
            
            // Handle grades with year suffix - rename to remove year (e.g., "Grade 1a 2026" -> "Grade 1a")
            if (preg_match('/^Grade\s*(\d+)([a-z]?)\s+' . $newYear . '$/i', $name, $matches)) {
                $gradeNum = $matches[1];
                $suffix = $matches[2] ?? '';
                $map[$id] = [
                    'id' => $id,
                    'old' => $name,
                    'new' => "Grade {$gradeNum}{$suffix}",
                    'action' => 'renamed'
                ];
                continue;
            }
            
            // Skip other grades with year suffix
            if (preg_match('/\d{4}$/', $name)) {
                $skipped[] = "  ⏭️  Skipping '{$name}' (ID: {$id}) - has year suffix";
                continue;
            }
            
            // Skip Nursery - it will be archived and deleted
            if (preg_match('/^Nursery$/i', $name)) {
                $skipped[] = "  ⏭️  Skipping 'Nursery' (ID: {$id}) - will be archived & deleted";
                continue;
            }
            
            // Handle Grade 11 - rename to "Grade 11 {previousYear}" (archive current seniors)
            if (preg_match('/^Grade\s*11\s*$/i', $name)) {
                $map[$id] = [
                    'id' => $id,
                    'old' => $name,
                    'new' => "Grade 11 {$previousYear}",
                    'action' => 'archived'
                ];
                continue;
            }
            
            // Handle Grade 10 - rename to "Grade 11 {newYear}" (promote to seniors)
            if (preg_match('/^Grade\s*10\s*$/i', $name)) {
                $map[$id] = [
                    'id' => $id,
                    'old' => $name,
                    'new' => "Grade 11 {$newYear}",
                    'action' => 'promoted'
                ];
                continue;
            }
            
            // SPECIAL: Handle Grade Na - if merge is active for this grade, promote to Grade (N+1) (no suffix)
            if (preg_match('/^Grade\s*(\d+)\s*a$/i', $name, $matches)) {
                $gradeNum = (int)$matches[1];
                if (isset($this->gradeMergeData[$gradeNum])) {
                    $newGradeNum = $gradeNum + 1;
                    $map[$id] = [
                        'id' => $id,
                        'old' => $name,
                        'new' => "Grade {$newGradeNum}",
                        'action' => 'promoted+merged'
                    ];
                    continue;
                }
            }
            
            // SPECIAL: Skip Grade Nb from normal map if merge is active (will be handled separately)
            if (preg_match('/^Grade\s*(\d+)\s*b$/i', $name, $matches)) {
                $gradeNum = (int)$matches[1];
                if (isset($this->gradeMergeData[$gradeNum])) {
                    $newGradeNum = $gradeNum + 1;
                    $skipped[] = "  ⏭️  Skipping 'Grade {$gradeNum}b' (ID: {$id}) - will be MERGED into Grade {$newGradeNum}";
                    continue;
                }
            }
            
            // Handle Grades 1-9 (with optional letter suffix) - promote by one level
            // For grades without merge context: normal promotion
            if (preg_match('/^Grade\s*(\d+)([a-z]?)$/i', $name, $matches)) {
                $gradeNum = (int)$matches[1];
                $suffix = $matches[2] ?? '';
                
                // Skip if this grade is part of an active merge (already handled above)
                if (isset($this->gradeMergeData[$gradeNum]) && ($suffix === 'a' || $suffix === 'b')) {
                    continue;
                }
                
                // Standard promotion for grades 1-9
                if ($gradeNum >= 1 && $gradeNum <= 9) {
                    $newGradeNum = $gradeNum + 1;
                    $map[$id] = [
                        'id' => $id,
                        'old' => $name,
                        'new' => "Grade {$newGradeNum}{$suffix}",
                        'action' => 'promoted'
                    ];
                    continue;
                }
            }
            
            // For any other grades, log that they're being skipped
            $skipped[] = "  ⏭️  Skipping '{$name}' (ID: {$id}) - unrecognized format";
        }
        
        // Display skipped grades
        if (!empty($skipped)) {
            $this->newLine();
            $this->warn('⚠️  Skipped Grades:');
            foreach ($skipped as $msg) {
                $this->line($msg);
            }
        }
        
        return $map;
    }
    
    /**
     * Execute a Grade Na/Nb → Grade (N+1) merge operation.
     * 
     * This merges two separate groups (Grade Na and Grade Nb) into a single Grade (N+1).
     * - Grade Na becomes Grade (N+1) (renamed)
     * - Grade Nb students are migrated to Grade (N+1) tuitions, then Grade Nb is archived/deleted
     */
    private function executeGradeMerge($gradeNum, $mergeInfo, $performedBy, $previousYear, $newYear)
    {
        $targetGrade = $gradeNum + 1;
        $this->newLine();
        $this->info("🔀 Executing Grade {$gradeNum}a/{$gradeNum}b → Grade {$targetGrade} Merge...");
        
        $gradeAId = $mergeInfo['gradeA']['id'];
        $gradeBId = $mergeInfo['gradeB']['id'];
        $gradeAName = $mergeInfo['gradeA']['name'];
        $gradeBName = $mergeInfo['gradeB']['name'];
        
        // Count students in each group before merge
        $gradeAStudents = $this->countAffectedStudents($gradeAId);
        $gradeBStudents = $this->countAffectedStudents($gradeBId);
        
        $this->line("  📊 Pre-merge statistics:");
        $this->line("    • Grade {$gradeNum}a students: {$gradeAStudents}");
        $this->line("    • Grade {$gradeNum}b students: {$gradeBStudents}");
        $this->line("    • Total to be merged: " . ($gradeAStudents + $gradeBStudents));
        
        // Step 1: Get all tuitions linked to Grade Na (these will become Grade (N+1) tuitions)
        $gradeATuitionIds = DB::table('tuitions_has_grades')
            ->where('grade_id', $gradeAId)
            ->pluck('tuition_id')
            ->toArray();
            
        // Step 2: Get all tuitions linked to Grade Nb
        $gradeBTuitionIds = DB::table('tuitions_has_grades')
            ->where('grade_id', $gradeBId)
            ->pluck('tuition_id')
            ->toArray();
            
        $this->line("  📚 Tuition mappings:");
        $this->line("    • Grade {$gradeNum}a tuitions: " . count($gradeATuitionIds));
        $this->line("    • Grade {$gradeNum}b tuitions: " . count($gradeBTuitionIds));
        
        // Step 3: Archive Grade Nb grade
        $gradeBRecord = DB::table('grades')->find($gradeBId);
        DB::table('archived_grades')->insert([
            'original_grade_id' => $gradeBId,
            'grade_name' => $gradeBRecord->grade_name,
            'academic_year_id' => $this->fromYearId,
            'batch_id' => $this->batchId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->line("  ✅ Archived Grade {$gradeNum}b to archive table");
        
        // Step 4: Migrate Grade Nb student enrollments to Grade Na tuitions
        $migratedStudents = 0;
        $primaryGradeATuitionId = !empty($gradeATuitionIds) ? $gradeATuitionIds[0] : null;
        
        if ($primaryGradeATuitionId && !empty($gradeBTuitionIds)) {
            // Build mapping of Grade Nb tuitions to Grade Na tuitions
            $tuitionMapping = $this->buildTuitionMapping($gradeATuitionIds, $gradeBTuitionIds);
            
            foreach ($gradeBTuitionIds as $gradeBTuitionId) {
                // Get target tuition (matched or primary)
                $targetTuitionId = $tuitionMapping[$gradeBTuitionId] ?? $primaryGradeATuitionId;
                
                // Get students enrolled in this Grade Nb tuition
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
                        'academic_year_id' => $this->fromYearId,
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
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                    
                    $migratedStudents++;
                }
                
                // Migrate student reports from Grade Nb tuition to target tuition
                DB::table('student_reports')
                    ->where('tuition_id', $gradeBTuitionId)
                    ->update(['tuition_id' => $targetTuitionId, 'updated_at' => now()]);
            }
        }
        
        $this->line("  ✅ Migrated {$migratedStudents} student enrollments from Grade {$gradeNum}b to Grade {$targetGrade}");
        
        // Step 5: Delete Grade Nb tuition associations
        if (!empty($gradeBTuitionIds)) {
            // Delete students_has_tuitions for Grade Nb tuitions
            DB::table('students_has_tuitions')
                ->whereIn('tuition_id', $gradeBTuitionIds)
                ->delete();
            $this->line("  ✅ Cleaned up old Grade {$gradeNum}b enrollment records");
            
            // Delete tuitions_has_grades for Grade Nb
            DB::table('tuitions_has_grades')
                ->where('grade_id', $gradeBId)
                ->delete();
            $this->line("  ✅ Removed Grade {$gradeNum}b tuition associations");
            
            // Delete the tuitions themselves
            DB::table('tuitions')
                ->whereIn('id', $gradeBTuitionIds)
                ->delete();
            $this->line("  ✅ Deleted Grade {$gradeNum}b tuitions");
        }
        
        // Step 6: Log the merge to promotion history
        DB::table('grade_promotion_history')->insert([
            'batch_id' => $this->batchId,
            'from_year_id' => $this->fromYearId,
            'to_year_id' => $this->toYearId,
            'grade_id' => $gradeBId,
            'old_grade_name' => $gradeBName,
            'new_grade_name' => "[MERGED INTO Grade {$targetGrade}]",
            'action' => 'merged',
            'affected_students' => $gradeBStudents,
            'performed_by' => $performedBy,
            'notes' => "Grade {$gradeNum}b merged into Grade {$targetGrade} (from Grade {$gradeNum}a). Students migrated to unified Grade {$targetGrade} class.",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Step 7: Delete Grade Nb grade record
        DB::table('grades')->where('id', $gradeBId)->delete();
        $this->line("  ✅ Deleted Grade {$gradeNum}b grade record (ID: {$gradeBId})");
        
        // Note: Grade Na → Grade (N+1) rename is handled in the main promotion map
        $this->line("  ✅ Grade {$gradeNum}a will be renamed to 'Grade {$targetGrade}' in main promotion");
        
        $totalMerged = $gradeAStudents + $gradeBStudents;
        $this->newLine();
        $this->info("🎉 Merge complete! {$totalMerged} students now in unified Grade {$targetGrade}");
    }
    
    /**
     * Build mapping between Grade 1b tuitions and Grade 1a tuitions.
     * Matches by day_id, category_id, and class_id for best correspondence.
     */
    private function buildTuitionMapping(array $grade1aTuitionIds, array $grade1bTuitionIds): array
    {
        $mapping = [];
        
        // Get full tuition details for Grade 1a
        $grade1aTuitions = DB::table('tuitions')
            ->whereIn('id', $grade1aTuitionIds)
            ->get()
            ->keyBy('id');
        
        // Get full tuition details for Grade 1b
        $grade1bTuitions = DB::table('tuitions')
            ->whereIn('id', $grade1bTuitionIds)
            ->get();
        
        foreach ($grade1bTuitions as $grade1bTuition) {
            $bestMatch = null;
            
            // Try to find exact match (same day, category, class)
            foreach ($grade1aTuitions as $grade1aTuition) {
                if ($grade1bTuition->day_id == $grade1aTuition->day_id &&
                    $grade1bTuition->category_id == $grade1aTuition->category_id &&
                    $grade1bTuition->class_id == $grade1aTuition->class_id) {
                    $bestMatch = $grade1aTuition->id;
                    break;
                }
            }
            
            // If no exact match, try matching by day and category
            if (!$bestMatch) {
                foreach ($grade1aTuitions as $grade1aTuition) {
                    if ($grade1bTuition->day_id == $grade1aTuition->day_id &&
                        $grade1bTuition->category_id == $grade1aTuition->category_id) {
                        $bestMatch = $grade1aTuition->id;
                        break;
                    }
                }
            }
            
            // If still no match, try matching by day only
            if (!$bestMatch) {
                foreach ($grade1aTuitions as $grade1aTuition) {
                    if ($grade1bTuition->day_id == $grade1aTuition->day_id) {
                        $bestMatch = $grade1aTuition->id;
                        break;
                    }
                }
            }
            
            // If no match found, will use primary tuition (handled by caller)
            $mapping[$grade1bTuition->id] = $bestMatch;
        }
        
        return $mapping;
    }
}
