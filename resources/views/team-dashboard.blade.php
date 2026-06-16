@extends('master')

@push('page-style')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    .kpi-card {
        background: var(--surface);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 24px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
    }
    .kpi-icon {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .bg-light-primary { background-color: rgba(79, 70, 229, 0.1); color: var(--primary); }
    .bg-light-success { background-color: rgba(16, 185, 129, 0.1); color: var(--success); }
    .bg-light-warning { background-color: rgba(245, 158, 11, 0.1); color: var(--warning); }
    
    .rank-badge {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: white;
    }
    .rank-1 { background-color: #fbbf24; box-shadow: 0 0 10px rgba(251, 191, 36, 0.5); }
    .rank-2 { background-color: #9ca3af; box-shadow: 0 0 10px rgba(156, 163, 175, 0.5); }
    .rank-3 { background-color: #d97706; box-shadow: 0 0 10px rgba(217, 119, 6, 0.5); }
    .rank-other { background-color: #f1f5f9; color: var(--text-muted); }
    
    .report-btn {
        background-color: transparent;
        color: var(--primary);
        border: 1px solid var(--primary);
        border-radius: 50px;
        padding: 6px 16px;
        font-weight: 500;
        font-size: 0.85rem;
        transition: all 0.2s;
    }
    .report-btn:hover {
        background-color: var(--primary);
        color: white;
    }
    .nav-tabs .nav-link,
    .nav-tabs .nav-link:hover,
    .nav-tabs .nav-link:focus,
    .nav-tabs .nav-link:active,
    .nav-tabs .nav-link.active {
        color: #888888 !important;
        font-weight: 700;
        border: none;
        border-bottom: 3px solid transparent;
        padding-top: 1rem;
        padding-bottom: 1rem;
        background: transparent !important;
    }
    .nav-tabs .nav-link.active {
        border-bottom-color: var(--primary) !important;
    }
</style>
@endpush

@section('page-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1 text-body">Team Performance</h3>
        <p class="text-muted mb-0">Overview of team performance, metrics, and management</p>
    </div>
</div>

<!-- Nav Tabs -->
<ul class="nav nav-tabs mb-4 border-bottom-0" id="teamTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="analytics-tab" data-bs-toggle="tab" data-bs-target="#analytics" type="button" role="tab" aria-controls="analytics" aria-selected="true">
            <i class="fa-solid fa-chart-line me-2"></i>Team Analytics
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="management-tab" data-bs-toggle="tab" data-bs-target="#management" type="button" role="tab" aria-controls="management" aria-selected="false">
            <i class="fa-solid fa-users-gear me-2"></i>Team Management
        </button>
    </li>
</ul>

<div class="tab-content" id="teamTabsContent">
    <!-- ANALYTICS TAB -->
    <div class="tab-pane fade show active" id="analytics" role="tabpanel" aria-labelledby="analytics-tab">

<!-- Top KPI Section -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="kpi-card">
            <div>
                <p class="text-muted mb-1 fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Total Teams</p>
                <h3 class="fw-bold mb-0 text-body">{{ $totalTeams }}</h3>
            </div>
            <div class="kpi-icon bg-light-primary">
                <i class="fa-solid fa-sitemap"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card">
            <div>
                <p class="text-muted mb-1 fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Best Performing Team</p>
                <h3 class="fw-bold mb-0 text-body">{{ $bestTeamName }}</h3>
            </div>
            <div class="kpi-icon bg-light-success">
                <i class="fa-solid fa-trophy"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card">
            <div>
                <p class="text-muted mb-1 fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Average Score</p>
                <h3 class="fw-bold mb-0 text-body">{{ $averageTeamScore }}</h3>
            </div>
            <div class="kpi-icon bg-light-warning">
                <i class="fa-solid fa-chart-pie"></i>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0">
            <div class="card-header border-bottom-0 bg-white pt-4 pb-0">
                <h5 class="fw-bold mb-0 text-body">Metrics Comparison</h5>
            </div>
            <div class="card-body">
                <div style="height: 350px; position: relative;">
                    <canvas id="teamChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="fw-bold mb-0 text-body">Team Leaderboard</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="leaderboardTable">
                        <thead>
                            <tr>
                                <th width="100" class="text-center">Rank</th>
                                <th>Team Name</th>
                                <th>Efficiency Score</th>
                                <th>Top Performer</th>
                                <th class="text-end px-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

    </div> <!-- End Analytics Tab -->

    <!-- MANAGEMENT TAB -->
    <div class="tab-pane fade" id="management" role="tabpanel" aria-labelledby="management-tab">
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-body">Teams Overview</h5>
                <div>
                    <button class="btn btn-outline-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#assignTaskModal">
                        <i class="fa-solid fa-tasks me-1"></i> Assign Task
                    </button>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createTeamModal">
                        <i class="fa-solid fa-plus me-1"></i> Create Team
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3">Team Name</th>
                                <th class="py-3">Description</th>
                                <th class="py-3">Members</th>
                                <th class="text-end px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($teamModels as $team)
                            <tr class="cursor-pointer" onclick="window.location.href='{{ route('teams.show', $team->name) }}'" style="cursor: pointer;">
                                <td class="px-4">
                                    <div class="fw-bold text-body text-primary text-decoration-underline">{{ $team->name }}</div>
                                </td>
                                <td>
                                    <div class="text-muted small text-truncate" style="max-width: 300px;">
                                        {{ $team->description ?? 'No description provided.' }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-primary border border-primary-subtle">
                                        <i class="fa-solid fa-users me-1"></i> {{ $team->employees->count() }}
                                    </span>
                                </td>
                                <td class="text-end px-4">
                                    <button class="btn btn-sm btn-light border-0 text-primary me-2" title="View Team Details" onclick="event.stopPropagation(); window.location.href='{{ route('teams.show', $team->name) }}'">
                                        <i class="fa-solid fa-arrow-right"></i> Details
                                    </button>
                                    <button class="action-btn manage-members-btn" data-team="{{ $team->name }}" data-members="{{ json_encode($team->employees->map(function($e){return ['id'=>$e->id, 'name'=>$e->name];})) }}" title="Manage Members" onclick="showManageMembersModal(this, event)">
                                        <i class="fa-solid fa-users-gear"></i>
                                    </button>
                                    <button class="action-btn edit-team-btn" data-team="{{ $team->name }}" data-desc="{{ $team->description }}" title="Edit Team" onclick="showEditTeamModal(this, event)">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <button class="action-btn delete-btn trigger-team-delete" data-team="{{ $team->name }}" title="Delete Team" onclick="showDeleteTeamModal(this, event)">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

    </div> <!-- End Management Tab -->
</div> <!-- End Tab Content -->

<!-- Modals -->
<!-- Create Team Modal -->
<div class="modal fade" id="createTeamModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('teams.store') }}" method="POST" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-body"><i class="fa-solid fa-plus-circle text-primary me-2"></i>Create New Team</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-medium">Team Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. Engineering">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Optional team details..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Add Members (Optional)</label>
                    <select name="employee_ids[]" class="form-select" multiple size="4" aria-label="multiple select employees">
                        @foreach($allEmployees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->team ?? 'No Team' }})</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Hold CTRL (or CMD) to select multiple members.</small>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-3">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary px-4">Create Team</button>
            </div>
        </form>
    </div>
</div>

<!-- Assign Task Modal -->
<div class="modal fade" id="assignTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-body"><i class="fa-solid fa-tasks text-primary me-2"></i>Assign Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Nav tabs -->
                <ul class="nav nav-pills mb-4 bg-light p-1 rounded-pill" id="assignTaskTabs" role="tablist">
                    <li class="nav-item w-50" role="presentation">
                        <button class="nav-link active w-100 rounded-pill fw-medium" id="assign-team-tab" data-bs-toggle="pill" data-bs-target="#assign-team" type="button" role="tab" aria-selected="true">
                            Assign to Team (Bulk)
                        </button>
                    </li>
                    <li class="nav-item w-50" role="presentation">
                        <button class="nav-link w-100 rounded-pill fw-medium" id="assign-individual-tab" data-bs-toggle="pill" data-bs-target="#assign-individual" type="button" role="tab" aria-selected="false">
                            Assign to Individual
                        </button>
                    </li>
                </ul>

                <form id="assignTaskForm" action="{{ route('teams.assign_task') }}" method="POST">
                    @csrf
                    <input type="hidden" name="assignment_type" id="assignment_type" value="team">
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Select Team</label>
                            <select name="team_name" id="assign_team_name" class="form-select" required>
                                <option value="">-- Select Team --</option>
                                @foreach($teamModels as $team)
                                    <option value="{{ $team->name }}" data-members="{{ json_encode($team->employees->map(function($e){return ['id'=>$e->id, 'name'=>$e->name];})) }}">{{ $team->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6" id="individual_employee_wrapper" style="display: none;">
                            <label class="form-label fw-medium">Select Employee</label>
                            <select name="employee_id" id="assign_employee_id" class="form-select">
                                <option value="">-- Select Employee --</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Task Title</label>
                        <input type="text" name="title" class="form-control" required placeholder="e.g. Update UI design">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Task details..."></textarea>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="low">Low</option>
                                <option value="normal" selected>Normal</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Deadline (Optional)</label>
                            <input type="datetime-local" name="deadline_at" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Est. Hours (Optional)</label>
                            <input type="number" step="0.5" min="0" name="estimated_hours" class="form-control" placeholder="e.g. 2.5">
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top text-end">
                        <button type="button" class="btn btn-secondary px-4 me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4" id="assignTaskSubmitBtn">Assign Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Team Modal -->
<div class="modal fade" id="editTeamModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="editTeamForm" method="POST" class="modal-content border-0 shadow">
            @csrf
            @method('PUT')
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-body"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Edit Team</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-medium">Team Name</label>
                    <input type="text" id="edit_team_name" class="form-control" disabled>
                    <small class="text-muted">Team names cannot be changed currently.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Description</label>
                    <textarea name="description" id="edit_team_description" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-3">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary px-4">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Manage Members Modal -->
<div class="modal fade" id="manageMembersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-body"><i class="fa-solid fa-users-gear text-primary me-2"></i>Manage Members: <span id="manageMembersTeamName" class="text-primary"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <!-- Current Members -->
                    <div class="col-md-6 border-end">
                        <h6 class="fw-bold mb-3">Current Members</h6>
                        <ul class="list-group list-group-flush" id="currentMembersList">
                            <!-- Populated via JS -->
                        </ul>
                    </div>
                    <!-- Add Member Form -->
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">Add Member</h6>
                        <form id="addMemberForm" method="POST">
                            @csrf
                            <div class="d-flex gap-2">
                                <select name="employee_id" class="form-select" required>
                                    <option value="">Select Employee</option>
                                    @foreach($allEmployees as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->team ?? 'No Team' }})</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-primary">Add</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Team Modal -->
<div class="modal fade" id="deleteTeamModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-4 border-0 shadow">
            <div class="mb-3">
                <i class="fa-solid fa-triangle-exclamation text-danger" style="font-size: 3rem;"></i>
            </div>
            <h5 class="mb-3 fw-bold">Delete Team</h5>
            <p class="text-muted mb-4" style="font-size: 0.9rem;">Are you sure you want to delete this team? Members will be safely unassigned.</p>
            <div class="d-flex justify-content-center gap-2">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteTeamForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger px-4">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- AI Report Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <h5 class="modal-title fw-bold text-body">
            <i class="fa-solid fa-wand-magic-sparkles text-primary me-2"></i> AI Report: <span id="modalTeamName" class="text-primary"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" id="modalBodyContent">
          <!-- Populated by JS -->
      </div>
    </div>
  </div>
</div>

<!-- Custom Confirmation Modal -->
<div class="modal fade" id="customConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-4 border-0 shadow" style="border-radius: 16px;">
            <div class="mb-3" id="customConfirmIconContainer">
                <i class="fa-solid fa-circle-question text-warning" style="font-size: 3rem;"></i>
            </div>
            <h5 class="mb-3 fw-bold" id="customConfirmTitle">Confirm Action</h5>
            <p class="text-muted mb-4" id="customConfirmMessage" style="font-size: 0.9rem;">Are you sure you want to proceed?</p>
            <div class="d-flex justify-content-center gap-2">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary px-4" id="customConfirmSubmitBtn">Confirm</button>
            </div>
        </div>
    </div>

@endsection

@push('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle Hash for Tabs
        if (window.location.hash === '#management') {
            const mgmtTab = new bootstrap.Tab(document.getElementById('management-tab'));
            mgmtTab.show();
        }

        // Chart Configuration defaults for sleek look
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = '#64748b';
        
        // 1. Load Chart Data
        fetch('/teams/chart-data')
            .then(res => res.json())
            .then(data => {
                const labels = data.map(item => item.team);
                const attendanceData = data.map(item => item.attendance_score);
                const taskCompletionData = data.map(item => item.task_completion);
                const leadershipData = data.map(item => item.leadership_score);
                const gitContributionData = data.map(item => item.git_contribution);

                const ctx = document.getElementById('teamChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Attendance',
                                data: attendanceData,
                                backgroundColor: '#4f46e5',
                                borderRadius: 4,
                            },
                            {
                                label: 'Tasks',
                                data: taskCompletionData,
                                backgroundColor: '#10b981',
                                borderRadius: 4,
                            },
                            {
                                label: 'Leadership',
                                data: leadershipData,
                                backgroundColor: '#f59e0b',
                                borderRadius: 4,
                            },
                            {
                                label: 'Git Commits',
                                data: gitContributionData,
                                backgroundColor: '#64748b',
                                borderRadius: 4,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 8 } },
                            tooltip: { mode: 'index', intersect: false, backgroundColor: 'rgba(15, 23, 42, 0.9)', padding: 12, borderRadius: 8 }
                        },
                        scales: {
                            y: { beginAtZero: true, max: 100, grid: { borderDash: [4, 4], color: '#e2e8f0' } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            });

        // 2. Load Leaderboard Table
        fetch('/teams/leaderboard')
            .then(res => res.json())
            .then(data => {
                const tbody = document.querySelector('#leaderboardTable tbody');
                tbody.innerHTML = '';
                
                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-muted">No team data available</td></tr>';
                    return;
                }

                data.forEach((team, index) => {
                    const rank = index + 1;
                    let rankBadge = 'rank-other';
                    if (rank === 1) rankBadge = 'rank-1';
                    else if (rank === 2) rankBadge = 'rank-2';
                    else if (rank === 3) rankBadge = 'rank-3';

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="text-center">
                            <div class="d-flex justify-content-center">
                                <div class="rank-badge ${rankBadge}">${rank}</div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold text-body">${team.team_name}</div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress w-50" style="height: 6px;">
                                    <div class="progress-bar bg-primary" style="width: ${team.efficiency_score}%"></div>
                                </div>
                                <span class="fw-bold text-body">${team.efficiency_score}%</span>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar bg-light text-primary" style="width: 32px; height: 32px; font-size: 0.8rem; font-weight: bold; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                    ${team.top_performer_name.charAt(0)}
                                </div>
                                <span class="fw-medium text-body">${team.top_performer_name}</span>
                            </div>
                        </td>
                        <td class="text-end px-4">
                            <button class="report-btn generate-report-btn" data-team="${team.team_name}">
                                Insights
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            });

        // 3. Handle Performance Button Clicks
        document.querySelector('#leaderboardTable').addEventListener('click', function(e) {
            const btn = e.target.closest('.generate-report-btn');
            if (btn) {
                const teamName = btn.getAttribute('data-team');
                
                const modalEl = document.getElementById('reportModal');
                const modal = new bootstrap.Modal(modalEl);
                document.getElementById('modalTeamName').innerText = teamName;
                document.getElementById('modalBodyContent').innerHTML = `
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary mb-3" role="status"></div>
                        <h6 class="text-muted fw-medium">Analyzing data and generating report...</h6>
                    </div>
                `;
                modal.show();

                fetch(`/teams/${teamName}/report`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.report) {
                            const r = data.report;
                            
                            const formatList = (arr, iconClass) => arr && arr.length ? arr.map(item => `<div class="d-flex gap-2 mb-2"><i class="${iconClass} mt-1"></i><span>${item}</span></div>`).join('') : '<span class="text-muted">None recorded</span>';
                            
                            document.getElementById('modalBodyContent').innerHTML = `
                                <div class="mb-4">
                                    <p class="text-muted lh-lg mb-0">${r.summary}</p>
                                </div>
                                <div class="row g-4 mb-4">
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded-3 h-100 bg-white">
                                            <h6 class="fw-bold text-success mb-3"><i class="fa-solid fa-arrow-trend-up me-2"></i>Strengths</h6>
                                            <div class="text-muted" style="font-size: 0.9rem;">${formatList(r.strengths, 'fa-solid fa-check text-success')}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded-3 h-100 bg-white">
                                            <h6 class="fw-bold text-danger mb-3"><i class="fa-solid fa-arrow-trend-down me-2"></i>Weaknesses</h6>
                                            <div class="text-muted" style="font-size: 0.9rem;">${formatList(r.weaknesses, 'fa-solid fa-xmark text-danger')}</div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="p-3 border rounded-3 bg-light">
                                        <h6 class="fw-bold text-primary mb-3"><i class="fa-solid fa-lightbulb me-2"></i>Recommendations</h6>
                                        <div class="text-muted" style="font-size: 0.9rem;">${formatList(r.recommendations, 'fa-solid fa-angle-right text-primary')}</div>
                                    </div>
                                </div>
                            `;
                        } else {
                            document.getElementById('modalBodyContent').innerHTML = `
                                <div class="alert alert-danger">
                                    <i class="fa-solid fa-circle-exclamation me-2"></i> Failed to generate report. ${data.error || 'Unknown error occurred.'}
                                </div>
                            `;
                        }
                    })
                    .catch(err => {
                        document.getElementById('modalBodyContent').innerHTML = `
                            <div class="alert alert-danger">
                                <i class="fa-solid fa-circle-exclamation me-2"></i> Network error occurred while fetching the report.
                            </div>
                        `;
                    });
            }
        });

        // Intercept add member form submit and prompt custom confirmation
        const addMemberForm = document.getElementById('addMemberForm');
        if (addMemberForm) {
            addMemberForm.addEventListener('submit', function(e) {
                e.preventDefault();
                if (!addMemberForm.reportValidity()) return;
                
                const selectEl = addMemberForm.querySelector('select[name="employee_id"]');
                const employeeName = selectEl.options[selectEl.selectedIndex].text;
                
                showCustomConfirm({
                    title: 'Add Team Member',
                    message: `Are you sure you want to add ${employeeName} to this team?`,
                    iconClass: 'fa-solid fa-user-plus text-primary',
                    btnClass: 'btn-primary',
                    onConfirm: function() {
                        addMemberForm.submit();
                    }
                });
            });
        }

        // Setup custom confirm submit button click handler
        const customConfirmSubmitBtn = document.getElementById('customConfirmSubmitBtn');
        if (customConfirmSubmitBtn) {
            customConfirmSubmitBtn.addEventListener('click', function() {
                if (onCustomConfirmCallback) {
                    onCustomConfirmCallback();
                }
                const modalEl = document.getElementById('customConfirmModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            });
        }
    });

    let onCustomConfirmCallback = null;

    function showCustomConfirm({ title, message, iconClass, btnClass, onConfirm }) {
        document.getElementById('customConfirmTitle').innerText = title;
        document.getElementById('customConfirmMessage').innerText = message;
        
        const iconContainer = document.getElementById('customConfirmIconContainer');
        iconContainer.innerHTML = `<i class="${iconClass || 'fa-solid fa-circle-question text-warning'}" style="font-size: 3rem;"></i>`;
        
        const submitBtn = document.getElementById('customConfirmSubmitBtn');
        submitBtn.className = `btn ${btnClass || 'btn-primary'} px-4`;
        
        onCustomConfirmCallback = onConfirm;
        
        const modalEl = document.getElementById('customConfirmModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    function confirmRemoveMember(memberName, formId) {
        showCustomConfirm({
            title: 'Remove Team Member',
            message: `Are you sure you want to remove ${memberName} from this team?`,
            iconClass: 'fa-solid fa-user-minus text-danger',
            btnClass: 'btn-danger',
            onConfirm: function() {
                document.getElementById(formId).submit();
            }
        });
    }

    function showManageMembersModal(btn, event) {
        event.stopPropagation();
        const team = btn.getAttribute('data-team');
        const members = JSON.parse(btn.getAttribute('data-members'));
        
        document.getElementById('manageMembersTeamName').innerText = team;
        document.getElementById('addMemberForm').action = `/teams/${encodeURIComponent(team)}/members`;
        
        const list = document.getElementById('currentMembersList');
        list.innerHTML = '';
        
        if (members.length === 0) {
            list.innerHTML = '<li class="list-group-item text-muted">No members yet.</li>';
        } else {
            members.forEach(member => {
                const escapedName = member.name.replace(/'/g, "\\'");
                list.innerHTML += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        ${member.name}
                        <form id="remove-member-form-${member.id}" action="/teams/${encodeURIComponent(team)}/members/${member.id}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" title="Remove Member" onclick="confirmRemoveMember('${escapedName}', 'remove-member-form-${member.id}')">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </form>
                    </li>
                `;
            });
        }
        
        new bootstrap.Modal(document.getElementById('manageMembersModal')).show();
    }

    function showEditTeamModal(btn, event) {
        event.stopPropagation();
        const team = btn.getAttribute('data-team');
        const desc = btn.getAttribute('data-desc');
        document.getElementById('edit_team_name').value = team;
        document.getElementById('edit_team_description').value = desc;
        document.getElementById('editTeamForm').action = `/teams/${encodeURIComponent(team)}`;
        new bootstrap.Modal(document.getElementById('editTeamModal')).show();
    }

    function showDeleteTeamModal(btn, event) {
        event.stopPropagation();
        const team = btn.getAttribute('data-team');
        document.getElementById('deleteTeamForm').action = `/teams/${encodeURIComponent(team)}`;
        new bootstrap.Modal(document.getElementById('deleteTeamModal')).show();
    }

    function showToast(message, type = 'success') {
        var container = $('.toast-container');
        if (container.length === 0) {
            container = $('<div class="toast-container position-fixed bottom-0 end-0 p-4" style="z-index: 1070;"></div>');
            $('body').append(container);
        }
        var bgClass = 'bg-success';
        if (type === 'danger') {
            bgClass = 'bg-danger';
        } else if (type === 'warning') {
            bgClass = 'bg-warning text-dark';
        } else if (type === 'info') {
            bgClass = 'bg-info text-dark';
        }
        var toastHtml = `
            <div class="toast align-items-center text-white ${bgClass} border-0 shadow-lg mb-2" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
                <div class="d-flex">
                    <div class="toast-body fw-medium py-3 px-4" style="font-size: 0.95rem;">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        var toastEl = $(toastHtml);
        container.append(toastEl);
        var toast = new bootstrap.Toast(toastEl[0]);
        toast.show();
        
        toastEl.on('hidden.bs.toast', function () {
            toastEl.remove();
        });
    }

    // Assign Task Logic
    const assignTaskTabs = document.getElementById('assignTaskTabs');
    const assignTypeInput = document.getElementById('assignment_type');
    const employeeWrapper = document.getElementById('individual_employee_wrapper');
    const employeeSelect = document.getElementById('assign_employee_id');
    const teamSelect = document.getElementById('assign_team_name');

    if (assignTaskTabs) {
        assignTaskTabs.addEventListener('click', function(e) {
            if (e.target.id === 'assign-individual-tab') {
                assignTypeInput.value = 'individual';
                employeeWrapper.style.display = 'block';
                employeeSelect.required = true;
            } else if (e.target.id === 'assign-team-tab') {
                assignTypeInput.value = 'team';
                employeeWrapper.style.display = 'none';
                employeeSelect.required = false;
            }
        });
    }

    if (teamSelect) {
        teamSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const membersAttr = selectedOption.getAttribute('data-members');
            employeeSelect.innerHTML = '<option value="">-- Select Employee --</option>';
            
            if (membersAttr) {
                try {
                    const members = JSON.parse(membersAttr);
                    members.forEach(member => {
                        employeeSelect.innerHTML += `<option value="${member.id}">${member.name}</option>`;
                    });
                } catch (e) {
                    console.error("Failed to parse members data", e);
                }
            }
        });
    }

    const assignTaskForm = document.getElementById('assignTaskForm');
    if (assignTaskForm) {
        assignTaskForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const submitBtn = document.getElementById('assignTaskSubmitBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Assigning...';
            submitBtn.disabled = true;

            fetch(this.action, {
                method: 'POST',
                body: new FormData(this),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                
                if (data.success) {
                    showToast(data.message || 'Task assigned successfully!', 'success');
                    const modal = bootstrap.Modal.getInstance(document.getElementById('assignTaskModal'));
                    if (modal) modal.hide();
                    assignTaskForm.reset();
                    employeeSelect.innerHTML = '<option value="">-- Select Employee --</option>';
                } else {
                    showToast(data.message || 'Failed to assign task.', 'danger');
                }
            })
            .catch(err => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                showToast('Network error occurred while assigning task.', 'danger');
                console.error(err);
            });
        });
    }
</script>
@endpush

