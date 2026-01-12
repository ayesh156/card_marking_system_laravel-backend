<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates archive tables for spoken class data
     */
    public function up(): void
    {
        // Archive table for student reports
        if (!Schema::hasTable('archived_student_reports')) {
            Schema::create('archived_student_reports', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('original_report_id');
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('tuition_id');
                $table->unsignedBigInteger('month_id');
                $table->unsignedBigInteger('year_id');
                $table->tinyInteger('week1')->default(0);
                $table->tinyInteger('week2')->default(0);
                $table->tinyInteger('week3')->default(0);
                $table->tinyInteger('week4')->default(0);
                $table->tinyInteger('week5')->default(0);
                $table->tinyInteger('paid')->default(0);
                $table->tinyInteger('reminder_week3')->default(0);
                $table->tinyInteger('reminder_week4')->default(0);
                $table->timestamp('original_created_at')->nullable();
                $table->timestamp('original_updated_at')->nullable();
                $table->string('archive_reason', 100)->nullable();
                $table->timestamp('archived_at')->useCurrent();

                $table->index('student_id');
                $table->index('tuition_id');
                $table->index('year_id');
            });
        }

        // Archive table for spoken student tuitions
        if (!Schema::hasTable('archived_spoken_student_tuitions')) {
            Schema::create('archived_spoken_student_tuitions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('original_id');
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('tuition_id');
                $table->tinyInteger('status')->default(1);
                $table->unsignedBigInteger('academic_year_id')->nullable();
                $table->timestamp('original_created_at')->nullable();
                $table->timestamp('original_updated_at')->nullable();
                $table->string('archive_reason', 100)->nullable();
                $table->timestamp('archived_at')->useCurrent();

                $table->index('student_id');
                $table->index('tuition_id');
            });
        }

        // Archive table for spoken tuitions
        if (!Schema::hasTable('archived_spoken_tuitions')) {
            Schema::create('archived_spoken_tuitions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('original_tuition_id');
                $table->unsignedBigInteger('day_id');
                $table->unsignedBigInteger('category_id');
                $table->unsignedBigInteger('class_id');
                $table->timestamp('original_created_at')->nullable();
                $table->timestamp('original_updated_at')->nullable();
                $table->string('archive_reason', 100)->nullable();
                $table->timestamp('archived_at')->useCurrent();
            });
        }

        // Archive table for spoken tuitions_has_grades
        if (!Schema::hasTable('archived_spoken_tuitions_has_grades')) {
            Schema::create('archived_spoken_tuitions_has_grades', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('original_id');
                $table->unsignedBigInteger('tuition_id');
                $table->unsignedBigInteger('grade_id');
                $table->timestamp('original_created_at')->nullable();
                $table->timestamp('original_updated_at')->nullable();
                $table->string('archive_reason', 100)->nullable();
                $table->timestamp('archived_at')->useCurrent();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archived_student_reports');
        Schema::dropIfExists('archived_spoken_student_tuitions');
        Schema::dropIfExists('archived_spoken_tuitions');
        Schema::dropIfExists('archived_spoken_tuitions_has_grades');
    }
};
