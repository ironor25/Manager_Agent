<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PrecomputeAnalytics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:precompute-analytics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Precompute expensive dashboard, employee, and team performance analytics metrics';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\AnalyticsPrecomputationService $service)
    {
        set_time_limit(0);
        $this->info('Starting metrics precomputation...');
        $service->runAll();
        $this->info('Metrics precomputation completed.');
    }
}
