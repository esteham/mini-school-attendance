<?php

namespace App\Listeners;

use App\Events\AttendanceRecorded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use Illuminate\Support\Facades\Log;

class SendAttendanceNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AttendanceRecorded $event): void
    {
        Log::info('Attendance recorded', [
            'student_id' => $event->attendance->student_id,
            'date'       => $event->attendance->date->toDateString(),
            'status'     => $event->attendance->status,
        ]);
        
    }
}
