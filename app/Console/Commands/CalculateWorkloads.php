<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WorkloadService;

class CalculateWorkloads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workload:calculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates and stores workload metrics for all employees';

    /**
     * Execute the console command.
     */
    public function handle(WorkloadService $service)
    {
        $this->info('Starting workload calculation...');
        $startTime = microtime(true);
        
        $service->calculateAndStoreWorkloads();
        
        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);
        
        $this->info("Workload calculation completed successfully in {$duration} seconds.");
    }
}
