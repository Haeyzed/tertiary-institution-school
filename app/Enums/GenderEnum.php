<?php

namespace App\Enums;

/**
 * Class GenderEnum
 * 
 * Represents the gender options available in the system.
 * 
 * @package App\Enums
 */
class GenderEnum
{
    /**
     * Male gender.
     *
     * @var string
     */
    public const MALE = 'male';
    
    /**
     * Female gender.
     *
     * @var string
     */
    public const FEMALE = 'female';
    
    /**
     * Other gender.
     *
     * @var string
     */
    public const OTHER = 'other';
    
    /**
     * Get all available gender options.
     *
     * @return array
     */
    public static function values(): array
    {
        return [
            self::MALE,
            self::FEMALE,
            self::OTHER,
        ];
    }
    
    /**
     * Get all gender options with labels.
     *
     * @return array
     */
    public static function options(): array
    {
        return [
            self::MALE => 'Male',
            self::FEMALE => 'Female',
            self::OTHER => 'Other',
        ];
    }
}
