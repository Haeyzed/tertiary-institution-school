<?php

namespace App\Models;

use App\Enums\StudentStatusEnum;
use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    /** @use HasFactory<StudentFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'student_id',
        'program_id',
        'parent_id',
        'admission_date',
        'current_semester',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'admission_date' => 'date',
        ];
    }

    /**
     * Get the user that owns the student.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the program that owns the student.
     *
     * @return BelongsTo
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Get the parent that owns the student.
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Parents::class, 'parent_id');
    }

    /**
     * Get the courses for the student.
     *
     * @return BelongsToMany
     */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'student_course')
            ->withPivot('semester_id')
            ->withTimestamps();
    }

    /**
     * Get the assignments for the student.
     *
     * @return HasMany
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(StudentAssignment::class);
    }

    /**
     * Get the results for the student.
     *
     * @return HasMany
     */
    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    /**
     * Get the payments for the student.
     *
     * @return HasMany
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Check if the student has a specific status.
     *
     * @param string $status
     * @return bool
     */
    public function hasStatus(string $status): bool
    {
        return $this->status === $status;
    }

    /**
     * Check if the student is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->hasStatus(StudentStatusEnum::ACTIVE);
    }

    /**
     * Check if the student is inactive.
     *
     * @return bool
     */
    public function isInactive(): bool
    {
        return $this->hasStatus(StudentStatusEnum::INACTIVE);
    }

    /**
     * Check if the student is graduated.
     *
     * @return bool
     */
    public function isGraduated(): bool
    {
        return $this->hasStatus(StudentStatusEnum::GRADUATED);
    }

    /**
     * Check if the student is suspended.
     *
     * @return bool
     */
    public function isSuspended(): bool
    {
        return $this->hasStatus(StudentStatusEnum::SUSPENDED);
    }
}
