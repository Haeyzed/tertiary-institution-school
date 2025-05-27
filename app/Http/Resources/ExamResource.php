<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamResource extends JsonResource
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
             * Unique identifier of the exam.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * The title of the exam.
             *
             * @var string $title
             * @example "Final Exam - Data Structures"
             */
            'title' => $this->title,

            /**
             * The date when the exam will be conducted.
             *
             * @var string $exam_date
             * @example "2024-12-20"
             */
            'exam_date' => $this->exam_date,

            /**
             * The start time of the exam.
             *
             * @var string $start_time
             * @example "09:00"
             */
            'start_time' => $this->start_time,

            /**
             * The end time of the exam.
             *
             * @var string $end_time
             * @example "12:00"
             */
            'end_time' => $this->end_time,

            /**
             * The total marks for the exam.
             *
             * @var float $total_marks
             * @example 100.0
             */
            'total_marks' => $this->total_marks,

            /**
             * The venue where the exam will be conducted.
             *
             * @var string|null $venue
             * @example "Main Hall A"
             */
            'venue' => $this->venue,

            /**
             * The current status of the exam.
             *
             * @var string|null $status
             * @example "scheduled"
             */
            'status' => $this->status,

            /**
             * The ID of the course this exam is for.
             *
             * @var int $course_id
             * @example 1
             */
            'course_id' => $this->course_id,

            /**
             * The ID of the semester this exam is scheduled for.
             *
             * @var int $semester_id
             * @example 1
             */
            'semester_id' => $this->semester_id,

            /**
             * The course this exam is for.
             *
             * @var CourseResource|null $course
             */
            'course' => new CourseResource($this->whenLoaded('course')),

            /**
             * The semester this exam is scheduled for.
             *
             * @var SemesterResource|null $semester
             */
            'semester' => new SemesterResource($this->whenLoaded('semester')),

            /**
             * Total number of results for this exam.
             *
             * @var int|null $results_count
             * @example 45
             */
            'results_count' => $this->when($this->results_count, $this->results_count),

            /**
             * A list of results for this exam.
             *
             * @var array|null $results
             */
            'results' => ResultResource::collection($this->whenLoaded('results')),

            /**
             * Timestamp when the exam was created.
             *
             * @var string|null $created_at
             * @example "2024-05-26T12:00:00Z"
             */
            'created_at' => $this->created_at,

            /**
             * Timestamp when the exam was last updated.
             *
             * @var string|null $updated_at
             * @example "2024-05-26T12:00:00Z"
             */
            'updated_at' => $this->updated_at,
        ];
    }
}
