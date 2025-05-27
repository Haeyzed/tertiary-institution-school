<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResultResource extends JsonResource
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
             * Unique identifier of the result.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * The score achieved by the student.
             *
             * @var float $score
             * @example 85.5
             */
            'score' => $this->score,

            /**
             * Additional remarks or comments about the result.
             *
             * @var string|null $remarks
             * @example "Excellent performance in practical section"
             */
            'remarks' => $this->remarks,

            /**
             * The ID of the student whose result is being recorded.
             *
             * @var int $student_id
             * @example 1
             */
            'student_id' => $this->student_id,

            /**
             * The ID of the course for which the result is recorded.
             *
             * @var int $course_id
             * @example 1
             */
            'course_id' => $this->course_id,

            /**
             * The ID of the exam for which the result is recorded.
             *
             * @var int $exam_id
             * @example 1
             */
            'exam_id' => $this->exam_id,

            /**
             * The ID of the semester when the exam was taken.
             *
             * @var int $semester_id
             * @example 1
             */
            'semester_id' => $this->semester_id,

            /**
             * The ID of the grade assigned based on the score.
             *
             * @var int|null $grade_id
             * @example 1
             */
            'grade_id' => $this->grade_id,

            /**
             * The student whose result is being recorded.
             *
             * @var StudentResource|null $student
             */
            'student' => new StudentResource($this->whenLoaded('student')),

            /**
             * The course for which the result is recorded.
             *
             * @var CourseResource|null $course
             */
            'course' => new CourseResource($this->whenLoaded('course')),

            /**
             * The exam for which the result is recorded.
             *
             * @var ExamResource|null $exam
             */
            'exam' => new ExamResource($this->whenLoaded('exam')),

            /**
             * The semester when the exam was taken.
             *
             * @var SemesterResource|null $semester
             */
            'semester' => new SemesterResource($this->whenLoaded('semester')),

            /**
             * The grade assigned based on the score.
             *
             * @var GradeResource|null $grade
             */
            'grade' => new GradeResource($this->whenLoaded('grade')),

            /**
             * Timestamp when the result was created.
             *
             * @var string|null $created_at
             * @example "2024-05-26T12:00:00Z"
             */
            'created_at' => $this->created_at,

            /**
             * Timestamp when the result was last updated.
             *
             * @var string|null $updated_at
             * @example "2024-05-26T12:00:00Z"
             */
            'updated_at' => $this->updated_at,
        ];
    }
}
