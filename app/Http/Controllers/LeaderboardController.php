<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Team;
use App\Models\EmployeePeriodMetric;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeaderboardController extends Controller
{
    private function getDateRange($filter, $startDate = null, $endDate = null)
    {
        $now = Carbon::now();
        switch ($filter) {
            case 'daily':
                return [$now->copy()->startOfDay(), $now->copy()->endOfDay()];
            case 'weekly':
                return [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()];
            case 'monthly':
                return [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()];
            case 'quarterly':
                return [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter()];
            case 'yearly':
                return [$now->copy()->startOfYear(), $now->copy()->endOfYear()];
            case 'custom':
                if ($startDate && $endDate) {
                    return [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()];
                }
                break;
        }
        return [null, null]; // all time
    }

    // --- INDIVIDUAL LEADERBOARD ---
    public function individual()
    {
        $teams = Team::all();
        return view('leaderboard.individual', compact('teams'));
    }

    public function getIndividualData(Request $request)
    {
        if ($request->ajax()) {
            $filter = $request->get('date_filter', 'all_time');
            $team = $request->get('team', 'all');
            [$start, $end] = $this->getDateRange($filter, $request->get('start_date'), $request->get('end_date'));

            // Map leaderboard filter names to period_metrics periods
            $periodMap = [
                'all_time' => 'all_time',
                'weekly'   => '7_days',
                'monthly'  => '30_days',
            ];
            $period = $periodMap[$filter] ?? null;

            if ($period) {
                // Fast path: use precomputed employee_period_metrics
                $query = DB::table('employees')
                    ->leftJoin('employee_period_metrics as epm', function($join) use ($period) {
                        $join->on('employees.id', '=', 'epm.employee_id')
                             ->where('epm.period', '=', $period);
                    })
                    ->select(
                        'employees.id', 'employees.name', 'employees.team',
                        DB::raw('COALESCE(epm.task_completion_score, 0) as task_completion_score'),
                        DB::raw('COALESCE(epm.task_quality_score, 0) as task_quality_score'),
                        DB::raw('COALESCE(epm.attendance_score, 0) as attendance_score'),
                        DB::raw('COALESCE(epm.gitlab_score, 0) as gitlab_score'),
                        DB::raw('COALESCE(epm.project_count, 0) * 10 as project_score'),
                        DB::raw('COALESCE(epm.overall_score, 0) as overall_score')
                    );
            } else {
                // Slow path: custom range — aggregate from employee_daily_metrics
                $metricsSubquery = DB::table('employee_daily_metrics')
                    ->select('employee_id')
                    ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                    ->selectRaw('
                        SUM(total_tasks) as total_tasks,
                        SUM(completed_tasks) as completed_tasks,
                        SUM(on_time_tasks) as on_time_tasks,
                        SUM(total_attendances) as total_attendances,
                        SUM(present_days) as present_days,
                        SUM(git_commit_count) as git_commit_count,
                        MAX(project_count) as project_count
                    ')
                    ->groupBy('employee_id');

                $innerQuery = DB::table('employees')
                    ->select('employees.id', 'employees.name', 'employees.team')
                    ->leftJoinSub($metricsSubquery, 'm', function ($join) {
                        $join->on('employees.id', '=', 'm.employee_id');
                    })
                    ->selectRaw('
                        COALESCE((m.completed_tasks / NULLIF(m.total_tasks, 0)) * 100, 0) as task_completion_score,
                        COALESCE((m.on_time_tasks / NULLIF(m.completed_tasks, 0)) * 100, 0) as task_quality_score,
                        COALESCE((m.present_days / NULLIF(m.total_attendances, 0)) * 100, 0) as attendance_score,
                        COALESCE(m.git_commit_count, 0) as gitlab_score,
                        COALESCE(m.project_count, 0) * 10 as project_score
                    ');

                $query = DB::table(DB::raw("({$innerQuery->toSql()}) as sub"))
                    ->mergeBindings($innerQuery)
                    ->select('*', DB::raw('(task_completion_score * 0.4 + task_quality_score * 0.2 + attendance_score * 0.2 + LEAST(gitlab_score, 100) * 0.1 + LEAST(project_score, 100) * 0.1) as overall_score'));
            }

            if ($team !== 'all') {
                $query->where('team', $team);
            }

            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->editColumn('overall_score', function($row) {
                    return number_format((float)$row->overall_score, 1);
                })
                ->addColumn('rank', function($row) {
                    return ''; // Filled by client side indexing
                })
                ->make(true);
        }
    }

    public function getEmployeeMetrics(Employee $employee, Request $request)
    {
        $filter = $request->get('date_filter', 'all_time');
        [$start, $end] = $this->getDateRange($filter, $request->get('start_date'), $request->get('end_date'));

        // Map leaderboard filter names to period_metrics periods
        $periodMap = [
            'all_time' => 'all_time',
            'weekly'   => '7_days',
            'monthly'  => '30_days',
        ];
        $period = $periodMap[$filter] ?? null;

        if ($period) {
            // Fast path: pull from precomputed employee_period_metrics
            $metrics = EmployeePeriodMetric::where('employee_id', $employee->id)
                ->where('period', $period)
                ->first();

            $taskScore    = $metrics->task_completion_score ?? 0;
            $qualityScore = $metrics->task_quality_score ?? 0;
            $attScore     = $metrics->attendance_score ?? 0;
            $gitScore     = $metrics->gitlab_score ?? 0;
            $projScore    = ($metrics->project_count ?? 0) * 10;
            $overall      = $metrics->overall_score ?? 0;
        } else {
            // Slow path: custom range
            $metrics = DB::table('employee_daily_metrics')
                ->where('employee_id', $employee->id)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->selectRaw('
                    SUM(total_tasks) as total_tasks,
                    SUM(completed_tasks) as completed_tasks,
                    SUM(on_time_tasks) as on_time_tasks,
                    SUM(total_attendances) as total_attendances,
                    SUM(present_days) as present_days,
                    SUM(git_commit_count) as git_commit_count,
                    MAX(project_count) as project_count
                ')->first();

            $taskTotal = $metrics->total_tasks ?? 0;
            $taskCompleted = $metrics->completed_tasks ?? 0;
            $taskOnTime = $metrics->on_time_tasks ?? 0;

            $taskScore    = $taskTotal > 0 ? ($taskCompleted / $taskTotal) * 100 : 0;
            $qualityScore = $taskCompleted > 0 ? ($taskOnTime / $taskCompleted) * 100 : 0;

            $attTotal  = $metrics->total_attendances ?? 0;
            $attPresent = $metrics->present_days ?? 0;
            $attScore  = $attTotal > 0 ? ($attPresent / $attTotal) * 100 : 0;

            $gitScore  = $metrics->git_commit_count ?? 0;
            $projScore = ($metrics->project_count ?? 0) * 10;
            $overall = ($taskScore * 0.4) + ($qualityScore * 0.2) + ($attScore * 0.2) + (min($gitScore, 100) * 0.1) + (min($projScore, 100) * 0.1);
        }

        return response()->json([
            'name' => $employee->name,
            'team' => $employee->team,
            'task_completion' => round($taskScore, 1),
            'task_quality'    => round($qualityScore, 1),
            'attendance'      => round($attScore, 1),
            'gitlab'          => $gitScore,
            'project'         => $projScore,
            'overall'         => round($overall, 1)
        ]);
    }

    // --- TEAM LEADERBOARD ---
    public function team()
    {
        return view('leaderboard.team');
    }

    public function getTeamData(Request $request)
    {
        if ($request->ajax()) {
            $filter = $request->get('date_filter', 'all_time');
            // 'team_performance_metrics' has 'period' column. We map 'all_time' to 'all'
            $period = $filter === 'all_time' ? 'all' : '30'; // fallback to 30 days if not all

            $baseQuery = DB::table('team_performance_metrics')
                ->where('period', $period)
                ->select(
                    'team_name',
                    'task_completion_score as productivity',
                    'leadership_score as delivery',
                    'attendance_score',
                    DB::raw('(task_completion_score * 0.4 + leadership_score * 0.3 + attendance_score * 0.3) as overall_score')
                );

            $query = DB::table(DB::raw("({$baseQuery->toSql()}) as sub"))
                ->mergeBindings($baseQuery)
                ->select('*');

            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->editColumn('overall_score', function($row) {
                    return number_format((float)$row->overall_score, 1);
                })
                ->make(true);
        }
    }

    public function teamDetails($team_name)
    {
        $team = Team::where('name', $team_name)->firstOrFail();
        return view('leaderboard.team_details', compact('team'));
    }

    // --- ORGANIZATION LEADERBOARD ---
    public function organization()
    {
        return view('leaderboard.organization');
    }

    public function getOrganizationData(Request $request)
    {
        $filter = $request->get('date_filter', 'all_time');
        
        $topEmployees = DB::table('employee_performance_metrics')
            ->join('employees', 'employees.id', '=', 'employee_performance_metrics.employee_id')
            ->select('employees.name', 'employees.team', 'employee_performance_metrics.final_score as score')
            ->orderByDesc('score')
            ->limit(5)
            ->get();

        $topTeams = DB::table('team_performance_metrics')
            ->where('period', 'all')
            ->select('team_name as name', DB::raw('(task_completion_score * 0.4 + leadership_score * 0.3 + attendance_score * 0.3) as score'))
            ->orderByDesc('score')
            ->limit(5)
            ->get();

        $topContributors = DB::table('employee_performance_metrics')
            ->join('employees', 'employees.id', '=', 'employee_performance_metrics.employee_id')
            ->select('employees.name', 'employee_performance_metrics.git_contribution_score as score')
            ->orderByDesc('score')
            ->limit(5)
            ->get();

        return response()->json([
            'top_employees' => $topEmployees,
            'top_teams' => $topTeams,
            'top_contributors' => $topContributors
        ]);
    }
}
