<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AttendanceMetricsPrecomputationService;

class CalculateAttendanceMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:calculate-metrics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Precomputes and updates all attendance metrics and trends';

    /**
     * Execute the console command.
     */
    public function handle(AttendanceMetricsPrecomputationService $service)
    {
        $this->info('Starting attendance metrics calculation...');
        
        $service->calculateAllMetrics();
        
        $this->info('Attendance metrics calculation completed successfully.');
    }
}
