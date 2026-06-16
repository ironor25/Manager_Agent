@extends('master')

@push('page-style')
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
    
    .progress-wrapper { margin-bottom: 1.5rem; }
    .progress { height: 8px; border-radius: 4px; }
</style>
@endpush

@section('page-content')
<div class="mb-4">
    <h2 class="fw-bold text-body">Good {{ date('H') < 12 ? 'Morning' : (date('H') < 17 ? 'Afternoon' : 'Evening') }}, {{ Auth::user()->name ?? 'Admin' }}! 👋</h2>
    <p class="text-muted">Here's what's happening with your team today.</p>
</div>

<!-- Loader -->
<div id="dashboard-loader" class="text-center py-5">
    <div class="spinner-border text-primary" role="status"></div>
    <p class="mt-3 text-muted fw-medium">Loading Dashboard Data...</p>
</div>

<div id="dashboard-content" style="display: none;">
    <!-- KPI Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="kpi-card">
                <div>
                    <p class="text-muted mb-1 fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Total Employees</p>
                    <h3 class="fw-bold mb-0 text-body" id="kpi-employees">0</h3>
                </div>
                <div class="kpi-icon bg-light-primary">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="kpi-card">
                <div>
                    <p class="text-muted mb-1 fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Total Tasks</p>
                    <h3 class="fw-bold mb-0 text-body" id="kpi-tasks">0</h3>
                </div>
                <div class="kpi-icon bg-light-warning">
                    <i class="fa-solid fa-list-check"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="kpi-card">
                <div>
                    <p class="text-muted mb-1 fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Completed Tasks</p>
                    <h3 class="fw-bold mb-0 text-body" id="kpi-completed">0</h3>
                </div>
                <div class="kpi-icon bg-light-success">
                    <i class="fa-solid fa-check-double"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Task Progress Lateral Bars -->
        <div class="col-md-5">
            <div class="card border-0 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="fw-bold mb-0 text-body">Task Progress</h5>
                </div>
                <div class="card-body">
                    
                    <div class="progress-wrapper">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-semibold text-success">Completed</span>
                            <span class="text-muted fw-bold" id="prog-completed-pct">0%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" id="prog-completed-bar" role="progressbar" style="width: 0%"></div>
                        </div>
                        <small class="text-muted" id="prog-completed-text">0 tasks completed</small>
                    </div>
                    
                    <div class="progress-wrapper">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-semibold text-primary">In Progress & Pending</span>
                            <span class="text-muted fw-bold" id="prog-ongoing-pct">0%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-primary" id="prog-ongoing-bar" role="progressbar" style="width: 0%"></div>
                        </div>
                        <small class="text-muted" id="prog-ongoing-text">0 tasks ongoing</small>
                    </div>
                    
                    <div class="progress-wrapper mb-0">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-semibold text-danger">Late Completion (Overdue)</span>
                            <span class="text-muted fw-bold" id="prog-late-pct">0%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-danger" id="prog-late-bar" role="progressbar" style="width: 0%"></div>
                        </div>
                        <small class="text-muted" id="prog-late-text">0 tasks past deadline</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Tasks Table -->
        <div class="col-md-7">
            <div class="card border-0 h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-body">Recent Tasks</h5>
                    <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-light border">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="recent-tasks-table">
                            <thead class="bg-light">
                                <tr>
                                    <th>Task</th>
                                    <th>Assigned To</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Populated by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        fetch('/dashboard/data')
            .then(response => response.json())
            .then(data => {
                // Populate KPIs
                document.getElementById('kpi-employees').textContent = data.totalEmployees;
                document.getElementById('kpi-tasks').textContent = data.totalTasks;
                document.getElementById('kpi-completed').textContent = data.completedTasks;
                
                // Populate Progress Bars
                const totalTasks = data.totalTasks > 0 ? data.totalTasks : 1;
                const ongoingTasks = data.pendingTasks + data.inProgressTasks;
                
                const completedPct = Math.round((data.completedTasks / totalTasks) * 100);
                const ongoingPct = Math.round((ongoingTasks / totalTasks) * 100);
                const latePct = Math.round((data.lateTasks / totalTasks) * 100);
                
                document.getElementById('prog-completed-pct').textContent = completedPct + '%';
                document.getElementById('prog-completed-bar').style.width = completedPct + '%';
                document.getElementById('prog-completed-text').textContent = data.completedTasks + ' tasks completed';
                
                document.getElementById('prog-ongoing-pct').textContent = ongoingPct + '%';
                document.getElementById('prog-ongoing-bar').style.width = ongoingPct + '%';
                document.getElementById('prog-ongoing-text').textContent = ongoingTasks + ' tasks ongoing';
                
                document.getElementById('prog-late-pct').textContent = latePct + '%';
                document.getElementById('prog-late-bar').style.width = latePct + '%';
                document.getElementById('prog-late-text').textContent = data.lateTasks + ' tasks past deadline';
                
                // Populate Recent Tasks
                const tbody = document.querySelector('#recent-tasks-table tbody');
                tbody.innerHTML = '';
                
                if(data.recentTasks.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="3" class="text-center py-4 text-muted">No tasks available yet.</td></tr>';
                } else {
                    data.recentTasks.forEach(task => {
                        let badgeClass = 'bg-warning text-body';
                        if(task.status === 'completed') badgeClass = 'bg-success text-white';
                        if(task.status === 'in_progress') badgeClass = 'bg-primary text-white';
                        
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>
                                <div class="fw-bold text-body">${task.title}</div>
                                <small class="text-muted">${task.created_at_human}</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar bg-light text-primary" style="width: 28px; height: 28px; font-size: 0.75rem; display:flex; align-items:center; justify-content:center; border-radius:50%; font-weight:bold;">
                                        ${task.employee_initial}
                                    </div>
                                    <span class="fw-medium text-body">${task.employee_name}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge ${badgeClass} rounded-pill" style="font-weight: 500; padding: 5px 10px;">
                                    ${task.status_formatted}
                                </span>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });
                }
                
                // Hide loader and show content
                document.getElementById('dashboard-loader').style.display = 'none';
                document.getElementById('dashboard-content').style.display = 'block';
            })
            .catch(error => {
                console.error("Error fetching dashboard data: ", error);
                document.getElementById('dashboard-loader').innerHTML = '<div class="alert alert-danger">Failed to load dashboard data. Please try refreshing the page.</div>';
            });
    });
</script>
@endpush

