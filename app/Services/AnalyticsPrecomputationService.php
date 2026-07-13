<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Task;
use App\Models\DashboardStatistic;
use App\Models\EmployeePerformanceMetric;
use App\Models\TeamPerformanceMetric;
use App\Models\EmployeePeriodMetric;
use App\Models\EmployeeDailyMetric;
use App\Services\PerformanceAnalyticsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnalyticsPrecomputationService
{
    protected TeamAnalyticsService $teamAnalyticsService;

    public function __construct(TeamAnalyticsService $teamAnalyticsService)
    {
        $this->teamAnalyticsService = $teamAnalyticsService;
    }

    /**
     * Run all precomputations.
     */
    public function runAll()
    {
        Log::info('Starting analytics precomputation...');

        $this->precomputeDashboardStatistics();
        $this->precomputeEmployeeMetrics();
        $this->precomputeTeamMetrics();
        $this->precomputeDailyMetrics();
        $this->precomputeEmployeePeriodMetrics();

        Log::info('Analytics precomputation finished successfully.');
    }

    /**
     * Precompute dashboard statistics.
     */
    public function precomputeDashboardStatistics()
    {
        Log::info('Precomputing dashboard statistics...');

        $totalEmployees = Employee::count();
        $totalTasks = Task::count();
        $completedTasks = Task::where('status', 'completed')->count();
        $pendingTasks = Task::where('status', 'pending')->count();
        $inProgressTasks = Task::where('status', 'in_progress')->count();
        
        $lateTasks = Task::where('deadline_at', '<', now())
            ->where('status', '!=', 'completed')
            ->count();

        $recentTasks = Task::with('employee')
            ->latest()
            ->take(5)
            ->get()
            ->map(function($task) {
                return [
                    'title' => $task->title,
                    'created_at_human' => $task->created_at ? $task->created_at->diffForHumans() : 'Unknown',
                    'employee_initial' => substr($task->employee->name ?? '?', 0, 1),
                    'employee_name' => $task->employee->name ?? 'Unknown',
                    'status' => $task->status,
                    'status_formatted' => str_replace('_', ' ', ucfirst($task->status))
                ];
            })
            ->toArray();

        DashboardStatistic::updateOrCreate(
            ['id' => 1],
            [
                'total_employees' => $totalEmployees,
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'pending_tasks' => $pendingTasks,
                'in_progress_tasks' => $inProgressTasks,
                'late_tasks' => $lateTasks,
                'recent_tasks' => $recentTasks,
            ]
        );

        Log::info('Dashboard statistics precomputed.');
    }

    /**
     * Precompute employee metrics.
     */
    public function precomputeEmployeeMetrics()
    {
        Log::info('Precomputing employee metrics...');

        Employee::with(['tasks', 'attendances', 'github_commits', 'meeting_notes'])->chunk(200, function ($employees) {
            foreach ($employees as $employee) {
                try {
                    $score = PerformanceAnalyticsService::calculateEmployeeScore($employee);

                    EmployeePerformanceMetric::updateOrCreate(
                        ['employee_id' => $employee->id],
                        [
                            'task_completion_rate' => $score['task_completion_rate'],
                            'on_time_completion_rate' => $score['on_time_completion_rate'],
                            'attendance_score' => $score['attendance_score'],
                            'git_contribution_score' => $score['git_contribution_score'],
                            'final_score' => $score['final_leadership_score'],
                            'total_tasks' => $score['total_tasks'],
                            'completed_tasks' => $score['completed_tasks'],
                            'late_tasks' => $score['late_tasks'],
                            'total_attendance_records' => $score['total_attendance_records'],
                            'git_commit_count' => $score['git_commit_count'],
                            'recent_commits' => $score['recent_commits'],
                            'commit_chart_data' => $score['commit_chart_data'],
                        ]
                    );
                } catch (\Exception $e) {
                    Log::error("Failed to precompute metrics for employee {$employee->id}: " . $e->getMessage());
                }
            }
        });

        Log::info('Employee metrics precomputed.');
    }

    /**
     * Precompute team metrics for all periods.
     */
    public function precomputeTeamMetrics()
    {
        Log::info('Precomputing team metrics...');

        $teams = $this->teamAnalyticsService->getAllTeams();
        $periods = ['7', '30', 'all'];

        foreach ($periods as $period) {
            Log::info("Clearing cache for period: {$period}");
            // Clear existing caches to ensure we query raw tables
            Cache::forget("team_leaderboard_{$period}");
            Cache::forget("team_bulk_chart_data_{$period}");
            Cache::forget("team_dashboard_kpis_{$period}");

            Log::info("Fetching bulk chart data and leaderboard for period: {$period}");
            $chartData = collect($this->teamAnalyticsService->getBulkChartData($period))->keyBy('team');
            $leaderboard = collect($this->teamAnalyticsService->getLeaderboard($period))->keyBy('team_name');

            foreach ($teams as $team) {
                $teamChart = $chartData->get($team);
                $teamLeaderboard = $leaderboard->get($team);

                TeamPerformanceMetric::updateOrCreate(
                    ['period' => $period, 'team_name' => $team],
                    [
                        'attendance_score' => $teamChart['attendance_score'] ?? 0.0,
                        'task_completion_score' => $teamChart['task_completion'] ?? 0.0,
                        'leadership_score' => $teamChart['leadership_score'] ?? 0.0,
                        'git_contribution_score' => $teamChart['git_contribution'] ?? 0.0,
                        'top_performers' => $teamLeaderboard['top_performers'] ?? [],
                    ]
                );
            }
        }

        Log::info('Team metrics precomputed.');
    }

    /**
     * Precompute daily snapshots for historical leaderboards.
     */
    public function precomputeDailyMetrics()
    {
        Log::info('Precomputing daily metrics...');
        
        // Compute for today and yesterday to capture late or rolling updates
        $dates = [now()->toDateString(), now()->subDay()->toDateString()];

        foreach ($dates as $date) {
            $this->calculateAndStoreDailyMetricForDate($date);
        }

        Log::info('Daily metrics precomputed.');
    }

    public function calculateAndStoreDailyMetricForDate(string $date)
    {
        Employee::with([
            'tasks' => function ($q) use ($date) {
                $q->whereDate('created_at', $date);
            },
            'attendances' => function ($q) use ($date) {
                $q->whereDate('date', $date);
            },
            'github_commits' => function ($q) use ($date) {
                $q->whereDate('commit_date', $date);
            },
            'projects'
        ])->chunk(200, function ($employees) use ($date) {
            foreach ($employees as $employee) {
                // Reuse existing robust formula business logic
                $scoreData = PerformanceAnalyticsService::calculateEmployeeScore($employee);
                
                // Derive raw component fields used in formula
                $completed = $scoreData['completed_tasks'];
                $late = $scoreData['late_tasks'];
                $onTime = max(0, $completed - $late);
                
                // Reverse-engineer present days from attendance score percent
                $presentDays = 0;
                if ($scoreData['total_attendance_records'] > 0) {
                    $presentDays = (int) round(($scoreData['attendance_score'] * $scoreData['total_attendance_records']) / 100);
                }

                \App\Models\EmployeeDailyMetric::updateOrCreate(
                    ['employee_id' => $employee->id, 'date' => $date],
                    [
                        'total_tasks' => $scoreData['total_tasks'],
                        'completed_tasks' => $completed,
                        'on_time_tasks' => $onTime,
                        'total_attendances' => $scoreData['total_attendance_records'],
                        'present_days' => $presentDays,
                        'git_commit_count' => $scoreData['git_commit_count'],
                        'project_count' => $employee->projects->count(),
                        'task_completion_score' => $scoreData['task_completion_rate'],
                        'task_quality_score' => $scoreData['on_time_completion_rate'],
                        'attendance_score' => $scoreData['attendance_score'],
                        'gitlab_score' => $scoreData['git_contribution_score'],
                        'overall_score' => $scoreData['final_leadership_score'],
                    ]
                );
            }
        });
    }

    /**
     * Precompute employee metrics for 7_days, 30_days, and all_time periods.
     */
    public function precomputeEmployeePeriodMetrics()
    {
        Log::info('Precomputing employee period metrics...');

        $now = now();
        $periods = [
            '7_days' => $now->copy()->subDays(6)->startOfDay(),
            '30_days' => $now->copy()->subDays(29)->startOfDay(),
            'all_time' => null,
        ];

        foreach ($periods as $periodName => $startDate) {
            $employees = Employee::all();

            foreach ($employees as $employee) {
                if ($periodName === 'all_time') {
                    $metrics = EmployeePerformanceMetric::where('employee_id', $employee->id)->first();
                    if (!$metrics) continue;

                    EmployeePeriodMetric::updateOrCreate(
                        ['employee_id' => $employee->id, 'period' => $periodName],
                        [
                            'total_tasks' => $metrics->total_tasks ?? 0,
                            'completed_tasks' => $metrics->completed_tasks ?? 0,
                            'on_time_tasks' => max(0, ($metrics->completed_tasks ?? 0) - ($metrics->late_tasks ?? 0)),
                            'total_attendances' => $metrics->total_attendance_records ?? 0,
                            'present_days' => (int)round((($metrics->attendance_score ?? 0) * ($metrics->total_attendance_records ?? 0)) / 100),
                            'git_commit_count' => $metrics->git_commit_count ?? 0,
                            'project_count' => DB::table('project_employee')->where('employee_id', $employee->id)->count(),
                            'task_completion_score' => $metrics->task_completion_rate ?? 0,
                            'task_quality_score' => $metrics->on_time_completion_rate ?? 0,
                            'attendance_score' => $metrics->attendance_score ?? 0,
                            'gitlab_score' => $metrics->git_contribution_score ?? 0,
                            'overall_score' => $metrics->final_score ?? 0,
                        ]
                    );
                } else {
                    $dailySum = EmployeeDailyMetric::where('employee_id', $employee->id)
                        ->where('date', '>=', $startDate->toDateString())
                        ->selectRaw('
                            SUM(total_tasks) as total_tasks,
                            SUM(completed_tasks) as completed_tasks,
                            SUM(on_time_tasks) as on_time_tasks,
                            SUM(total_attendances) as total_attendances,
                            SUM(present_days) as present_days,
                            SUM(git_commit_count) as git_commit_count,
                            MAX(project_count) as project_count
                        ')->first();

                    $taskTotal = $dailySum->total_tasks ?? 0;
                    $taskCompleted = $dailySum->completed_tasks ?? 0;
                    $taskOnTime = $dailySum->on_time_tasks ?? 0;

                    $taskScore = $taskTotal > 0 ? ($taskCompleted / $taskTotal) * 100 : 0;
                    $qualityScore = $taskCompleted > 0 ? ($taskOnTime / $taskCompleted) * 100 : 0;

                    $attTotal = $dailySum->total_attendances ?? 0;
                    $attPresent = $dailySum->present_days ?? 0;
                    $attScore = $attTotal > 0 ? ($attPresent / $attTotal) * 100 : 0;

                    $gitScore = $dailySum->git_commit_count ?? 0;
                    $projScore = ($dailySum->project_count ?? 0) * 10;

                    $overall = ($taskScore * 0.4) + ($qualityScore * 0.2) + ($attScore * 0.2) + (min($gitScore, 100) * 0.1) + (min($projScore, 100) * 0.1);

                    EmployeePeriodMetric::updateOrCreate(
                        ['employee_id' => $employee->id, 'period' => $periodName],
                        [
                            'total_tasks' => $taskTotal,
                            'completed_tasks' => $taskCompleted,
                            'on_time_tasks' => $taskOnTime,
                            'total_attendances' => $attTotal,
                            'present_days' => $attPresent,
                            'git_commit_count' => $gitScore,
                            'project_count' => $dailySum->project_count ?? 0,
                            'task_completion_score' => $taskScore,
                            'task_quality_score' => $qualityScore,
                            'attendance_score' => $attScore,
                            'gitlab_score' => $gitScore,
                            'overall_score' => $overall,
                        ]
                    );
                }
            }
        }

        Log::info('Employee period metrics precomputed.');
    }
}
