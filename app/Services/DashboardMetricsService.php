<?php

namespace App\Services;

use App\Enums\ExamStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\StudentStatusEnum;
use App\Models\AcademicSession;
use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Department;
use App\Models\Exam;
use App\Models\Faculty;
use App\Models\Fee;
use App\Models\Notification;
use App\Models\Parents;
use App\Models\Payment;
use App\Models\Program;
use App\Models\Result;
use App\Models\Semester;
use App\Models\Staff;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Class DashboardMetricsService
 *
 * Handles dashboard metrics and statistics.
 *
 * @package App\Services
 */
class DashboardMetricsService
{
    /**
     * Get general statistics.
     *
     * @return array
     */
    public function getGeneralStats(): array
    {
        return [
            'total_users' => User::query()->count(),
            'total_students' => Student::query()->count(),
            'total_staff' => Staff::query()->count(),
            'total_parents' => Parents::query()->count(),
            'total_faculties' => Faculty::query()->count(),
            'total_departments' => Department::query()->count(),
            'total_programs' => Program::query()->count(),
            'total_courses' => Course::query()->count(),
        ];
    }

    /**
     * Get student statistics.
     *
     * @return array
     */
    public function getStudentStats(): array
    {
        $totalStudents = Student::query()->count();
        $activeStudents = Student::query()->where('status', StudentStatusEnum::ACTIVE->value)->count();
        $inactiveStudents = Student::query()->where('status', StudentStatusEnum::INACTIVE->value)->count();
        $graduatedStudents = Student::query()->where('status', StudentStatusEnum::GRADUATED->value)->count();
        $suspendedStudents = Student::query()->where('status', StudentStatusEnum::SUSPENDED->value)->count();

        return [
            'total_students' => $totalStudents,
            'active_students' => $activeStudents,
            'inactive_students' => $inactiveStudents,
            'graduated_students' => $graduatedStudents,
            'suspended_students' => $suspendedStudents,
            'active_percentage' => $totalStudents > 0 ? round(($activeStudents / $totalStudents) * 100, 2) : 0,
            'inactive_percentage' => $totalStudents > 0 ? round(($inactiveStudents / $totalStudents) * 100, 2) : 0,
            'graduated_percentage' => $totalStudents > 0 ? round(($graduatedStudents / $totalStudents) * 100, 2) : 0,
            'suspended_percentage' => $totalStudents > 0 ? round(($suspendedStudents / $totalStudents) * 100, 2) : 0,
            'students_by_program' => $this->getStudentsByProgram(),
            'students_by_gender' => $this->getStudentsByGender(),
        ];
    }

    /**
     * Get staff statistics.
     *
     * @return array
     */
    public function getStaffStats(): array
    {
        $staffByDepartment = Staff::query()
            ->select('department_id', DB::raw('count(*) as count'))
            ->groupBy('department_id')
            ->with('department:id,name')
            ->get()
            ->map(function ($item) {
                return [
                    'department' => $item->department->name ?? 'Unknown',
                    'count' => $item->count,
                ];
            });

        $staffByPosition = Staff::query()
            ->select('position', DB::raw('count(*) as count'))
            ->whereNotNull('position')
            ->groupBy('position')
            ->get()
            ->map(function ($item) {
                return [
                    'position' => $item->position,
                    'count' => $item->count,
                ];
            });

        return [
            'total_staff' => Staff::query()->count(),
            'staff_by_department' => $staffByDepartment->toArray(),
            'staff_by_position' => $staffByPosition->toArray(),
        ];
    }

    /**
     * Get academic statistics.
     *
     * @return array
     */
    public function getAcademicStats(): array
    {
        $currentSession = AcademicSession::query()->where('is_current', true)->first();
        $currentSemesterIds = [];

        if ($currentSession) {
            $currentSemesterIds = Semester::query()->where('academic_session_id', $currentSession->id)
                ->pluck('id')
                ->toArray();
        }

        return [
            'total_courses' => Course::query()->count(),
            'total_exams' => Exam::query()->count(),
            'total_assignments' => Assignment::query()->count(),
            'total_sessions' => AcademicSession::query()->count(),
            'total_semesters' => Semester::query()->count(),
            'current_session' => $currentSession ? $currentSession->name : null,
            'current_semesters' => Semester::query()->whereIn('id', $currentSemesterIds)->count(),
            'upcoming_exams' => Exam::query()->where('exam_date', '>=', now())
                ->where('status', '!=', ExamStatusEnum::CANCELLED->value)
                ->count(),
            'ongoing_exams' => Exam::query()->where('status', ExamStatusEnum::ONGOING->value)->count(),
            'completed_exams' => Exam::query()->where('exam_date', '<', now())->count(),
            'upcoming_assignments' => Assignment::query()->where('due_date', '>=', now())->count(),
            'current_exams' => $currentSemesterIds ? Exam::query()->whereIn('semester_id', $currentSemesterIds)->count() : 0,
        ];
    }

