<?php

namespace App\Models;

use App\Enums\ExamStatusEnum;
use Database\Factories\ExamFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    /** @use HasFactory<ExamFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'course_id',
        'semester_id',
        'exam_date',
        'start_time',
        'end_time',
        'total_marks',
        'venue',
        'status',
    ];

    /**
     * Get the course that owns the exam.
     *
     * @return BelongsTo
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the semester that owns the exam.
     *
     * @return BelongsTo
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Get the results for the exam.
     *
     * @return HasMany
     */
    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    /**
     * Check if the exam is pending.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->hasStatus(ExamStatusEnum::PENDING->value);
    }

    /**
     * Check if the exam has a specific status.
     *
     * @param string $status
     * @return bool
     */
    public function hasStatus(string $status): bool
    {
        return $this->status === $status;
    }

    /**
     * Check if the exam is ongoing.
     *
     * @return bool
     */
    public function isOngoing(): bool
    {
        return $this->hasStatus(ExamStatusEnum::ONGOING->value);
    }

    /**
     * Check if the exam is completed.
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->hasStatus(ExamStatusEnum::COMPLETED->value);
    }

    /**
     * Check if the exam is cancelled.
     *
     * @return bool
     */
    public function isCancelled(): bool
    {
        return $this->hasStatus(ExamStatusEnum::CANCELLED->value);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'exam_date' => 'date',
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'total_marks' => 'decimal:2',
        ];
    }
}
