<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeePeriodMetric extends Model
{
    protected $guarded = [];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
