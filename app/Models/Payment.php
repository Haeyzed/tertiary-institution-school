<?php

namespace App\Models;

use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'fee_id',
        'amount_paid',
        'payment_method',
        'transaction_id',
        'payment_date',
        'status',
        'remarks',
        'payment_method_id',
    ];

    /**
     * Get the student that owns the payment.
     *
     * @return BelongsTo
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the fee that owns the payment.
     *
     * @return BelongsTo
     */
    public function fee(): BelongsTo
    {
        return $this->belongsTo(Fee::class);
    }

    /**
     * Get the payment method that owns the payment.
     *
     * @return BelongsTo
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Check if the payment is cash.
     *
     * @return bool
     */
    public function isCash(): bool
    {
        return $this->hasPaymentMethod(PaymentMethodEnum::CASH->value);
    }

    /**
     * Check if the payment has a specific payment method.
     *
     * @param string $method
     * @return bool
     */
    public function hasPaymentMethod(string $method): bool
    {
        return $this->payment_method === $method;
    }

    /**
     * Check if the payment is bank transfer.
     *
     * @return bool
     */
    public function isBankTransfer(): bool
    {
        return $this->hasPaymentMethod(PaymentMethodEnum::BANK_TRANSFER->value);
    }

    /**
     * Check if the payment is credit card.
     *
     * @return bool
     */
    public function isCreditCard(): bool
    {
        return $this->hasPaymentMethod(PaymentMethodEnum::CREDIT_CARD->value);
    }

    /**
     * Check if the payment is debit card.
     *
     * @return bool
     */
    public function isDebitCard(): bool
    {
        return $this->hasPaymentMethod(PaymentMethodEnum::DEBIT_CARD->value);
    }

    /**
     * Check if the payment is mobile money.
     *
     * @return bool
     */
    public function isMobileMoney(): bool
    {
        return $this->hasPaymentMethod(PaymentMethodEnum::MOBILE_MONEY->value);
    }

    /**
     * Check if the payment is cheque.
     *
     * @return bool
     */
    public function isCheque(): bool
    {
        return $this->hasPaymentMethod(PaymentMethodEnum::CHEQUE->value);
    }

    /**
     * Check if the payment is pending.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->hasStatus(PaymentStatusEnum::PENDING->value);
    }

    /**
     * Check if the payment has a specific status.
     *
     * @param string $status
     * @return bool
     */
    public function hasStatus(string $status): bool
    {
        return $this->status === $status;
    }

    /**
     * Check if the payment is completed.
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->hasStatus(PaymentStatusEnum::COMPLETED->value);
    }

    /**
     * Check if the payment is partial.
     *
     * @return bool
     */
    public function isPartial(): bool
    {
        return $this->hasStatus(PaymentStatusEnum::PARTIAL->value);
    }

    /**
     * Check if the payment is failed.
     *
     * @return bool
     */
    public function isFailed(): bool
    {
        return $this->hasStatus(PaymentStatusEnum::FAILED->value);
    }

    /**
     * Check if the payment is refunded.
     *
     * @return bool
     */
    public function isRefunded(): bool
    {
        return $this->hasStatus(PaymentStatusEnum::REFUNDED->value);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount_paid' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }
}
