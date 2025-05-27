<?php

namespace App\Models;

use App\Enums\NotificationTypeEnum;
use Database\Factories\NotificationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    /** @use HasFactory<NotificationFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'message',
        'user_id',
        'type',
        'is_read',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the notification.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the notification has a specific type.
     *
     * @param string $type
     * @return bool
     */
    public function hasType(string $type): bool
    {
        return $this->type === $type;
    }

    /**
     * Check if the notification is general.
     *
     * @return bool
     */
    public function isGeneral(): bool
    {
        return $this->hasType(NotificationTypeEnum::GENERAL);
    }

    /**
     * Check if the notification is academic.
     *
     * @return bool
     */
    public function isAcademic(): bool
    {
        return $this->hasType(NotificationTypeEnum::ACADEMIC);
    }

    /**
     * Check if the notification is financial.
     *
     * @return bool
     */
    public function isFinancial(): bool
    {
        return $this->hasType(NotificationTypeEnum::FINANCIAL);
    }

    /**
     * Check if the notification is an alert.
     *
     * @return bool
     */
    public function isAlert(): bool
    {
        return $this->hasType(NotificationTypeEnum::ALERT);
    }
}
