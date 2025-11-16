<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Pest\Laravel\actingAs;
use Pest\Laravel\postJson;

it('can bulk store attendance', function () {
    $user = User::factory()->create();
    actingAs($user);

    $students = Student::factory()->count(2)->create();

    $payload = [
        'date'    => now()->toDateString(),
        'records' => $students->map(function ($s) {
            return [
                'student_id' => $s->id,
                'status'     => 'present',
                'note'       => null,
            ];
        })->toArray(),
    ];

    $response = postJson('/api/attendance/bulk', $payload);

    $response->assertOk()->assertJsonPath('message', 'Attendance saved');
});
