<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\AcademicSessionController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AnnouncementController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// API version prefix
Route::prefix('v1')->group(function () {

    // Dashboard routes
    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index']);
        Route::get('/general-stats', [DashboardController::class, 'generalStats']);
        Route::get('/academic-stats', [DashboardController::class, 'academicStats']);
        Route::get('/financial-stats', [DashboardController::class, 'financialStats']);
        Route::get('/student-stats', [DashboardController::class, 'studentStats']);
        Route::get('/staff-stats', [DashboardController::class, 'staffStats']);
        Route::get('/performance-stats', [DashboardController::class, 'performanceStats']);
        Route::get('/enrollment-stats', [DashboardController::class, 'enrollmentStats']);
        Route::get('/notification-stats', [DashboardController::class, 'notificationStats']);
        Route::get('/recent-activities', [DashboardController::class, 'recentActivities']);
        Route::get('/performance-trends', [DashboardController::class, 'performanceTrends']);
        Route::get('/top-performing-students', [DashboardController::class, 'topPerformingStudents']);
        Route::get('/course-performance-stats', [DashboardController::class, 'coursePerformanceStats']);
        Route::get('/score-distribution', [DashboardController::class, 'scoreDistribution']);
        Route::get('/grade-distribution', [DashboardController::class, 'gradeDistribution']);
        Route::get('/payments-by-method', [DashboardController::class, 'paymentsByMethod']);
        Route::get('/payments-by-month', [DashboardController::class, 'paymentsByMonth']);
        Route::get('/students-by-program', [DashboardController::class, 'studentsByProgram']);
        Route::get('/students-by-gender', [DashboardController::class, 'studentsByGender']);
    });

    // User routes
    Route::apiResource('users', UserController::class);
    Route::get('users/search/{term}', [UserController::class, 'search']);

    // Student routes
    Route::apiResource('students', StudentController::class);
    Route::get('programs/{programId}/students', [StudentController::class, 'getByProgram']);
    Route::post('students/enroll', [StudentController::class, 'enrollInCourse']);
    Route::post('students/unenroll', [StudentController::class, 'unenrollFromCourse']);

    // Course routes
    Route::apiResource('courses', CourseController::class);
    Route::get('departments/{departmentId}/courses', [CourseController::class, 'getByDepartment']);
    Route::get('semesters/{semesterId}/courses', [CourseController::class, 'getBySemester']);
    Route::post('courses/assign', [CourseController::class, 'assignToSemester']);
    Route::post('courses/remove', [CourseController::class, 'removeFromSemester']);

    // Result routes
    Route::apiResource('results', ResultController::class);
    Route::get('students/{studentId}/results', [ResultController::class, 'getByStudent']);
    Route::get('courses/{courseId}/results', [ResultController::class, 'getByCourse']);
    Route::get('exams/{examId}/results', [ResultController::class, 'getByExam']);
    Route::get('semesters/{semesterId}/results', [ResultController::class, 'getBySemester']);
    Route::get('students/{studentId}/semesters/{semesterId}/gpa', [ResultController::class, 'getStudentSemesterGPA']);
    Route::get('students/{studentId}/gpa', [ResultController::class, 'getStudentCumulativeGPA']);

    // Payment routes
    Route::apiResource('payments', PaymentController::class);
    Route::get('students/{studentId}/payments', [PaymentController::class, 'getByStudent']);
    Route::get('fees/{feeId}/payments', [PaymentController::class, 'getByFee']);
    Route::get('students/{studentId}/fee-balance', [PaymentController::class, 'getStudentFeeBalance']);
    Route::get('students/{studentId}/fees/{feeId}/balance', [PaymentController::class, 'getStudentFeeBalance']);

    // Faculty routes
    Route::apiResource('faculties', FacultyController::class);
    Route::get('faculties/search/{term}', [FacultyController::class, 'search']);

    // Department routes
    Route::apiResource('departments', DepartmentController::class);
    Route::get('faculties/{facultyId}/departments', [DepartmentController::class, 'getByFaculty']);
    Route::get('departments/search/{term}', [DepartmentController::class, 'search']);

    // Program routes
    Route::apiResource('programs', ProgramController::class);
    Route::get('departments/{departmentId}/programs', [ProgramController::class, 'getByDepartment']);
    Route::get('programs/search/{term}', [ProgramController::class, 'search']);

    // Academic Session routes
    Route::apiResource('academic-sessions', AcademicSessionController::class);
    Route::get('academic-sessions/current', [AcademicSessionController::class, 'getCurrent']);
    Route::put('academic-sessions/{id}/set-current', [AcademicSessionController::class, 'setCurrent']);

    // Semester routes
    Route::apiResource('semesters', SemesterController::class);
    Route::get('academic-sessions/{academicSessionId}/semesters', [SemesterController::class, 'getByAcademicSession']);
    Route::get('semesters/current', [SemesterController::class, 'getCurrent']);

    // Grade routes
    Route::apiResource('grades', GradeController::class);
    Route::get('grades/by-score', [GradeController::class, 'getByScore']);

    // Staff routes
    Route::apiResource('staff', StaffController::class);
    Route::get('departments/{departmentId}/staff', [StaffController::class, 'getByDepartment']);
    Route::get('staff/search/{term}', [StaffController::class, 'search']);

    // Parent routes
    Route::apiResource('parents', ParentController::class);
    Route::get('parents/search/{term}', [ParentController::class, 'search']);

    // Exam routes
    Route::apiResource('exams', ExamController::class);
    Route::get('courses/{courseId}/exams', [ExamController::class, 'getByCourse']);
    Route::get('semesters/{semesterId}/exams', [ExamController::class, 'getBySemester']);
    Route::get('exams/upcoming', [ExamController::class, 'getUpcoming']);
    Route::put('exams/{id}/status', [ExamController::class, 'updateStatus']);

    // Assignment routes
    Route::apiResource('assignments', AssignmentController::class);
    Route::get('courses/{courseId}/assignments', [AssignmentController::class, 'getByCourse']);
    Route::get('semesters/{semesterId}/assignments', [AssignmentController::class, 'getBySemester']);
    Route::get('staff/{staffId}/assignments', [AssignmentController::class, 'getByStaff']);
    Route::get('assignments/upcoming', [AssignmentController::class, 'getUpcoming']);
    Route::post('assignments/submit', [AssignmentController::class, 'submitAssignment']);
    Route::put('student-assignments/{id}/grade', [AssignmentController::class, 'gradeAssignment']);
    Route::get('assignments/{assignmentId}/submissions', [AssignmentController::class, 'getStudentAssignmentsByAssignment']);
    Route::get('students/{studentId}/assignments', [AssignmentController::class, 'getStudentAssignmentsByStudent']);

    // Fee routes
    Route::apiResource('fees', FeeController::class);
    Route::get('programs/{programId}/fees', [FeeController::class, 'getByProgram']);
    Route::get('semesters/{semesterId}/fees', [FeeController::class, 'getBySemester']);
    Route::get('fees/current-semester', [FeeController::class, 'getCurrentSemesterFees']);

    // Notification routes
    Route::apiResource('notifications', NotificationController::class);
    Route::get('users/{userId}/notifications', [NotificationController::class, 'getByUser']);
    Route::get('users/{userId}/notifications/unread', [NotificationController::class, 'getUnreadByUser']);
    Route::put('notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead']);
    Route::put('users/{userId}/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);

    // Announcement routes
    Route::apiResource('announcements', AnnouncementController::class);
    Route::get('users/{createdBy}/announcements', [AnnouncementController::class, 'getByCreator']);
    Route::get('announcements/recent', [AnnouncementController::class, 'getRecent']);
});

// Fallback route for undefined API routes
Route::fallback(function () {
    return response()->error('API endpoint not found', null, 404);
});
