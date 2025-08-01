<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\API\AttendanceController;
use App\Http\Controllers\API\CardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root based on user type
Route::get('/', function () {
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

// Authenticated user routes
Route::middleware('auth')->group(function () {

    // Admin-only routes
    Route::middleware('can:isAdmin')->group(function () {
        Route::get('/admin/home', [AdminController::class, 'home'])->name('admin.home');
        Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    });

    // Lecturer-only dashboard
    Route::get('/users/home', function () {
        if (Auth::user()->utype !== 'user') {
            abort(403, 'Access denied');
        }
        return view('users.home');
    })->name('users.home');

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// Public routes (not restricted by role)
Route::get('/cards', [CardController::class, 'index'])->name('cards.index');
Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');

// Demo/UI pages
Route::view('/basic-table', 'pages.basic-table')->name('basic.table');
Route::view('/chartjs', 'pages.chartjs')->name('chartjs');
Route::view('/basic_elements', 'pages.basic_elements')->name('form.elements');
Route::view('/mdi', 'pages.mdi')->name('mdi');
Route::view('/buttons', 'pages.buttons')->name('buttons');
Route::view('/typography', 'pages.typography')->name('typography');

