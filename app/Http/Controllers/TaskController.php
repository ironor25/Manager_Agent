<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Employee;
use App\Models\EmployeeTaskMetric;
use App\Models\TaskMetricsTrend;
use App\Models\EmployeePeriodMetric;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Traits\DateFilterable;

class TaskController extends Controller
{
    use DateFilterable;
    public function index()
    {
        $projects = \App\Models\Project::select('id', 'name')->orderBy('name')->get();
        return view('tasks-management', compact('projects'));
    }

    public function search(Request $request)
    {
        $q = $request->input('q');
        $tasks = Task::where('title', 'like', "%{$q}%")
            ->limit(20)
            ->get(['id', 'title']);
        return response()->json($tasks);
    }

    public function getTasks(Request $request)
    {
        if ($request->ajax()) {
            $data = Task::with(['employee:id,name,team', 'project:id,name'])->select('tasks.*');
            
            return DataTables::of($data)
                ->addColumn('employee_name', function($row) {
                    return $row->employee ? $row->employee->name : 'Unassigned';
                })
                ->addColumn('employee_initial', function($row) {
                    return substr($row->employee ? $row->employee->name : '?', 0, 1);
                })
                ->addColumn('team', function($row) {
                    return $row->employee ? $row->employee->team : 'N/A';
                })
                ->addColumn('project_name', function($row) {
                    if ($row->project) {
                        return '<a href="'.route('projects.show', $row->project_id).'#nav-tasks" class="text-decoration-none hover-underline text-primary fw-medium">'.$row->project->name.'</a>';
                    }
                    return '<span class="text-muted fst-italic">No Project</span>';
                })
                ->addColumn('formatted_deadline', function($row) {
                    return $row->deadline_at ? $row->deadline_at->format('M d, Y') : 'No Deadline';
                })
                ->addColumn('formatted_created_at', function($row) {
                    return $row->created_at ? $row->created_at->format('M d, Y') : '';
                })
                ->addColumn('action', function($row) {
                    $taskJson = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
                    return '
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <button class="action-btn edit-task-btn" data-task="'.$taskJson.'" title="Edit Task">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button type="button" class="action-btn delete-btn trigger-delete" data-action="'.route('tasks.destroy', $row->id).'" title="Delete Task">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['action', 'project_name'])
                ->make(true);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'employee_id' => 'required|exists:employees,id',
            'deadline_at' => 'nullable|date',
            'priority' => 'required|string|in:low,normal,high',
            'status' => 'required|string|in:pending,in_progress,completed,blocked',
            'depends_on_task_id' => 'nullable|exists:tasks,id',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        Task::create($validated);

        return redirect()->back()->with('success', 'Task assigned successfully.');
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'employee_id' => 'required|exists:employees,id',
            'deadline_at' => 'nullable|date',
            'priority' => 'required|string|in:low,normal,high',
            'status' => 'required|string|in:pending,in_progress,completed,blocked',
            'depends_on_task_id' => 'nullable|exists:tasks,id',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        $task->update($validated);

        return redirect()->back()->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
    }

    /**
     * Display Task Monitoring view.
     */
    public function monitoring()
    {
        return view('tasks-monitoring');
    }

    /**
     * Get data for Task Monitoring KPIs and Charts.
     */
    public function getMonitoringData(Request $request)
    {
        $filter = $request->get('date_filter', 'all_time');
        [$start, $end] = $this->getDateRange($filter, $request->get('start_date'), $request->get('end_date'));

        $now = now();
        $todayStart = $now->copy()->startOfDay();
        $todayEnd = $now->copy()->endOfDay();

        $taskQuery = Task::query();
        if ($filter !== 'all_time') {
            $taskQuery->whereBetween('created_at', [$start, $end]);
        }

        // KPIs
        $totalActive = (clone $taskQuery)->whereIn('status', ['pending', 'in_progress'])->count();
        $dueToday = (clone $taskQuery)->whereBetween('deadline_at', [$todayStart, $todayEnd])->count();
        $overdue = (clone $taskQuery)->whereNotIn('status', ['completed', 'late_completed'])
            ->where('deadline_at', '<', $now)
            ->count();
        $highPriority = (clone $taskQuery)->where('priority', 'high')
            ->whereNotIn('status', ['completed', 'late_completed'])
            ->count();
            
        $blocked = (clone $taskQuery)->where('status', 'blocked')
            ->orWhere(function($query) use ($now) {
                $query->whereNotNull('depends_on_task_id')
                      ->whereHas('dependsOnTask', function($q) {
                          $q->whereNotIn('status', ['completed', 'late_completed']);
                      });
            })
            ->count();

        // Chart 1: Tasks by Status
        $tasksByStatus = (clone $taskQuery)->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // Chart 2: Tasks by Priority
        $tasksByPriority = (clone $taskQuery)->selectRaw('priority, count(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority');

        // Chart 3: Overdue Trend (from task_metrics_trends)
        $trendsQuery = TaskMetricsTrend::orderBy('date', 'asc')->take(30);
        if ($filter !== 'all_time') {
            $trendsQuery->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
        }
        $trends = $trendsQuery->get();

        // Chart 4: Due Date Distribution (next 7 days)
        $dueDates = (clone $taskQuery)->whereBetween('deadline_at', [$todayStart, $now->copy()->addDays(7)->endOfDay()])
            ->selectRaw('DATE(deadline_at) as date, count(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Chart 5: Team Task Distribution
        $teamDistQuery = DB::table('tasks')
            ->join('employees', 'tasks.employee_id', '=', 'employees.id')
            ->selectRaw('employees.team, count(tasks.id) as count')
            ->whereNotNull('employees.team');
        if ($filter !== 'all_time') {
            $teamDistQuery->whereBetween('tasks.created_at', [$start, $end]);
        }
        $teamDistribution = $teamDistQuery->groupBy('employees.team')
            ->orderByDesc('count')
            ->get();

        return response()->json([
            'kpis' => [
                'totalActive' => $totalActive,
                'dueToday' => $dueToday,
                'overdue' => $overdue,
                'highPriority' => $highPriority,
                'blocked' => $blocked,
            ],
            'charts' => [
                'status' => [
                    'pending' => $tasksByStatus['pending'] ?? 0,
                    'in_progress' => $tasksByStatus['in_progress'] ?? 0,
                    'completed' => ($tasksByStatus['completed'] ?? 0) + ($tasksByStatus['late_completed'] ?? 0),
                    'blocked' => $tasksByStatus['blocked'] ?? 0,
                ],
                'priority' => [
                    'low' => $tasksByPriority['low'] ?? 0,
                    'normal' => $tasksByPriority['normal'] ?? 0,
                    'high' => $tasksByPriority['high'] ?? 0,
                ],
                'trends' => $trends,
                'dueDates' => $dueDates,
                'teamDistribution' => $teamDistribution,
            ]
        ]);
    }

    /**
     * Get Monitoring DataTables data.
     */
    public function getMonitoringTable(Request $request, $type)
    {
        $filter = $request->get('date_filter', 'all_time');
        [$start, $end] = $this->getDateRange($filter, $request->get('start_date'), $request->get('end_date'));

        $now = now();
        $query = Task::select('tasks.*', 'employees.name as employee_name', 'employees.team')
            ->leftJoin('employees', 'tasks.employee_id', '=', 'employees.id');

        if ($filter !== 'all_time') {
            $query->whereBetween('tasks.created_at', [$start, $end]);
        }

        switch ($type) {
            case 'overdue':
                $query->whereNotIn('tasks.status', ['completed', 'late_completed'])
                    ->where('tasks.deadline_at', '<', $now);
                break;
            case 'upcoming':
                $query->whereNotIn('tasks.status', ['completed', 'late_completed'])
                    ->whereBetween('tasks.deadline_at', [$now, $now->copy()->addDays(7)->endOfDay()]);
                break;
            case 'high-priority':
                $query->where('tasks.priority', 'high')
                    ->whereNotIn('tasks.status', ['completed', 'late_completed']);
                break;
            case 'blocked':
                $query->where('tasks.status', 'blocked');
                break;
        }

        return DataTables::of($query)
            ->filterColumn('employee_name', function($query, $keyword) {
                $query->where('employees.name', 'like', "%{$keyword}%");
            })
            ->filterColumn('team', function($query, $keyword) {
                $query->where('employees.team', 'like', "%{$keyword}%");
            })
            ->addColumn('formatted_deadline', function($row) {
                return $row->deadline_at ? \Carbon\Carbon::parse($row->deadline_at)->format('M d, Y') : 'No Deadline';
            })
            ->make(true);
    }

    /**
     * Display Task Metrics view.
     */
    public function metrics()
    {
        return view('tasks-metrics');
    }

    /**
     * Get data for Task Metrics KPIs, charts, and leaderboards.
     */
    public function getMetricsData(Request $request)
    {
        $filter = $request->get('date_filter', 'all_time');
        [$start, $end] = $this->getDateRange($filter, $request->get('start_date'), $request->get('end_date'));

        if ($filter === 'all_time') {
            // Global precomputed KPIs
            $globalStats = EmployeeTaskMetric::selectRaw("
                SUM(tasks_assigned) as total_assigned,
                SUM(tasks_completed) as total_completed,
                SUM(tasks_delayed) as total_delayed,
                AVG(completion_rate) as avg_completion_rate,
                AVG(avg_completion_time_hours) as avg_time,
                AVG(productivity_score) as avg_prod_score
            ")->first();

            // Team Productivity Comparison
            $teamProductivity = EmployeeTaskMetric::join('employees', 'employee_task_metrics.employee_id', '=', 'employees.id')
                ->selectRaw('employees.team, AVG(employee_task_metrics.productivity_score) as avg_score')
                ->whereNotNull('employees.team')
                ->groupBy('employees.team')
                ->orderByDesc('avg_score')
                ->get();

            // Employee Productivity Comparison (Top 5 and Bottom 5)
            $topEmployees = EmployeeTaskMetric::with('employee:id,name')
                ->orderByDesc('productivity_score')
                ->take(5)
                ->get();

            $bottomEmployees = EmployeeTaskMetric::with('employee:id,name')
                ->orderBy('productivity_score')
                ->take(5)
                ->get();

            // Leaderboards
            $topPerformers = EmployeeTaskMetric::with('employee:id,name,team')
                ->orderByDesc('productivity_score')
                ->take(10)
                ->get();

            $lowestCompletion = EmployeeTaskMetric::with('employee:id,name,team')
                ->orderBy('completion_rate')
                ->take(10)
                ->get();
        } else {
            // Map date-filter component values to employee_period_metrics period keys
            $periodMap = [
                'weekly'  => '7_days',
                'monthly' => '30_days',
            ];
            $period = $periodMap[$filter] ?? null;

            if ($period) {
                // Fast path: use precomputed employee_period_metrics
                $employeeMetrics = DB::table('employee_period_metrics as epm')
                    ->join('employees', 'employees.id', '=', 'epm.employee_id')
                    ->where('epm.period', $period)
                    ->selectRaw('
                        epm.employee_id,
                        employees.name,
                        employees.team,
                        epm.total_tasks as tasks_assigned,
                        epm.completed_tasks as tasks_completed,
                        (epm.total_tasks - epm.completed_tasks) as tasks_delayed,
                        epm.task_completion_score as completion_rate,
                        0 as avg_time,
                        epm.task_completion_score as productivity_score
                    ')
                    ->get()
                    ->map(function($item) {
                        return (object)[
                            'employee' => (object)['name' => $item->name, 'team' => $item->team],
                            'productivity_score'  => round($item->productivity_score, 2),
                            'completion_rate'     => round($item->completion_rate, 2),
                            'tasks_assigned'      => $item->tasks_assigned,
                            'productivity_category' => $item->productivity_score > 80 ? 'Excellent' : 'Average'
                        ];
                    });

                $globalStats = DB::table('employee_period_metrics')
                    ->where('period', $period)
                    ->selectRaw('
                        SUM(total_tasks) as total_assigned,
                        SUM(completed_tasks) as total_completed,
                        SUM(total_tasks - completed_tasks) as total_delayed,
                        AVG(task_completion_score) as avg_completion_rate,
                        0 as avg_time,
                        AVG(task_completion_score) as avg_prod_score
                    ')->first();

                $teamProductivity = DB::table('employee_period_metrics as epm')
                    ->join('employees', 'employees.id', '=', 'epm.employee_id')
                    ->where('epm.period', $period)
                    ->selectRaw('employees.team, AVG(epm.task_completion_score) as avg_score')
                    ->whereNotNull('employees.team')
                    ->groupBy('employees.team')
                    ->orderByDesc('avg_score')
                    ->get();
            } else {
                // Custom date range: dynamic aggregation (execute once, process in memory)
                $employeeMetricsData = DB::table('employee_daily_metrics')
                    ->join('employees', 'employees.id', '=', 'employee_daily_metrics.employee_id')
                    ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                    ->selectRaw("
                        employees.id as employee_id,
                        employees.name,
                        employees.team,
                        SUM(total_tasks) as tasks_assigned,
                        SUM(completed_tasks) as tasks_completed,
                        SUM(total_tasks) - SUM(completed_tasks) as tasks_delayed,
                        (SUM(completed_tasks) / NULLIF(SUM(total_tasks), 0)) * 100 as completion_rate,
                        (SUM(completed_tasks) / NULLIF(SUM(total_tasks), 0)) * 100 as productivity_score
                    ")
                    ->groupBy('employees.id', 'employees.name', 'employees.team')
                    ->get();

                $globalStats = (object)[
                    'total_assigned' => $employeeMetricsData->sum('tasks_assigned'),
                    'total_completed' => $employeeMetricsData->sum('tasks_completed'),
                    'total_delayed' => $employeeMetricsData->sum('tasks_delayed'),
                    'avg_completion_rate' => $employeeMetricsData->avg('completion_rate'),
                    'avg_time' => 0,
                    'avg_prod_score' => $employeeMetricsData->avg('productivity_score')
                ];

                $teamProductivity = $employeeMetricsData->whereNotNull('team')
                    ->groupBy('team')
                    ->map(function($teamMembers, $team) {
                        return (object)[
                            'team' => $team,
                            'avg_score' => $teamMembers->avg('productivity_score')
                        ];
                    })->sortByDesc('avg_score')->values();

                $employeeMetrics = $employeeMetricsData->map(function($item) {
                    return (object)[
                        'employee' => (object)['name' => $item->name, 'team' => $item->team],
                        'productivity_score'    => round((float)$item->productivity_score, 2),
                        'completion_rate'       => round((float)$item->completion_rate, 2),
                        'tasks_assigned'        => $item->tasks_assigned,
                        'productivity_category' => $item->productivity_score > 80 ? 'Excellent' : 'Average'
                    ];
                });
            }

            $topEmployees    = $employeeMetrics->sortByDesc('productivity_score')->take(5)->values();
            $bottomEmployees = $employeeMetrics->sortBy('productivity_score')->take(5)->values();
            $topPerformers   = $employeeMetrics->sortByDesc('productivity_score')->take(10)->values();
            $lowestCompletion = $employeeMetrics->sortBy('completion_rate')->take(10)->values();
        }

        // Completion rate trend & Delayed trend (from task_metrics_trends)
        $trendsQuery = TaskMetricsTrend::query();
        if ($filter === 'all_time') {
            $trendsQuery->where('date', '>=', \Carbon\Carbon::now()->subDays(30)->toDateString());
        } else {
            $trendsQuery->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
        }
        $trends = $trendsQuery->orderBy('date', 'asc')->get();

        // Monthly Trend (aggregated by month)
        $monthlyTrendQuery = TaskMetricsTrend::selectRaw("
                DATE_FORMAT(date, '%Y-%m') as month,
                SUM(tasks_assigned) as assigned,
                SUM(tasks_completed) as completed,
                SUM(tasks_delayed) as `delayed`,
                AVG(avg_completion_time_hours) as avg_time
            ");
        if ($filter !== 'all_time') {
            $monthlyTrendQuery->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
        }
        $monthlyTrend = $monthlyTrendQuery->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        return response()->json([
            'kpis' => [
                'assigned' => (int)($globalStats->total_assigned ?? 0),
                'completed' => (int)($globalStats->total_completed ?? 0),
                'delayed' => (int)($globalStats->total_delayed ?? 0),
                'completionRate' => round($globalStats->avg_completion_rate ?? 0.00, 2),
                'avgTime' => round($globalStats->avg_time ?? 0.00, 2),
                'prodScore' => round($globalStats->avg_prod_score ?? 0.00, 2),
            ],
            'charts' => [
                'trends' => $trends,
                'teamProductivity' => $teamProductivity,
                'topEmployees' => $topEmployees,
                'bottomEmployees' => $bottomEmployees,
                'monthlyTrend' => $monthlyTrend,
            ],
            'leaderboards' => [
                'topPerformers' => $topPerformers,
                'lowestCompletion' => $lowestCompletion,
            ]
        ]);
    }
}
