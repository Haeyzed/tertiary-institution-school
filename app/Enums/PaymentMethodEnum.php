<?php

namespace App\Enums;

/**
 * Class PaymentMethodEnum
 *
 * Represents payment method options in the system.
 *
 * @package App\Enums
 */
enum PaymentMethodEnum: string
{
    case CASH = 'cash';
    case BANK_TRANSFER = 'bank_transfer';
    case CREDIT_CARD = 'credit_card';
    case DEBIT_CARD = 'debit_card';
    case MOBILE_MONEY = 'mobile_money';
    case CHEQUE = 'cheque';
    case PAYSTACK = 'paystack';

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
            self::CASH => 'Cash',
            self::BANK_TRANSFER => 'Bank Transfer',
            self::CREDIT_CARD => 'Credit Card',
            self::DEBIT_CARD => 'Debit Card',
            self::MOBILE_MONEY => 'Mobile Money',
            self::CHEQUE => 'Cheque',
            self::PAYSTACK => 'Paystack',
        };
    }
}
