<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\App\Models\GithubCommit::truncate();
$employees = \App\Models\Employee::all();
foreach($employees as $employee) {
    \App\Models\GithubCommit::factory()->count(rand(5,50))->create(['employee_id' => $employee->id]);
}
echo "Done seeding commits.\n";
