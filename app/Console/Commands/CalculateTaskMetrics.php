<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TaskMetricsPrecomputationService;

class CalculateTaskMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:calculate-metrics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Precompute task analytics metrics and trends';

    /**
     * Execute the console command.
     */
    public function handle(TaskMetricsPrecomputationService $service)
    {
        $this->info('Starting task metrics precomputation...');
        $startTime = microtime(true);

        $service->run();

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        $this->info("Task metrics precomputation completed successfully in {$duration} seconds.");
    }
}
