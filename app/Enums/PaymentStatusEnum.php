<?php

namespace App\Enums;

/**
 * Class PaymentStatusEnum
 *
 * Represents payment status options in the system.
 *
 * @package App\Enums
 */
enum PaymentStatusEnum: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case PARTIAL = 'partial';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';

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

    /**
     * Get a human-readable label for the enum value.
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::COMPLETED => 'Completed',
            self::PARTIAL => 'Partial',
            self::FAILED => 'Failed',
            self::REFUNDED => 'Refunded',
        };
    }
}
