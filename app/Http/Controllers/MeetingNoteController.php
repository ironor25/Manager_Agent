<?php

namespace App\Http\Controllers;

use App\Models\MeetingNote;
use Illuminate\Http\Request;
use App\Traits\DateFilterable;

class MeetingNoteController extends Controller
{
    use DateFilterable;

    public function index(Request $request)
    {
        $query = MeetingNote::with('employee');

        $filter = $request->get('date_filter', 'all_time');
        [$start, $end] = $this->getDateRange($filter, $request->get('start_date'), $request->get('end_date'));

        if ($filter !== 'all_time' && $start && $end) {
            $query->whereBetween('meeting_date', [$start, $end]);
        }

        $meetings = $query->latest('meeting_date')->paginate(10)->withQueryString();
        return view('meetings', compact('meetings'));
    }
}
