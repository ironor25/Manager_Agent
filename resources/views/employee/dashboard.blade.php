@extends('master')

@push('page-style')
<style>
    .dashboard-header {
        background: linear-gradient(135deg, var(--primary) 0%, #4f46e5 100%);
        color: white;
        border-radius: 12px;
        padding: 32px;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(79, 70, 229, 0.15);
    }
    .profile-avatar {
        width: 80px;
        height: 80px;
        background: rgba(255, 255, 255, 0.2);
        color: white;
        font-size: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        border: 3px solid rgba(255, 255, 255, 0.5);
        overflow: hidden;
        box-shadow: var(--shadow-sm);
    }
    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .kpi-card {
        background: var(--surface);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-md);
        padding: 24px;
        box-shadow: var(--shadow-sm);
        display: flex;
        align-items: center;
        gap: 16px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }
    .kpi-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .bg-light-primary { background-color: rgba(var(--primary-rgb), 0.1); color: var(--primary); }
    .bg-light-success { background-color: rgba(16, 185, 129, 0.1); color: var(--success); }
    .bg-light-warning { background-color: rgba(245, 158, 11, 0.1); color: var(--warning); }
    .bg-light-info { background-color: rgba(59, 130, 246, 0.1); color: var(--info); }
    .bg-light-purple { background-color: rgba(139, 92, 246, 0.1); color: #8b5cf6; }

    .leaderboard-list-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 8px;
        border: 1px solid var(--border-color);
        background: var(--surface);
    }
    .leaderboard-list-item.highlighted {
        background: linear-gradient(90deg, rgba(var(--primary-rgb), 0.08) 0%, rgba(var(--primary-rgb), 0.02) 100%);
        border-color: rgba(var(--primary-rgb), 0.3);
        font-weight: 600;
    }
    .rank-badge {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        font-weight: bold;
    }
    .rank-1 { background-color: #f59e0b; color: white; }
    .rank-2 { background-color: #94a3b8; color: white; }
    .rank-3 { background-color: #b45309; color: white; }
    .rank-other { background-color: var(--border-color); color: var(--text-muted); }
</style>
@endpush

@section('page-content')
<div class="container-fluid">
    @if(!$employee)
        <div class="alert alert-warning border-0 shadow-sm p-4 mb-4" role="alert">
            <div class="d-flex align-items-center gap-3">
                <i class="fa-solid fa-triangle-exclamation fa-2x text-warning"></i>
                <div>
                    <h5 class="alert-heading fw-bold mb-1">No Profile Linked</h5>
                    <p class="mb-0 text-muted">Your user account is not currently linked to any employee record. Please contact the administrator to link your profile under Access Control.</p>
                </div>
            </div>
        </div>
    @else
        <!-- Welcome Header -->
        <div class="dashboard-header shadow-sm d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-4">
                <div class="profile-avatar">
                    @if($employee->photo)
                        <img src="{{ asset('storage/' . $employee->photo) }}" alt="{{ $employee->name }}">
                    @else
                        {{ substr($employee->name, 0, 1) }}
                    @endif
                </div>
                <div>
                    <h2 class="fw-bold mb-1">Welcome back, {{ $employee->name }}!</h2>
                    <p class="mb-2 opacity-75 fs-5">
                        <i class="fa-solid fa-users me-2"></i>{{ $employee->team ?? 'No Team Assigned' }} &bull; {{ $employee->designation ?? 'Employee' }}
                    </p>
                    <div class="d-flex gap-3 text-sm opacity-90">
                        <span><i class="fa-solid fa-envelope me-1"></i> {{ $employee->email }}</span>
                        <span><i class="fa-solid fa-calendar me-1"></i> Joined: {{ $employee->created_at ? $employee->created_at->format('M d, Y') : 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI Grid -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="kpi-card">
                    <div>
                        <div class="text-muted small fw-medium">Assigned Tasks</div>
                        <div class="fw-bold text-body fs-4">{{ $employee->tasks()->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="kpi-card">
                    <div>
                        <div class="text-muted small fw-medium">Completed Tasks</div>
                        <div class="fw-bold text-body fs-4">{{ $employee->tasks()->where('status', 'completed')->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="kpi-card">
                    <div>
                        <div class="text-muted small fw-medium">Overall Score</div>
                        <div class="fw-bold text-body fs-4">{{ number_format($score, 1) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="kpi-card">
                    <div>
                        <div class="text-muted small fw-medium">Team Rank</div>
                        <div class="fw-bold text-body fs-4">#{{ $rank }} <span class="fs-6 text-muted font-normal">of {{ $totalTeamMembers }}</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Details Grid -->
        <div class="row g-4 mb-4">
            <!-- Recent Tasks -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0"><i class="fa-solid fa-tasks text-primary me-2"></i>My Active Tasks</h5>
                        <a href="{{ route('employee.tasks') }}" class="btn btn-sm btn-outline-secondary rounded-pill">View All</a>
                    </div>
                    <div class="card-body px-4 pb-4">
                        @if($recentTasks->isEmpty())
                            <div class="text-center py-5">
                                <i class="fa-solid fa-tasks text-muted fa-3x mb-3"></i>
                                <p class="text-muted mb-0">No tasks currently assigned to you.</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Priority</th>
                                            <th>Status</th>
                                            <th>Deadline</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentTasks as $task)
                                            <tr>
                                                <td class="fw-semibold">{{ $task->title }}</td>
                                                <td>
                                                    @php
                                                        $priorityColors = ['low' => 'secondary', 'normal' => 'info', 'high' => 'warning', 'urgent' => 'danger'];
                                                        $pColor = $priorityColors[$task->priority] ?? 'primary';
                                                    @endphp
                                                    <span class="badge bg-{{ $pColor }}">{{ ucfirst($task->priority) }}</span>
                                                </td>
                                                <td>
                                                    @php
                                                        $statusColors = ['pending' => 'warning', 'in_progress' => 'primary', 'completed' => 'success', 'late_completed' => 'secondary'];
                                                        $sColor = $statusColors[$task->status] ?? 'primary';
                                                    @endphp
                                                    <span class="badge bg-{{ $sColor }}">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                                                </td>
                                                <td class="small">{{ $task->deadline_at ? \Carbon\Carbon::parse($task->deadline_at)->format('M d, Y') : 'No Deadline' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Team Leaderboard Rank -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0"><i class="fa-solid fa-medal text-warning me-2"></i>Team Standings</h5>
                        <a href="{{ route('employee.leaderboard') }}" class="btn btn-sm btn-outline-secondary rounded-pill">Standings</a>
                    </div>
                    <div class="card-body px-4 pb-4">
                        @if(!$employee->team)
                            <div class="text-center py-5">
                                <i class="fa-solid fa-users text-muted fa-3x mb-3"></i>
                                <p class="text-muted mb-0">You are not currently assigned to a team.</p>
                            </div>
                        @else
                            @php
                                $teamMembers = \App\Models\Employee::where('team', $employee->team)
                                    ->leftJoin('employee_period_metrics as epm', function($join) {
                                        $join->on('employees.id', '=', 'epm.employee_id')
                                             ->where('epm.period', '=', 'all_time');
                                    })
                                    ->select('employees.*', \Illuminate\Support\Facades\DB::raw('COALESCE(epm.overall_score, 0) as overall_score'))
                                    ->orderByDesc('overall_score')
                                    ->take(5)
                                    ->get();
                            @endphp
                            <div class="leaderboard-list">
                                @foreach($teamMembers as $index => $member)
                                    @php
                                        $mRank = $index + 1;
                                        $badgeClass = 'rank-other';
                                        if ($mRank == 1) $badgeClass = 'rank-1';
                                        elseif ($mRank == 2) $badgeClass = 'rank-2';
                                        elseif ($mRank == 3) $badgeClass = 'rank-3';
                                    @endphp
                                    <div class="leaderboard-list-item {{ $member->id == $employee->id ? 'highlighted' : '' }}">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rank-badge {{ $badgeClass }}">{{ $mRank }}</div>
                                            <span>{{ $member->name }}</span>
                                        </div>
                                        <div class="fw-bold text-primary">{{ number_format($member->overall_score, 1) }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Commits & Meetings -->
        <div class="row g-4">
            <!-- Recent Git Commits -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0"><i class="fa-brands fa-github me-2"></i>My Recent Commits</h5>
                        <a href="{{ route('employee.commits') }}" class="btn btn-sm btn-outline-secondary rounded-pill">All Commits</a>
                    </div>
                    <div class="card-body px-4 pb-4">
                        @if($recentCommits->isEmpty())
                            <div class="text-center py-5">
                                <i class="fa-brands fa-github text-muted fa-3x mb-3"></i>
                                <p class="text-muted mb-0">No GitHub commits recorded.</p>
                            </div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach($recentCommits as $commit)
                                    <div class="list-group-item px-0 py-3 bg-transparent border-color d-flex justify-content-between align-items-start gap-3">
                                        <div>
                                            <div class="fw-semibold mb-1">{{ $commit->commit_message }}</div>
                                            <div class="small text-muted">
                                                <i class="fa-solid fa-code-commit me-1"></i> <span class="font-monospace">{{ substr($commit->commit_hash, 0, 8) }}</span> &bull; 
                                                <i class="fa-regular fa-clock me-1"></i> {{ $commit->commit_date ? \Carbon\Carbon::parse($commit->commit_date)->format('M d, Y') : 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Recent Meetings -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0"><i class="fa-solid fa-users-viewfinder text-info me-2"></i>My Meetings</h5>
                        <a href="{{ route('employee.meetings') }}" class="btn btn-sm btn-outline-secondary rounded-pill">All Meetings</a>
                    </div>
                    <div class="card-body px-4 pb-4">
                        @if($recentMeetings->isEmpty())
                            <div class="text-center py-5">
                                <i class="fa-solid fa-users-viewfinder text-muted fa-3x mb-3"></i>
                                <p class="text-muted mb-0">No meeting notes recorded.</p>
                            </div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach($recentMeetings as $meeting)
                                    <div class="list-group-item px-0 py-3 bg-transparent border-color d-flex justify-content-between align-items-start gap-3">
                                        <div>
                                            <div class="fw-semibold mb-1">{{ \Illuminate\Support\Str::limit($meeting->notes_text, 65) }}</div>
                                            <div class="small text-muted">
                                                <i class="fa-regular fa-calendar me-1"></i> {{ $meeting->meeting_date ? \Carbon\Carbon::parse($meeting->meeting_date)->format('M d, Y') : 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
