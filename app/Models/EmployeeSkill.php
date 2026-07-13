<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class EmployeeSkill extends Pivot
{
    protected $table = 'employee_skill';
    
    protected $fillable = ['employee_id', 'skill_id', 'proficiency_level'];
}
