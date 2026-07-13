<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\AttendanceMetric;
use App\Models\AttendanceMetricsTrend;
use App\Models\EmployeePeriodMetric;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\DateFilterable;

class AttendanceController extends Controller
{
    use DateFilterable;
    /**
     * Display Attendance Management.
     */
    public function index()
    {
        $employees = Employee::select('id', 'name')->get();
        return view('attendance.management', compact('employees'));
    }

    /**
     * Get DataTable data for Management.
     */
    public function getAttendances(Request $request)
    {
        if ($request->ajax()) {
            $data = Attendance::with('employee')->select('attendances.*');
            
            return \Yajra\DataTables\Facades\DataTables::of($data)
                ->addColumn('employee_name', function($row) {
                    return $row->employee->name ?? 'Unknown';
                })
                ->addColumn('team', function($row) {
                    return $row->employee->team ?? 'None';
                })
                ->addColumn('working_hours', function($row) {
                    if ($row->login_time && $row->logout_time) {
                        return round($row->logout_time->diffInMinutes($row->login_time) / 60, 1);
                    }
                    return 0;
                })
                ->addColumn('attendance_status', function($row) {
                    if ($row->leave_flag) return '<span class="badge bg-secondary">On Leave</span>';
                    if ($row->late_flag) return '<span class="badge bg-warning">Late</span>';
                    if ($row->logout_time) return '<span class="badge bg-success">Present</span>';
                    return '<span class="badge bg-primary">Checked In</span>';
                })
                ->addColumn('action', function($row) {
                    $json = htmlspecialchars(json_encode([
                        'id'            => $row->id,
                        'employee_id'   => $row->employee_id,
                        'employee_name' => $row->employee->name ?? 'Unknown',
                        'date'          => $row->date ? $row->date->format('Y-m-d') : ($row->created_at ? $row->created_at->format('Y-m-d') : null),
                        'login_time'    => $row->login_time ? $row->login_time->format('H:i') : null,
                        'logout_time'   => $row->logout_time ? $row->logout_time->format('H:i') : null,
                        'late_flag'     => $row->late_flag,
                        'leave_flag'    => $row->leave_flag,
                    ]), ENT_QUOTES, 'UTF-8');
                    return '
                        <div class="d-flex justify-content-end align-items-center gap-2">
                            <button class="action-btn edit-btn" data-record="'.$json.'" title="Edit">
                                <i class="fa-solid fa-pen text-primary"></i>
                            </button>
                            <form action="'.route('attendance.destroy', $row->id).'" method="POST" class="d-inline" onsubmit="return confirm(\'Delete this record?\')">
                                '.csrf_field().method_field('DELETE').'
                                <button type="submit" class="action-btn delete-btn" title="Delete">
                                    <i class="fa-solid fa-trash text-danger"></i>
                                </button>
                            </form>
                        </div>
                    ';
                })
                ->addColumn('date_display', function($row) {
                    // Prefer the attendance date column; fall back to created_at
                    if ($row->date) return $row->date->format('Y-m-d');
                    return $row->created_at ? $row->created_at->format('Y-m-d') : '-';
                })
                ->rawColumns(['attendance_status', 'action'])
                ->make(true);
        }
    }

