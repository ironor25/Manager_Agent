<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingNote extends Model
{
    /** @use HasFactory<\Database\Factories\MeetingNoteFactory> */
    use HasFactory;

    protected $fillable = ['employee_id', 'notes_text', 'meeting_date'];

    protected $casts = [
        'meeting_date' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
