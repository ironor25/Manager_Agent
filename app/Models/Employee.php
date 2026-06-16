<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'team',
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

    public function teamDetails()
    {
        return $this->belongsTo(Team::class, 'team', 'name');
    }
}
