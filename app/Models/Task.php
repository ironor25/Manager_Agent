<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::saving(function ($task) {
            if ($task->isDirty('status')) {
                if (in_array($task->status, ['completed', 'late_completed'])) {
                    if (is_null($task->completed_at)) {
                        $task->completed_at = now();
                    }
                } else {
                    $task->completed_at = null;
                }
            }
        });
    }

    protected $fillable = [
        'employee_id',
        'title',
        'description',
        'status',
        'assigned_at',
        'started_at',
        'deadline_at',
        'completed_at',
        'estimated_hours',
        'actual_hours',
        'delay_hours',
        'priority',
        'depends_on_task_id',
        'project_id',
        'timer_started_at',
        'timer_accumulated_seconds',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'deadline_at' => 'datetime',
        'completed_at' => 'datetime',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'delay_hours' => 'decimal:2',
        'timer_started_at' => 'datetime',
        'timer_accumulated_seconds' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function dependsOnTask()
    {
        return $this->belongsTo(Task::class, 'depends_on_task_id');
    }

    public function dependentTasks()
    {
        return $this->hasMany(Task::class, 'depends_on_task_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