    /**
     * Store new record.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'login_time' => 'nullable|date_format:H:i',
            'logout_time' => 'nullable|date_format:H:i',
        ]);

        $data = $request->all();
        $data['late_flag'] = $request->has('late_flag');
        $data['leave_flag'] = $request->has('leave_flag');

        if ($request->login_time) {
            $data['login_time'] = Carbon::parse($request->date . ' ' . $request->login_time);
        }
        if ($request->logout_time) {
            $data['logout_time'] = Carbon::parse($request->date . ' ' . $request->logout_time);
        }

        Attendance::create($data);

        return redirect()->route('attendance.index')->with('success', 'Attendance record added.');
    }

    /**
     * Update record.
     */
    public function update(Request $request, Attendance $attendance)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'login_time' => 'nullable|date_format:H:i',
            'logout_time' => 'nullable|date_format:H:i',
        ]);

        $data = $request->all();
        $data['late_flag'] = $request->has('late_flag');
        $data['leave_flag'] = $request->has('leave_flag');

        if ($request->login_time) {
            $data['login_time'] = Carbon::parse($request->date . ' ' . $request->login_time);
        } else {
            $data['login_time'] = null;
        }
        
        if ($request->logout_time) {
            $data['logout_time'] = Carbon::parse($request->date . ' ' . $request->logout_time);
        } else {
            $data['logout_time'] = null;
        }

        $attendance->update($data);

        return redirect()->route('attendance.index')->with('success', 'Attendance record updated.');
    }

    /**
     * Destroy record.
     */
    public function destroy(Attendance $attendance)
    {
        $attendance->delete();
        return redirect()->route('attendance.index')->with('success', 'Attendance record deleted.');
    }

    /**
     * Display Attendance Analytics.
     */
    public function analytics()
    {
        return view('attendance.analytics');
    }

    /**
     * Get JSON data for Analytics.
     */
    public function getAnalyticsData(Request $request)
    {
        $filter = $request->get('date_filter', 'all_time');
        [$start, $end] = $this->getDateRange($filter, $request->get('start_date'), $request->get('end_date'));

        if ($filter === 'all_time') {
            $todayStr = Carbon::today()->toDateString();
            $todayTrend = AttendanceMetricsTrend::where('date', $todayStr)->first();

            // If command hasn't run today, provide fallbacks
            $presentCount = $todayTrend->present_count ?? 0;
            $absentCount = $todayTrend->absent_count ?? 0;
            $lateCount = $todayTrend->late_count ?? 0;
            $leaveCount = $todayTrend->leave_count ?? 0;
            $avgAttendance = $todayTrend->avg_attendance_percentage ?? 0;

            $metrics = AttendanceMetric::all();
            $avgScore = $metrics->count() > 0 ? $metrics->avg('attendance_score') : 0;

            // Daily Attendance Trend (last 14 days)
            $trends = AttendanceMetricsTrend::orderBy('date', 'desc')->take(14)->get()->reverse();
            $dates = $trends->pluck('date')->map(function($d) { return Carbon::parse($d)->format('M d'); });
            $trendPresent = $trends->pluck('present_count');
            $trendAbsent = $trends->pluck('absent_count');

            // Distribution (Categories)
            $categories = AttendanceMetric::select('attendance_category', DB::raw('count(*) as count'))
                ->groupBy('attendance_category')
                ->pluck('count', 'attendance_category')->toArray();
        } else {
            $trendQuery = AttendanceMetricsTrend::whereBetween('date', [$start->toDateString(), $end->toDateString()]);
            
            $presentCount = (clone $trendQuery)->sum('present_count');
            $absentCount = (clone $trendQuery)->sum('absent_count');
            $lateCount = (clone $trendQuery)->sum('late_count');
            $leaveCount = (clone $trendQuery)->sum('leave_count');
            $avgAttendance = (clone $trendQuery)->avg('avg_attendance_percentage') ?? 0;

            // Compute score from raw attendance
            $agg = DB::table('attendances')
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->selectRaw("
                    employee_id,
                    count(*) as total,
                    sum(case when logout_time is not null and leave_flag = 0 then 1 else 0 end) as present
                ")
                ->groupBy('employee_id')
                ->get();
            $avgScore = $agg->count() > 0 ? $agg->avg(function($r) { return ($r->present / max($r->total, 1)) * 100; }) : 0;

            // Daily Attendance Trend
            $trends = AttendanceMetricsTrend::whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->orderBy('date', 'asc')->get();
            $dates = $trends->pluck('date')->map(function($d) { return Carbon::parse($d)->format('M d'); });
            $trendPresent = $trends->pluck('present_count');
            $trendAbsent = $trends->pluck('absent_count');

            // Distribution (Categories) 
            $categories = [
                'Excellent' => 0, 'Good' => 0, 'Average' => 0, 'Needs Attention' => 0
            ];
            foreach ($agg as $r) {
                $pct = ($r->present / max($r->total, 1)) * 100;
                if ($pct >= 95) $categories['Excellent']++;
                elseif ($pct >= 85) $categories['Good']++;
                elseif ($pct >= 75) $categories['Average']++;
                else $categories['Needs Attention']++;
            }
            $categories = array_filter($categories, function($v) { return $v > 0; });
        }

        return response()->json([
            'kpis' => [
                'present' => $presentCount,
                'absent' => $absentCount,
                'late' => $lateCount,
                'leave' => $leaveCount,
                'avg_percentage' => round($avgAttendance, 1),
                'avg_score' => round($avgScore, 1)
            ],
            'charts' => [
                'trend' => [
                    'labels' => $dates,
                    'present' => $trendPresent,
                    'absent' => $trendAbsent
                ],
                'categories' => $categories
            ]
        ]);
    }

    /**
     * Display Attendance Reports.
     */
    public function reports()
    {
        return view('attendance.reports');
    }

    /**
     * Get DataTable data for Reports.
     */
    public function getReportsData(Request $request)
    {
        if ($request->ajax()) {
            $filter = $request->get('date_filter', 'all_time');
            [$start, $end] = $this->getDateRange($filter, $request->get('start_date'), $request->get('end_date'));

            if ($filter === 'all_time') {
                $data = AttendanceMetric::with(['employee'])->select('attendance_metrics.*');
                
                return \Yajra\DataTables\Facades\DataTables::of($data)
                    ->addColumn('employee_name', function($row) {
                        return $row->employee->name ?? 'Unknown';
                    })
                    ->addColumn('team', function($row) {
                        return $row->employee->team ?? 'None';
                    })
                    ->addColumn('attendance_percentage', function($row) {
                        $color = $row->attendance_percentage >= 80 ? 'success' : ($row->attendance_percentage >= 60 ? 'warning' : 'danger');
                        return '<span class="text-'.$color.' fw-bold">'.round($row->attendance_percentage).'%</span>';
                    })
                    ->addColumn('attendance_score', function($row) {
                        return '<strong>'.round($row->attendance_score).'</strong>';
                    })
                    ->addColumn('category_badge', function($row) {
                        $colors = [
                            'Excellent' => 'success',
                            'Good' => 'primary',
                            'Average' => 'warning',
                            'Needs Attention' => 'danger'
                        ];
                        $color = $colors[$row->attendance_category] ?? 'secondary';
                        return '<span class="badge bg-'.$color.'">'.$row->attendance_category.'</span>';
                    })
                    ->rawColumns(['attendance_percentage', 'attendance_score', 'category_badge'])
                    ->make(true);
            }

            // Map filter to period_metrics period
            $periodMap = [
                '7_days'  => '7_days',
                '30_days' => '30_days',
            ];
            $period = $periodMap[$filter] ?? null;

            if ($period) {
                // Fast path: use precomputed employee_period_metrics
                $data = DB::table('employee_period_metrics as epm')
                    ->join('employees as e', 'e.id', '=', 'epm.employee_id')
                    ->where('epm.period', $period)
                    ->select(
                        'e.name as employee_name',
                        'e.team',
                        'epm.present_days',
                        DB::raw('(epm.total_attendances - epm.present_days) as absent_days'),
                        DB::raw('0 as late_days'),
                        DB::raw('0 as leave_days'),
                        'epm.attendance_score',
                        DB::raw('epm.attendance_score as attendance_percentage')
                    )
                    ->get()
                    ->map(function($row) {
                        $pct = $row->attendance_percentage;
                        $cat = 'Needs Attention';
                        if ($pct >= 95) $cat = 'Excellent';
                        elseif ($pct >= 85) $cat = 'Good';
                        elseif ($pct >= 75) $cat = 'Average';

                        return (object)[
                            'employee_name'       => $row->employee_name,
                            'team'                => $row->team,
                            'present_days'        => $row->present_days,
                            'absent_days'         => $row->absent_days,
                            'late_days'           => $row->late_days,
                            'leave_days'          => $row->leave_days,
                            'attendance_percentage' => $pct,
                            'attendance_score'    => $row->attendance_score,
                            'attendance_category' => $cat,
                        ];
                    });
            } else {
                // Custom range: dynamic aggregation from raw attendances
                $attendances = DB::table('attendances')
                    ->join('employees', 'attendances.employee_id', '=', 'employees.id')
                    ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                    ->selectRaw("
                        employees.id as employee_id,
                        employees.name as employee_name,
                        employees.team,
                        count(*) as total_days,
                        sum(case when logout_time is not null and leave_flag = 0 then 1 else 0 end) as present_days,
                        sum(case when logout_time is null and leave_flag = 0 then 1 else 0 end) as absent_days,
                        sum(case when late_flag = 1 then 1 else 0 end) as late_days,
                        sum(case when leave_flag = 1 then 1 else 0 end) as leave_days
                    ")
                    ->groupBy('employees.id', 'employees.name', 'employees.team')
                    ->get();

                $data = $attendances->map(function($row) {
                    $total = max($row->total_days, 1);
                    $pct   = ($row->present_days / $total) * 100;
                    $score = max(0, min(100, $pct - ($row->late_days * 2)));

                    $cat = 'Needs Attention';
                    if ($pct >= 95) $cat = 'Excellent';
                    elseif ($pct >= 85) $cat = 'Good';
                    elseif ($pct >= 75) $cat = 'Average';

                    return (object)[
                        'employee_name'         => $row->employee_name,
                        'team'                  => $row->team,
                        'present_days'          => $row->present_days,
                        'absent_days'           => $row->absent_days,
                        'late_days'             => $row->late_days,
                        'leave_days'            => $row->leave_days,
                        'attendance_percentage' => $pct,
                        'attendance_score'      => $score,
                        'attendance_category'   => $cat
                    ];
                });
            }

            return \Yajra\DataTables\Facades\DataTables::of($data)
                ->addColumn('attendance_percentage', function($row) {
                    $color = $row->attendance_percentage >= 80 ? 'success' : ($row->attendance_percentage >= 60 ? 'warning' : 'danger');
                    return '<span class="text-'.$color.' fw-bold">'.round($row->attendance_percentage).'%</span>';
                })
                ->addColumn('attendance_score', function($row) {
                    return '<strong>'.round($row->attendance_score).'</strong>';
                })
                ->addColumn('category_badge', function($row) {
                    $colors = [
                        'Excellent'       => 'success',
                        'Good'            => 'primary',
                        'Average'         => 'warning',
                        'Needs Attention' => 'danger'
                    ];
                    $color = $colors[$row->attendance_category] ?? 'secondary';
                    return '<span class="badge bg-'.$color.'">'.$row->attendance_category.'</span>';
                })
                ->rawColumns(['attendance_percentage', 'attendance_score', 'category_badge'])
                ->make(true);
        }
    }
}
