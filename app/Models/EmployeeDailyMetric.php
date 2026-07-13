<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDailyMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'total_tasks',
        'completed_tasks',
        'on_time_tasks',
        'total_attendances',
        'present_days',
        'git_commit_count',
        'project_count',
        'task_completion_score',
        'task_quality_score',
        'attendance_score',
        'gitlab_score',
        'overall_score',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
