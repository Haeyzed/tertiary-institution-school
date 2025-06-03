<?php

use App\Http\Controllers\ACLController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\UserProfileController;
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

    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
        Route::get('/verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');

        // Protected auth routes
        Route::middleware('auth:api')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/refresh', [AuthController::class, 'refreshToken']);
            Route::get('/profile', [AuthController::class, 'profile']);
            Route::put('/profile', [AuthController::class, 'updateProfile']);
            Route::post('/profile/photo', [AuthController::class, 'uploadProfilePhoto']);
            Route::delete('/profile/photo', [AuthController::class, 'removeProfilePhoto']);
            Route::post('/change-password', [AuthController::class, 'changePassword']);
            Route::post('/resend-verification', [AuthController::class, 'resendEmailVerification']);
        });
    });

    // Protected routes
//    Route::middleware('auth:api')->group(function () {

        // Upload routes
        Route::prefix('uploads')->group(function () {
            Route::get('/', [UploadController::class, 'index']);
            Route::post('/', [UploadController::class, 'store']);
            Route::post('/multiple', [UploadController::class, 'storeMultiple']);
            Route::get('/statistics', [UploadController::class, 'statistics']);
            Route::get('/{upload}', [UploadController::class, 'show']);
            Route::put('/{upload}', [UploadController::class, 'update']);
            Route::delete('/{upload}', [UploadController::class, 'destroy']);
            Route::get('/{upload}/download', [UploadController::class, 'download'])->name('uploads.download');
            Route::get('/{upload}/thumbnail/{size?}', [UploadController::class, 'thumbnail'])->name('uploads.thumbnail');
            Route::post('/{upload}/temporary-url', [UploadController::class, 'temporaryUrl']);
        });

        // Profile photo routes (example use case)
        Route::prefix('profile')->group(function () {
            Route::get('/', [UserProfileController::class, 'getProfile']);
            Route::post('/photo', [UserProfileController::class, 'uploadProfilePhoto']);
            Route::delete('/photo', [UserProfileController::class, 'removeProfilePhoto']);
        });

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

        // Role and permission routes
        Route::apiResource('roles', RoleController::class);
        Route::post('roles/{id}/permissions', [RoleController::class, 'assignPermissions']);
        Route::post('roles/create-defaults', [RoleController::class, 'createDefaultRoles']);

        Route::apiResource('permissions', PermissionController::class);
        Route::post('permissions/create-defaults', [PermissionController::class, 'createDefaultPermissions']);

        // ACL routes
        Route::get('users/{userId}/roles', [ACLController::class, 'getUserRoles']);
        Route::get('users/{userId}/permissions/direct', [ACLController::class, 'getUserDirectPermissions']);
        Route::get('users/{userId}/permissions/all', [ACLController::class, 'getUserAllPermissions']);
        Route::post('users/{userId}/roles', [ACLController::class, 'assignRolesToUser']);
        Route::post('users/{userId}/permissions', [ACLController::class, 'assignPermissionsToUser']);
        Route::delete('users/{userId}/roles', [ACLController::class, 'removeRolesFromUser']);
        Route::delete('users/{userId}/permissions', [ACLController::class, 'removePermissionsFromUser']);
        Route::get('users/{userId}/roles/{roleName}/check', [ACLController::class, 'checkUserHasRole']);
        Route::get('users/{userId}/permissions/{permissionName}/check', [ACLController::class, 'checkUserHasPermission']);

        // User routes
        Route::apiResource('users', UserController::class);
        Route::prefix('users')->group(function () {
            Route::put('/{id}/restore', [UserController::class, 'restore']);

            // User photo management
            Route::post('/{id}/photo', [UserController::class, 'uploadProfilePhoto']);
            Route::delete('/{id}/photo', [UserController::class, 'removeProfilePhoto']);

            // User uploads
            Route::get('/{id}/uploads', [UserController::class, 'getUserUploads']);
            Route::get('/{id}/uploads/statistics', [UserController::class, 'getUserUploadStatistics']);

            // Role and permission management
            Route::post('/{id}/roles', [UserController::class, 'assignRoles']);
            Route::post('/{id}/permissions', [UserController::class, 'assignPermissions']);
            Route::get('/{id}/permissions', [UserController::class, 'getUserPermissions']);
        });

        // Student routes
        Route::apiResource('students', StudentController::class);
        Route::put('students/{id}/restore', [UserController::class, 'restore']);
        Route::get('programs/{programId}/students', [StudentController::class, 'getByProgram']);
        Route::post('students/enroll', [StudentController::class, 'enrollInCourse']);
        Route::post('students/unenroll', [StudentController::class, 'unenrollFromCourse']);

        // Course routes
        Route::apiResource('courses', CourseController::class);
        Route::put('courses/{id}/restore', [CourseController::class, 'restore']);
        Route::get('departments/{departmentId}/courses', [CourseController::class, 'getByDepartment']);
        Route::get('semesters/{semesterId}/courses', [CourseController::class, 'getBySemester']);
        Route::post('courses/assign', [CourseController::class, 'assignToSemester']);
        Route::post('courses/remove', [CourseController::class, 'removeFromSemester']);

        // Result routes
        Route::apiResource('results', ResultController::class);
        Route::put('results/{id}/restore', [ResultController::class, 'restore']);
        Route::get('students/{studentId}/results', [ResultController::class, 'getByStudent']);
        Route::get('courses/{courseId}/results', [ResultController::class, 'getByCourse']);
        Route::get('exams/{examId}/results', [ResultController::class, 'getByExam']);
        Route::get('semesters/{semesterId}/results', [ResultController::class, 'getBySemester']);
        Route::get('students/{studentId}/semesters/{semesterId}/gpa', [ResultController::class, 'getStudentSemesterGPA']);
        Route::get('students/{studentId}/gpa', [ResultController::class, 'getStudentCumulativeGPA']);

        // Payment routes
        Route::apiResource('payments', PaymentController::class);
        Route::put('payments/{id}/restore', [PaymentController::class, 'restore']);
        Route::get('students/{studentId}/payments', [PaymentController::class, 'getByStudent']);
        Route::get('fees/{feeId}/payments', [PaymentController::class, 'getByFee']);
        Route::get('students/{studentId}/fee-balance', [PaymentController::class, 'getStudentFeeBalance']);
        Route::get('students/{studentId}/fees/{feeId}/balance', [PaymentController::class, 'getStudentFeeBalance']);

        // Faculty routes
        Route::apiResource('faculties', FacultyController::class);
        Route::put('faculties/{id}/restore', [FacultyController::class, 'restore']);

        // Department routes
        Route::apiResource('departments', DepartmentController::class);
        Route::put('departments/{id}/restore', [DepartmentController::class, 'restore']);
        Route::get('faculties/{facultyId}/departments', [DepartmentController::class, 'getByFaculty']);

        // Program routes
        Route::apiResource('programs', ProgramController::class);
        Route::put('programs/{id}/restore', [ProgramController::class, 'restore']);
        Route::get('departments/{departmentId}/programs', [ProgramController::class, 'getByDepartment']);

        // Academic Session routes
        Route::apiResource('academic-sessions', AcademicSessionController::class);
        Route::put('academic-sessions/{id}/restore', [AcademicSessionController::class, 'restore']);
        Route::get('academic-sessions/current', [AcademicSessionController::class, 'getCurrent']);
        Route::put('academic-sessions/{id}/set-current', [AcademicSessionController::class, 'setCurrent']);

        // Semester routes
        Route::apiResource('semesters', SemesterController::class);
        Route::put('semesters/{id}/restore', [SemesterController::class, 'restore']);
        Route::get('academic-sessions/{academicSessionId}/semesters', [SemesterController::class, 'getByAcademicSession']);
        Route::get('semesters/current', [SemesterController::class, 'getCurrent']);

        // Grade routes
        Route::apiResource('grades', GradeController::class);
        Route::put('grades/{id}/restore', [GradeController::class, 'restore']);
        Route::get('grades/by-score', [GradeController::class, 'getByScore']);

        // Staff routes
        Route::apiResource('staff', StaffController::class);
        Route::put('staff/{id}/restore', [StaffController::class, 'restore']);
        Route::get('departments/{departmentId}/staff', [StaffController::class, 'getByDepartment']);

        // Parent routes
        Route::apiResource('parents', ParentController::class);
        Route::put('parents/{id}/restore', [ParentController::class, 'restore']);

        // Exam routes
        Route::apiResource('exams', ExamController::class);
        Route::put('exams/{id}/restore', [ExamController::class, 'restore']);
        Route::get('courses/{courseId}/exams', [ExamController::class, 'getByCourse']);
        Route::get('semesters/{semesterId}/exams', [ExamController::class, 'getBySemester']);
        Route::get('exams/upcoming', [ExamController::class, 'getUpcoming']);
        Route::put('exams/{id}/status', [ExamController::class, 'updateStatus']);

        // Assignment routes
        Route::apiResource('assignments', AssignmentController::class);
        Route::put('assignments/{id}/restore', [AssignmentController::class, 'restore']);
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
        Route::put('fees/{id}/restore', [FeeController::class, 'restore']);
        Route::get('programs/{programId}/fees', [FeeController::class, 'getByProgram']);
        Route::get('semesters/{semesterId}/fees', [FeeController::class, 'getBySemester']);
        Route::get('fees/current-semester', [FeeController::class, 'getCurrentSemesterFees']);

        // Notification routes
        Route::apiResource('notifications', NotificationController::class);
        Route::put('notifications/{id}/restore', [NotificationController::class, 'restore']);
        Route::get('users/{userId}/notifications', [NotificationController::class, 'getByUser']);
        Route::get('users/{userId}/notifications/unread', [NotificationController::class, 'getUnreadByUser']);
        Route::put('notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead']);
        Route::put('users/{userId}/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);

        // Announcement routes
        Route::apiResource('announcements', AnnouncementController::class);
        Route::put('announcements/{id}/restore', [AnnouncementController::class, 'restore']);
        Route::get('users/{createdBy}/announcements', [AnnouncementController::class, 'getByCreator']);
        Route::get('announcements/recent', [AnnouncementController::class, 'getRecent']);
//    });
});

// Fallback route for undefined API routes
Route::fallback(function () {
    return response()->error('API endpoint not found', null, 404);
});
