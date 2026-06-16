<?php

namespace App\Http\Controllers;

use App\Models\MeetingNote;
use Illuminate\Http\Request;

class MeetingNoteController extends Controller
{
    public function index()
    {
        $meetings = MeetingNote::with('employee')->latest('meeting_date')->paginate(10);
        return view('meetings', compact('meetings'));
    }
}
