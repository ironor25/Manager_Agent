<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\ValidateApiKey;
use App\Http\Controllers\TaskProgressController;
use App\Models\Employee;
use App\Models\Task;
use App\Models\Attendance;
use App\Models\Performance_report;
use App\Models\TeamReport;

Route::middleware([ValidateApiKey::class])->group(function () {
    // Read Endpoints
    Route::get('/employees', function () {
        return response()->json(['data' => Employee::get()]);
    });

    Route::get('/teams', function () {
        $teams = Employee::whereNotNull('team')
            ->select('team as name')
            ->distinct()
            ->get();
        return response()->json(['data' => $teams]);
    });

    Route::get('/leaderboard', function () {
        $leaderboard = Performance_report::with('employee')
            ->orderBy('leadership_score', 'desc')
            ->take(10)
            ->get()
            ->map(function ($report) {
                return [
                    'employee_id' => $report->employee_id,
                    'name' => $report->employee->name ?? 'Unknown',
                    'score' => $report->leadership_score,
                ];
            });
        return response()->json(['data' => $leaderboard]);
    });

    Route::get('/team/{team}/report', function ($team) {
        $report = TeamReport::where('team_name', $team)->latest()->first();
        if (!$report) {
            return response()->json(['message' => 'Report not found', 'status' => 'error'], 404);
        }
        return response()->json(['data' => $report, 'status' => 'success']);
    });

    Route::get('/employee/{id}/report', function ($id) {
        $report = Performance_report::with('employee')->where('employee_id', $id)->latest()->first();
        if (!$report) {
            return response()->json(['message' => 'Report not found', 'status' => 'error'], 404);
        }
        return response()->json(['data' => $report, 'status' => 'success']);
    });

    // Ingestion Endpoints (Write)
    Route::post('/tasks', function (Request $request) {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
            'assigned_at' => 'nullable|date',
            'started_at' => 'nullable|date',
            'deadline_at' => 'nullable|date',
            'completed_at' => 'nullable|date',
            'estimated_hours' => 'nullable|numeric',
            'actual_hours' => 'nullable|numeric',
            'delay_hours' => 'nullable|numeric',
            'priority' => 'nullable|string',
        ]);
        
        $task = Task::create($validated);
        return response()->json(['message' => 'Task ingested successfully', 'data' => $task], 201);
    });

    Route::put('/tasks/{id}/progress', [TaskProgressController::class, 'update']);

    Route::post('/attendance', function (Request $request) {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'login_time' => 'required|date',
            'logout_time' => 'nullable|date',
            'late_flag' => 'boolean',
            'leave_flag' => 'boolean',
        ]);
        
        $attendance = Attendance::create($validated);
        return response()->json(['message' => 'Attendance data ingested successfully', 'data' => $attendance], 201);
    });

    Route::post('/employees', function (Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'team' => 'required|string|max:255',
        ]);
        
        $employee = Employee::create($validated);
        return response()->json(['message' => 'Employee ingested successfully', 'data' => $employee], 201);
    });
});
