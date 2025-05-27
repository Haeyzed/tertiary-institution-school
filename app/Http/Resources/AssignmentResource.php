<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentResource extends JsonResource
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
             * Unique identifier of the assignment.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * The title of the assignment.
             *
             * @var string $title
             * @example "Data Structures and Algorithms Assignment 1"
             */
            'title' => $this->title,

            /**
             * The detailed description of the assignment.
             *
             * @var string $description
             * @example "Implement a binary search tree with insert, delete, and search operations."
             */
            'description' => $this->description,

            /**
             * The due date for the assignment submission.
             *
             * @var string $due_date
             * @example "2024-12-15"
             */
            'due_date' => $this->due_date,

            /**
             * The total marks/points for the assignment.
             *
             * @var float $total_marks
             * @example 100.0
             */
            'total_marks' => $this->total_marks,

            /**
             * Optional attachment file path or URL.
             *
             * @var string|null $attachment
             * @example "/uploads/assignments/assignment1.pdf"
             */
            'attachment' => $this->attachment,

            /**
             * The ID of the course this assignment belongs to.
             *
             * @var int $course_id
             * @example 1
             */
            'course_id' => $this->course_id,

            /**
             * The ID of the semester this assignment is for.
             *
             * @var int $semester_id
             * @example 1
             */
            'semester_id' => $this->semester_id,

            /**
             * The ID of the staff member who created the assignment.
             *
             * @var int $staff_id
             * @example 1
             */
            'staff_id' => $this->staff_id,

            /**
             * The course this assignment belongs to.
             *
             * @var CourseResource|null $course
             */
            'course' => new CourseResource($this->whenLoaded('course')),

            /**
             * The semester this assignment is for.
             *
             * @var SemesterResource|null $semester
             */
            'semester' => new SemesterResource($this->whenLoaded('semester')),

            /**
             * The staff member who created this assignment.
             *
             * @var StaffResource|null $staff
             */
            'staff' => new StaffResource($this->whenLoaded('staff')),

            /**
             * Total number of student submissions for this assignment.
             *
             * @var int|null $student_assignments_count
             * @example 25
             */
            'student_assignments_count' => $this->when($this->student_assignments_count, $this->student_assignments_count),

            /**
             * A list of student submissions for this assignment.
             *
             * @var array|null $student_assignments
             */
            'student_assignments' => StudentAssignmentResource::collection($this->whenLoaded('studentAssignments')),

            /**
             * Timestamp when the assignment was created.
             *
             * @var string|null $created_at
             * @example "2024-05-26T12:00:00Z"
             */
            'created_at' => $this->created_at,

            /**
             * Timestamp when the assignment was last updated.
             *
             * @var string|null $updated_at
             * @example "2024-05-26T12:00:00Z"
             */
            'updated_at' => $this->updated_at,
        ];
    }
}
