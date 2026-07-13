<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'name',
        'description',
        'status',
        'category',
        'team_name',
        'manager_id',
        'start_date',
        'end_date',
        'budget_hours',
        'is_archived',
        'repo_name',
        'repo_url',
        'requirements',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'budget_hours' => 'decimal:2',
        'is_archived' => 'boolean',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_name', 'name');
    }

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'project_employee');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function metric()
    {
        return $this->hasOne(ProjectMetric::class);
    }
}
