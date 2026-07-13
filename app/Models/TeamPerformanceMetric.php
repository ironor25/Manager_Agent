<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamPerformanceMetric extends Model
{
    protected $table = 'team_performance_metrics';

    protected $fillable = [
        'period',
        'team_name',
        'attendance_score',
        'task_completion_score',
        'leadership_score',
        'git_contribution_score',
        'top_performers',
    ];

    protected $casts = [
        'top_performers' => 'array',
    ];
}
