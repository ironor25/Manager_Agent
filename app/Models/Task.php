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
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'deadline_at' => 'datetime',
        'completed_at' => 'datetime',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'delay_hours' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
