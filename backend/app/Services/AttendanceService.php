<?php

namespace App\Services;

use App\Events\AttendanceRecorded;
use App\Models\Attendance;
use App\Models\Student;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function bulkRecord(array $data, int $userId = null): void
    {
        $date = Carbon::parse($data['date'])->toDateString();

        DB::transaction(function () use ($data, $date, $userId) {
            foreach ($data['records'] as $record) {
                $attendance = Attendance::updateOrCreate(
                    [
                        'student_id' => $record['student_id'],
                        'date'       => $date,
                    ],
                    [
                        'status'      => $record['status'],
                        'note'        => $record['note'] ?? null,
                        'recorded_by' => $userId,
                    ]
                );

                AttendanceRecorded::dispatch($attendance);
            }

            // cache invalidate (আজকের attendance stats)
            Cache::forget($this->getDailyStatsCacheKey($date));
        });
    }

    public function dailyStats(string $date): array
    {
        $key = $this->getDailyStatsCacheKey($date);

        return Cache::remember($key, now()->addMinutes(10), function () use ($date) {
            $total = Attendance::whereDate('date', $date)->count();

            $present = Attendance::whereDate('date', $date)->where('status', 'present')->count();
            $absent  = Attendance::whereDate('date', $date)->where('status', 'absent')->count();
            $late    = Attendance::whereDate('date', $date)->where('status', 'late')->count();

            return [
                'date'     => $date,
                'total'    => $total,
                'present'  => $present,
                'absent'   => $absent,
                'late'     => $late,
                'percent'  => $total > 0 ? round($present * 100 / $total, 2) : 0,
            ];
        });
    }

    public function monthlyReport(int $year, int $month, ?string $class = null): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end   = (clone $start)->endOfMonth();

        $query = Attendance::with('student')->whereBetween('date', [$start, $end]);

        if ($class) {
            $query->whereHas('student', function ($q) use ($class) {
                $q->where('class', $class);
            });
        }

        $records = $query->get(); // eager loaded

        $grouped = $records->groupBy('student_id')->map(function ($attendances, $studentId) {
            /** @var Student $student */
            $student = $attendances->first()->student;

            $present = $attendances->where('status', 'present')->count();
            $total   = $attendances->count();
            $percent = $total > 0 ? round($present * 100 / $total, 2) : 0;

            return [
                'student'   => [
                    'id'         => $student->id,
                    'name'       => $student->name,
                    'student_id' => $student->student_id,
                    'class'      => $student->class,
                    'section'    => $student->section,
                ],
                'present'   => $present,
                'total'     => $total,
                'percent'   => $percent,
                'attendances' => $attendances->map(function ($a) {
                    return [
                        'date'   => $a->date->toDateString(),
                        'status' => $a->status,
                        'note'   => $a->note,
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        return [
            'year'    => $year,
            'month'   => $month,
            'class'   => $class,
            'records' => $grouped,
        ];
    }

    protected function getDailyStatsCacheKey(string $date): string
    {
        return "attendance:stats:{$date}";
    }
}