    /**
     * Get financial statistics.
     *
     * @return array
     */
    public function getFinancialStats(): array
    {
        $currentSession = AcademicSession::query()->where('is_current', true)->first();
        $currentSemesterIds = [];

        if ($currentSession) {
            $currentSemesterIds = Semester::query()->where('academic_session_id', $currentSession->id)
                ->pluck('id')
                ->toArray();
        }

        $totalFees = Fee::query()->sum('amount');
        $totalPayments = Payment::query()->where('status', PaymentStatusEnum::COMPLETED->value)
            ->sum('amount_paid');
        $pendingPayments = Payment::query()->where('status', PaymentStatusEnum::PENDING->value)
            ->sum('amount_paid');

        $currentFees = $currentSemesterIds ? Fee::query()->whereIn('semester_id', $currentSemesterIds)->sum('amount') : 0;
        $currentPayments = $currentSemesterIds ?
            Payment::query()->whereHas('fee', function ($query) use ($currentSemesterIds) {
                $query->whereIn('semester_id', $currentSemesterIds);
            })->where('status', PaymentStatusEnum::COMPLETED->value)->sum('amount_paid') : 0;

        return [
            'total_fees' => $totalFees,
            'total_payments' => $totalPayments,
            'pending_payments' => $pendingPayments,
            'payment_collection_rate' => $totalFees > 0 ? ($totalPayments / $totalFees) * 100 : 0,
            'current_fees' => $currentFees,
            'current_payments' => $currentPayments,
            'current_payment_percentage' => $currentFees > 0 ? round(($currentPayments / $currentFees) * 100, 2) : 0,
            'payments_by_method' => $this->getPaymentsByMethod(),
            'payments_by_month' => $this->getPaymentsByMonth(),
        ];
    }

    /**
     * Get notification statistics.
     *
     * @return array
     */
    public function getNotificationStats(): array
    {
        return [
            'total_notifications' => Notification::query()->count(),
            'unread_notifications' => Notification::query()->where('is_read', false)->count(),
            'total_announcements' => Announcement::query()->count(),
            'recent_announcements' => Announcement::query()->orderBy('date', 'desc')
                ->limit(config('app.limit', 10))
                ->get(),
        ];
    }

    /**
     * Get academic performance statistics.
     *
     * @return array
     */
    public function getPerformanceStats(): array
    {
        $totalResults = Result::query()->count();
        $passedResults = Result::query()->where('score', '>=', 50)->count();
        $failedResults = $totalResults - $passedResults;
        $averageScore = Result::query()->avg('score');
        $highestScore = Result::query()->max('score');
        $lowestScore = Result::query()->min('score');

        return [
            'total_results' => $totalResults,
            'passed_results' => $passedResults,
            'failed_results' => $failedResults,
            'pass_rate' => $totalResults > 0 ? ($passedResults / $totalResults) * 100 : 0,
            'average_score' => round($averageScore ?? 0, 2),
            'highest_score' => $highestScore ?? 0,
            'lowest_score' => $lowestScore ?? 0,
            'score_distribution' => $this->getScoreDistribution(),
            'grade_distribution' => $this->getGradeDistribution(),
        ];
    }

    /**
     * Get enrollment statistics.
     *
     * @return array
     */
    public function getEnrollmentStats(): array
    {
        $enrollmentByProgram = Student::query()
            ->select('program_id', DB::raw('count(*) as count'))
            ->groupBy('program_id')
            ->with('program:id,name')
            ->get()
            ->map(function ($item) {
                return [
                    'program' => $item->program->name ?? 'Unknown',
                    'count' => $item->count,
                ];
            });

        $enrollmentTrend = Student::query()
            ->select(
                DB::raw('YEAR(admission_date) as year'),
                DB::raw('count(*) as count')
            )
            ->groupBy(DB::raw('YEAR(admission_date)'))
            ->orderBy('year')
            ->get()
            ->map(function ($item) {
                return [
                    'year' => $item->year,
                    'count' => $item->count,
                ];
            });

        return [
            'enrollment_by_program' => $enrollmentByProgram->toArray(),
            'enrollment_trend' => $enrollmentTrend->toArray(),
        ];
    }

