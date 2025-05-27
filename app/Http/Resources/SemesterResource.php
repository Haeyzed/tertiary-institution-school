<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SemesterResource extends JsonResource
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
             * Unique identifier of the semester.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * The name of the semester.
             *
             * @var string $name
             * @example "Fall 2024"
             */
            'name' => $this->name,

            /**
             * The start date of the semester.
             *
             * @var string $start_date
             * @example "2024-09-01"
             */
            'start_date' => $this->start_date,

            /**
             * The end date of the semester.
             *
             * @var string $end_date
             * @example "2024-12-20"
             */
            'end_date' => $this->end_date,

            /**
             * The ID of the academic session this semester belongs to.
             *
             * @var int $academic_session_id
             * @example 1
             */
            'academic_session_id' => $this->academic_session_id,

            /**
             * The academic session this semester belongs to.
             *
             * @var AcademicSessionResource|null $academic_session
             */
            'academic_session' => new AcademicSessionResource($this->whenLoaded('academicSession')),

            /**
             * Total number of courses offered in this semester.
             *
             * @var int|null $courses_count
             * @example 12
             */
            'courses_count' => $this->when($this->courses_count, $this->courses_count),

            /**
             * A list of courses offered in this semester.
             *
             * @var array|null $courses
             */
            'courses' => CourseResource::collection($this->whenLoaded('courses')),

            /**
             * Total number of timetable entries for this semester.
             *
             * @var int|null $timetables_count
             * @example 36
             */
            'timetables_count' => $this->when($this->timetables_count, $this->timetables_count),

            /**
             * A list of timetable entries for this semester.
             *
             * @var array|null $timetables
             */
            'timetables' => TimetableResource::collection($this->whenLoaded('timetables')),

            /**
             * Total number of exams scheduled for this semester.
             *
             * @var int|null $exams_count
             * @example 8
             */
            'exams_count' => $this->when($this->exams_count, $this->exams_count),

            /**
             * A list of exams scheduled for this semester.
             *
             * @var array|null $exams
             */
            'exams' => ExamResource::collection($this->whenLoaded('exams')),

            /**
             * Total number of assignments for this semester.
             *
             * @var int|null $assignments_count
             * @example 24
             */
            'assignments_count' => $this->when($this->assignments_count, $this->assignments_count),

            /**
             * A list of assignments for this semester.
             *
             * @var array|null $assignments
             */
            'assignments' => AssignmentResource::collection($this->whenLoaded('assignments')),

            /**
             * Total number of results recorded for this semester.
             *
             * @var int|null $results_count
             * @example 360
             */
            'results_count' => $this->when($this->results_count, $this->results_count),

            /**
             * A list of results recorded for this semester.
             *
             * @var array|null $results
             */
            'results' => ResultResource::collection($this->whenLoaded('results')),

            /**
             * Total number of fees applicable for this semester.
             *
             * @var int|null $fees_count
             * @example 3
             */
            'fees_count' => $this->when($this->fees_count, $this->fees_count),

            /**
             * A list of fees applicable for this semester.
             *
             * @var array|null $fees
             */
            'fees' => FeeResource::collection($this->whenLoaded('fees')),

            /**
             * Timestamp when the semester was created.
             *
             * @var string|null $created_at
             * @example "2024-05-26T12:00:00Z"
             */
            'created_at' => $this->created_at,

            /**
             * Timestamp when the semester was last updated.
             *
             * @var string|null $updated_at
             * @example "2024-05-26T12:00:00Z"
             */
            'updated_at' => $this->updated_at,
        ];
    }
}
