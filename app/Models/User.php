<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\GenderEnum;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'gender',
        'photo',
        'address',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the staff record associated with the user.
     *
     * @return HasOne
     */
    public function staff(): HasOne
    {
        return $this->hasOne(Staff::class);
    }

    /**
     * Get the student record associated with the user.
     *
     * @return HasOne
     */
    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    /**
     * Get the parent record associated with the user.
     *
     * @return HasOne
     */
    public function parent(): HasOne
    {
        return $this->hasOne(Parents::class);
    }

    /**
     * Get the notifications for the user.
     *
     * @return HasMany
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the announcements created by the user.
     *
     * @return HasMany
     */
    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'created_by');
    }

    /**
     * Check if the user has a specific gender.
     *
     * @param string $gender
     * @return bool
     */
    public function hasGender(string $gender): bool
    {
        return $this->gender === $gender;
    }

    /**
     * Check if the user is male.
     *
     * @return bool
     */
    public function isMale(): bool
    {
        return $this->hasGender(GenderEnum::MALE);
    }

    /**
     * Check if the user is female.
     *
     * @return bool
     */
    public function isFemale(): bool
    {
        return $this->hasGender(GenderEnum::FEMALE);
    }

    /**
     * Check if the user has other gender.
     *
     * @return bool
     */
    public function isOtherGender(): bool
    {
        return $this->hasGender(GenderEnum::OTHER);
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
