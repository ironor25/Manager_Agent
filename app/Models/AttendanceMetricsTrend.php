<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceMetricsTrend extends Model
{
    protected $fillable = [
        'date',
        'present_count',
        'absent_count',
        'late_count',
        'leave_count',
        'avg_attendance_percentage',
    ];

    protected $casts = [
        'date' => 'date',
        'avg_attendance_percentage' => 'float',
    ];
}
