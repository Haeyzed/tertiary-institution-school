<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimetableResource extends JsonResource
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
             * Unique identifier of the timetable entry.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * The day of the week for this timetable entry.
             *
             * @var string $day
             * @example "Monday"
             */
            'day' => $this->day,

            /**
             * The start time of the class.
             *
             * @var string $start_time
             * @example "09:00"
             */
            'start_time' => $this->start_time,

            /**
             * The end time of the class.
             *
             * @var string $end_time
             * @example "10:30"
             */
            'end_time' => $this->end_time,

            /**
             * The venue where the class will be held.
             *
             * @var string|null $venue
             * @example "Room 101, Science Building"
             */
            'venue' => $this->venue,

            /**
             * The ID of the course for this timetable entry.
             *
             * @var int $course_id
             * @example 1
             */
            'course_id' => $this->course_id,

            /**
             * The ID of the semester for this timetable entry.
             *
             * @var int $semester_id
             * @example 1
             */
            'semester_id' => $this->semester_id,

            /**
             * The ID of the staff member teaching this class.
             *
             * @var int $staff_id
             * @example 1
             */
            'staff_id' => $this->staff_id,

            /**
             * The course for this timetable entry.
             *
             * @var CourseResource|null $course
             */
            'course' => new CourseResource($this->whenLoaded('course')),

            /**
             * The semester for this timetable entry.
             *
             * @var SemesterResource|null $semester
             */
            'semester' => new SemesterResource($this->whenLoaded('semester')),

            /**
             * The staff member teaching this class.
             *
             * @var StaffResource|null $staff
             */
            'staff' => new StaffResource($this->whenLoaded('staff')),

            /**
             * Timestamp when the timetable entry was created.
             *
             * @var string|null $created_at
             * @example "2024-05-26T12:00:00Z"
             */
            'created_at' => $this->created_at,

            /**
             * Timestamp when the timetable entry was last updated.
             *
             * @var string|null $updated_at
             * @example "2024-05-26T12:00:00Z"
             */
            'updated_at' => $this->updated_at,
        ];
    }
}
