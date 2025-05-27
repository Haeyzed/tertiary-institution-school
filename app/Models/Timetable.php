<?php

namespace App\Models;

use App\Enums\DayOfWeekEnum;
use Database\Factories\TimetableFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Timetable extends Model
{
    /** @use HasFactory<TimetableFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'course_id',
        'semester_id',
        'staff_id',
        'day',
        'start_time',
        'end_time',
        'venue',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
        ];
    }

    /**
     * Get the course that owns the timetable.
     *
     * @return BelongsTo
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the semester that owns the timetable.
     *
     * @return BelongsTo
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Get the staff that owns the timetable.
     *
     * @return BelongsTo
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Check if the timetable is on a specific day.
     *
     * @param string $day
     * @return bool
     */
    public function isOnDay(string $day): bool
    {
        return $this->day === $day;
    }

    /**
     * Check if the timetable is on Monday.
     *
     * @return bool
     */
    public function isOnMonday(): bool
    {
        return $this->isOnDay(DayOfWeekEnum::MONDAY);
    }

    /**
     * Check if the timetable is on Tuesday.
     *
     * @return bool
     */
    public function isOnTuesday(): bool
    {
        return $this->isOnDay(DayOfWeekEnum::TUESDAY);
    }

    /**
     * Check if the timetable is on Wednesday.
     *
     * @return bool
     */
    public function isOnWednesday(): bool
    {
        return $this->isOnDay(DayOfWeekEnum::WEDNESDAY);
    }

    /**
     * Check if the timetable is on Thursday.
     *
     * @return bool
     */
    public function isOnThursday(): bool
    {
        return $this->isOnDay(DayOfWeekEnum::THURSDAY);
    }

    /**
     * Check if the timetable is on Friday.
     *
     * @return bool
     */
    public function isOnFriday(): bool
    {
        return $this->isOnDay(DayOfWeekEnum::FRIDAY);
    }

    /**
     * Check if the timetable is on Saturday.
     *
     * @return bool
     */
    public function isOnSaturday(): bool
    {
        return $this->isOnDay(DayOfWeekEnum::SATURDAY);
    }

    /**
     * Check if the timetable is on Sunday.
     *
     * @return bool
     */
    public function isOnSunday(): bool
    {
        return $this->isOnDay(DayOfWeekEnum::SUNDAY);
    }
}
