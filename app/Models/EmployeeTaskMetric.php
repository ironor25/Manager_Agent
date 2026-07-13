<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeTaskMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'tasks_assigned',
        'tasks_completed',
        'tasks_delayed',
        'completion_rate',
        'avg_completion_time_hours',
        'productivity_score',
        'productivity_category',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
