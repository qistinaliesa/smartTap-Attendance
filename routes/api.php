<?php

use App\Http\Controllers\API\AttendanceController;
use App\Http\Controllers\API\CardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// API test route
Route::get('/', function () {
    return response()->json(['message' => 'API connection successful']);
});
Route::get('/ping', function () {
    return response()->json(['status' => 'API is working!']);
});
// Card registration
Route::post('/register', [CardController::class, 'register']);

// Attendance recording
Route::post('/attendance', [AttendanceController::class, 'recordAttendance']);
