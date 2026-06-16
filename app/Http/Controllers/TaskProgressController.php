<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TaskProgressController extends Controller
{
    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $validated = $request->validate([
            'started_at' => 'nullable|date',
            'completed_at' => 'nullable|date',
        ]);

        if (isset($validated['started_at'])) {
            $task->started_at = Carbon::parse($validated['started_at']);
        }

        if (isset($validated['completed_at'])) {
            $task->completed_at = Carbon::parse($validated['completed_at']);
            $task->status = 'completed'; // Auto-update status if completed_at is provided
        } elseif (isset($validated['started_at'])) {
            $task->status = 'in_progress';
        }

        // Calculate actual hours if both started_at and completed_at are present
        if ($task->started_at && $task->completed_at) {
            $task->actual_hours = round($task->started_at->diffInMinutes($task->completed_at) / 60, 2);
        }

        // Calculate delay hours if deadline_at and completed_at are present
        if ($task->deadline_at && $task->completed_at) {
            if ($task->completed_at->greaterThan($task->deadline_at)) {
                $task->delay_hours = round($task->deadline_at->diffInMinutes($task->completed_at) / 60, 2);
            } else {
                $task->delay_hours = 0;
            }
        }

        $task->save();

        return response()->json([
            'message' => 'Task progress updated successfully',
            'data' => $task
        ], 200);
    }
}
