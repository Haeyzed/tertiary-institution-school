<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
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
             * Unique identifier of the course.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * The name of the course.
             *
             * @var string $name
             * @example "Data Structures and Algorithms"
             */
            'name' => $this->name,

            /**
             * The unique course code.
             *
             * @var string $code
             * @example "CS201"
             */
            'code' => $this->code,

            /**
             * Optional description of the course.
             *
             * @var string|null $description
             * @example "This course covers fundamental data structures and algorithms used in computer science."
             */
            'description' => $this->description,

            /**
             * The number of credit hours for the course.
             *
             * @var int $credit_hours
             * @example 3
             */
            'credit_hours' => $this->credit_hours,

            /**
             * The ID of the department offering the course.
             *
             * @var int $department_id
             * @example 1
             */
            'department_id' => $this->department_id,

            /**
             * The department offering this course.
             *
             * @var DepartmentResource|null $department
             */
            'department' => new DepartmentResource($this->whenLoaded('department')),

            /**
             * A list of semesters when this course is offered.
             *
             * @var array|null $semesters
             */
            'semesters' => SemesterResource::collection($this->whenLoaded('semesters')),

            /**
             * Total number of students enrolled in this course.
             *
             * @var int|null $students_count
             * @example 45
             */
            'students_count' => $this->when($this->students_count, $this->students_count),

            /**
             * A list of students enrolled in this course.
             *
             * @var array|null $students
             */
            'students' => StudentResource::collection($this->whenLoaded('students')),

            /**
             * Total number of timetable entries for this course.
             *
             * @var int|null $timetables_count
             * @example 3
             */
            'timetables_count' => $this->when($this->timetables_count, $this->timetables_count),

            /**
             * A list of timetable entries for this course.
             *
             * @var array|null $timetables
             */
            'timetables' => TimetableResource::collection($this->whenLoaded('timetables')),

            /**
             * Total number of exams for this course.
             *
             * @var int|null $exams_count
             * @example 2
             */
            'exams_count' => $this->when($this->exams_count, $this->exams_count),

            /**
             * A list of exams for this course.
             *
             * @var array|null $exams
             */
            'exams' => ExamResource::collection($this->whenLoaded('exams')),

            /**
             * Total number of assignments for this course.
             *
             * @var int|null $assignments_count
             * @example 5
             */
            'assignments_count' => $this->when($this->assignments_count, $this->assignments_count),

            /**
             * A list of assignments for this course.
             *
             * @var array|null $assignments
             */
            'assignments' => AssignmentResource::collection($this->whenLoaded('assignments')),

            /**
             * Total number of results for this course.
             *
             * @var int|null $results_count
             * @example 90
             */
            'results_count' => $this->when($this->results_count, $this->results_count),

            /**
             * A list of results for this course.
             *
             * @var array|null $results
             */
            'results' => ResultResource::collection($this->whenLoaded('results')),

            /**
             * Timestamp when the course was created.
             *
             * @var string|null $created_at
             * @example "2024-05-26T12:00:00Z"
             */
            'created_at' => $this->created_at,

            /**
             * Timestamp when the course was last updated.
             *
             * @var string|null $updated_at
             * @example "2024-05-26T12:00:00Z"
             */
            'updated_at' => $this->updated_at,
        ];
    }
}
