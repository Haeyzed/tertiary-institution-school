<?php

namespace App\Models;

use Database\Factories\FacultyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Faculty extends Model
{
    /** @use HasFactory<FacultyFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
    ];

    /**
     * Get the departments for the faculty.
     *
     * @return HasMany
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }
}
