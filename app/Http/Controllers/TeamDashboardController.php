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
        $teams = $this->analyticsService->getAllTeams();
        $totalTeams = count($teams);

        // Calculate average team score across all teams
        $totalScore = 0;
        $bestTeamName = 'N/A';
        $highestScore = -1;

        foreach ($teams as $team) {
            $efficiency = $this->analyticsService->calculateTeamEfficiency($team);
            $totalScore += $efficiency;

            if ($efficiency > $highestScore) {
                $highestScore = $efficiency;
                $bestTeamName = $team;
            }
        }

        $averageTeamScore = $totalTeams > 0 ? round($totalScore / $totalTeams, 2) : 0;
        
        $teamModels = \App\Models\Team::with('employees')->get();
        $allEmployees = \App\Models\Employee::all();

        return view('team-dashboard', compact('totalTeams', 'bestTeamName', 'averageTeamScore', 'teamModels', 'allEmployees'));
    }

    /**
     * Return JSON data for the Chart.js grouped bar chart.
     */
    public function getChartData()
    {
        $teams = $this->analyticsService->getAllTeams();
        $chartData = [];

        foreach ($teams as $team) {
            $chartData[] = [
                'team' => $team,
                'attendance_score' => $this->analyticsService->calculateAverageAttendance($team),
                'task_completion' => $this->analyticsService->calculateAverageTaskCompletion($team),
                'leadership_score' => $this->analyticsService->calculateTeamEfficiency($team),
                'git_contribution' => $this->analyticsService->calculateAverageGitContribution($team),
            ];
        }

        return response()->json($chartData);
    }

    /**
     * Return JSON data for the Leaderboard table.
     */
    public function leaderboard()
    {
        $leaderboard = $this->analyticsService->getLeaderboard();
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

            return response()->json([
                'success' => true,
                'report' => $report,
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

        return redirect(route('teams.dashboard') . '#management')->with('success', 'Team created successfully with ' . count($request->employee_ids ?? []) . ' members.');
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

        return redirect(route('teams.dashboard') . '#management')->with('success', 'Team updated successfully.');
    }

    public function destroyTeam($teamName)
    {
        $team = \App\Models\Team::findOrFail($teamName);
        
        // Remove this team from all associated employees (setting it to null)
        \App\Models\Employee::where('team', $team->name)->update(['team' => null]);
        
        $team->delete();

        return redirect(route('teams.dashboard') . '#management')->with('success', 'Team deleted successfully.');
    }

    public function addMember(Request $request, $teamName)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
        ]);

        $team = \App\Models\Team::findOrFail($teamName);
        $employee = \App\Models\Employee::findOrFail($request->employee_id);
        
        $employee->update(['team' => $team->name]);

        return redirect(route('teams.dashboard') . '#management')->with('success', 'Member added to team.');
    }

    public function removeMember($teamName, $employeeId)
    {
        $employee = \App\Models\Employee::findOrFail($employeeId);
        
        if ($employee->team === $teamName) {
            $employee->update(['team' => null]);
        }

        return redirect(route('teams.dashboard') . '#management')->with('success', 'Member removed from team.');
    }

    public function show($teamName)
    {
        $team = \App\Models\Team::with(['employees' => function($q) {
            $q->with('performance_reports');
        }])->findOrFail($teamName);

        return view('team-details', compact('team'));
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
