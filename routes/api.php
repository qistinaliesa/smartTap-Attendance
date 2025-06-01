<?php

use App\Http\Controllers\API\AttendanceController;
use App\Http\Controllers\API\CardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
Route::get('/', function () {
    return view('admin.home');
})->name('home');

// API test route
Route::get('/', function () {
    return response()->json(['message' => 'API connection successful']);
});

// Card registration
Route::post('/register', [CardController::class, 'register']);

// Attendance recording
Route::post('/attendance', [AttendanceController::class, 'recordAttendance']);
