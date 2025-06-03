<?php

namespace App\Services;

use App\Models\Student;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class StudentService
{
    /**
     * Get all students with optional pagination.
     *
     * @param string $term
     * @param int|null $perPage
     * @param array $relations
     * @param bool|null $onlyDeleted
     * @return Collection|LengthAwarePaginator
     */
    public function getAllStudents(string $term, ?int $perPage = null, array $relations = [], ?bool $onlyDeleted = null): Collection|LengthAwarePaginator
    {
        $query = Student::query()
            ->where(function ($q) use ($term) {
                $q->whereLike('current_semester', "%$term%");
            });

        if (!empty($relations)) {
            $query->with($relations);
        }

        if ($onlyDeleted === true) {
            $query->onlyTrashed();
        } elseif ($onlyDeleted === false) {
            $query->withoutTrashed();
        }

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get a student by ID.
     *
     * @param int $id
     * @param array $relations
     * @return Student|null
     */
    public function getStudentById(int $id, array $relations = []): ?Student
    {
        $query = Student::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->find($id);
    }

    /**
     * Create a new student with user account.
     *
     * @param array $data
     * @return Student
     * @throws Exception|Throwable
     */
    public function createStudent(array $data): Student
    {
        return DB::transaction(function () use ($data) {
            // Create user account
            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone' => $data['phone'] ?? null,
                'gender' => $data['gender'] ?? null,
                'address' => $data['address'] ?? null,
            ];

            $user = User::query()->create($userData);

            // Create student record
            $studentData = [
                'user_id' => $user->id,
                'student_id' => $data['student_id'],
                'program_id' => $data['program_id'],
                'parent_id' => $data['parent_id'] ?? null,
                'admission_date' => $data['admission_date'],
                'current_semester' => $data['current_semester'] ?? 1,
                'status' => $data['status'] ?? 'active',
            ];

            return Student::query()->create($studentData);
        });
    }

    /**
     * Update an existing student.
     *
     * @param int $id
     * @param array $data
     * @return Student|null
     * @throws Exception|Throwable
     */
    public function updateStudent(int $id, array $data): ?Student
    {
        $student = Student::query()->find($id);

        if (!$student) {
            return null;
        }

        return DB::transaction(function () use ($student, $data) {
            // Update user data if provided
            if (isset($data['name']) || isset($data['email']) || isset($data['password']) ||
                isset($data['phone']) || isset($data['gender']) || isset($data['address'])) {

                $userData = [];

                if (isset($data['name'])) $userData['name'] = $data['name'];
                if (isset($data['email'])) $userData['email'] = $data['email'];
                if (isset($data['password'])) $userData['password'] = Hash::make($data['password']);
                if (isset($data['phone'])) $userData['phone'] = $data['phone'];
                if (isset($data['gender'])) $userData['gender'] = $data['gender'];
                if (isset($data['address'])) $userData['address'] = $data['address'];

                $student->user->update($userData);
            }

            // Update student data
            $studentData = [];

            if (isset($data['student_id'])) $studentData['student_id'] = $data['student_id'];
            if (isset($data['program_id'])) $studentData['program_id'] = $data['program_id'];
            if (isset($data['parent_id'])) $studentData['parent_id'] = $data['parent_id'];
            if (isset($data['admission_date'])) $studentData['admission_date'] = $data['admission_date'];
            if (isset($data['current_semester'])) $studentData['current_semester'] = $data['current_semester'];
            if (isset($data['status'])) $studentData['status'] = $data['status'];

            $student->update($studentData);

            return $student;
        });
    }

    /**
     * Delete or force delete a student.
     *
     * @param int $id
     * @param bool $force
     * @return bool
     * @throws Exception|Throwable
     */
    public function deleteStudent(int $id, bool $force = false): bool
    {
        $student = Student::withTrashed()->find($id);

        if (!$student) {
            return false;
        }

        return DB::transaction(function () use ($student, $force) {
            if ($force) {
                $student->forceDelete();
                $student->user->forceDelete();
            } else {
                $student->delete();
                $student->user->delete();
            }
            return true;
        });
    }

    /**
     * Restore a delete student.
     *
     * @param int $id
     * @return Student|null
     */
    public function restoreStudent(int $id): ?Student
    {
        $student = Student::onlyTrashed()->find($id);

        if (!$student) {
            return null;
        }

        $student->restore();

        return $student->fresh();
    }

    /**
     * Get students by program.
     *
     * @param int $programId
     * @param int|null $perPage
     * @return Collection|LengthAwarePaginator
     */
    public function getStudentsByProgram(int $programId, ?int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = Student::query()->where('program_id', $programId);

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Enroll a student in a course.
     *
     * @param int $studentId
     * @param int $courseId
     * @param int $semesterId
     * @return bool
     */
    public function enrollStudentInCourse(int $studentId, int $courseId, int $semesterId): bool
    {
        $student = Student::query()->find($studentId);

        if (!$student) {
            return false;
        }

        // Check if already enrolled
        $exists = $student->courses()
            ->wherePivot('course_id', $courseId)
            ->wherePivot('semester_id', $semesterId)
            ->exists();

        if ($exists) {
            return true; // Already enrolled
        }

        // Enroll student
        $student->courses()->attach($courseId, ['semester_id' => $semesterId]);

        return true;
    }

    /**
     * Unenroll a student from a course.
     *
     * @param int $studentId
     * @param int $courseId
     * @param int $semesterId
     * @return bool
     */
    public function unenrollStudentFromCourse(int $studentId, int $courseId, int $semesterId): bool
    {
        $student = Student::query()->find($studentId);

        if (!$student) {
            return false;
        }

        // Unenroll student
        $student->courses()
            ->wherePivot('course_id', $courseId)
            ->wherePivot('semester_id', $semesterId)
            ->detach();

        return true;
    }
}
