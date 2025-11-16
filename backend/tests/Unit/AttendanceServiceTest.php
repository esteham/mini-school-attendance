<?php

namespace Tests\Unit;

use App\Models\Attendance;
use App\Models\Student;
use App\Services\AttendanceService;

it('calculates daily stats correctly', function () {
    $service = app(AttendanceService::class);

    $student = Student::factory()->create();

    Attendance::factory()->create([
        'student_id' => $student->id,
        'date'       => today(),
        'status'     => 'present',
    ]);

    $stats = $service->dailyStats(today()->toDateString());

    expect($stats['total'])->toBe(1)
        ->and($stats['present'])->toBe(1)
        ->and($stats['percent'])->toBe(100.0);
});
