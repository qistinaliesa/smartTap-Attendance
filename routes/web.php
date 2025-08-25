<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\API\AttendanceController;
use App\Http\Controllers\API\CardController;
use App\Http\Controllers\LecturerController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\LecturerCourseController;


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

// Authenticated user routes (regular users and admin)
Route::middleware('auth')->group(function () {

    // Admin-only routes
    Route::middleware('can:isAdmin')->group(function () {
        Route::get('/admin/home', [AdminController::class, 'home'])->name('admin.home');
        Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');

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
    });

    // GET: Show form and list
    Route::get('/lecturer-registration', [LecturerController::class, 'showRegistrationForm'])->name('lecturer.register.form');

    // POST: Handle form submission
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
});

// Lecturer-only routes (using lecturer guard)
Route::middleware(['lecturer.auth'])->prefix('lecturer')->group(function () {
    Route::get('/dashboard', function () {
        return view('users.home');
    })->name('lusers.home');

    Route::get('/courses', [LecturerCourseController::class, 'index'])->name('lecturer.courses');
    Route::get('/courses/{course}', [LecturerCourseController::class, 'show'])->name('lecturer.course.show');

    // Take attendance route
    Route::get('/courses/{course}/take-attendance', [LecturerCourseController::class, 'takeAttendance'])->name('lecturer.course.take_attendance');

    // Optional - View past attendance records
    Route::get('/courses/{course}/attendance-history', [LecturerCourseController::class, 'showAttendanceHistory'])->name('lecturer.course.attendance_history');

    // View individual student attendance
    Route::get('/courses/{course}/student/{enrollment}/attendance', [LecturerCourseController::class, 'showStudentAttendance'])->name('lecturer.course.student_attendance');
     Route::get('/courses/{course}/overview', [LecturerCourseController::class, 'showOverview'])->name('lecturer.course.overview');
Route::get('/courses/{course}/student/{enrollment}/recent-attendance', [LecturerCourseController::class, 'getRecentAttendanceRecords'])->name('lecturer.student.recent_attendance');
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
