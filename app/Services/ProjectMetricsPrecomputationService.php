<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectMetric;
use App\Models\ProjectMetricsTrend;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectMetricsPrecomputationService
{
    public function calculateAllMetrics()
    {
        Log::info("Starting project metrics precomputation...");
        $startTime = microtime(true);

        $this->calculateProjectLevelMetrics();
        $this->calculateTrendMetrics();

        $duration = microtime(true) - $startTime;
        Log::info("Project metrics precomputation completed in {$duration} seconds.");
    }

    private function calculateProjectLevelMetrics()
    {
        $now = Carbon::now();

        // Calculate metrics using raw SQL for performance since there can be many projects and tasks
        $metricsQuery = DB::table('projects as p')
            ->leftJoin('tasks as t', 'p.id', '=', 't.project_id')
            ->select(
                'p.id as project_id',
                'p.budget_hours',
                DB::raw('COUNT(t.id) as total_tasks'),
                DB::raw("SUM(CASE WHEN t.status IN ('completed', 'late_completed') THEN 1 ELSE 0 END) as completed_tasks"),
                DB::raw("SUM(CASE WHEN t.deadline_at < '{$now}' AND t.status NOT IN ('completed', 'late_completed') THEN 1 ELSE 0 END) as overdue_tasks"),
                DB::raw('COALESCE(SUM(t.estimated_hours), 0) as total_estimated_hours'),
                DB::raw('COALESCE(SUM(t.actual_hours), 0) as total_actual_hours')
            )
            ->groupBy('p.id', 'p.budget_hours');

        $metricsData = [];

        foreach ($metricsQuery->cursor() as $row) {
            $completionPercentage = 0;
            if ($row->total_tasks > 0) {
                $completionPercentage = ($row->completed_tasks / $row->total_tasks) * 100;
            }

            // Calculate health score
            $healthScore = 100.0;
            
            // Deduct for overdue tasks (e.g., 10 points for every 10% of tasks overdue)
            if ($row->total_tasks > 0) {
                $overdueRatio = $row->overdue_tasks / $row->total_tasks;
                $healthScore -= ($overdueRatio * 100); 
            }

            // Deduct for budget overrun
            if (!is_null($row->budget_hours) && $row->budget_hours > 0) {
                if ($row->total_actual_hours > $row->budget_hours) {
                    $overrunRatio = ($row->total_actual_hours - $row->budget_hours) / $row->budget_hours;
                    $healthScore -= ($overrunRatio * 100);
                }
            }

            // Ensure bounds
            $healthScore = max(0, min(100, $healthScore));

            // Determine status
            $healthStatus = 'On Track';
            if ($healthScore < 50) {
                $healthStatus = 'Critical';
            } elseif ($healthScore < 80) {
                $healthStatus = 'At Risk';
            }

            $metricsData[] = [
                'project_id' => $row->project_id,
                'total_tasks' => $row->total_tasks,
                'completed_tasks' => $row->completed_tasks,
                'overdue_tasks' => $row->overdue_tasks,
                'total_estimated_hours' => $row->total_estimated_hours,
                'total_actual_hours' => $row->total_actual_hours,
                'completion_percentage' => round($completionPercentage, 2),
                'health_score' => round($healthScore, 2),
                'health_status' => $healthStatus,
                'updated_at' => $now,
                'created_at' => $now,
            ];
        }

        // Upsert in chunks to handle thousands of projects
        $chunks = array_chunk($metricsData, 500);
        foreach ($chunks as $chunk) {
            ProjectMetric::upsert(
                $chunk,
                ['project_id'],
                [
                    'total_tasks', 
                    'completed_tasks', 
                    'overdue_tasks', 
                    'total_estimated_hours', 
                    'total_actual_hours', 
                    'completion_percentage', 
                    'health_score', 
                    'health_status', 
                    'updated_at'
                ]
            );
        }
    }

    private function calculateTrendMetrics()
    {
        $today = Carbon::today()->toDateString();
        $now = Carbon::now();

        // Active projects (not archived and not completed status)
        $activeProjects = Project::where('is_archived', false)
            ->where('status', '!=', 'completed')
            ->count();

        // Get statuses from project_metrics for active projects
        $metrics = DB::table('project_metrics as pm')
            ->join('projects as p', 'pm.project_id', '=', 'p.id')
            ->where('p.is_archived', false)
            ->where('p.status', '!=', 'completed')
            ->select(
                DB::raw("SUM(CASE WHEN pm.health_status = 'At Risk' THEN 1 ELSE 0 END) as at_risk"),
                DB::raw("SUM(CASE WHEN pm.health_status = 'Critical' THEN 1 ELSE 0 END) as critical"),
                DB::raw("AVG(pm.completion_percentage) as avg_completion")
            )
            ->first();

        ProjectMetricsTrend::updateOrCreate(
            ['date' => $today],
            [
                'active_projects' => $activeProjects,
                'at_risk_projects' => $metrics->at_risk ?? 0,
                'critical_projects' => $metrics->critical ?? 0,
                'avg_completion_percentage' => $metrics->avg_completion ?? 0,
            ]
        );
    }
}
