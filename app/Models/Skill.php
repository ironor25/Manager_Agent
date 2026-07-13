<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $fillable = ['name', 'category'];

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_skill')
                    ->withPivot('proficiency_level')
                    ->withTimestamps();
    }
}
