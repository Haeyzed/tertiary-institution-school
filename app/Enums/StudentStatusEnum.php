<?php

namespace App\Enums;

/**
 * Class StudentStatusEnum
 * 
 * Represents the status options available for students.
 * 
 * @package App\Enums
 */
class StudentStatusEnum
{
    /**
     * Active student status.
     *
     * @var string
     */
    public const ACTIVE = 'active';
    
    /**
     * Inactive student status.
     *
     * @var string
     */
    public const INACTIVE = 'inactive';
    
    /**
     * Graduated student status.
     *
     * @var string
     */
    public const GRADUATED = 'graduated';
    
    /**
     * Suspended student status.
     *
     * @var string
     */
    public const SUSPENDED = 'suspended';
    
    /**
     * Get all available student status options.
     *
     * @return array
     */
    public static function values(): array
    {
        return [
            self::ACTIVE,
            self::INACTIVE,
            self::GRADUATED,
            self::SUSPENDED,
        ];
    }
    
    /**
     * Get all student status options with labels.
     *
     * @return array
     */
    public static function options(): array
    {
        return [
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::GRADUATED => 'Graduated',
            self::SUSPENDED => 'Suspended',
        ];
    }
}
