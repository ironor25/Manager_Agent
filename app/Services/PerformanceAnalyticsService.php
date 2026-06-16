<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Task;
use App\Models\Attendance;
use Illuminate\Support\Collection;

class PerformanceAnalyticsService
{
    public const WEIGHTS = [
        'task_completion' => 0.40,
        'on_time_delivery' => 0.20,
        'attendance' => 0.20,
        'git_contribution' => 0.20,
    ];
    
    public static function calculateEmployeeScore(int $employeeId, ?int $gitCommitCount = null): array
    {
        $employee = self::getEmployee($employeeId);
        $tasks = $employee->tasks()->get();
        $attendances = $employee->attendances()->get();

        $completedTasks = self::getCompletedTasks($tasks);
        $lateTasks = self::getLateTasks($completedTasks);

        $taskCompletionRate = self::calculateCompletionRate($tasks, $completedTasks);
        $onTimeCompletionRate = self::calculateOnTimeCompletionRate($completedTasks, $lateTasks);
        $attendanceScore = self::calculateAttendanceScore($attendances);
        $gitCommitCount = $employee->github_commits()->count();
        $gitContributionScore = self::calculateGitContributionScore($completedTasks, $onTimeCompletionRate, $gitCommitCount);
        $finalScore = self::calculateFinalScore($taskCompletionRate, $onTimeCompletionRate, $attendanceScore, $gitContributionScore);

        return [
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
            'task_completion_rate' => round($taskCompletionRate, 2),
            'on_time_completion_rate' => round($onTimeCompletionRate, 2),
            'attendance_score' => round($attendanceScore, 2),
            'git_contribution_score' => round($gitContributionScore, 2),
            'final_leadership_score' => round($finalScore, 2),
            'total_tasks' => $tasks->count(),
            'completed_tasks' => $completedTasks->count(),
            'late_tasks' => $lateTasks->count(),
            'total_attendance_records' => $attendances->count(),
            'meeting_notes' => $employee->meeting_notes()->pluck('notes_text')->toArray(),
            'git_commit_count' => $gitCommitCount,
            'recent_commits' => $employee->github_commits()->latest('commit_date')->take(10)->pluck('commit_message')->toArray(),
            'commit_chart_data' => self::getCommitChartData($employee),
        ];
    }

    protected static function getEmployee(int $employeeId): Employee
    {
        return Employee::findOrFail($employeeId);
    }

    protected static function getCompletedTasks(Collection $tasks): Collection
    {
        return $tasks->filter(fn (Task $task) => 
            !is_null($task->completed_at) || 
            in_array($task->status, ['completed', 'late_completed'])
        );
    }

    protected static function getLateTasks(Collection $completedTasks): Collection
    {
        return $completedTasks->filter(fn (Task $task) => self::isTaskLate($task));
    }

    protected static function calculateCompletionRate(Collection $tasks, Collection $completedTasks): float
    {
        $total = $tasks->count();

        if ($total === 0) {
            return 0.0;
        }

        return ($completedTasks->count() / $total) * 100;
    }

    protected static function calculateOnTimeCompletionRate(Collection $completedTasks, Collection $lateTasks): float
    {
        $completed = $completedTasks->count();

        if ($completed === 0) {
            return 0.0;
        }

        $onTime = max(0, $completed - $lateTasks->count());

        return ($onTime / $completed) * 100;
    }

    protected static function isTaskLate(Task $task): bool
    {
        if ($task->delay_hours !== null && $task->delay_hours > 0) {
            return true;
        }

        if ($task->deadline_at === null) {
            return false;
        }

        $completedAt = $task->completed_at;
        if ($completedAt === null) {
            if (in_array($task->status, ['completed', 'late_completed'])) {
                $completedAt = $task->updated_at;
            } else {
                return false;
            }
        }

        return $completedAt->greaterThan($task->deadline_at);
    }

    protected static function calculateAttendanceScore(Collection $attendances): float
    {
        $total = $attendances->count();

        if ($total === 0) {
            return 0.0;
        }

        $excellentDays = $attendances->filter(fn (Attendance $attendance) => !$attendance->leave_flag && !$attendance->late_flag)->count();

        return ($excellentDays / $total) * 100;
    }

    protected static function calculateGitContributionScore(Collection $completedTasks, float $onTimeCompletionRate, ?int $gitCommitCount = null): float
    {
        if ($gitCommitCount !== null) {
            return self::normalizeGitCommits($gitCommitCount);
        }

        // Fallback to a derived score when git metadata is not available in the current schema.
        $accuracyScore = self::calculateEstimateAccuracyScore($completedTasks);

        return ($onTimeCompletionRate * 0.6) + ($accuracyScore * 0.4);
    }

    protected static function normalizeGitCommits(int $commitCount): float
    {
        return min(100.0, max(0.0, ($commitCount / 50) * 100));
    }

    protected static function calculateEstimateAccuracyScore(Collection $completedTasks): float
    {
        $total = $completedTasks->count();

        if ($total === 0) {
            return 0.0;
        }

        $accurateTasks = $completedTasks->filter(fn (Task $task) =>
            $task->actual_hours !== null
            && $task->estimated_hours !== null
            && $task->actual_hours <= $task->estimated_hours
        )->count();

        return ($accurateTasks / $total) * 100;
    }

    protected static function calculateFinalScore(float $completionRate, float $onTimeRate, float $attendanceScore, float $gitScore = 0): float
    {
        return (
            ($completionRate * self::WEIGHTS['task_completion']) +
            ($onTimeRate * self::WEIGHTS['on_time_delivery']) +
            ($attendanceScore * self::WEIGHTS['attendance']) +
            ($gitScore * self::WEIGHTS['git_contribution'])
        );
    }

    protected static function getCommitChartData(Employee $employee): array
    {
        $commits = $employee->github_commits()
            ->where('commit_date', '>=', now()->subDays(30))
            ->orderBy('commit_date')
            ->get();

        $data = [];
        // Initialize last 30 days with 0
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $data[$date] = 0;
        }

        foreach ($commits as $commit) {
            $date = $commit->commit_date->format('Y-m-d');
            if (isset($data[$date])) {
                $data[$date]++;
            }
        }

        return [
            'labels' => array_keys($data),
            'values' => array_values($data),
        ];
    }
}
