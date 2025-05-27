<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /**
             * Unique identifier of the payment.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * The amount paid by the student.
             *
             * @var float $amount_paid
             * @example 2500.00
             */
            'amount_paid' => $this->amount_paid,

            /**
             * The method used for payment.
             *
             * @var string $payment_method
             * @example "bank_transfer"
             */
            'payment_method' => $this->payment_method,

            /**
             * The transaction ID from the payment processor.
             *
             * @var string|null $transaction_id
             * @example "TXN123456789"
             */
            'transaction_id' => $this->transaction_id,

            /**
             * The date when the payment was made.
             *
             * @var string $payment_date
             * @example "2024-12-01"
             */
            'payment_date' => $this->payment_date,

            /**
             * The current status of the payment.
             *
             * @var string|null $status
             * @example "completed"
             */
            'status' => $this->status,

            /**
             * Additional remarks or notes about the payment.
             *
             * @var string|null $remarks
             * @example "Partial payment for semester fee"
             */
            'remarks' => $this->remarks,

            /**
             * The ID of the student making the payment.
             *
             * @var int $student_id
             * @example 1
             */
            'student_id' => $this->student_id,

            /**
             * The ID of the fee being paid.
             *
             * @var int $fee_id
             * @example 1
             */
            'fee_id' => $this->fee_id,

            /**
             * The student making the payment.
             *
             * @var StudentResource|null $student
             */
            'student' => new StudentResource($this->whenLoaded('student')),

            /**
             * The fee being paid.
             *
             * @var FeeResource|null $fee
             */
            'fee' => new FeeResource($this->whenLoaded('fee')),

            /**
             * Timestamp when the payment was created.
             *
             * @var string|null $created_at
             * @example "2024-05-26T12:00:00Z"
             */
            'created_at' => $this->created_at,

            /**
             * Timestamp when the payment was last updated.
             *
             * @var string|null $updated_at
             * @example "2024-05-26T12:00:00Z"
             */
            'updated_at' => $this->updated_at,
        ];
    }
}
