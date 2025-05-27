<?php

namespace App\Models;

use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    /** @use HasFactory<CourseFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'credit_hours',
        'department_id',
    ];

    /**
     * Get the department that owns the course.
     *
     * @return BelongsTo
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the semesters for the course.
     *
     * @return BelongsToMany
     */
    public function semesters(): BelongsToMany
    {
        return $this->belongsToMany(Semester::class, 'course_semester')
            ->withPivot('staff_id')
            ->withTimestamps();
    }

    /**
     * Get the students for the course.
     *
     * @return BelongsToMany
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_course')
            ->withPivot('semester_id')
            ->withTimestamps();
    }

    /**
     * Get the timetables for the course.
     *
     * @return HasMany
     */
    public function timetables(): HasMany
    {
        return $this->hasMany(Timetable::class);
    }

    /**
     * Get the exams for the course.
     *
     * @return HasMany
     */
    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    /**
     * Get the assignments for the course.
     *
     * @return HasMany
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    /**
     * Get the results for the course.
     *
     * @return HasMany
     */
    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }
}
