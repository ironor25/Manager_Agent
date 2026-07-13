<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Skill;
use App\Models\EmployeePeriodMetric;
use Illuminate\Support\Facades\DB;
use App\Traits\DateFilterable;

class EmployeeAnalyticsController extends Controller
{
    use DateFilterable;
    public function index()
    {
        return view('employees.analytics');
    }

    public function getAnalyticsData(Request $request)
    {
        $filter = $request->get('date_filter', 'all_time');
        [$start, $end] = $this->getDateRange($filter, $request->get('start_date'), $request->get('end_date'));

        // Map the date-filter component values to employee_period_metrics period keys
        // The date-filter component sends 'weekly' and 'monthly', not '7_days'/'30_days'
        $periodMap = [
            'all_time' => 'all_time',
            'weekly'   => '7_days',
            'monthly'  => '30_days',
        ];
        $period = $periodMap[$filter] ?? null;

        // KPIs — these don't change per period so keep them outside the branch
        // Use a single query with a conditional COUNT to avoid two full scans
        $empCounts = DB::table('employees')
            ->selectRaw("COUNT(*) as total, SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active")
            ->first();
        $totalEmployees  = $empCounts->total;
        $activeEmployees = $empCounts->active;

        if ($period) {
            // Fast path: single query against precomputed employee_period_metrics
            $aggs = DB::table('employee_period_metrics')
                ->where('period', $period)
                ->selectRaw('
                    AVG(task_completion_score) as avg_task,
                    AVG(attendance_score)      as avg_att,
                    AVG(overall_score)         as avg_overall
                ')
                ->first();

            $avgTaskScore       = $aggs->avg_task ?? 0;
            $avgAttendanceScore = $aggs->avg_att ?? 0;
            $avgWorkload        = $aggs->avg_overall ?? 0;

            $topPerformers = DB::table('employee_period_metrics as epm')
                ->join('employees as e', 'e.id', '=', 'epm.employee_id')
                ->where('epm.period', $period)
                ->select('e.name', 'e.team', 'epm.overall_score')
                ->orderByDesc('epm.overall_score')
                ->limit(5)
                ->get();
        } else {
            // Slow path: custom date range — aggregate from employee_daily_metrics
            $metrics = DB::table('employee_daily_metrics')
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->selectRaw('
                    employee_id,
                    SUM(completed_tasks) / NULLIF(SUM(total_tasks), 0) * 100        as task_score,
                    SUM(present_days)    / NULLIF(SUM(total_attendances), 0) * 100   as att_score,
                    overall_score
                ')
                ->groupBy('employee_id', 'overall_score');

            $aggs = DB::query()->fromSub($metrics, 'm')
                ->selectRaw('AVG(task_score) as avg_task, AVG(att_score) as avg_att, AVG(overall_score) as avg_overall')
                ->first();

            $avgTaskScore       = $aggs->avg_task ?? 0;
            $avgAttendanceScore = $aggs->avg_att ?? 0;
            $avgWorkload        = $aggs->avg_overall ?? 0;

            $topPerformers = DB::table('employees as e')
                ->joinSub($metrics, 'm', 'e.id', '=', 'm.employee_id')
                ->select('e.name', 'e.team', DB::raw('(COALESCE(m.task_score, 0) * 0.6 + COALESCE(m.att_score, 0) * 0.4) as overall_score'))
                ->orderByDesc('overall_score')
                ->limit(5)
                ->get();
        }

        // Performance Trend Line Chart Data
        $trendQuery = DB::table('employee_daily_metrics')
            ->select('date', DB::raw('AVG(overall_score) as avg_score'));
            
        if ($filter === 'all_time') {
            $trendQuery->where('date', '>=', \Carbon\Carbon::now()->subDays(30)->toDateString());
        } else {
            $trendQuery->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
        }
        
        $trends = $trendQuery->groupBy('date')->orderBy('date', 'asc')->get();

        // Skill Matrix — small table, fast
        $skills = DB::table('skills')
            ->join('employee_skill', 'skills.id', '=', 'employee_skill.skill_id')
            ->select('skills.name', DB::raw('count(employee_skill.employee_id) as employee_count'))
            ->groupBy('skills.id', 'skills.name')
            ->orderByDesc('employee_count')
            ->get();

        // Department/Team Distribution (reacts to date filter)
        if ($period) {
            $teams = DB::table('employee_period_metrics as epm')
                ->join('employees as e', 'e.id', '=', 'epm.employee_id')
                ->where('epm.period', $period)
                ->select('e.team', DB::raw('count(distinct epm.employee_id) as count'))
                ->groupBy('e.team')
                ->pluck('count', 'e.team')
                ->toArray();
        } else if ($filter === 'all_time') {
            $teams = Employee::select('team', DB::raw('count(*) as count'))->groupBy('team')->pluck('count', 'team')->toArray();
        } else {
            $teams = DB::table('employee_daily_metrics as edm')
                ->join('employees as e', 'e.id', '=', 'edm.employee_id')
                ->whereBetween('edm.date', [$start->toDateString(), $end->toDateString()])
                ->select('e.team', DB::raw('count(distinct edm.employee_id) as count'))
                ->groupBy('e.team')
                ->pluck('count', 'e.team')
                ->toArray();
        }

        // Workload Distribution (reacts to date filter using workload_trends)
        $workloadTrendQuery = DB::table('workload_trends')
            ->selectRaw('
                SUM(underutilized_count) as underutilized,
                SUM(optimal_count) as optimal,
                SUM(busy_count) as busy,
                SUM(overloaded_count) as overloaded
            ');

        if ($filter !== 'all_time') {
            $workloadTrendQuery->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
        }

        $workloadDistData = $workloadTrendQuery->first();

        if ($workloadDistData && ($workloadDistData->underutilized || $workloadDistData->optimal || $workloadDistData->busy || $workloadDistData->overloaded)) {
            $workloadDist = [
                'underutilized' => (int)$workloadDistData->underutilized,
                'optimal' => (int)$workloadDistData->optimal,
                'busy' => (int)$workloadDistData->busy,
                'overloaded' => (int)$workloadDistData->overloaded,
            ];
        } else {
            $workloadDist = DB::table('workload_metrics')
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')->toArray();
        }

        return response()->json([
            'kpis' => [
                'total'                => $totalEmployees,
                'active'               => $activeEmployees,
                'avg_task_score'       => round($avgTaskScore, 1),
                'avg_attendance_score' => round($avgAttendanceScore, 1),
                'avg_workload'         => round($avgWorkload, 1) . '%'
            ],
            'top_performers' => $topPerformers,
            'charts' => [
                'teams'    => $teams,
                'workload' => $workloadDist,
                'trends'   => [
                    'labels' => $trends->pluck('date'),
                    'data'   => $trends->pluck('avg_score')->map(fn($v) => round($v, 1))
                ],
                'skills'   => [
                    'labels' => $skills->pluck('name'),
                    'data'   => $skills->pluck('employee_count')
                ]
            ]
        ]);
    }
}
