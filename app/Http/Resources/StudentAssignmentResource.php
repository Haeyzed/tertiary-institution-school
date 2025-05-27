<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentAssignmentResource extends JsonResource
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
             * Unique identifier of the student assignment submission.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * The submission content or file path.
             *
             * @var string|null $submission
             * @example "Solution to the binary search tree implementation..."
             */
            'submission' => $this->submission,

            /**
             * Additional remarks or feedback on the submission.
             *
             * @var string|null $remarks
             * @example "Good implementation, but could improve code documentation"
             */
            'remarks' => $this->remarks,

            /**
             * The score awarded for the assignment.
             *
             * @var float|null $score
             * @example 85.0
             */
            'score' => $this->score,

            /**
             * The date when the assignment was submitted.
             *
             * @var string|null $submission_date
             * @example "2024-12-10 14:30:00"
             */
            'submission_date' => $this->submission_date,

            /**
             * The ID of the student submitting the assignment.
             *
             * @var int $student_id
             * @example 1
             */
            'student_id' => $this->student_id,

            /**
             * The ID of the assignment being submitted.
             *
             * @var int $assignment_id
             * @example 1
             */
            'assignment_id' => $this->assignment_id,

            /**
             * The student submitting the assignment.
             *
             * @var StudentResource|null $student
             */
            'student' => new StudentResource($this->whenLoaded('student')),

            /**
             * The assignment being submitted.
             *
             * @var AssignmentResource|null $assignment
             */
            'assignment' => new AssignmentResource($this->whenLoaded('assignment')),

            /**
             * Timestamp when the submission was created.
             *
             * @var string|null $created_at
             * @example "2024-05-26T12:00:00Z"
             */
            'created_at' => $this->created_at,

            /**
             * Timestamp when the submission was last updated.
             *
             * @var string|null $updated_at
             * @example "2024-05-26T12:00:00Z"
             */
            'updated_at' => $this->updated_at,
        ];
    }
}
