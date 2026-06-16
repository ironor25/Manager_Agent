<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Task;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('main-dashboard');
    }

    public function getDashboardData()
    {
        $totalEmployees = Employee::count();
        $totalTasks = Task::count();
        $completedTasks = Task::where('status', 'completed')->count();
        
        $pendingTasks = Task::where('status', 'pending')->count();
        $inProgressTasks = Task::where('status', 'in_progress')->count();
        $lateTasks = Task::where('deadline_at', '<', now())->where('status', '!=', 'completed')->count();

        $recentTasks = Task::with('employee')->latest()->take(5)->get()->map(function($task) {
            return [
                'title' => $task->title,
                'created_at_human' => $task->created_at ? $task->created_at->diffForHumans() : 'Unknown',
                'employee_initial' => substr($task->employee->name ?? '?', 0, 1),
                'employee_name' => $task->employee->name ?? 'Unknown',
                'status' => $task->status,
                'status_formatted' => str_replace('_', ' ', ucfirst($task->status))
            ];
        });

        return response()->json([
            'totalEmployees' => $totalEmployees,
            'totalTasks' => $totalTasks,
            'completedTasks' => $completedTasks,
            'pendingTasks' => $pendingTasks,
            'inProgressTasks' => $inProgressTasks,
            'lateTasks' => $lateTasks,
            'recentTasks' => $recentTasks,
        ]);
    }
}
