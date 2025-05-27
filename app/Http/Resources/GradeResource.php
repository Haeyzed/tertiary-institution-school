<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GradeResource extends JsonResource
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
             * Unique identifier of the grade.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * The grade letter or symbol.
             *
             * @var string $grade
             * @example "A"
             */
            'grade' => $this->grade,

            /**
             * The minimum score for this grade.
             *
             * @var float $min_score
             * @example 90.0
             */
            'min_score' => $this->min_score,

            /**
             * The maximum score for this grade.
             *
             * @var float $max_score
             * @example 100.0
             */
            'max_score' => $this->max_score,

            /**
             * Optional remark or description for the grade.
             *
             * @var string|null $remark
             * @example "Excellent performance"
             */
            'remark' => $this->remark,

            /**
             * Total number of results with this grade.
             *
             * @var int|null $results_count
             * @example 15
             */
            'results_count' => $this->when($this->results_count, $this->results_count),

            /**
             * Timestamp when the grade was created.
             *
             * @var string|null $created_at
             * @example "2024-05-26T12:00:00Z"
             */
            'created_at' => $this->created_at,

            /**
             * Timestamp when the grade was last updated.
             *
             * @var string|null $updated_at
             * @example "2024-05-26T12:00:00Z"
             */
            'updated_at' => $this->updated_at,
        ];
    }
}
