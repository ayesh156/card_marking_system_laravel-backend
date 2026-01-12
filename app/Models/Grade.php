<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Grade extends Model
{
    protected $fillable = [
        'grade_name',
        'marking_deadline', // Date until which archived grades can be marked
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'marking_deadline' => 'date',
    ];

    /**
     * Define the many-to-many relationship with the Tuition model.
     */
    public function tuitions()
    {
        return $this->belongsToMany(Tuition::class, 'tuitions_has_grades', 'grade_id', 'tuition_id');
    }

    /**
     * Check if this grade can still be marked (attendance/payment).
     * Archived grades with a marking_deadline can be marked until that date.
     * 
     * @return bool
     */
    public function canBeMarked(): bool
    {
        // If no marking deadline is set, grade can always be marked
        if (is_null($this->marking_deadline)) {
            return true;
        }

        // Check if current date is before or on the marking deadline
        return Carbon::now()->lte($this->marking_deadline);
    }

    /**
     * Check if this is an archived grade (has year suffix in name).
     * 
     * @return bool
     */
    public function isArchived(): bool
    {
        return preg_match('/\b(20\d{2})\b/', $this->grade_name) === 1;
    }

    /**
     * Get the year from the grade name if it's an archived grade.
     * 
     * @return int|null
     */
    public function getGradeYear(): ?int
    {
        if (preg_match('/\b(20\d{2})\b/', $this->grade_name, $matches)) {
            return (int) $matches[1];
        }
        return null;
    }
}
