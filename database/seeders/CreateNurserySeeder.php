<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CreateNurserySeeder extends Seeder
{
    /**
     * Create Nursery grade with default tuition.
     */
    public function run(): void
    {
        // Check if Nursery already exists
        $existingNursery = DB::table('grades')->where('grade_name', 'Nursery')->first();
        
        if ($existingNursery) {
            $this->command->info('Nursery grade already exists (ID: ' . $existingNursery->id . ')');
            return;
        }
        
        // Create Nursery grade
        $nurseryId = DB::table('grades')->insertGetId([
            'grade_name' => 'Nursery',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->command->info('Created Nursery grade (ID: ' . $nurseryId . ')');
        
        // Create default tuition (Sunday Theory English)
        $tuitionId = DB::table('tuitions')->insertGetId([
            'day_id' => 7, // Sunday
            'category_id' => 2, // Theory
            'class_id' => 1, // English
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->command->info('Created default tuition (ID: ' . $tuitionId . ') - Sunday Theory English');
        
        // Link tuition to Nursery grade
        DB::table('tuitions_has_grades')->insert([
            'tuition_id' => $tuitionId,
            'grade_id' => $nurseryId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->command->info('Linked Nursery to tuition');
    }
}
