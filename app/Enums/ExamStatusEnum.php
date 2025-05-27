<?php

namespace App\Enums;

/**
 * Class ExamStatusEnum
 * 
 * Represents the exam status options available in the system.
 * 
 * @package App\Enums
 */
class ExamStatusEnum
{
    /**
     * Pending exam status.
     *
     * @var string
     */
    public const PENDING = 'pending';
    
    /**
     * Ongoing exam status.
     *
     * @var string
     */
    public const ONGOING = 'ongoing';
    
    /**
     * Completed exam status.
     *
     * @var string
     */
    public const COMPLETED = 'completed';
    
    /**
     * Cancelled exam status.
     *
     * @var string
     */
    public const CANCELLED = 'cancelled';
    
    /**
     * Get all available exam status options.
     *
     * @return array
     */
    public static function values(): array
    {
        return [
            self::PENDING,
            self::ONGOING,
            self::COMPLETED,
            self::CANCELLED,
        ];
    }
    
    /**
     * Get all exam status options with labels.
     *
     * @return array
     */
    public static function options(): array
    {
        return [
            self::PENDING => 'Pending',
            self::ONGOING => 'Ongoing',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        ];
    }
}
