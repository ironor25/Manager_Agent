<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskMetricsTrend extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'tasks_assigned',
        'tasks_completed',
        'tasks_delayed',
        'avg_completion_time_hours',
    ];
}
