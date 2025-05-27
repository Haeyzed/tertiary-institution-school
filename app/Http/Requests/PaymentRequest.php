<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use Illuminate\Validation\Rule;

/**
 * Request validation for payment operations.
 *
 * Handles validation for creating and updating payment records.
 */
class PaymentRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            /**
             * The ID of the student making the payment.
             *
             * Must reference an existing student.
             * @var int $student_id
             * @example 1
             */
            'student_id' => ['required', 'integer', 'exists:students,id'],

            /**
             * The ID of the fee being paid.
             *
             * Must reference an existing fee.
             * @var int $fee_id
             * @example 1
             */
            'fee_id' => ['required', 'integer', 'exists:fees,id'],

            /**
             * The amount paid by the student.
             *
             * Must be a positive number.
             * @var float $amount_paid
             * @example 2500.00
             */
            'amount_paid' => ['required', 'numeric', 'min:0'],

            /**
             * The method used for payment.
             *
             * Must be one of the accepted payment methods.
             * @var string $payment_method
             * @example "bank_transfer"
             */
            'payment_method' => ['required', 'string', Rule::in(PaymentMethodEnum::values())],

            /**
             * The transaction ID from the payment processor.
             *
             * Optional unique identifier for the transaction.
             * @var string|null $transaction_id
             * @example "TXN123456789"
             */
            'transaction_id' => ['nullable', 'string', 'max:100'],

            /**
             * The date when the payment was made.
             *
             * Must be a valid date format.
             * @var string $payment_date
             * @example "2024-12-01"
             */
            'payment_date' => ['required', 'date'],

            /**
             * The current status of the payment.
             *
             * Must be one of the predefined payment statuses.
             * @var string|null $status
             * @example "completed"
             */
            'status' => ['nullable', 'string', Rule::in(PaymentStatusEnum::options())],

            /**
             * Additional remarks or notes about the payment.
             *
             * Optional comments about the payment.
             * @var string|null $remarks
             * @example "Partial payment for semester fee"
             */
            'remarks' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'student_id.exists' => 'The selected student does not exist.',
            'fee_id.exists' => 'The selected fee does not exist.',
            'amount_paid.min' => 'Amount paid cannot be negative.',
            'payment_method.in' => 'Invalid payment method selected.',
            'status.in' => 'Invalid payment status selected.',
        ];
    }
}
