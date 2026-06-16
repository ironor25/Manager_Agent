<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Employee;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::with('employee')->latest()->get();
        $employees = Employee::all();
        return view('tasks', compact('tasks', 'employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'employee_id' => 'required|exists:employees,id',
            'deadline_at' => 'nullable|date',
            'priority' => 'required|string|in:low,normal,high',
            'status' => 'required|string|in:pending,in_progress,completed',
        ]);

        Task::create($validated);

        return redirect()->route('tasks.index')->with('success', 'Task assigned successfully.');
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'employee_id' => 'required|exists:employees,id',
            'deadline_at' => 'nullable|date',
            'priority' => 'required|string|in:low,normal,high',
            'status' => 'required|string|in:pending,in_progress,completed',
        ]);

        $task->update($validated);

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
    }
}
