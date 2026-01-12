<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds a marking_deadline column to grades table to allow archived grades
     * (like Grade 11 2025) to be marked until a specific date (e.g., February 2026).
     */
    public function up(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->date('marking_deadline')->nullable()->after('grade_name')
                ->comment('Optional deadline until which this grade can be marked (for archived grades)');
        });

        // Set marking deadline for Grade 11 2025 to end of February 2026
        DB::table('grades')
            ->where('grade_name', 'Grade 11 2025')
            ->update(['marking_deadline' => '2026-02-28']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn('marking_deadline');
        });
    }
};
