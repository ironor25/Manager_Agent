<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ProjectMetricsPrecomputationService;

class CalculateProjectMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projects:calculate-metrics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Precomputes and updates all project metrics and trends';

    /**
     * Execute the console command.
     */
    public function handle(ProjectMetricsPrecomputationService $service)
    {
        $this->info('Starting project metrics calculation...');
        
        $service->calculateAllMetrics();
        
        $this->info('Project metrics calculation completed successfully.');
    }
}
