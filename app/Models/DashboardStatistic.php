<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardStatistic extends Model
{
    protected $fillable = [
        'total_employees',
        'total_tasks',
        'completed_tasks',
        'pending_tasks',
        'in_progress_tasks',
        'late_tasks',
        'recent_tasks',
    ];

    protected $casts = [
        'recent_tasks' => 'array',
    ];
}
