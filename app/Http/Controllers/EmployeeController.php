<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Task;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Get employees data for DataTables.
     */
    public function getEmployees(Request $request)
    {
        if ($request->ajax()) {
            $data = Employee::select(['id', 'name', 'email', 'password', 'photo', 'team', 'status', 'created_at', 'user_id']);
            return \Yajra\DataTables\Facades\DataTables::of($data)
                ->addColumn('status_badge', function($row) {
                    $colors = ['active' => 'success', 'inactive' => 'danger', 'on_leave' => 'secondary'];
                    $color = $colors[$row->status] ?? 'primary';
                    return '<span class="badge bg-'.$color.'">'.ucfirst(str_replace('_', ' ', $row->status)).'</span>';
                })
                ->addColumn('joining_date', function($row) {
                    return $row->created_at ? $row->created_at->format('M d, Y') : 'N/A';
                })
                ->addColumn('action', function($row){
                    $empJson = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
                    return '
                        <div class="d-flex gap-1 justify-content-end align-items-center">
                            <a href="'.route('employees.profile', $row->id).'" class="action-btn view-performance-btn" title="View Profile">
                                <i class="fa-solid fa-user-circle text-primary"></i>
                            </a>
                            <button class="action-btn edit-emp-btn" data-emp="'.$empJson.'" title="Edit Employee">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button type="button" class="action-btn delete-btn trigger-delete-emp" data-action="'.route('employees.destroy', $row->id).'" title="Delete Employee">
                                <i class="fa-solid fa-trash text-danger"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }
    }

    public function search(Request $request)
    {
        $query = Employee::query();
        if ($request->has('q') && !empty($request->q)) {
            $searchTerm = $request->q;
            $query->where('name', 'like', '%' . $searchTerm . '%');
        }

        $employees = $query->select('id', 'name', 'team')->limit(20)->get();

        return response()->json($employees);
    }

    public function index()
    {
        $teams = \App\Models\Team::select('name')->get();
        return view('employees.management', compact('teams'));
    }

    public function profile(Employee $employee)
    {
        $employee->load([
            'tasks' => function($q) { $q->orderBy('created_at', 'desc')->take(5); },
            'attendances' => function($q) { $q->orderBy('date', 'desc')->take(5); },
            'projects',
            'teamDetails',
            'skills'
        ]);
        
        $taskMetrics = \App\Models\EmployeeTaskMetric::where('employee_id', $employee->id)->first();
        $attendanceMetrics = \App\Models\AttendanceMetric::where('employee_id', $employee->id)->first();
        $workloadMetrics = \App\Models\WorkloadMetric::where('employee_id', $employee->id)->first();
        $githubCommits = \App\Models\GithubCommit::where('employee_id', $employee->id)->count();
        $existingReport = \App\Models\Performance_report::where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->first();

        return view('employees.profile', compact('employee', 'taskMetrics', 'attendanceMetrics', 'workloadMetrics', 'githubCommits', 'existingReport'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email|unique:users,email',
            'team' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive,on_leave',
            'join_date' => 'nullable|date',
            'avatar' => 'nullable|image|max:2048',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        $password = (string) random_int(10000000, 99999999);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('employee_photos', 'public');
        }

        $user = \App\Models\User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($password),
            'role' => 'employee',
            'profile_image' => $photoPath,
        ]);

        $employeeData = array_merge($validated, [
            'user_id' => $user->id,
            'password' => $password,
            'photo' => $photoPath,
        ]);

        Employee::create($employeeData);

        return redirect()->back()->with('success', 'Employee added successfully.');
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $employee->id . '|unique:users,email,' . ($employee->user_id ?? 'NULL'),
            'team' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive,on_leave',
            'join_date' => 'nullable|date',
            'password' => 'required|string|min:6|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        $user = $employee->user;
        if (!$user) {
            $user = \App\Models\User::where('email', $validated['email'])->first();
            if (!$user) {
                $user = \App\Models\User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
                    'role' => 'employee',
                ]);
            }
            $employee->user_id = $user->id;
        }

        $photoPath = $employee->photo;
        if ($request->hasFile('photo')) {
            if ($employee->photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($employee->photo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($employee->photo);
            }
            $photoPath = $request->file('photo')->store('employee_photos', 'public');
        }

        $userUpdate = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'profile_image' => $photoPath,
        ];

        if ($employee->password !== $validated['password']) {
            $userUpdate['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']);
        }

        $user->update($userUpdate);

        $employee->update(array_merge($validated, [
            'user_id' => $user->id,
            'photo' => $photoPath,
        ]));

        return redirect()->back()->with('success', 'Employee updated successfully.');
    }



    public function destroy(Employee $employee)
    {
        if ($employee->photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($employee->photo)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($employee->photo);
        }
        $employee->delete();
        return redirect()->back()->with('success', 'Employee deleted successfully.');
    }

    public function employeeDashboard()
    {
        $employee = auth()->user()->employee;
        if (!$employee) {
            return view('employee.dashboard', ['employee' => null]);
        }

        // Fetch recent tasks
        $recentTasks = $employee->tasks()->orderBy('created_at', 'desc')->take(5)->get();

        // Fetch recent commits
        $recentCommits = $employee->github_commits()->orderBy('commit_date', 'desc')->take(5)->get();

        // Fetch recent meetings
        $recentMeetings = $employee->meeting_notes()->orderBy('meeting_date', 'desc')->take(5)->get();

        // Calculate rank within team
        $rank = 1;
        $score = 0;
        $totalTeamMembers = 1;
        if ($employee->team) {
            $teamMembers = Employee::where('team', $employee->team)
                ->leftJoin('employee_period_metrics as epm', function($join) {
                    $join->on('employees.id', '=', 'epm.employee_id')
                         ->where('epm.period', '=', 'all_time');
                })
                ->select('employees.id', \Illuminate\Support\Facades\DB::raw('COALESCE(epm.overall_score, 0) as overall_score'))
                ->orderByDesc('overall_score')
                ->get();
            
            $totalTeamMembers = $teamMembers->count();
            foreach ($teamMembers as $index => $member) {
                if ($member->id === $employee->id) {
                    $rank = $index + 1;
                    $score = $member->overall_score;
                    break;
                }
            }
        }

        return view('employee.dashboard', compact('employee', 'recentTasks', 'recentCommits', 'recentMeetings', 'rank', 'score', 'totalTeamMembers'));
    }

    public function employeeTasks()
    {
        $employee = auth()->user()->employee;
        if (!$employee) {
            return view('employee.tasks', ['employee' => null]);
        }
        return view('employee.tasks', compact('employee'));
    }

    public function employeeLeaderboard()
    {
        $employee = auth()->user()->employee;
        if (!$employee) {
            return view('employee.leaderboard', ['employee' => null]);
        }
        return view('employee.leaderboard', compact('employee'));
    }

    public function employeeCommits()
    {
        $employee = auth()->user()->employee;
        if (!$employee) {
            return view('employee.commits', ['employee' => null]);
        }
        return view('employee.commits', compact('employee'));
    }

    public function employeeMeetings()
    {
        $employee = auth()->user()->employee;
        if (!$employee) {
            return view('employee.meetings', ['employee' => null]);
        }
        return view('employee.meetings', compact('employee'));
    }

    public function employeeTasksData()
    {
        $employee = auth()->user()->employee;
        if (!$employee) {
            return response()->json(['data' => []]);
        }
        $query = $employee->tasks()->select('id', 'title', 'description', 'priority', 'status', 'created_at', 'deadline_at', 'started_at', 'completed_at', 'actual_hours', 'timer_started_at', 'timer_accumulated_seconds');
        return \Yajra\DataTables\Facades\DataTables::of($query)
            ->addColumn('status_badge', function($row) {
                $statusColors = ['pending' => 'warning', 'in_progress' => 'primary', 'completed' => 'success', 'late_completed' => 'secondary'];
                $sColor = $statusColors[$row->status] ?? 'primary';
                $statusLabel = ucfirst(str_replace('_', ' ', $row->status));
                return "<span class=\"badge bg-{$sColor}\">{$statusLabel}</span>";
            })
            ->addColumn('priority_badge', function($row) {
                $priorityColors = ['low' => 'secondary', 'normal' => 'info', 'high' => 'warning', 'urgent' => 'danger'];
                $pColor = $priorityColors[$row->priority] ?? 'primary';
                return "<span class=\"badge bg-{$pColor}\">" . ucfirst($row->priority) . "</span>";
            })
            ->addColumn('time_spent', function($row) {
                $accumulatedSeconds = $row->timer_accumulated_seconds ?? round(($row->actual_hours ?? 0) * 3600);
                $timerStartedAt = $row->timer_started_at ? $row->timer_started_at->timestamp : '';
                return "<span class=\"timer-display\" 
                              data-task-id=\"{$row->id}\" 
                              data-accumulated-seconds=\"{$accumulatedSeconds}\" 
                              data-timer-started-at=\"{$timerStartedAt}\">
                            00:00:00
                        </span>";
            })
            ->addColumn('actions', function($row) {
                if ($row->status === 'completed' || $row->status === 'late_completed') {
                    return '<span class="text-muted small">No Actions</span>';
                }

                $isPlaying = !is_null($row->timer_started_at);
                $startClass = $isPlaying ? 'd-none' : '';
                $pauseClass = $isPlaying ? '' : 'd-none';

                return "
                    <div class=\"d-flex gap-1\">
                        <button type=\"button\" class=\"btn btn-success btn-sm start-timer-btn {$startClass}\" data-id=\"{$row->id}\" title=\"Start/Resume Timer\">
                            <i class=\"fa-solid fa-play\"></i> Start
                        </button>
                        <button type=\"button\" class=\"btn btn-warning btn-sm pause-timer-btn {$pauseClass}\" data-id=\"{$row->id}\" title=\"Pause Timer\">
                            <i class=\"fa-solid fa-pause\"></i> Pause
                        </button>
                        <button type=\"button\" class=\"btn btn-danger btn-sm stop-timer-btn\" data-id=\"{$row->id}\" title=\"Stop & Complete Task\">
                            <i class=\"fa-solid fa-stop\"></i> Stop
                        </button>
                    </div>
                ";
            })
            ->editColumn('deadline_at', function($row) {
                return $row->deadline_at ? \Carbon\Carbon::parse($row->deadline_at)->format('M d, Y') : 'No Deadline';
            })
            ->editColumn('created_at', function($row) {
                return $row->created_at ? $row->created_at->format('M d, Y') : 'N/A';
            })
            ->rawColumns(['status_badge', 'priority_badge', 'time_spent', 'actions'])
            ->make(true);
    }

    public function employeeLeaderboardData()
    {
        $employee = auth()->user()->employee;
        if (!$employee || !$employee->team) {
            return response()->json(['data' => []]);
        }
        $query = Employee::where('team', $employee->team)
            ->leftJoin('employee_period_metrics as epm', function($join) {
                $join->on('employees.id', '=', 'epm.employee_id')
                     ->where('epm.period', '=', 'all_time');
            })
            ->select(
                'employees.id',
                'employees.name',
                'employees.designation',
                \Illuminate\Support\Facades\DB::raw('COALESCE(epm.overall_score, 0) as overall_score'),
                \Illuminate\Support\Facades\DB::raw('COALESCE(epm.task_completion_score, 0) as task_completion_score'),
                \Illuminate\Support\Facades\DB::raw('COALESCE(epm.task_quality_score, 0) as task_quality_score'),
                \Illuminate\Support\Facades\DB::raw('COALESCE(epm.attendance_score, 0) as attendance_score'),
                \Illuminate\Support\Facades\DB::raw('COALESCE(epm.gitlab_score, 0) as gitlab_score')
            )
            ->orderByDesc('overall_score');

        return \Yajra\DataTables\Facades\DataTables::of($query)
            ->editColumn('task_completion_score', function($row) {
                return number_format($row->task_completion_score, 1) . '%';
            })
            ->editColumn('task_quality_score', function($row) {
                return number_format($row->task_quality_score, 1) . '%';
            })
            ->editColumn('attendance_score', function($row) {
                return number_format($row->attendance_score, 1) . '%';
            })
            ->editColumn('gitlab_score', function($row) {
                return number_format($row->gitlab_score, 0);
            })
            ->editColumn('overall_score', function($row) {
                return number_format($row->overall_score, 1);
            })
            ->addColumn('is_self', function($row) use ($employee) {
                return $row->id == $employee->id;
            })
            ->make(true);
    }

    public function employeeCommitsData()
    {
        $employee = auth()->user()->employee;
        if (!$employee) {
            return response()->json(['data' => []]);
        }
        $query = $employee->github_commits()->select('id', 'commit_hash', 'commit_message', 'project_id', 'commit_date');
        return \Yajra\DataTables\Facades\DataTables::of($query)
            ->addColumn('hash_short', function($row) {
                $short = substr($row->commit_hash, 0, 8);
                return "<span class=\"font-monospace badge bg-light text-dark border small\">{$short}</span>";
            })
            ->editColumn('commit_date', function($row) {
                return $row->commit_date ? \Carbon\Carbon::parse($row->commit_date)->format('M d, Y h:i A') : 'N/A';
            })
            ->rawColumns(['hash_short'])
            ->make(true);
    }

    public function employeeMeetingsData()
    {
        $employee = auth()->user()->employee;
        if (!$employee) {
            return response()->json(['data' => []]);
        }
        $query = $employee->meeting_notes()->select('id', 'notes_text', 'meeting_date');
        return \Yajra\DataTables\Facades\DataTables::of($query)
            ->editColumn('meeting_date', function($row) {
                return $row->meeting_date ? \Carbon\Carbon::parse($row->meeting_date)->format('M d, Y h:i A') : 'N/A';
            })
            ->addColumn('notes_summary', function($row) {
                return "<div style=\"white-space: pre-wrap;\">" . e($row->notes_text) . "</div>";
            })
            ->rawColumns(['notes_summary'])
            ->make(true);
    }

    public function employeeAttendance()
    {
        $employee = auth()->user()->employee;
        if (!$employee) {
            return view('employee.attendance', ['employee' => null]);
        }
        return view('employee.attendance', compact('employee'));
    }

    public function employeeAttendanceData(Request $request)
    {
        $employee = auth()->user()->employee;
        if (!$employee) {
            return response()->json([]);
        }

        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $attendances = $employee->attendances()
            ->where(function($query) use ($year, $month) {
                $query->whereYear('date', $year)->whereMonth('date', $month)
                    ->orWhere(function($q) use ($year, $month) {
                        $q->whereNull('date')->whereYear('created_at', $year)->whereMonth('created_at', $month);
                    });
            })
            ->get();

        $formatted = $attendances->map(function($att) {
            $d = $att->date ? $att->date->format('Y-m-d') : ($att->created_at ? $att->created_at->format('Y-m-d') : null);
            return [
                'date' => $d,
                'status' => $att->leave_flag ? 'leave' : ($att->late_flag ? 'late' : 'present'),
                'login_time' => $att->login_time ? $att->login_time->format('H:i') : null,
                'logout_time' => $att->logout_time ? $att->logout_time->format('H:i') : null,
            ];
        })->filter(fn($item) => !is_null($item['date']));

        return response()->json($formatted->values());
    }

    public function startTaskTimer(Task $task, Request $request)
    {
        $employee = auth()->user()->employee;
        if (!$employee || $task->employee_id !== $employee->id) {
            if ($request->isMethod('get')) {
                return redirect()->route('employee.tasks')->with('error', 'Unauthorized.');
            }
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        if (in_array($task->status, ['completed', 'late_completed'])) {
            if ($request->isMethod('get')) {
                return redirect()->route('employee.tasks')->with('error', 'Task is already completed.');
            }
            return response()->json(['error' => 'Task is already completed.'], 400);
        }

        // Set start time if first run
        if (is_null($task->started_at)) {
            $task->started_at = now();
        }

        $task->status = 'in_progress';
        $task->timer_started_at = now();
        $task->save();

        if ($request->isMethod('get')) {
            return redirect()->route('employee.tasks')->with('success', 'Timer started.');
        }

        return response()->json([
            'success' => true,
            'message' => 'Timer started successfully.',
            'task' => $task
        ]);
    }

    public function pauseTaskTimer(Task $task, Request $request)
    {
        $employee = auth()->user()->employee;
        if (!$employee || $task->employee_id !== $employee->id) {
            if ($request->isMethod('get')) {
                return redirect()->route('employee.tasks')->with('error', 'Unauthorized.');
            }
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        if (in_array($task->status, ['completed', 'late_completed'])) {
            if ($request->isMethod('get')) {
                return redirect()->route('employee.tasks')->with('error', 'Task is already completed.');
            }
            return response()->json(['error' => 'Task is already completed.'], 400);
        }

        if ($task->timer_started_at) {
            $elapsedSeconds = max(0, time() - $task->timer_started_at->timestamp);
            $accumulated = max(0, (int)($task->timer_accumulated_seconds ?? 0)) + $elapsedSeconds;
            $task->timer_accumulated_seconds = $accumulated;
            $task->actual_hours = max(0.0, $accumulated / 3600.0);
            $task->timer_started_at = null;
            $task->save();
        }

        if ($request->isMethod('get')) {
            return redirect()->route('employee.tasks')->with('success', 'Timer paused.');
        }

        return response()->json([
            'success' => true,
            'message' => 'Timer paused successfully.',
            'task' => $task
        ]);
    }

    public function stopTaskTimer(Task $task, Request $request)
    {
        $employee = auth()->user()->employee;
        if (!$employee || $task->employee_id !== $employee->id) {
            if ($request->isMethod('get')) {
                return redirect()->route('employee.tasks')->with('error', 'Unauthorized.');
            }
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        if (in_array($task->status, ['completed', 'late_completed'])) {
            if ($request->isMethod('get')) {
                return redirect()->route('employee.tasks')->with('error', 'Task is already completed.');
            }
            return response()->json(['error' => 'Task is already completed.'], 400);
        }

        if ($task->timer_started_at) {
            $elapsedSeconds = max(0, time() - $task->timer_started_at->timestamp);
            $accumulated = max(0, (int)($task->timer_accumulated_seconds ?? 0)) + $elapsedSeconds;
            $task->timer_accumulated_seconds = $accumulated;
            $task->timer_started_at = null;
        }

        $task->actual_hours = max(0.0, ($task->timer_accumulated_seconds ?? 0) / 3600.0);
        $task->completed_at = now();
        if ($task->deadline_at && $task->completed_at > $task->deadline_at) {
            $task->status = 'late_completed';
            $task->delay_hours = max(0.0, $task->completed_at->diffInSeconds($task->deadline_at) / 3600.0);
        } else {
            $task->status = 'completed';
            $task->delay_hours = 0.0;
        }

        $task->save();

        if ($request->isMethod('get')) {
            return redirect()->route('employee.tasks')->with('success', 'Task completed.');
        }

        return response()->json([
            'success' => true,
            'message' => 'Task completed and timer stopped.',
            'task' => $task
        ]);
    }
}
