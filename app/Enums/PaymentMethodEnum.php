<?php

namespace App\Enums;

/**
 * Class PaymentMethodEnum
 * 
 * Represents the payment method options available in the system.
 * 
 * @package App\Enums
 */
class PaymentMethodEnum
{
    /**
     * Cash payment method.
     *
     * @var string
     */
    public const CASH = 'cash';
    
    /**
     * Bank transfer payment method.
     *
     * @var string
     */
    public const BANK_TRANSFER = 'bank_transfer';
    
    /**
     * Credit card payment method.
     *
     * @var string
     */
    public const CREDIT_CARD = 'credit_card';
    
    /**
     * Debit card payment method.
     *
     * @var string
     */
    public const DEBIT_CARD = 'debit_card';
    
    /**
     * Mobile money payment method.
     *
     * @var string
     */
    public const MOBILE_MONEY = 'mobile_money';
    
    /**
     * Cheque payment method.
     *
     * @var string
     */
    public const CHEQUE = 'cheque';
    
    /**
     * Get all available payment method options.
     *
     * @return array
     */
    public static function values(): array
    {
        return [
            self::CASH,
            self::BANK_TRANSFER,
            self::CREDIT_CARD,
            self::DEBIT_CARD,
            self::MOBILE_MONEY,
            self::CHEQUE,
        ];
    }
    
    /**
     * Get all payment method options with labels.
     *
     * @return array
     */
    public static function options(): array
    {
        return [
            self::CASH => 'Cash',
            self::BANK_TRANSFER => 'Bank Transfer',
            self::CREDIT_CARD => 'Credit Card',
            self::DEBIT_CARD => 'Debit Card',
            self::MOBILE_MONEY => 'Mobile Money',
            self::CHEQUE => 'Cheque',
        ];
    }
}
