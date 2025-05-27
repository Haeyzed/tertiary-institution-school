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
        return [
            'total_students' => Student::query()->count(),
            'active_students' => Student::query()->where('status', StudentStatusEnum::ACTIVE)->count(),
            'inactive_students' => Student::query()->where('status', StudentStatusEnum::INACTIVE)->count(),
            'graduated_students' => Student::query()->where('status', StudentStatusEnum::GRADUATED)->count(),
            'suspended_students' => Student::query()->where('status', StudentStatusEnum::SUSPENDED)->count(),
            'students_by_program' => $this->getStudentsByProgram(),
            'students_by_gender' => $this->getStudentsByGender(),
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
            'current_session' => $currentSession ? $currentSession->name : null,
            'current_semesters' => Semester::query()->whereIn('id', $currentSemesterIds)->count(),
            'upcoming_exams' => Exam::query()->where('exam_date', '>=', now())
                ->where('status', '!=', ExamStatusEnum::CANCELLED)
                ->count(),
            'ongoing_exams' => Exam::query()->where('status', ExamStatusEnum::ONGOING)->count(),
            'upcoming_assignments' => Assignment::query()->where('due_date', '>=', now())->count(),
        ];
    }

    /**
     * Get financial statistics.
     *
     * @return array
     */
    public function getFinancialStats(): array
    {
        $totalFees = Fee::query()->sum('amount');
        $totalPayments = Payment::query()->where('status', PaymentStatusEnum::COMPLETED)
            ->sum('amount_paid');
        $pendingPayments = Payment::query()->where('status', PaymentStatusEnum::PENDING)
            ->sum('amount_paid');

        return [
            'total_fees' => $totalFees,
            'total_payments' => $totalPayments,
            'pending_payments' => $pendingPayments,
            'payment_collection_rate' => $totalFees > 0 ? ($totalPayments / $totalFees) * 100 : 0,
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
                ->limit(config('app.limit'))
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

        return [
            'total_results' => $totalResults,
            'passed_results' => $passedResults,
            'failed_results' => $failedResults,
            'pass_rate' => $totalResults > 0 ? ($passedResults / $totalResults) * 100 : 0,
            'average_score' => Result::query()->avg('score') ?? 0,
            'score_distribution' => $this->getScoreDistribution(),
        ];
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
            'academic' => $this->getAcademicStats(),
            'financial' => $this->getFinancialStats(),
            'notifications' => $this->getNotificationStats(),
            'performance' => $this->getPerformanceStats(),
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
            ->where('status', PaymentStatusEnum::COMPLETED)
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
            ->where('status', PaymentStatusEnum::COMPLETED)
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

//    /**
//     * Get score distribution.
//     *
//     * @return array
//     */
//    private function getScoreDistribution(): array
//    {
//        $ranges = [
//            '0-9' => [0, 9],
//            '10-19' => [10, 19],
//            '20-29' => [20, 29],
//            '30-39' => [30, 39],
//            '40-49' => [40, 49],
//            '50-59' => [50, 59],
//            '60-69' => [60, 69],
//            '70-79' => [70, 79],
//            '80-89' => [80, 89],
//            '90-100' => [90, 100],
//        ];
//
//        $distribution = [];
//
//        foreach ($ranges as $label => [$min,
}
