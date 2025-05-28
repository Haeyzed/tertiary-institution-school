<?php

namespace App\Enums;

/**
 * Class NotificationTypeEnum
 *
 * Represents notification type options in the system.
 *
 * @package App\Enums
 */
enum NotificationTypeEnum: string
{
    case GENERAL = 'general';
    case ACADEMIC = 'academic';
    case FINANCIAL = 'financial';
    case ALERT = 'alert';

    /**
     * Get all values as an array.
     *
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get a human-readable label for the enum value.
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::GENERAL => 'General',
            self::ACADEMIC => 'Academic',
            self::FINANCIAL => 'Financial',
            self::ALERT => 'Alert',
        };
    }

    /**
     * Get all enum values with their labels.
     *
     * @return array
     */
    public static function options(): array
    {
        return array_reduce(self::cases(), function ($carry, $enum) {
            $carry[$enum->value] = $enum->label();
            return $carry;
        }, []);
    }
}
