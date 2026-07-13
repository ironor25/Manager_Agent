<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectMetric extends Model
{
    protected $fillable = [
        'project_id',
        'total_tasks',
        'completed_tasks',
        'overdue_tasks',
        'total_estimated_hours',
        'total_actual_hours',
        'completion_percentage',
        'health_score',
        'health_status',
    ];

    protected $casts = [
        'total_estimated_hours' => 'decimal:2',
        'total_actual_hours' => 'decimal:2',
        'completion_percentage' => 'float',
        'health_score' => 'float',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
