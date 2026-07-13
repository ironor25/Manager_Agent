<?php

namespace App\Http\Controllers;

use App\Services\TeamAnalyticsService;
use App\Services\TeamReportService;
use App\Models\TeamReport;
use Illuminate\Http\Request;

class TeamDashboardController extends Controller
{
    protected TeamAnalyticsService $analyticsService;
    protected TeamReportService $reportService;

    public function __construct(TeamAnalyticsService $analyticsService, TeamReportService $reportService)
    {
        $this->analyticsService = $analyticsService;
        $this->reportService = $reportService;
    }

    /**
     * Display the Team Performance Dashboard.
     */
    public function index()
    {
        return view('team-dashboard');
    }

    /**
     * Display the Team Management Dashboard.
     */
    public function management()
    {
        $teams = \App\Models\Team::select('name')->get();
        return view('team-management', compact('teams'));
    }

    /**
     * Return JSON data for the Teams Yajra DataTable.
     */
    public function getTeamsData(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $data = \App\Models\Team::withCount('employees');

            return \Yajra\DataTables\Facades\DataTables::of($data)
                ->addColumn('action', function($row) {
                    return '
                        <div class="d-flex justify-content-end align-items-center gap-2">
                            <button class="action-btn edit-team-btn" data-team="'.htmlspecialchars($row->name, ENT_QUOTES, 'UTF-8').'" data-desc="'.htmlspecialchars($row->description ?? '', ENT_QUOTES, 'UTF-8').'" title="Edit Team">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button class="action-btn delete-btn trigger-team-delete" data-team="'.htmlspecialchars($row->name, ENT_QUOTES, 'UTF-8').'" title="Delete Team">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function getKpis(Request $request)
    {
        $period = $request->get('period', 7);
        $metrics = \App\Models\TeamPerformanceMetric::where('period', $period)->get();
        
        if ($metrics->isEmpty()) {
            $kpis = $this->analyticsService->getDashboardKpis($period);
            return response()->json($kpis);
        }

        $totalTeams = $metrics->count();
        $totalScore = $metrics->sum('leadership_score');
        
        $bestTeamName = 'N/A';
        $highestScore = -1;
        foreach ($metrics as $m) {
            if ($m->leadership_score > $highestScore) {
                $highestScore = $m->leadership_score;
                $bestTeamName = $m->team_name;
            }
        }

        $averageTeamScore = $totalTeams > 0 ? round($totalScore / $totalTeams, 2) : 0;

        return response()->json([
            'totalTeams' => $totalTeams,
            'bestTeamName' => $bestTeamName,
            'averageTeamScore' => $averageTeamScore
        ]);
    }

    public function getChartData(Request $request)
    {
        $period = $request->get('period', 7);
        $metrics = \App\Models\TeamPerformanceMetric::where('period', $period)->get();

        if ($metrics->isEmpty()) {
            $chartData = $this->analyticsService->getBulkChartData($period);
            return response()->json($chartData);
        }

        $chartData = $metrics->map(function($m) {
            return [
                'team' => $m->team_name,
                'attendance_score' => $m->attendance_score,
                'task_completion' => $m->task_completion_score,
                'leadership_score' => $m->leadership_score,
                'git_contribution' => $m->git_contribution_score,
            ];
        })->toArray();

        return response()->json($chartData);
    }

    public function getMembersList($teamName)
    {
        $members = \App\Models\Employee::where('team', $teamName)
            ->select('id', 'name')
            ->get();
            
        return response()->json($members);
    }

    public function leaderboard(Request $request)
    {
        $period = $request->get('period', 7);
        $metrics = \App\Models\TeamPerformanceMetric::where('period', $period)
            ->orderByDesc('leadership_score')
            ->get();

        if ($metrics->isEmpty()) {
            $leaderboard = $this->analyticsService->getLeaderboard($period);
            return response()->json($leaderboard);
        }

        $leaderboard = $metrics->map(function($m) {
            return [
                'team_name' => $m->team_name,
                'efficiency_score' => $m->leadership_score,
                'top_performers' => $m->top_performers ?? [],
            ];
        })->toArray();

        return response()->json($leaderboard);
    }

    /**
     * Generate an AI report for the given team.
     */
    public function generateReport($team)
    {
        try {
            // This interacts with AI and DB
            $report = $this->reportService->generateTeamReport($team);
            $members = \App\Models\Employee::where('team', $team)->select('id', 'name')->get();

            return response()->json([
                'success' => true,
                'report' => $report,
                'members' => $members,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeTeam(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:teams,name',
            'description' => 'nullable|string',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'exists:employees,id',
        ]);

        $team = \App\Models\Team::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        if ($request->has('employee_ids') && is_array($request->employee_ids)) {
            \App\Models\Employee::whereIn('id', $request->employee_ids)
                ->update(['team' => $team->name]);
        }

        return redirect()->route('teams.management')->with('success', 'Team created successfully with ' . count($request->employee_ids ?? []) . ' members.');
    }

    public function updateTeam(Request $request, $teamName)
    {
        $request->validate([
            'description' => 'nullable|string',
        ]);

        $team = \App\Models\Team::findOrFail($teamName);
        $team->update([
            'description' => $request->description,
        ]);

        return redirect()->route('teams.management')->with('success', 'Team updated successfully.');
    }

    public function destroyTeam($teamName)
    {
        $team = \App\Models\Team::findOrFail($teamName);
        
        // Remove this team from all associated employees (setting it to null)
        \App\Models\Employee::where('team', $team->name)->update(['team' => null]);
        
        $team->delete();

        return redirect()->route('teams.management')->with('success', 'Team deleted successfully.');
    }

    public function addMember(Request $request, $teamName)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
        ]);

        $team = \App\Models\Team::findOrFail($teamName);
        $employee = \App\Models\Employee::findOrFail($request->employee_id);
        
        $employee->update(['team' => $team->name]);

        return redirect()->back()->with('success', 'Member added to team.');
    }

    public function removeMember($teamName, $employeeId)
    {
        $employee = \App\Models\Employee::findOrFail($employeeId);
        
        if ($employee->team === $teamName) {
            $employee->update(['team' => null]);
        }

        return redirect()->back()->with('success', 'Member removed from team.');
    }

    public function show($teamName)
    {
        $team = \App\Models\Team::findOrFail($teamName);
        $totalMembersCount = \App\Models\Employee::where('team', $teamName)->count();

        return view('team-details', compact('team', 'totalMembersCount'));
    }

    public function getTeamMembersData(Request $request, $teamName)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $data = \App\Models\Employee::where('team', $teamName)
                ->select(['id', 'name', 'email']);

            return \Yajra\DataTables\Facades\DataTables::of($data)
                ->addColumn('action', function($row) use ($teamName) {
                    $removeUrl = route('teams.members.remove', [$teamName, $row->id]);
                    return '
                        <div class="d-flex justify-content-end align-items-center gap-2">
                            <button class="report-btn generate-individual-report-btn" data-id="'.$row->id.'" data-name="'.htmlspecialchars($row->name, ENT_QUOTES, 'UTF-8').'">
                                <i class="fa-solid fa-chart-simple me-1"></i> Individual Performance
                            </button>
                            <form action="'.$removeUrl.'" method="POST" class="d-inline remove-member-form" onsubmit="return confirm(\'Are you sure you want to remove this employee from this team?\');">
                                '.csrf_field().'
                                '.method_field('DELETE').'
                                <button type="submit" class="btn btn-sm btn-light text-danger border-0">
                                    <i class="fa-solid fa-user-minus me-1"></i> Remove
                                </button>
                            </form>
                        </div>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function assignTask(Request $request)
    {
        $request->validate([
            'assignment_type' => 'required|in:team,individual',
            'team_name' => 'required|exists:teams,name',
            'employee_id' => 'required_if:assignment_type,individual|nullable|exists:employees,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline_at' => 'nullable|date',
            'estimated_hours' => 'nullable|numeric|min:0',
            'priority' => 'required|in:low,normal,high',
        ]);

        $taskData = [
            'title' => $request->title,
            'description' => $request->description,
            'deadline_at' => $request->deadline_at,
            'estimated_hours' => $request->estimated_hours,
            'priority' => $request->priority,
            'status' => 'pending',
        ];

        if ($request->assignment_type === 'team') {
            $employees = \App\Models\Employee::where('team', $request->team_name)->get();
            if ($employees->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No members found in this team.']);
            }
            foreach ($employees as $emp) {
                $data = $taskData;
                $data['employee_id'] = $emp->id;
                \App\Models\Task::create($data);
            }
            return response()->json(['success' => true, 'message' => 'Task assigned to all team members successfully.']);
        } else {
            $taskData['employee_id'] = $request->employee_id;
            \App\Models\Task::create($taskData);
            return response()->json(['success' => true, 'message' => 'Task assigned to individual member successfully.']);
        }
    }
}
