<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\API\AttendanceController;
use App\Http\Controllers\API\CardController;
use App\Http\Controllers\LecturerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\LecturerCourseController;
use Illuminate\Support\Facades\Mail;
use App\Mail\AttendanceWarningMail;


// Add this temporarily to your routes file for debugging
Route::get('/debug-user', function () {
    if (!Auth::check()) {
        return 'Not logged in';
    }

    $user = Auth::user();
    return response()->json([
        'user_id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'utype' => $user->utype,
        'is_admin_check' => $user->utype === 'admin'
    ]);
})->middleware('auth');

// Redirect root based on user type (updated to handle lecturers)
Route::get('/', function () {
    // Check lecturer authentication first
    if (Auth::guard('lecturer')->check()) {
        return redirect('/lecturer/dashboard');
    }

    // Then check regular user authentication (your original logic)
    if (Auth::check()) {
        return Auth::user()->utype === 'admin'
            ? redirect('/admin/home')
            : redirect('/users/home');
    }

    return redirect('/login');
})->name('users.home');

// Guest routes (unauthenticated users)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});
Route::get('/admin/dashboard/course-average-attendance', [App\Http\Controllers\Admin\DashboardController::class, 'getCourseAverageAttendance']);

// Authenticated user routes (regular users and admin)
Route::middleware('auth')->group(function () {

    // Admin-only routes
    Route::middleware('can:isAdmin')->group(function () {
        Route::get('/admin/home', [DashboardController::class, 'index'])->name('admin.home');
        Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');

        // Dashboard AJAX routes
        Route::get('/admin/dashboard/realtime-data', [DashboardController::class, 'getRealtimeData'])->name('admin.dashboard.realtime');
        Route::get('/dashboard/course-average-attendance', [DashboardController::class, 'getCourseAverageAttendance']);
    Route::get('/admin/dashboard/course-attendance', [DashboardController::class, 'getCourseAttendance'])->name('admin.dashboard.course_attendance');
    Route::get('/admin/dashboard/course-enrollment', [DashboardController::class, 'getCourseEnrollment'])->name('admin.dashboard.course_enrollment'); // NEW ROUTE
    Route::get('/admin/dashboard/recent-attendance', [DashboardController::class, 'getRecentAttendance'])->name('admin.dashboard.recent_attendance');
    Route::get('/admin/dashboard/weekly-attendance', [DashboardController::class, 'getWeeklyAttendance'])->name('admin.dashboard.weekly_attendance');
    Route::get('/admin/dashboard/top-courses', [DashboardController::class, 'getTopCourses'])->name('admin.dashboard.top_courses');
    Route::get('/admin/dashboard/attendance-stats', [DashboardController::class, 'getAttendanceStats'])->name('admin.dashboard.attendance_stats');

        // Lecturer management routes
        Route::get('/admin/lecturers', [LecturerController::class, 'index'])->name('admin.lecturer.index');
        Route::get('/admin/lecturers/create', [LecturerController::class, 'create'])->name('admin.lecturer.create');
        Route::post('/admin/lecturers', [LecturerController::class, 'store'])->name('admin.lecturer.store');
        Route::get('/admin/lecturers/{lecturer}', [LecturerController::class, 'show'])->name('admin.lecturer.show');
        Route::get('/admin/lecturers/{lecturer}/edit', [LecturerController::class, 'edit'])->name('admin.lecturer.edit');
        Route::put('/admin/lecturers/{lecturer}', [LecturerController::class, 'update'])->name('admin.lecturer.update');
        Route::delete('/admin/lecturers/{lecturer}', [LecturerController::class, 'destroy'])->name('admin.lecturer.destroy');

        // Course management routes
        Route::get('/admin/courses', [CourseController::class, 'index'])->name('admin.course.index');
        Route::get('/admin/courses/create', [CourseController::class, 'create'])->name('admin.course.create');
        Route::post('/admin/courses', [CourseController::class, 'store'])->name('admin.course.store');
        Route::get('/admin/courses/{course}', [CourseController::class, 'show'])->name('admin.course.show');
        Route::get('/admin/courses/{course}/edit', [CourseController::class, 'edit'])->name('admin.course.edit');
        Route::put('/admin/courses/{course}', [CourseController::class, 'update'])->name('admin.course.update');
        Route::delete('/admin/courses/{course}', [CourseController::class, 'destroy'])->name('admin.course.destroy');
    }); // Close admin middleware group

    // Lecturer registration routes (for authenticated regular users)
    Route::get('/lecturer-registration', [LecturerController::class, 'showRegistrationForm'])->name('lecturer.register.form');
    Route::post('/lecturer-registration', [LecturerController::class, 'register'])->name('lecturer.register');

    // Regular user dashboard
    Route::get('/users/home', function () {
        if (Auth::user()->utype !== 'user') {
            abort(403, 'Access denied');
        }
        return view('users.home');
    })->name('users.home');

    // Logout (updated to handle both guards)
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
}); // Close auth middleware group

