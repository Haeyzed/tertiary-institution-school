<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
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
             * Unique identifier of the student.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * The unique student identification number.
             *
             * @var string $student_id
             * @example "STU2024001"
             */
            'student_id' => $this->student_id,

            /**
             * The date when the student was admitted.
             *
             * @var string $admission_date
             * @example "2024-09-01"
             */
            'admission_date' => $this->admission_date,

            /**
             * The current semester the student is in.
             *
             * @var int|null $current_semester
             * @example 3
             */
            'current_semester' => $this->current_semester,

            /**
             * The current status of the student.
             *
             * @var string|null $status
             * @example "active"
             */
            'status' => $this->status,

            /**
             * The ID of the user account associated with this student.
             *
             * @var int $user_id
             * @example 1
             */
            'user_id' => $this->user_id,

            /**
             * The ID of the program the student is enrolled in.
             *
             * @var int $program_id
             * @example 1
             */
            'program_id' => $this->program_id,

            /**
             * The ID of the student's parent or guardian.
             *
             * @var int|null $parent_id
             * @example 1
             */
            'parent_id' => $this->parent_id,

            /**
             * The user account associated with this student.
             *
             * @var UserResource|null $user
             */
            'user' => new UserResource($this->whenLoaded('user')),

            /**
             * The program the student is enrolled in.
             *
             * @var ProgramResource|null $program
             */
            'program' => new ProgramResource($this->whenLoaded('program')),

            /**
             * The parent or guardian of this student.
             *
             * @var ParentResource|null $parent
             */
            'parent' => new ParentResource($this->whenLoaded('parent')),

            /**
             * Total number of courses the student is enrolled in.
             *
             * @var int|null $courses_count
             * @example 6
             */
            'courses_count' => $this->when($this->courses_count, $this->courses_count),

            /**
             * A list of courses the student is enrolled in.
             *
             * @var array|null $courses
             */
            'courses' => CourseResource::collection($this->whenLoaded('courses')),

            /**
             * Total number of assignment submissions by this student.
             *
             * @var int|null $assignments_count
             * @example 18
             */
            'assignments_count' => $this->when($this->assignments_count, $this->assignments_count),

            /**
             * A list of assignment submissions by this student.
             *
             * @var array|null $assignments
             */
            'assignments' => StudentAssignmentResource::collection($this->whenLoaded('assignments')),

            /**
             * Total number of exam results for this student.
             *
             * @var int|null $results_count
             * @example 12
             */
            'results_count' => $this->when($this->results_count, $this->results_count),

            /**
             * A list of exam results for this student.
             *
             * @var array|null $results
             */
            'results' => ResultResource::collection($this->whenLoaded('results')),

            /**
             * Total number of payments made by this student.
             *
             * @var int|null $payments_count
             * @example 4
             */
            'payments_count' => $this->when($this->payments_count, $this->payments_count),

            /**
             * A list of payments made by this student.
             *
             * @var array|null $payments
             */
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),

            /**
             * Timestamp when the student record was created.
             *
             * @var string|null $created_at
             * @example "2024-05-26T12:00:00Z"
             */
            'created_at' => $this->created_at,

            /**
             * Timestamp when the student record was last updated.
             *
             * @var string|null $updated_at
             * @example "2024-05-26T12:00:00Z"
             */
            'updated_at' => $this->updated_at,
        ];
    }
}
