<?php

namespace App\Models;

use Database\Factories\DepartmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    /** @use HasFactory<DepartmentFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'faculty_id',
    ];

    /**
     * Get the faculty that owns the department.
     *
     * @return BelongsTo
     */
    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    /**
     * Get the programs for the department.
     *
     * @return HasMany
     */
    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }

    /**
     * Get the courses for the department.
     *
     * @return HasMany
     */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    /**
     * Get the staff for the department.
     *
     * @return HasMany
     */
    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }
}