    /**
     * Get recent activities.
     *
     * @param int $limit
     * @return array
     */
    public function getRecentActivities(int $limit = 10): array
    {
        $recentPayments = Payment::query()
            ->with(['student.user:id,name', 'fee:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($payment) {
                return [
                    'type' => 'payment',
                    'id' => $payment->id,
                    'title' => "Payment of {$payment->amount_paid} for {$payment->fee->name}",
                    'description' => "by {$payment->student->user->name}",
                    'date' => $payment->created_at,
                ];
            });

        $recentResults = Result::query()
            ->with(['student.user:id,name', 'course:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($result) {
                return [
                    'type' => 'result',
                    'id' => $result->id,
                    'title' => "Result for {$result->course->name}",
                    'description' => "{$result->student->user->name} scored {$result->score}",
                    'date' => $result->created_at,
                ];
            });

        $recentExams = Exam::query()
            ->with(['course:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($exam) {
                return [
                    'type' => 'exam',
                    'id' => $exam->id,
                    'title' => "Exam for {$exam->course->name}",
                    'description' => "scheduled on " . Carbon::parse($exam->exam_date)->format('Y-m-d'),
                    'date' => $exam->created_at,
                ];
            });

        $activities = $recentPayments->concat($recentResults)->concat($recentExams)
            ->sortByDesc('date')
            ->take($limit)
            ->values()
            ->all();

        return $activities;
    }

    /**
     * Get all dashboard metrics.
     *
     * @return array
     */
    public function getAllMetrics(): array
    {
        return [
            'general' => $this->getGeneralStats(),
            'students' => $this->getStudentStats(),
            'staff' => $this->getStaffStats(),
            'academic' => $this->getAcademicStats(),
            'financial' => $this->getFinancialStats(),
            'notifications' => $this->getNotificationStats(),
            'performance' => $this->getPerformanceStats(),
            'enrollment' => $this->getEnrollmentStats(),
            'recent_activities' => $this->getRecentActivities(),
        ];
    }

