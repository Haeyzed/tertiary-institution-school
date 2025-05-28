<?php

namespace App\Enums;

/**
 * Class DayOfWeekEnum
 *
 * Represents days of the week in the system.
 *
 * @package App\Enums
 */
enum DayOfWeekEnum: string
{
    case MONDAY = 'monday';
    case TUESDAY = 'tuesday';
    case WEDNESDAY = 'wednesday';
    case THURSDAY = 'thursday';
    case FRIDAY = 'friday';
    case SATURDAY = 'saturday';
    case SUNDAY = 'sunday';

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
            self::MONDAY => 'Monday',
            self::TUESDAY => 'Tuesday',
            self::WEDNESDAY => 'Wednesday',
            self::THURSDAY => 'Thursday',
            self::FRIDAY => 'Friday',
            self::SATURDAY => 'Saturday',
            self::SUNDAY => 'Sunday',
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
