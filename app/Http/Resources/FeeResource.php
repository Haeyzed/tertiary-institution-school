<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeeResource extends JsonResource
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
             * Unique identifier of the fee.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * The name of the fee.
             *
             * @var string $name
             * @example "Tuition Fee"
             */
            'name' => $this->name,

            /**
             * The amount of the fee.
             *
             * @var float $amount
             * @example 5000.00
             */
            'amount' => $this->amount,

            /**
             * Optional description of the fee.
             *
             * @var string|null $description
             * @example "Annual tuition fee for undergraduate programs"
             */
            'description' => $this->description,

            /**
             * The ID of the program this fee applies to.
             *
             * @var int $program_id
             * @example 1
             */
            'program_id' => $this->program_id,

            /**
             * The ID of the semester this fee applies to.
             *
             * @var int|null $semester_id
             * @example 1
             */
            'semester_id' => $this->semester_id,

            /**
             * The program this fee applies to.
             *
             * @var ProgramResource|null $program
             */
            'program' => new ProgramResource($this->whenLoaded('program')),

            /**
             * The semester this fee applies to.
             *
             * @var SemesterResource|null $semester
             */
            'semester' => new SemesterResource($this->whenLoaded('semester')),

            /**
             * Total number of payments made for this fee.
             *
             * @var int|null $payments_count
             * @example 120
             */
            'payments_count' => $this->when($this->payments_count, $this->payments_count),

            /**
             * A list of payments made for this fee.
             *
             * @var array|null $payments
             */
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),

            /**
             * Timestamp when the fee was created.
             *
             * @var string|null $created_at
             * @example "2024-05-26T12:00:00Z"
             */
            'created_at' => $this->created_at,

            /**
             * Timestamp when the fee was last updated.
             *
             * @var string|null $updated_at
             * @example "2024-05-26T12:00:00Z"
             */
            'updated_at' => $this->updated_at,
        ];
    }
}
