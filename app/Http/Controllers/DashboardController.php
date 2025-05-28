<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\DashboardMetricsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class DashboardController
 *
 * Handles dashboard related API endpoints.
 *
 * @package App\Http\Controllers\API
 */
class DashboardController extends Controller
{
    /**
     * The dashboard metrics service instance.
     *
     * @var DashboardMetricsService
     */
    protected $dashboardMetricsService;

    /**
     * Create a new controller instance.
     *
     * @param DashboardMetricsService $dashboardMetricsService
     * @return void
     */
    public function __construct(DashboardMetricsService $dashboardMetricsService)
    {
        $this->dashboardMetricsService = $dashboardMetricsService;
    }

    /**
     * Get all dashboard metrics.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $metrics = $this->dashboardMetricsService->getAllMetrics();

        return response()->success(
            $metrics,
            'Dashboard metrics retrieved successfully'
        );
    }

    /**
     * Get general statistics for the dashboard.
     *
     * @return JsonResponse
     */
    public function generalStats(): JsonResponse
    {
        $stats = $this->dashboardMetricsService->getGeneralStats();

        return response()->success(
            $stats,
            'General statistics retrieved successfully'
        );
    }

    /**
     * Get academic statistics for the dashboard.
     *
     * @return JsonResponse
     */
    public function academicStats(): JsonResponse
    {
        $stats = $this->dashboardMetricsService->getAcademicStats();

        return response()->success(
            $stats,
            'Academic statistics retrieved successfully'
        );
    }

    /**
     * Get financial statistics for the dashboard.
     *
     * @return JsonResponse
     */
    public function financialStats(): JsonResponse
    {
        $stats = $this->dashboardMetricsService->getFinancialStats();

        return response()->success(
            $stats,
            'Financial statistics retrieved successfully'
        );
    }

    /**
     * Get student statistics for the dashboard.
     *
     * @return JsonResponse
     */
    public function studentStats(): JsonResponse
    {
        $stats = $this->dashboardMetricsService->getStudentStats();

        return response()->success(
            $stats,
            'Student statistics retrieved successfully'
        );
    }

    /**
     * Get staff statistics for the dashboard.
     *
     * @return JsonResponse
     */
    public function staffStats(): JsonResponse
    {
        $stats = $this->dashboardMetricsService->getStaffStats();

        return response()->success(
            $stats,
            'Staff statistics retrieved successfully'
        );
    }

    /**
     * Get academic performance statistics for the dashboard.
     *
     * @return JsonResponse
     */
    public function performanceStats(): JsonResponse
    {
        $stats = $this->dashboardMetricsService->getPerformanceStats();

        return response()->success(
            $stats,
            'Performance statistics retrieved successfully'
        );
    }

    /**
     * Get enrollment statistics for the dashboard.
     *
     * @return JsonResponse
     */
    public function enrollmentStats(): JsonResponse
    {
        $stats = $this->dashboardMetricsService->getEnrollmentStats();

        return response()->success(
            $stats,
            'Enrollment statistics retrieved successfully'
        );
    }

    /**
     * Get notification statistics for the dashboard.
     *
     * @return JsonResponse
     */
    public function notificationStats(): JsonResponse
    {
        $stats = $this->dashboardMetricsService->getNotificationStats();

        return response()->success(
            $stats,
            'Notification statistics retrieved successfully'
        );
    }

    /**
     * Get recent activities for the dashboard.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function recentActivities(Request $request): JsonResponse
    {
        $limit = $request->query('limit', 10);
        $activities = $this->dashboardMetricsService->getRecentActivities($limit);

        return response()->success(
            $activities,
            'Recent activities retrieved successfully'
        );
    }

    /**
     * Get performance trends over time.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function performanceTrends(Request $request): JsonResponse
    {
        $months = $request->query('months', 6);
        $trends = $this->dashboardMetricsService->getPerformanceTrends($months);

        return response()->success(
            $trends,
            'Performance trends retrieved successfully'
        );
    }

    /**
     * Get top performing students.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function topPerformingStudents(Request $request): JsonResponse
    {
        $limit = $request->query('limit', 10);
        $students = $this->dashboardMetricsService->getTopPerformingStudents($limit);

        return response()->success(
            $students,
            'Top performing students retrieved successfully'
        );
    }

    /**
     * Get course performance statistics.
     *
     * @return JsonResponse
     */
    public function coursePerformanceStats(): JsonResponse
    {
        $stats = $this->dashboardMetricsService->getCoursePerformanceStats();

        return response()->success(
            $stats,
            'Course performance statistics retrieved successfully'
        );
    }

    /**
     * Get score distribution statistics.
     *
     * @return JsonResponse
     */
    public function scoreDistribution(): JsonResponse
    {
        $performanceStats = $this->dashboardMetricsService->getPerformanceStats();
        $scoreDistribution = $performanceStats['score_distribution'] ?? [];

        return response()->success(
            $scoreDistribution,
            'Score distribution retrieved successfully'
        );
    }

    /**
     * Get grade distribution statistics.
     *
     * @return JsonResponse
     */
    public function gradeDistribution(): JsonResponse
    {
        $performanceStats = $this->dashboardMetricsService->getPerformanceStats();
        $gradeDistribution = $performanceStats['grade_distribution'] ?? [];

        return response()->success(
            $gradeDistribution,
            'Grade distribution retrieved successfully'
        );
    }

    /**
     * Get payments by method statistics.
     *
     * @return JsonResponse
     */
    public function paymentsByMethod(): JsonResponse
    {
        $financialStats = $this->dashboardMetricsService->getFinancialStats();
        $paymentsByMethod = $financialStats['payments_by_method'] ?? [];

        return response()->success(
            $paymentsByMethod,
            'Payments by method retrieved successfully'
        );
    }

    /**
     * Get payments by month statistics.
     *
     * @return JsonResponse
     */
    public function paymentsByMonth(): JsonResponse
    {
        $financialStats = $this->dashboardMetricsService->getFinancialStats();
        $paymentsByMonth = $financialStats['payments_by_month'] ?? [];

        return response()->success(
            $paymentsByMonth,
            'Payments by month retrieved successfully'
        );
    }

    /**
     * Get students by program statistics.
     *
     * @return JsonResponse
     */
    public function studentsByProgram(): JsonResponse
    {
        $studentStats = $this->dashboardMetricsService->getStudentStats();
        $studentsByProgram = $studentStats['students_by_program'] ?? [];

        return response()->success(
            $studentsByProgram,
            'Students by program retrieved successfully'
        );
    }

    /**
     * Get students by gender statistics.
     *
     * @return JsonResponse
     */
    public function studentsByGender(): JsonResponse
    {
        $studentStats = $this->dashboardMetricsService->getStudentStats();
        $studentsByGender = $studentStats['students_by_gender'] ?? [];

        return response()->success(
            $studentsByGender,
            'Students by gender retrieved successfully'
        );
    }
}
