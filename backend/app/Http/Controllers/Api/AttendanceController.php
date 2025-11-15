<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceBulkStoreRequest;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(private AttendanceService $attendanceService)
    {
    }

    public function bulkStore(AttendanceBulkStoreRequest $request)
    {
        $this->attendanceService->bulkRecord(
            $request->validated(),
            $request->user()?->id
        );

        return response()->json(['message' => 'Attendance saved']);
    }

    public function dailyStats(Request $request)
    {
        $date = $request->query('date', now()->toDateString());

        return response()->json(
            $this->attendanceService->dailyStats($date)
        );
    }

    public function monthlyReport(Request $request)
    {
        $validated = $request->validate([
            'year'  => ['required', 'integer'],
            'month' => ['required', 'integer', 'between:1,12'],
            'class' => ['nullable', 'string'],
            'section' => ['nullable', 'string'],
        ]);

        $report = $this->attendanceService->monthlyReport(
            $validated['year'],
            $validated['month'],
            $validated['class'] ?? null,
            $validated['section'] ?? null
        );

        return response()->json($report);
    }

    public function index(Request $request)
    {
        $date = $request->query('date');

        $query = Attendance::with('student');

        if ($date) {
            $query->whereDate('date', $date);
        }

        $attendances = $query->paginate(20);

        return AttendanceResource::collection($attendances);
    }
}
