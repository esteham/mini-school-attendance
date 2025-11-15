<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\AttendanceService;
use Carbon\Carbon;

class GenerateAttendanceReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:generate-report {month} {class?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly attendance report for given month and class';

    public function __construct(private AttendanceService $attendanceService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $monthInput = $this->argument('month'); // e.g. 2025-01
        $class = $this->argument('class');

        try {
            $date = Carbon::parse($monthInput);
        } catch (\Exception $e) {
            $this->error('Invalid month format. Use YYYY-MM.');
            return self::FAILURE;
        }

        $report = $this->attendanceService->monthlyReport(
            $date->year,
            $date->month,
            $class
        );

        $this->info("Attendance Report for {$report['year']}-{$report['month']} Class: " . ($class ?? 'ALL'));

        foreach ($report['records'] as $row) {
            $this->line(sprintf(
                '%s (%s) - Present: %d / %d (%.2f%%)',
                $row['student']['name'],
                $row['student']['student_id'],
                $row['present'],
                $row['total'],
                $row['percent'],
            ));
        }

        return self::SUCCESS;
    }
}
