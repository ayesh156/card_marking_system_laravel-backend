<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('students_has_tuitions', function (Blueprint $table) {
            // Add academic year reference to track which year the enrollment belongs to
            $table->unsignedBigInteger('academic_year_id')->nullable()->after('status');
            $table->foreign('academic_year_id')->references('id')->on('years')->onDelete('set null');
            
            // Add index for faster queries by year
            $table->index('academic_year_id');
        });

        // Set existing records to 2025 (year_id = 1 based on db.sql)
        DB::table('students_has_tuitions')
            ->whereNull('academic_year_id')
            ->update(['academic_year_id' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students_has_tuitions', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropIndex(['academic_year_id']);
            $table->dropColumn('academic_year_id');
        });
    }
};
