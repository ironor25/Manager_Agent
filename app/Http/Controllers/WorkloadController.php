<?php

namespace App\Http\Controllers;

use App\Models\WorkloadMetric;
use App\Models\WorkloadTrend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Traits\DateFilterable;

class WorkloadController extends Controller
{
    use DateFilterable;
    public function index()
    {
        return view('workload');
    }

    public function getKpis(Request $request)
    {
        $filter = $request->get('date_filter', 'all_time');
        [$start, $end] = $this->getDateRange($filter, $request->get('start_date'), $request->get('end_date'));

        $query = WorkloadMetric::query();
        if ($filter !== 'all_time') {
            $query->whereBetween('updated_at', [$start, $end]);
        }

        $avgWorkload = null;
        if ($filter !== 'all_time') {
            $avgWorkload = DB::table('workload_trends')
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->avg('average_workload');
        }
        
        if (is_null($avgWorkload)) {
            $avgWorkload = (clone $query)->avg('workload_percentage');
        }

        $counts = null;
        if ($filter !== 'all_time') {
            $counts = DB::table('workload_trends')
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->selectRaw("
                    SUM(overloaded_count) as overloaded,
                    SUM(underutilized_count) as underutilized,
                    SUM(optimal_count) as optimal
                ")->first();
        }
        
        if (!$counts || (is_null($counts->overloaded) && is_null($counts->underutilized) && is_null($counts->optimal))) {
            $counts = (clone $query)->selectRaw("
                SUM(CASE WHEN status = 'overloaded' THEN 1 ELSE 0 END) as overloaded,
                SUM(CASE WHEN status = 'underutilized' THEN 1 ELSE 0 END) as underutilized,
                SUM(CASE WHEN status = 'optimal' THEN 1 ELSE 0 END) as optimal
            ")->first();
        }

        // Team aggregate
        $teamWorkloads = (clone $query)->join('employees', 'workload_metrics.employee_id', '=', 'employees.id')
            ->select('employees.team', DB::raw('AVG(workload_metrics.workload_percentage) as avg_workload'))
            ->groupBy('employees.team')
            ->orderBy('avg_workload', 'desc')
            ->get();
            
        if ($teamWorkloads->isEmpty()) {
            $teamWorkloads = WorkloadMetric::join('employees', 'workload_metrics.employee_id', '=', 'employees.id')
                ->select('employees.team', DB::raw('AVG(workload_metrics.workload_percentage) as avg_workload'))
                ->groupBy('employees.team')
                ->orderBy('avg_workload', 'desc')
                ->get();
        }
            
        $mostLoadedTeam = $teamWorkloads->first();
        $leastLoadedTeam = $teamWorkloads->last();

        return response()->json([
            'totalOverloaded' => (int)($counts->overloaded ?? 0),
            'totalUnderutilized' => (int)($counts->underutilized ?? 0),
            'totalOptimal' => (int)($counts->optimal ?? 0),
            'averageOrgWorkload' => round($avgWorkload ?? 0, 2),
            'mostLoadedTeam' => $mostLoadedTeam ? $mostLoadedTeam->team : 'N/A',
            'leastLoadedTeam' => $leastLoadedTeam ? $leastLoadedTeam->team : 'N/A',
        ]);
    }

    public function getChartData(Request $request)
    {
        $filter = $request->get('date_filter', 'all_time');
        [$start, $end] = $this->getDateRange($filter, $request->get('start_date'), $request->get('end_date'));

        $query = WorkloadMetric::query();
        if ($filter !== 'all_time') {
            $query->whereBetween('updated_at', [$start, $end]);
        }

        // 1. Distribution Chart
        $distribution = null;
        if ($filter !== 'all_time') {
            $trendDistribution = DB::table('workload_trends')
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->selectRaw('
                    SUM(underutilized_count) as underutilized,
                    SUM(optimal_count) as optimal,
                    SUM(busy_count) as busy,
                    SUM(overloaded_count) as overloaded
                ')->first();
                
            if ($trendDistribution && ($trendDistribution->underutilized || $trendDistribution->optimal || $trendDistribution->busy || $trendDistribution->overloaded)) {
                $distribution = [
                    'underutilized' => (int)$trendDistribution->underutilized,
                    'optimal' => (int)$trendDistribution->optimal,
                    'busy' => (int)$trendDistribution->busy,
                    'overloaded' => (int)$trendDistribution->overloaded,
                ];
            }
        }
        
        if (is_null($distribution)) {
            $distributionData = (clone $query)->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status');
            $distribution = [
                'underutilized' => $distributionData['underutilized'] ?? 0,
                'optimal' => $distributionData['optimal'] ?? 0,
                'busy' => $distributionData['busy'] ?? 0,
                'overloaded' => $distributionData['overloaded'] ?? 0,
            ];
        }

        // 2. Top 10 Most Loaded
        $topLoaded = (clone $query)->with('employee:id,name')
            ->orderByDesc('workload_percentage')
            ->take(10)
            ->get();
        if ($topLoaded->isEmpty()) {
            $topLoaded = WorkloadMetric::with('employee:id,name')
                ->orderByDesc('workload_percentage')
                ->take(10)
                ->get();
        }
        $topLoaded = $topLoaded->map(function($m) {
            return [
                'name' => $m->employee->name ?? 'Unknown',
                'workload' => $m->workload_percentage
            ];
        });

        // 3. Top 10 Least Utilized
        $leastUtilized = (clone $query)->with('employee:id,name')
            ->orderBy('workload_percentage')
            ->take(10)
            ->get();
        if ($leastUtilized->isEmpty()) {
            $leastUtilized = WorkloadMetric::with('employee:id,name')
                ->orderBy('workload_percentage')
                ->take(10)
                ->get();
        }
        $leastUtilized = $leastUtilized->map(function($m) {
            return [
                'name' => $m->employee->name ?? 'Unknown',
                'workload' => $m->workload_percentage
            ];
        });

        // 4. Team Workload Comparison
        $teamComparison = (clone $query)->join('employees', 'workload_metrics.employee_id', '=', 'employees.id')
            ->select('employees.team', DB::raw('AVG(workload_metrics.workload_percentage) as avg_workload'))
            ->groupBy('employees.team')
            ->orderByDesc('avg_workload')
            ->get();
            
        if ($teamComparison->isEmpty()) {
            $teamComparison = WorkloadMetric::join('employees', 'workload_metrics.employee_id', '=', 'employees.id')
                ->select('employees.team', DB::raw('AVG(workload_metrics.workload_percentage) as avg_workload'))
                ->groupBy('employees.team')
                ->orderByDesc('avg_workload')
                ->get();
        }

        // 5. Trend Chart (fixed ordering and filter bug)
        $trendQuery = WorkloadTrend::query();
        if ($filter === 'all_time') {
            $trendQuery->where('date', '>=', \Carbon\Carbon::now()->subDays(30)->toDateString());
        } else {
            $trendQuery->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
        }
        $trends = $trendQuery->orderBy('date', 'asc')->get();

        return response()->json([
            'distribution' => $distribution,
            'topLoaded' => $topLoaded,
            'leastUtilized' => $leastUtilized,
            'teamComparison' => $teamComparison,
            'trends' => $trends
        ]);
    }

    public function getEmployeesData(Request $request)
    {
        $filter = $request->get('date_filter', 'all_time');
        [$start, $end] = $this->getDateRange($filter, $request->get('start_date'), $request->get('end_date'));

        $query = WorkloadMetric::with('employee:id,name,team')
            ->select('workload_metrics.*');

        if ($filter !== 'all_time') {
            $query->whereBetween('updated_at', [$start, $end]);
        }

        if ((clone $query)->count() === 0) {
            $query = WorkloadMetric::with('employee:id,name,team')
                ->select('workload_metrics.*');
        }

        return DataTables::of($query)
            ->addColumn('employee_name', function ($metric) {
                return $metric->employee->name ?? 'Unknown';
            })
            ->addColumn('team', function ($metric) {
                return $metric->employee->team ?? 'N/A';
            })
            ->editColumn('updated_at', function ($metric) {
                return $metric->updated_at ? $metric->updated_at->diffForHumans() : 'N/A';
            })
            ->make(true);
    }
    
    public function getTeamWorkloadData(Request $request)
    {
        $filter = $request->get('date_filter', 'all_time');
        [$start, $end] = $this->getDateRange($filter, $request->get('start_date'), $request->get('end_date'));

        $query = WorkloadMetric::join('employees', 'workload_metrics.employee_id', '=', 'employees.id')
            ->select(
                'employees.team as team_name',
                DB::raw('COUNT(employees.id) as total_members'),
                DB::raw('AVG(workload_metrics.workload_percentage) as average_workload'),
                DB::raw("SUM(CASE WHEN workload_metrics.status = 'overloaded' THEN 1 ELSE 0 END) as overloaded_count"),
                DB::raw("SUM(CASE WHEN workload_metrics.status = 'underutilized' THEN 1 ELSE 0 END) as underutilized_count")
            );

        if ($filter !== 'all_time') {
            $query->whereBetween('workload_metrics.updated_at', [$start, $end]);
        }

        $query->groupBy('employees.team');

        if ((clone $query)->get()->isEmpty()) {
            $query = WorkloadMetric::join('employees', 'workload_metrics.employee_id', '=', 'employees.id')
                ->select(
                    'employees.team as team_name',
                    DB::raw('COUNT(employees.id) as total_members'),
                    DB::raw('AVG(workload_metrics.workload_percentage) as average_workload'),
                    DB::raw("SUM(CASE WHEN workload_metrics.status = 'overloaded' THEN 1 ELSE 0 END) as overloaded_count"),
                    DB::raw("SUM(CASE WHEN workload_metrics.status = 'underutilized' THEN 1 ELSE 0 END) as underutilized_count")
                )->groupBy('employees.team');
        }

        return DataTables::of($query)
            ->editColumn('average_workload', function($row) {
                return round($row->average_workload, 2);
            })
            ->make(true);
    }

    public function recalculate(\App\Services\WorkloadService $service)
    {
        $service->calculateAndStoreWorkloads();
        return response()->json(['success' => true, 'message' => 'Workload metrics recalculated successfully.']);
    }
}
