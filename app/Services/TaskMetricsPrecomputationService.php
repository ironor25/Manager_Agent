<?php

namespace App\Services;

use App\Models\EmployeeTaskMetric;
use App\Models\TaskMetricsTrend;
use App\Models\Task;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaskMetricsPrecomputationService
{
    /**
     * Run precomputation for tasks metrics.
     */
    public function run()
    {
        Log::info('Starting task metrics precomputation...');
        
        $this->precomputeEmployeeTaskMetrics();
        $this->precomputeTrends();
        $this->mockHistoricalTrendsIfNeeded();
        $this->mockTaskDependenciesIfNeeded();

        Log::info('Task metrics precomputation finished successfully.');
    }

    /**
     * Precompute employee task metrics.
     */
    protected function precomputeEmployeeTaskMetrics()
    {
        $now = now();
        $results = DB::table('employees')
            ->leftJoin('tasks', 'employees.id', '=', 'tasks.employee_id')
            ->selectRaw("
                employees.id as employee_id,
                COUNT(tasks.id) as tasks_assigned,
                SUM(CASE WHEN tasks.status IN ('completed', 'late_completed') OR tasks.completed_at IS NOT NULL THEN 1 ELSE 0 END) as tasks_completed,
                SUM(CASE 
                    WHEN (tasks.status IN ('completed', 'late_completed') OR tasks.completed_at IS NOT NULL) AND (tasks.completed_at > tasks.deadline_at OR (tasks.delay_hours IS NOT NULL AND tasks.delay_hours > 0)) THEN 1
                    WHEN tasks.status NOT IN ('completed', 'late_completed') AND tasks.completed_at IS NULL AND tasks.deadline_at IS NOT NULL AND tasks.deadline_at < ? THEN 1
                    ELSE 0 
                END) as tasks_delayed,
                AVG(CASE 
                    WHEN tasks.status IN ('completed', 'late_completed') OR tasks.completed_at IS NOT NULL THEN
                        COALESCE(tasks.actual_hours, TIMESTAMPDIFF(HOUR, tasks.assigned_at, COALESCE(tasks.completed_at, tasks.updated_at)))
                    ELSE NULL 
                END) as avg_completion_time_hours
            ", [$now])
            ->groupBy('employees.id')
            ->get();

        $upsertData = [];
        foreach ($results as $row) {
            $assigned = (int)$row->tasks_assigned;
            $completed = (int)$row->tasks_completed;
            $delayed = (int)$row->tasks_delayed;
            $avgTime = $row->avg_completion_time_hours !== null ? (float)$row->avg_completion_time_hours : 0.00;

            if ($assigned > 0) {
                $completionRate = ($completed / $assigned) * 100;
                $delayPenalty = ($delayed / $assigned) * 100;
            } else {
                $completionRate = 0.00;
                $delayPenalty = 0.00;
            }

            $prodScore = ($completionRate * 0.7) + (max(0.00, 100.00 - $delayPenalty) * 0.3);

            if ($prodScore >= 90) {
                $category = 'Excellent';
            } elseif ($prodScore >= 75) {
                $category = 'Good';
            } elseif ($prodScore >= 60) {
                $category = 'Average';
            } else {
                $category = 'Needs Improvement';
            }

            $upsertData[] = [
                'employee_id' => $row->employee_id,
                'tasks_assigned' => $assigned,
                'tasks_completed' => $completed,
                'tasks_delayed' => $delayed,
                'completion_rate' => round($completionRate, 2),
                'avg_completion_time_hours' => round($avgTime, 2),
                'productivity_score' => round($prodScore, 2),
                'productivity_category' => $category,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Chunk and upsert to avoid MySQL placeholder limit issues
        foreach (array_chunk($upsertData, 500) as $chunk) {
            EmployeeTaskMetric::upsert(
                $chunk,
                ['employee_id'],
                ['tasks_assigned', 'tasks_completed', 'tasks_delayed', 'completion_rate', 'avg_completion_time_hours', 'productivity_score', 'productivity_category', 'updated_at']
            );
        }
    }

    /**
     * Precompute today's trends.
     */
    protected function precomputeTrends()
    {
        $now = now();
        $date = $now->toDateString();

        $metrics = EmployeeTaskMetric::selectRaw("
            SUM(tasks_assigned) as total_assigned,
            SUM(tasks_completed) as total_completed,
            SUM(tasks_delayed) as total_delayed,
            AVG(avg_completion_time_hours) as avg_time
        ")->first();

        TaskMetricsTrend::updateOrCreate(
            ['date' => $date],
            [
                'tasks_assigned' => (int)($metrics->total_assigned ?? 0),
                'tasks_completed' => (int)($metrics->total_completed ?? 0),
                'tasks_delayed' => (int)($metrics->total_delayed ?? 0),
                'avg_completion_time_hours' => round($metrics->avg_time ?? 0.00, 2),
            ]
        );
    }

    /**
     * Mock historical trend data if none exists.
     */
    protected function mockHistoricalTrendsIfNeeded()
    {
        if (TaskMetricsTrend::count() > 1) {
            return;
        }

        Log::info('Mocking historical task trends...');
        $now = now();

        $todayTrend = TaskMetricsTrend::where('date', $now->toDateString())->first();
        $baseAssigned = $todayTrend ? $todayTrend->tasks_assigned : 150000;
        $baseCompleted = $todayTrend ? $todayTrend->tasks_completed : 120000;
        $baseDelayed = $todayTrend ? $todayTrend->tasks_delayed : 30000;
        $baseTime = $todayTrend ? $todayTrend->avg_completion_time_hours : 24.5;

        for ($i = 29; $i >= 1; $i--) {
            $date = (clone $now)->subDays($i)->toDateString();
            
            // Generate some logical, smooth fluctuating numbers
            $assigned = $baseAssigned + rand(-5000, 5000);
            $completed = (int)($assigned * (0.75 + (rand(-5, 5) / 100)));
            $delayed = (int)($assigned * (0.15 + (rand(-3, 3) / 100)));
            $time = max(5.00, $baseTime + (rand(-30, 30) / 10));

            TaskMetricsTrend::firstOrCreate(
                ['date' => $date],
                [
                    'tasks_assigned' => $assigned,
                    'tasks_completed' => $completed,
                    'tasks_delayed' => $delayed,
                    'avg_completion_time_hours' => round($time, 2),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    /**
     * Mock some task dependencies if there are none, to show blocked tasks.
     */
    protected function mockTaskDependenciesIfNeeded()
    {
        $hasDependencies = Task::whereNotNull('depends_on_task_id')->exists();
        if ($hasDependencies) {
            return;
        }

        Log::info('Mocking task dependencies and blocked statuses...');
        // We will take a sample of tasks and assign dependencies
        $uncompletedTasks = Task::where('status', '!=', 'completed')->take(100)->get();
        if ($uncompletedTasks->count() < 2) {
            return;
        }

        // Make every second task depend on the previous task
        for ($i = 1; $i < $uncompletedTasks->count(); $i += 2) {
            $task = $uncompletedTasks[$i];
            $dependency = $uncompletedTasks[$i - 1];
            
            $task->update([
                'depends_on_task_id' => $dependency->id,
                'status' => 'blocked' // Set some to blocked status
            ]);
        }
    }
}
