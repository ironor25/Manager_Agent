<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkloadTrend extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'average_workload',
        'underutilized_count',
        'optimal_count',
        'busy_count',
        'overloaded_count',
    ];
}
