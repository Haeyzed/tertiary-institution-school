<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AcademicSessionResource extends JsonResource
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
             * Unique identifier of the academic session.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * The name of the academic session.
             *
             * @var string $name
             * @example "2024/2025 Academic Session"
             */
            'name' => $this->name,

            /**
             * The start date of the academic session.
             *
             * @var string $start_date
             * @example "2024-09-01"
             */
            'start_date' => $this->start_date,

            /**
             * The end date of the academic session.
             *
             * @var string $end_date
             * @example "2025-06-30"
             */
            'end_date' => $this->end_date,

            /**
             * Indicates if this is the current academic session.
             *
             * @var bool|null $is_current
             * @example true
             */
            'is_current' => $this->is_current,

            /**
             * Total number of semesters within this academic session.
             *
             * @var int|null $semesters_count
             * @example 2
             */
            'semesters_count' => $this->when($this->semesters_count, $this->semesters_count),

            /**
             * A list of semesters linked to this academic session.
             *
             * @var array|null $semesters
             */
            'semesters' => SemesterResource::collection($this->whenLoaded('semesters')),

            /**
             * Timestamp when the academic session was created.
             *
             * @var string|null $created_at
             * @example "2024-05-26T12:00:00Z"
             */
            'created_at' => $this->created_at,

            /**
             * Timestamp when the academic session was last updated.
             *
             * @var string|null $updated_at
             * @example "2024-05-26T12:00:00Z"
             */
            'updated_at' => $this->updated_at,
        ];
    }
}
