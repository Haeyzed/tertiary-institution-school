<?php

namespace App\Enums;

/**
 * Class PaymentStatusEnum
 * 
 * Represents the payment status options available in the system.
 * 
 * @package App\Enums
 */
class PaymentStatusEnum
{
    /**
     * Pending payment status.
     *
     * @var string
     */
    public const PENDING = 'pending';
    
    /**
     * Completed payment status.
     *
     * @var string
     */
    public const COMPLETED = 'completed';
    
    /**
     * Partial payment status.
     *
     * @var string
     */
    public const PARTIAL = 'partial';
    
    /**
     * Failed payment status.
     *
     * @var string
     */
    public const FAILED = 'failed';
    
    /**
     * Refunded payment status.
     *
     * @var string
     */
    public const REFUNDED = 'refunded';
    
    /**
     * Get all available payment status options.
     *
     * @return array
     */
    public static function values(): array
    {
        return [
            self::PENDING,
            self::COMPLETED,
            self::PARTIAL,
            self::FAILED,
            self::REFUNDED,
        ];
    }
    
    /**
     * Get all payment status options with labels.
     *
     * @return array
     */
    public static function options(): array
    {
        return [
            self::PENDING => 'Pending',
            self::COMPLETED => 'Completed',
            self::PARTIAL => 'Partial',
            self::FAILED => 'Failed',
            self::REFUNDED => 'Refunded',
        ];
    }
}
