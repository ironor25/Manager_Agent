<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Performance_report extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leadership_score',
        'summary',
        'strengths',
        'weaknesses',
        'recommendations',
    ];

    protected $casts = [
        'strengths' => 'array',
        'weaknesses' => 'array',
        'recommendations' => 'array',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
