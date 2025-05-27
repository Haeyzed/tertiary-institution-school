<?php

namespace App\Enums;

/**
 * Class NotificationTypeEnum
 * 
 * Represents the notification type options available in the system.
 * 
 * @package App\Enums
 */
class NotificationTypeEnum
{
    /**
     * General notification type.
     *
     * @var string
     */
    public const GENERAL = 'general';
    
    /**
     * Academic notification type.
     *
     * @var string
     */
    public const ACADEMIC = 'academic';
    
    /**
     * Financial notification type.
     *
     * @var string
     */
    public const FINANCIAL = 'financial';
    
    /**
     * Alert notification type.
     *
     * @var string
     */
    public const ALERT = 'alert';
    
    /**
     * Get all available notification type options.
     *
     * @return array
     */
    public static function values(): array
    {
        return [
            self::GENERAL,
            self::ACADEMIC,
            self::FINANCIAL,
            self::ALERT,
        ];
    }
    
    /**
     * Get all notification type options with labels.
     *
     * @return array
     */
    public static function options(): array
    {
        return [
            self::GENERAL => 'General',
            self::ACADEMIC => 'Academic',
            self::FINANCIAL => 'Financial',
            self::ALERT => 'Alert',
        ];
    }
}
