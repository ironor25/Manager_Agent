<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'password',
        'photo',
        'team',
        'designation',
        'status',
        'join_date',
    ];

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function performance_reports()
    {
        return $this->hasMany(Performance_report::class);
    }

    public function github_commits()
    {
        return $this->hasMany(GithubCommit::class);
    }

    public function meeting_notes()
    {
        return $this->hasMany(MeetingNote::class);
    }

    public function teamMetrics()
    {
        return $this->hasMany(TeamPerformanceMetric::class, 'team_name', 'team');
    }

    public function attendanceMetric()
    {
        return $this->hasOne(AttendanceMetric::class);
    }

    public function teamDetails()
    {
        return $this->belongsTo(Team::class, 'team', 'name');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_employee');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'employee_skill')
                    ->withPivot('proficiency_level')
                    ->withTimestamps();
    }
}
