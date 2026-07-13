<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkloadMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'active_tasks',
        'completed_tasks',
        'overdue_tasks',
        'workload_percentage',
        'status',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
