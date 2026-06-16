<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GithubCommit extends Model
{
    /** @use HasFactory<\Database\Factories\GithubCommitFactory> */
    use HasFactory;

    protected $fillable = ['employee_id', 'repo_name', 'commit_hash', 'commit_message', 'commit_date'];

    protected $casts = [
        'commit_date' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
