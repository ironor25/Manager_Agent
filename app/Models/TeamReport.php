<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamReport extends Model
{
    protected $fillable = [
        'team_name',
        'efficiency_score',
        'top_performer_id',
        'top_performer_name',
        'summary',
        'strengths',
        'weaknesses',
        'recommendations',
    ];

    protected function casts(): array
    {
        return [
            'strengths' => 'array',
            'weaknesses' => 'array',
            'recommendations' => 'array',
        ];
    }
}
