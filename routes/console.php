<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

\Illuminate\Support\Facades\Schedule::command('app:precompute-analytics')->everyMinute();
\Illuminate\Support\Facades\Schedule::command('workload:calculate')->everyMinute();
\Illuminate\Support\Facades\Schedule::command('tasks:calculate-metrics')->everyMinute();
\Illuminate\Support\Facades\Schedule::command('projects:calculate-metrics')->everyMinute();
\Illuminate\Support\Facades\Schedule::command('attendance:calculate-metrics')->everyMinute();