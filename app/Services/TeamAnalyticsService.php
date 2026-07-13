<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Performance_report;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class TeamAnalyticsService
{
    public function getAllTeams(): array
    {
        return Employee::whereNotNull('team')
            ->distinct()
            ->pluck('team')
            ->toArray();
    }

    public function calculateTeamEfficiency(string $team): float
    {
        $employeeIds = Employee::where('team', $team)->pluck('id');
        if ($employeeIds->isEmpty()) {
            return 0.0;
        }
        $avgScore = DB::table('employee_performance_metrics')
            ->whereIn('employee_id', $employeeIds)
            ->avg('final_score');
        return round($avgScore ?? 0.0, 2);
    }

    public function calculateAverageAttendance(string $team): float
    {
        $employeeIds = Employee::where('team', $team)->pluck('id');
        if ($employeeIds->isEmpty()) return 0.0;

        $totalAttendances = \App\Models\Attendance::whereIn('employee_id', $employeeIds)->count();
        if ($totalAttendances === 0) return 0.0;

        $excellentAttendances = \App\Models\Attendance::whereIn('employee_id', $employeeIds)
            ->where('leave_flag', false)
            ->where('late_flag', false)
            ->count();

        return round(($excellentAttendances / $totalAttendances) * 100, 2);
    }

    public function calculateAverageTaskCompletion(string $team): float
    {
        $employeeIds = Employee::where('team', $team)->pluck('id');
        if ($employeeIds->isEmpty()) return 0.0;

        $totalTasks = \App\Models\Task::whereIn('employee_id', $employeeIds)->count();
        if ($totalTasks === 0) return 0.0;

        $completedTasks = \App\Models\Task::whereIn('employee_id', $employeeIds)
            ->where(function($q) {
                $q->whereNotNull('completed_at')
                  ->orWhereIn('status', ['completed', 'late_completed']);
            })->count();

        return round(($completedTasks / $totalTasks) * 100, 2);
    }

    public function getTopPerformer(string $team): ?Employee
    {
        $employeeIds = Employee::where('team', $team)->pluck('id');
        if ($employeeIds->isEmpty()) return null;

        $topMetric = DB::table('employee_performance_metrics')
            ->whereIn('employee_id', $employeeIds)
            ->orderByDesc('final_score')
            ->first();

        if ($topMetric) {
            return Employee::find($topMetric->employee_id);
        }

        return null;
    }

    public function getLeaderboard(int|string $period = 7): array
    {
        return Cache::remember("team_leaderboard_{$period}", 300, function () use ($period) {
            $employees = Employee::whereNotNull('team')->get();
            $employeeScores = [];

            if ($period === 'all') {
                // Fetch precomputed all-time metrics
                $metrics = DB::table('employee_performance_metrics')
                    ->pluck('final_score', 'employee_id');

                foreach ($employees as $emp) {
                    $employeeScores[] = [
                        'id' => $emp->id,
                        'name' => $emp->name,
                        'team' => $emp->team,
                        'score' => round($metrics->get($emp->id, 0.0), 1),
                    ];
                }
            } else {
                // Custom period: aggregate daily metrics
                $start = now()->subDays((int)$period)->startOfDay();
                $end = now()->endOfDay();

                $metricsGroup = DB::table('employee_daily_metrics')
                    ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                    ->get()
                    ->groupBy('employee_id');

                foreach ($employees as $emp) {
                    $mList = $metricsGroup->get($emp->id);
                    if ($mList && $mList->isNotEmpty()) {
                        $total_tasks = $mList->sum('total_tasks');
                        $completed_tasks = $mList->sum('completed_tasks');
                        $on_time_tasks = $mList->sum('on_time_tasks');
                        $total_attendances = $mList->sum('total_attendances');
                        $present_days = $mList->sum('present_days');
                        $git_commit_count = $mList->sum('git_commit_count');
                        $project_count = $mList->max('project_count');

                        $task_completion_score = $total_tasks > 0 ? ($completed_tasks / $total_tasks) * 100 : 0;
                        $task_quality_score = $completed_tasks > 0 ? ($on_time_tasks / $completed_tasks) * 100 : 0;
                        $attendance_score = $total_attendances > 0 ? ($present_days / $total_attendances) * 100 : 0;
                        $gitlab_score = $git_commit_count;
                        $project_score = $project_count * 10;

                        $score = ($task_completion_score * 0.4) + ($task_quality_score * 0.2) + ($attendance_score * 0.2) + (min($gitlab_score, 100) * 0.1) + (min($project_score, 100) * 0.1);
                    } else {
                        $score = 0.0;
                    }

                    $employeeScores[] = [
                        'id' => $emp->id,
                        'name' => $emp->name,
                        'team' => $emp->team,
                        'score' => round($score, 1),
                    ];
                }
            }

            $efficiencies = collect($employeeScores)
                ->groupBy('team')
                ->map(function ($members) {
                    $count = $members->count();
                    if ($count === 0) return 0.0;
                    return round($members->avg('score'), 2);
                });

            $topPerformersByTeam = collect($employeeScores)
                ->groupBy('team')
                ->map(function ($members) {
                    return $members->sortByDesc('score')->take(3)->values();
                });

            $teams = collect($this->getAllTeams());
            $leaderboard = [];

            foreach ($teams as $team) {
                $performers = $topPerformersByTeam->get($team, collect());
                $leaderboard[] = [
                    'team_name' => $team,
                    'efficiency_score' => $efficiencies->get($team, 0.0),
                    'top_performers' => $performers->map(function($p) {
                        return [
                            'id' => $p['id'],
                            'name' => $p['name'],
                            'score' => $p['score'],
                        ];
                    })->toArray(),
                ];
            }

            usort($leaderboard, function ($a, $b) {
                return $b['efficiency_score'] <=> $a['efficiency_score'];
            });

            return $leaderboard;
        });
    }

    public function getBulkChartData(int|string $period = 7): array
    {
        return Cache::remember("team_bulk_chart_data_{$period}", 300, function () use ($period) {
            $attQuery = DB::table('attendances')
                ->join('employees', 'attendances.employee_id', '=', 'employees.id')
                ->selectRaw('
                    employees.team, 
                    COUNT(*) as total, 
                    SUM(CASE WHEN leave_flag = false AND late_flag = false THEN 1 ELSE 0 END) as excellent
                ')
                ->whereNotNull('employees.team');
            if ($period !== 'all') $attQuery->where('attendances.login_time', '>=', now()->subDays((int)$period));
            
            $attendanceScores = $attQuery->groupBy('employees.team')
                ->get()
                ->mapWithKeys(function($item) {
                    return [$item->team => $item->total > 0 ? round(($item->excellent / $item->total) * 100, 2) : 0];
                });

            $taskQuery = DB::table('tasks')
                ->join('employees', 'tasks.employee_id', '=', 'employees.id')
                ->selectRaw("
                    employees.team, 
                    COUNT(*) as total, 
                    SUM(CASE WHEN tasks.completed_at IS NOT NULL OR tasks.status IN ('completed', 'late_completed') THEN 1 ELSE 0 END) as completed
                ")
                ->whereNotNull('employees.team');
            if ($period !== 'all') $taskQuery->where('tasks.created_at', '>=', now()->subDays((int)$period));

            $taskScores = $taskQuery->groupBy('employees.team')
                ->get()
                ->mapWithKeys(function($item) {
                    return [$item->team => $item->total > 0 ? round(($item->completed / $item->total) * 100, 2) : 0];
                });

            $employees = Employee::whereNotNull('team')->get();
            $employeeScores = [];

            if ($period === 'all') {
                $metrics = DB::table('employee_performance_metrics')
                    ->pluck('final_score', 'employee_id');

                foreach ($employees as $emp) {
                    $employeeScores[] = [
                        'team' => $emp->team,
                        'score' => $metrics->get($emp->id, 0.0),
                    ];
                }
            } else {
                $start = now()->subDays((int)$period)->startOfDay();
                $end = now()->endOfDay();

                $metricsGroup = DB::table('employee_daily_metrics')
                    ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                    ->get()
                    ->groupBy('employee_id');

                foreach ($employees as $emp) {
                    $mList = $metricsGroup->get($emp->id);
                    if ($mList && $mList->isNotEmpty()) {
                        $total_tasks = $mList->sum('total_tasks');
                        $completed_tasks = $mList->sum('completed_tasks');
                        $on_time_tasks = $mList->sum('on_time_tasks');
                        $total_attendances = $mList->sum('total_attendances');
                        $present_days = $mList->sum('present_days');
                        $git_commit_count = $mList->sum('git_commit_count');
                        $project_count = $mList->max('project_count');

                        $task_completion_score = $total_tasks > 0 ? ($completed_tasks / $total_tasks) * 100 : 0;
                        $task_quality_score = $completed_tasks > 0 ? ($on_time_tasks / $completed_tasks) * 100 : 0;
                        $attendance_score = $total_attendances > 0 ? ($present_days / $total_attendances) * 100 : 0;
                        $gitlab_score = $git_commit_count;
                        $project_score = $project_count * 10;

                        $score = ($task_completion_score * 0.4) + ($task_quality_score * 0.2) + ($attendance_score * 0.2) + (min($gitlab_score, 100) * 0.1) + (min($project_score, 100) * 0.1);
                    } else {
                        $score = 0.0;
                    }

                    $employeeScores[] = [
                        'team' => $emp->team,
                        'score' => $score,
                    ];
                }
            }

            $leadershipScores = collect($employeeScores)
                ->groupBy('team')
                ->map(function ($members) {
                    return round($members->avg('score'), 2);
                });

            $gitQuery = DB::table('github_commits')
                ->join('employees', 'github_commits.employee_id', '=', 'employees.id')
                ->selectRaw('employees.team, employees.id, count(*) as count')
                ->whereNotNull('employees.team');
            if ($period !== 'all') $gitQuery->where('github_commits.commit_date', '>=', now()->subDays((int)$period));

            $gitCounts = $gitQuery->groupBy('employees.team', 'employees.id')->get();

            $gitTeamScores = [];
            foreach ($gitCounts as $row) {
                $score = min(100.0, max(0.0, ($row->count / 50) * 100));
                if (!isset($gitTeamScores[$row->team])) {
                    $gitTeamScores[$row->team] = ['total_score' => 0, 'emp_count' => 0];
                }
                $gitTeamScores[$row->team]['total_score'] += $score;
                $gitTeamScores[$row->team]['emp_count']++;
            }

            $teamEmployeeCounts = Employee::whereNotNull('team')->selectRaw('team, count(*) as count')->groupBy('team')->pluck('count', 'team');

            $finalGitScores = [];
            foreach ($teamEmployeeCounts as $team => $count) {
                $totalGitScore = $gitTeamScores[$team]['total_score'] ?? 0;
                $finalGitScores[$team] = $count > 0 ? round($totalGitScore / $count, 2) : 0;
            }

            $chartData = [];
            $teams = $this->getAllTeams();
            foreach ($teams as $team) {
                $chartData[] = [
                    'team' => $team,
                    'attendance_score' => $attendanceScores->get($team, 0.0),
                    'task_completion' => $taskScores->get($team, 0.0),
                    'leadership_score' => $leadershipScores->get($team, 0.0),
                    'git_contribution' => $finalGitScores[$team] ?? 0.0,
                ];
            }

            return $chartData;
        });
    }

    public function getDashboardKpis(int|string $period = 7): array
    {
        return Cache::remember("team_dashboard_kpis_{$period}", 300, function () use ($period) {
            $leaderboard = $this->getLeaderboard($period);
            $efficiencies = collect($leaderboard)->pluck('efficiency_score', 'team_name');

            $totalTeams = $efficiencies->count();
            $totalScore = $efficiencies->sum();
            
            $bestTeamName = 'N/A';
            $highestScore = -1;
            foreach($efficiencies as $team => $score) {
                if ($score > $highestScore) {
                    $highestScore = $score;
                    $bestTeamName = $team;
                }
            }

            $averageTeamScore = $totalTeams > 0 ? round($totalScore / $totalTeams, 2) : 0;

            $actualTotalTeams = count($this->getAllTeams());

            return [
                'totalTeams' => $actualTotalTeams,
                'bestTeamName' => $bestTeamName,
                'averageTeamScore' => $averageTeamScore
            ];
        });
    }

    public function calculateAverageGitContribution(string $team): float
    {
        $employeeIds = Employee::where('team', $team)->pluck('id');
        if ($employeeIds->isEmpty()) return 0.0;

        $commitCounts = \App\Models\GithubCommit::whereIn('employee_id', $employeeIds)
            ->selectRaw('employee_id, count(*) as count')
            ->groupBy('employee_id')
            ->pluck('count', 'employee_id');

        $totalGitScore = 0;
        foreach ($employeeIds as $id) {
            $count = $commitCounts->get($id, 0);
            $totalGitScore += min(100.0, max(0.0, ($count / 50) * 100));
        }

        return round($totalGitScore / $employeeIds->count(), 2);
    }

    public function getTeamMeetingNotes(string $team): array
    {
        $employeeIds = Employee::where('team', $team)->pluck('id');
        return \App\Models\MeetingNote::whereIn('employee_id', $employeeIds)
            ->latest('meeting_date')
            ->take(15)
            ->pluck('notes_text')
            ->toArray();
    }

    public function getTeamRecentCommits(string $team): array
    {
        $employeeIds = Employee::where('team', $team)->pluck('id');
        return \App\Models\GithubCommit::whereIn('employee_id', $employeeIds)
            ->latest('commit_date')
            ->take(15)
            ->pluck('commit_message')
            ->toArray();
    }
}