// FIXED: Lecturer-only routes (using lecturer guard) with /lecturer prefix
Route::middleware(['lecturer.auth'])->prefix('lecturer')->group(function () {
    Route::get('/dashboard', function () {
        return view('users.home');
    })->name('lusers.home');

    // Basic course routes
    Route::get('/courses', [LecturerCourseController::class, 'index'])->name('lecturer.courses');
    Route::get('/courses/{course}', [LecturerCourseController::class, 'show'])->name('lecturer.course.show');

    // Take attendance route
    Route::get('/courses/{course}/take-attendance', [LecturerCourseController::class, 'takeAttendance'])->name('lecturer.course.take_attendance');

    // Optional - View past attendance records
    Route::get('/courses/{course}/attendance-history', [LecturerCourseController::class, 'showAttendanceHistory'])->name('lecturer.course.attendance_history');

    // View individual student attendance
    Route::get('/courses/{course}/student/{enrollment}/attendance', [LecturerCourseController::class, 'showStudentAttendance'])->name('lecturer.course.student_attendance');
    Route::get('/courses/{course}/overview', [LecturerCourseController::class, 'showOverview'])->name('lecturer.course.overview');

    // FIXED: Student-specific routes (all using {enrollment} parameter for consistency)
    Route::get('/courses/{course}/student/{enrollment}/recent-attendance', [LecturerCourseController::class, 'getRecentAttendanceRecords'])->name('lecturer.student.recent_attendance');
    Route::get('/courses/{course}/student/{enrollment}/medical-certificates', [LecturerCourseController::class, 'getStudentMedicalCertificates'])->name('lecturer.student.medical_certificates');
    Route::get('/courses/{course}/student/{enrollment}/absent-dates', [LecturerCourseController::class, 'getAbsentDates'])->name('lecturer.student.absent_dates');
    Route::post('/courses/{course}/student/{enrollment}/mark-present', [LecturerCourseController::class, 'markPresentWithReason'])->name('lecturer.student.mark_present');
    Route::post('/courses/{course}/student/{enrollment}/send-warning', [LecturerCourseController::class, 'sendAttendanceWarning'])->name('lecturer.student.send_warning');

    // Medical certificate file routes
    Route::get('/courses/{course}/mc/{mc}/download', [LecturerCourseController::class, 'downloadMedicalCertificate'])->name('lecturer.mc.download');
    Route::delete('/courses/{course}/mc/{mc}', [LecturerCourseController::class, 'deleteMedicalCertificate'])->name('lecturer.mc.delete');

    // AJAX route to refresh attendance data without page reload
    Route::get('/courses/{course}/attendance-stats', [LecturerCourseController::class, 'getAttendanceStats'])->name('lecturer.course.attendance_stats');
});

// Public routes (not restricted by role)
Route::get('/cards', [CardController::class, 'index'])->name('cards.index');

// MOVED: General attendance route (separate from lecturer-specific attendance)
Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');

// Demo/UI pages
Route::view('/basic-table', 'pages.basic-table')->name('basic.table');
Route::view('/chartjs', 'pages.chartjs')->name('chartjs');

// Course registration route
Route::get('/course-registration', [CourseController::class, 'showRegistrationForm'])->name('course.register');
Route::post('/course-registration', [CourseController::class, 'store'])->name('course.store');

Route::view('/mdi', 'pages.mdi')->name('mdi');
Route::view('/buttons', 'pages.buttons')->name('buttons');
Route::view('/typography', 'pages.typography')->name('typography');
