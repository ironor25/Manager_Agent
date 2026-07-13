<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectMetricsTrend extends Model
{
    protected $fillable = [
        'date',
        'active_projects',
        'at_risk_projects',
        'critical_projects',
        'avg_completion_percentage',
    ];

    protected $casts = [
        'date' => 'date',
        'avg_completion_percentage' => 'float',
    ];
}
