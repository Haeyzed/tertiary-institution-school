<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgramResource extends JsonResource
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
             * Unique identifier of the program.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * The name of the academic program.
             *
             * @var string $name
             * @example "Bachelor of Science in Computer Science"
             */
            'name' => $this->name,

            /**
             * The unique program code.
             *
             * @var string $code
             * @example "BSCS"
             */
            'code' => $this->code,

            /**
             * The ID of the department offering the program.
             *
             * @var int $department_id
             * @example 1
             */
            'department_id' => $this->department_id,

            /**
             * The duration of the program in years.
             *
             * @var int $duration
             * @example 4
             */
            'duration' => $this->duration,

            /**
             * The department offering this program.
             *
             * @var DepartmentResource|null $department
             */
            'department' => new DepartmentResource($this->whenLoaded('department')),

            /**
             * Total number of students enrolled in this program.
             *
             * @var int|null $students_count
             * @example 150
             */
            'students_count' => $this->when($this->students_count, $this->students_count),

            /**
             * A list of students enrolled in this program.
             *
             * @var array|null $students
             */
            'students' => StudentResource::collection($this->whenLoaded('students')),

            /**
             * Total number of fees associated with this program.
             *
             * @var int|null $fees_count
             * @example 5
             */
            'fees_count' => $this->when($this->fees_count, $this->fees_count),

            /**
             * A list of fees associated with this program.
             *
             * @var array|null $fees
             */
            'fees' => FeeResource::collection($this->whenLoaded('fees')),

            /**
             * Timestamp when the program was created.
             *
             * @var string|null $created_at
             * @example "2024-05-26T12:00:00Z"
             */
            'created_at' => $this->created_at,

            /**
             * Timestamp when the program was last updated.
             *
             * @var string|null $updated_at
             * @example "2024-05-26T12:00:00Z"
             */
            'updated_at' => $this->updated_at,
        ];
    }
}
