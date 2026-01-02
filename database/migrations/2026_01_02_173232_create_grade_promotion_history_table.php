<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('grade_promotion_history', function (Blueprint $table) {
            $table->id();
            
            // Track the promotion batch
            $table->string('batch_id', 36)->index(); // UUID for grouping related promotions
            $table->unsignedBigInteger('from_year_id'); // Academic year before promotion
            $table->unsignedBigInteger('to_year_id');   // Academic year after promotion
            
            // Grade change tracking
            $table->unsignedBigInteger('grade_id');
            $table->string('old_grade_name', 50);
            $table->string('new_grade_name', 50);
            
            // Action type: 'promoted', 'archived', 'deleted', 'renamed'
            $table->string('action', 20);
            
            // Optional: track affected student count
            $table->unsignedInteger('affected_students')->default(0);
            
            // Who performed the promotion
            $table->string('performed_by', 100)->nullable();
            
            // Any notes about this specific change
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('from_year_id')->references('id')->on('years');
            $table->foreign('to_year_id')->references('id')->on('years');
            $table->foreign('grade_id')->references('id')->on('grades')->onDelete('cascade');
        });

        // Archive table for deleted grades (like Nursery)
        Schema::create('archived_grades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_grade_id'); // Original ID before deletion
            $table->string('grade_name', 50);
            $table->unsignedBigInteger('academic_year_id');
            $table->string('batch_id', 36)->index(); // Links to promotion batch
            $table->timestamps();
            
            $table->foreign('academic_year_id')->references('id')->on('years');
        });

        // Archive table for student enrollments before deletion
        Schema::create('archived_student_tuitions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_id'); // Original students_has_tuitions ID
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('tuition_id');
            $table->tinyInteger('status')->default(1);
            $table->unsignedBigInteger('academic_year_id');
            $table->string('batch_id', 36)->index();
            $table->timestamp('original_created_at')->nullable();
            $table->timestamps();
            
            $table->foreign('student_id')->references('id')->on('students');
            $table->foreign('academic_year_id')->references('id')->on('years');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archived_student_tuitions');
        Schema::dropIfExists('archived_grades');
        Schema::dropIfExists('grade_promotion_history');
    }
};
