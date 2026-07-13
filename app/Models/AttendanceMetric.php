<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceMetric extends Model
{
    protected $fillable = [
        'employee_id',
        'total_expected_days',
        'present_days',
        'absent_days',
        'late_days',
        'leave_days',
        'attendance_percentage',
        'punctuality_score',
        'leave_utilization_score',
        'attendance_score',
        'attendance_category',
    ];

    protected $casts = [
        'attendance_percentage' => 'float',
        'punctuality_score' => 'float',
        'leave_utilization_score' => 'float',
        'attendance_score' => 'float',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