    /**
     * Get students by program.
     *
     * @return array
     */
    private function getStudentsByProgram(): array
    {
        return Program::query()->select('programs.name', DB::raw('COUNT(students.id) as count'))
            ->leftJoin('students', 'programs.id', '=', 'students.program_id')
            ->groupBy('programs.id', 'programs.name')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'count' => $item->count,
                ];
            })
            ->toArray();
    }

    /**
     * Get students by gender.
     *
     * @return array
     */
    private function getStudentsByGender(): array
    {
        return User::query()->select('gender', DB::raw('COUNT(students.id) as count'))
            ->join('students', 'users.id', '=', 'students.user_id')
            ->groupBy('gender')
            ->get()
            ->map(function ($item) {
                return [
                    'gender' => $item->gender,
                    'count' => $item->count,
                ];
            })
            ->toArray();
    }

    /**
     * Get payments by method.
     *
     * @return array
     */
    private function getPaymentsByMethod(): array
    {
        return Payment::query()->select('payment_method', DB::raw('SUM(amount_paid) as total'))
            ->where('status', PaymentStatusEnum::COMPLETED->value)
            ->groupBy('payment_method')
            ->get()
            ->map(function ($item) {
                return [
                    'method' => $item->payment_method,
                    'total' => $item->total,
                ];
            })
            ->toArray();
    }

    /**
     * Get payments by month.
     *
     * @return array
     */
    private function getPaymentsByMonth(): array
    {
        $startDate = Carbon::now()->subMonths(11)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $payments = Payment::query()->select(
            DB::raw('YEAR(payment_date) as year'),
            DB::raw('MONTH(payment_date) as month'),
            DB::raw('SUM(amount_paid) as total')
        )
            ->where('status', PaymentStatusEnum::COMPLETED->value)
            ->where('payment_date', '>=', $startDate)
            ->where('payment_date', '<=', $endDate)
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $result = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $year = $currentDate->year;
            $month = $currentDate->month;
            $monthName = $currentDate->format('M Y');

            $payment = $payments->first(function ($item) use ($year, $month) {
                return $item->year == $year && $item->month == $month;
            });

            $result[] = [
                'month' => $monthName,
                'total' => $payment ? $payment->total : 0,
            ];

            $currentDate->addMonth();
        }

        return $result;
    }

    /**
     * Get score distribution across different ranges.
     *
     * @return array
     */
    private function getScoreDistribution(): array
    {
        $ranges = [
            '0-9' => [0, 9],
            '10-19' => [10, 19],
            '20-29' => [20, 29],
            '30-39' => [30, 39],
            '40-49' => [40, 49],
            '50-59' => [50, 59],
            '60-69' => [60, 69],
            '70-79' => [70, 79],
            '80-89' => [80, 89],
            '90-100' => [90, 100],
        ];

        $distribution = [];

        foreach ($ranges as $label => [$min, $max]) {
            $count = Result::query()
                ->where('score', '>=', $min)
                ->where('score', '<=', $max)
                ->count();

            $distribution[] = [
                'range' => $label,
                'count' => $count,
                'min_score' => $min,
                'max_score' => $max,
            ];
        }

        return $distribution;
    }

    /**
     * Get grade distribution based on predefined grade ranges.
     *
     * @return array
     */
    private function getGradeDistribution(): array
    {
        return Result::query()
            ->select('grades.grade', DB::raw('COUNT(results.id) as count'))
            ->leftJoin('grades', 'results.grade_id', '=', 'grades.id')
            ->groupBy('grades.id', 'grades.grade')
            ->orderBy('grades.min_score', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'grade' => $item->grade ?? 'Ungraded',
                    'count' => $item->count,
                ];
            })
            ->toArray();
    }

    /**
     * Get performance trends over time.
     *
     * @param int $months Number of months to look back
     * @return array
     */
    public function getPerformanceTrends(int $months = 6): array
    {
        $startDate = Carbon::now()->subMonths($months - 1)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $trends = Result::query()
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('AVG(score) as average_score'),
                DB::raw('COUNT(*) as total_results')
            )
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $result = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $year = $currentDate->year;
            $month = $currentDate->month;
            $monthName = $currentDate->format('M Y');

            $trend = $trends->first(function ($item) use ($year, $month) {
                return $item->year == $year && $item->month == $month;
            });

            $result[] = [
                'month' => $monthName,
                'average_score' => $trend ? round($trend->average_score, 2) : 0,
                'total_results' => $trend ? $trend->total_results : 0,
            ];

            $currentDate->addMonth();
        }

        return $result;
    }

    /**
     * Get top performing students.
     *
     * @param int $limit Number of students to return
     * @return array
     */
    public function getTopPerformingStudents(int $limit = 10): array
    {
        return Result::query()
            ->select(
                'students.student_id',
                'users.name',
                DB::raw('AVG(results.score) as average_score'),
                DB::raw('COUNT(results.id) as total_exams')
            )
            ->join('students', 'results.student_id', '=', 'students.id')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->groupBy('students.id', 'students.student_id', 'users.name')
            ->having('total_exams', '>=', 3) // At least 3 exam results
            ->orderBy('average_score', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'student_id' => $item->student_id,
                    'name' => $item->name,
                    'average_score' => round($item->average_score, 2),
                    'total_exams' => $item->total_exams,
                ];
            })
            ->toArray();
    }

    /**
     * Get course performance statistics.
     *
     * @return array
     */
    public function getCoursePerformanceStats(): array
    {
        return Course::query()
            ->select(
                'courses.name',
                'courses.code',
                DB::raw('AVG(results.score) as average_score'),
                DB::raw('COUNT(results.id) as total_results'),
                DB::raw('COUNT(CASE WHEN results.score >= 50 THEN 1 END) as passed_count')
            )
            ->leftJoin('results', 'courses.id', '=', 'results.course_id')
            ->groupBy('courses.id', 'courses.name', 'courses.code')
            ->having('total_results', '>', 0)
            ->orderBy('average_score', 'desc')
            ->get()
            ->map(function ($item) {
                $passRate = $item->total_results > 0 ?
                    ($item->passed_count / $item->total_results) * 100 : 0;

                return [
                    'course_name' => $item->name,
                    'course_code' => $item->code,
                    'average_score' => round($item->average_score, 2),
                    'total_results' => $item->total_results,
                    'pass_rate' => round($passRate, 2),
                ];
            })
            ->toArray();
    }
}
