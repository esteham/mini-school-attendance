<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\AttendanceController;


Route::post('/login', [AuthController::class, 'login']);

Route::get('/students', [StudentController::class, 'index']);
Route::post('/students', [StudentController::class, 'store']);
Route::get('/students/{student}', [StudentController::class, 'show']);
Route::put('/students/{student}', [StudentController::class, 'update']);
Route::delete('/students/{student}', [StudentController::class, 'destroy']);

// Attendance
Route::post('/attendance/bulk', [AttendanceController::class, 'bulkStore']);
Route::get('/attendance', [AttendanceController::class, 'index']);
Route::get('/attendance/check', [AttendanceController::class, 'checkAttendance']);
Route::get('/attendance/daily-stats', [AttendanceController::class, 'dailyStats']);
Route::get('/attendance/monthly-report', [AttendanceController::class, 'monthlyReport']);

