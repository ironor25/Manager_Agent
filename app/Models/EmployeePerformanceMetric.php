<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeePerformanceMetric extends Model
{
    protected $table = 'employee_performance_metrics';

    protected $fillable = [
        'employee_id',
        'task_completion_rate',
        'on_time_completion_rate',
        'attendance_score',
        'git_contribution_score',
        'final_score',
        'total_tasks',
        'completed_tasks',
        'late_tasks',
        'total_attendance_records',
        'git_commit_count',
        'recent_commits',
        'commit_chart_data',
    ];

    protected $casts = [
        'recent_commits' => 'array',
        'commit_chart_data' => 'array',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
