<?php

namespace App\Enums;

/**
 * Class DayOfWeekEnum
 * 
 * Represents the days of the week.
 * 
 * @package App\Enums
 */
class DayOfWeekEnum
{
    /**
     * Monday.
     *
     * @var string
     */
    public const MONDAY = 'monday';
    
    /**
     * Tuesday.
     *
     * @var string
     */
    public const TUESDAY = 'tuesday';
    
    /**
     * Wednesday.
     *
     * @var string
     */
    public const WEDNESDAY = 'wednesday';
    
    /**
     * Thursday.
     *
     * @var string
     */
    public const THURSDAY = 'thursday';
    
    /**
     * Friday.
     *
     * @var string
     */
    public const FRIDAY = 'friday';
    
    /**
     * Saturday.
     *
     * @var string
     */
    public const SATURDAY = 'saturday';
    
    /**
     * Sunday.
     *
     * @var string
     */
    public const SUNDAY = 'sunday';
    
    /**
     * Get all available day of week options.
     *
     * @return array
     */
    public static function values(): array
    {
        return [
            self::MONDAY,
            self::TUESDAY,
            self::WEDNESDAY,
            self::THURSDAY,
            self::FRIDAY,
            self::SATURDAY,
            self::SUNDAY,
        ];
    }
    
    /**
     * Get all day of week options with labels.
     *
     * @return array
     */
    public static function options(): array
    {
        return [
            self::MONDAY => 'Monday',
            self::TUESDAY => 'Tuesday',
            self::WEDNESDAY => 'Wednesday',
            self::THURSDAY => 'Thursday',
            self::FRIDAY => 'Friday',
            self::SATURDAY => 'Saturday',
            self::SUNDAY => 'Sunday',
        ];
    }
}
