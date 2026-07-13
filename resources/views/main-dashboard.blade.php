@extends('master')

@push('page-style')
<style>
    /* Dashboard Print Styles */
    @media print {
        body { background: #fff !important; }
        .app-sidebar, .app-header, .btn, .widget-loader, .sidebar-overlay { display: none !important; }
        .main-content { padding: 0 !important; margin: 0 !important; }
        .dashboard-grid { display: block !important; }
        .dashboard-widget { break-inside: avoid; margin-bottom: 20px; box-shadow: none !important; border: 1px solid #ddd !important; }
        * { color: #000 !important; }
        .bg-light-primary, .bg-light-success, .bg-light-warning, .bg-light-danger { background-color: transparent !important; border: 1px solid #eee !important; }
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: 24px;
        margin-bottom: 24px;
    }

    .dashboard-widget {
        background: var(--surface);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-md);
        padding: 24px;
        box-shadow: var(--shadow-sm);
        display: flex;
        flex-direction: column;
        position: relative;
    }
    
    .widget-header {
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .widget-title {
        font-weight: 600;
        font-size: 1.05rem;
        color: var(--text-main);
        margin: 0;
    }

    /* Grid Span Helpers */
    .col-span-12 { grid-column: span 12; }
    .col-span-8 { grid-column: span 8; }
    .col-span-6 { grid-column: span 6; }
    .col-span-4 { grid-column: span 4; }
    .col-span-3 { grid-column: span 3; }

    @media (max-width: 1200px) {
        .dashboard-grid { grid-template-columns: repeat(2, 1fr); }
        .col-span-12, .col-span-8, .col-span-6, .col-span-4, .col-span-3 { grid-column: span 2; }
    }
    @media (max-width: 768px) {
        .dashboard-grid { grid-template-columns: 1fr; }
        .col-span-12, .col-span-8, .col-span-6, .col-span-4, .col-span-3 { grid-column: span 1; }
    }

    /* Custom widget specific styles */
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
    .bg-light-danger { background-color: rgba(239, 68, 68, 0.1); color: var(--danger); }
    .bg-light-info { background-color: rgba(59, 130, 246, 0.1); color: var(--info); }
    .bg-light-purple { background-color: rgba(139, 92, 246, 0.1); color: var(--purple); }

    .progress { height: 8px; border-radius: 4px; background-color: var(--border-color); }
    
    .ai-insight-box {
        background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.05) 0%, rgba(139, 92, 246, 0.05) 100%);
        border: 1px solid rgba(var(--primary-rgb), 0.2);
        border-radius: var(--border-radius-md);
        padding: 20px;
        position: relative;
        overflow: hidden;
    }
    .ai-insight-box::before {
        content: '';
        position: absolute;
        top: 0; left: 0; width: 4px; height: 100%;
        background: linear-gradient(to bottom, var(--primary), var(--purple));
    }

    /* Individual Widget Loader Styles */
    .widget-loader {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(2px);
        z-index: 10;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--border-radius-md);
    }
</style>
@endpush

@section('page-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold ">Workspace Overview 👋</h2>
        <p class="text-muted mb-0">Here's what's happening across your organization today.</p>
    </div>
    <div>
        <button class="btn btn-outline-primary shadow-sm" onclick="window.print()">
            <i class="fa-solid fa-download me-2"></i> Export Report
        </button>
    </div>
</div>

<div id="dashboard-content">
    <!-- 1. AI Recommendations -->
    <div class="dashboard-grid">
        <div class="dashboard-widget col-span-12 p-0 border-0 shadow-none bg-transparent">
            <div class="ai-insight-box">
                <div class="widget-loader"><div class="spinner-border spinner-border-sm text-purple"></div></div>
                <div class="d-flex align-items-start gap-3">
                    <div class="kpi-icon bg-light-purple flex-shrink-0">
                        <i class="fa-solid fa-sparkles"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold  mb-1">AI Executive Summary</h6>
                        <p class="text-muted mb-0" id="ai-insight-text">
                            Analyzing workspace data...
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. KPI Cards Grid -->
    <div class="dashboard-grid">
        <div class="dashboard-widget col-span-3">
            <div class="widget-loader"><div class="spinner-border spinner-border-sm text-primary"></div></div>
            <p class="text-muted mb-2 fw-bold text-uppercase" style="font-size: 0.75rem;">Total Employees</p>
            <h3 class="fw-bold mb-0 " id="kpi-employees">-</h3>
        </div>
        <div class="dashboard-widget col-span-3">
            <div class="widget-loader"><div class="spinner-border spinner-border-sm text-warning"></div></div>
            <p class="text-muted mb-2 fw-bold text-uppercase" style="font-size: 0.75rem;">Total Tasks</p>
            <h3 class="fw-bold mb-0 " id="kpi-tasks">-</h3>
        </div>
        <div class="dashboard-widget col-span-3">
            <div class="widget-loader"><div class="spinner-border spinner-border-sm text-success"></div></div>
            <p class="text-muted mb-2 fw-bold text-uppercase" style="font-size: 0.75rem;">Completed</p>
            <h3 class="fw-bold mb-0 " id="kpi-completed">-</h3>
        </div>
        <div class="dashboard-widget col-span-3">
            <div class="widget-loader"><div class="spinner-border spinner-border-sm text-info"></div></div>
            <p class="text-muted mb-2 fw-bold text-uppercase" style="font-size: 0.75rem;">Avg Attendance</p>
            <h3 class="fw-bold mb-0 " id="kpi-att-avg">-%</h3>
        </div>
    </div>

    <!-- 3. Complex Overview Grid -->
    <div class="dashboard-grid">
        
        <!-- Task Completion Overview -->
        <div class="dashboard-widget col-span-8">
            <div class="widget-loader"><div class="spinner-border text-primary"></div></div>
            <div class="widget-header">
                <h5 class="widget-title">Task Completion Overview</h5>
                <a href="{{ route('tasks.monitoring') }}" class="btn btn-sm btn-light border">Details</a>
            </div>
            <div class="row align-items-center h-100">
                <div class="col-md-5 text-center">
                    <div style="position: relative; width: 160px; height: 160px; margin: 0 auto;">
                        <canvas id="taskCompletionChart"></canvas>
                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                            <h4 class="fw-bold mb-0" id="task-total-center">0</h4>
                            <small class="text-muted">Tasks</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-medium text-success"><i class="fa-solid fa-circle fa-2xs me-2"></i>Completed</span>
                            <span class="fw-bold" id="prog-completed-pct">0%</span>
                        </div>
                        <div class="progress"><div class="progress-bar bg-success" id="prog-completed-bar" style="width: 0%"></div></div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-medium text-warning"><i class="fa-solid fa-circle fa-2xs me-2"></i>In Progress</span>
                            <span class="fw-bold" id="prog-ongoing-pct">0%</span>
                        </div>
                        <div class="progress"><div class="progress-bar bg-warning" id="prog-ongoing-bar" style="width: 0%"></div></div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-medium text-danger"><i class="fa-solid fa-circle fa-2xs me-2"></i>Late/Overdue</span>
                            <span class="fw-bold" id="prog-late-pct">0%</span>
                        </div>
                        <div class="progress"><div class="progress-bar bg-danger" id="prog-late-bar" style="width: 0%"></div></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Overview -->
        <div class="dashboard-widget col-span-4">
            <div class="widget-loader"><div class="spinner-border text-primary"></div></div>
            <div class="widget-header">
                <h5 class="widget-title">Today's Attendance</h5>
                <a href="{{ route('attendance.analytics') }}" class="btn btn-sm btn-light border">Analytics</a>
            </div>
            <div class="d-flex flex-column gap-3 mt-2">
                <div class="d-flex align-items-center justify-content-between p-3 rounded bg-light-success">
                    <div class="d-flex align-items-center gap-3">
                        <i class="fa-solid fa-user-check fs-4"></i>
                        <span class="fw-semibold text-body">Present</span>
                    </div>
                    <h4 class="fw-bold mb-0" id="kpi-att-present">-</h4>
                </div>
                <div class="d-flex align-items-center justify-content-between p-3 rounded bg-light-danger">
                    <div class="d-flex align-items-center gap-3">
                        <i class="fa-solid fa-user-xmark fs-4"></i>
                        <span class="fw-semibold text-body">Absent</span>
                    </div>
                    <h4 class="fw-bold mb-0" id="kpi-att-absent">-</h4>
                </div>
                <div class="d-flex align-items-center justify-content-between p-3 rounded" style="background-color: rgba(100,116,139,0.1);">
                    <div class="d-flex align-items-center gap-3">
                        <i class="fa-solid fa-umbrella-beach fs-4 text-muted"></i>
                        <span class="fw-semibold text-body">On Leave</span>
                    </div>
                    <h4 class="fw-bold mb-0" id="kpi-att-leave">-</h4>
                </div>
            </div>
        </div>

        <!-- Organization Performance Overview -->
        <div class="dashboard-widget col-span-4">
            <div class="widget-loader"><div class="spinner-border text-primary"></div></div>
            <div class="widget-header">
                <h5 class="widget-title">Top Performers</h5>
                <a href="{{ route('leaderboard.organization') }}" class="btn btn-sm btn-light border">Leaderboard</a>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless table-sm mb-0 align-middle">
                    <tbody id="org-perf-tbody">
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Team Performance Overview -->
        <div class="dashboard-widget col-span-4">
            <div class="widget-loader"><div class="spinner-border text-primary"></div></div>
            <div class="widget-header">
                <h5 class="widget-title">Top Teams</h5>
                <a href="{{ route('leaderboard.team') }}" class="btn btn-sm btn-light border">Teams</a>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless table-sm mb-0 align-middle">
                    <tbody id="team-perf-tbody">
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Git Activity / Top Contributors -->
        <div class="dashboard-widget col-span-4">
            <div class="widget-loader"><div class="spinner-border text-primary"></div></div>
            <div class="widget-header">
                <h5 class="widget-title">Top Git Contributors</h5>
                <a href="{{ route('commits.index') }}" class="btn btn-sm btn-light border">Commits</a>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless table-sm mb-0 align-middle">
                    <tbody id="git-activity-tbody">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fetchDashboard = fetch('/admin/dashboard/data').then(res => res.json());
        const fetchLeaderboard = fetch('/admin/leaderboard/organization/data').then(res => res.json());

        Promise.all([fetchDashboard, fetchLeaderboard])
            .then(([dashboardData, leaderboardData]) => {
                
                // Hide all loaders
                document.querySelectorAll('.widget-loader').forEach(loader => loader.style.display = 'none');

                // 1. KPI & Attendance
                document.getElementById('kpi-employees').textContent = dashboardData.totalEmployees;
                document.getElementById('kpi-tasks').textContent = dashboardData.totalTasks;
                document.getElementById('kpi-completed').textContent = dashboardData.completedTasks;
                
                if (dashboardData.attendance) {
                    document.getElementById('kpi-att-present').textContent = dashboardData.attendance.present;
                    document.getElementById('kpi-att-absent').textContent = dashboardData.attendance.absent;
                    document.getElementById('kpi-att-leave').textContent = dashboardData.attendance.leave;
                    document.getElementById('kpi-att-avg').textContent = dashboardData.attendance.avg_percentage + '%';
                }

                // 2. Task Completion
                const totalTasks = dashboardData.totalTasks > 0 ? dashboardData.totalTasks : 1;
                const ongoingTasks = dashboardData.pendingTasks + dashboardData.inProgressTasks;
                const lateTasks = dashboardData.lateTasks;
                const completedTasks = dashboardData.completedTasks;

                const completedPct = Math.round((completedTasks / totalTasks) * 100);
                const ongoingPct = Math.round((ongoingTasks / totalTasks) * 100);
                const latePct = Math.round((lateTasks / totalTasks) * 100);

                document.getElementById('prog-completed-pct').textContent = completedPct + '%';
                document.getElementById('prog-completed-bar').style.width = completedPct + '%';
                document.getElementById('prog-ongoing-pct').textContent = ongoingPct + '%';
                document.getElementById('prog-ongoing-bar').style.width = ongoingPct + '%';
                document.getElementById('prog-late-pct').textContent = latePct + '%';
                document.getElementById('prog-late-bar').style.width = latePct + '%';
                document.getElementById('task-total-center').textContent = dashboardData.totalTasks;

                // Task Donut Chart
                new Chart(document.getElementById('taskCompletionChart'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Completed', 'In Progress', 'Late'],
                        datasets: [{
                            data: [completedTasks, ongoingTasks, lateTasks],
                            backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                            borderWidth: 0,
                            cutout: '75%'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } }
                    }
                });

                // 3. Organization Performance (Top Employees)
                const orgTbody = document.getElementById('org-perf-tbody');
                orgTbody.innerHTML = '';
                if(leaderboardData.top_employees && leaderboardData.top_employees.length > 0) {
                    leaderboardData.top_employees.slice(0,5).forEach((emp, index) => {
                        orgTbody.innerHTML += `
                            <tr>
                                <td style="width: 30px;"><span class="badge bg-light text-muted">${index + 1}</span></td>
                                <td>
                                    <div class="fw-semibold text-body">${emp.name}</div>
                                    <small class="text-muted">${emp.team || 'Unassigned'}</small>
                                </td>
                                <td class="text-end"><span class="fw-bold text-primary">${parseFloat(emp.score).toFixed(1)}</span></td>
                            </tr>
                        `;
                    });
                } else {
                    orgTbody.innerHTML = '<tr><td class="text-center text-muted py-3">No data available</td></tr>';
                }

                // 4. Team Performance
                const teamTbody = document.getElementById('team-perf-tbody');
                teamTbody.innerHTML = '';
                if(leaderboardData.top_teams && leaderboardData.top_teams.length > 0) {
                    leaderboardData.top_teams.slice(0,5).forEach((team, index) => {
                        teamTbody.innerHTML += `
                            <tr>
                                <td style="width: 30px;"><span class="badge bg-light text-muted">${index + 1}</span></td>
                                <td>
                                    <div class="fw-semibold text-body">${team.name}</div>
                                </td>
                                <td class="text-end"><span class="fw-bold text-primary">${parseFloat(team.score).toFixed(1)}</span></td>
                            </tr>
                        `;
                    });
                } else {
                    teamTbody.innerHTML = '<tr><td class="text-center text-muted py-3">No data available</td></tr>';
                }

                // 5. Git Activity (Top Contributors)
                const gitTbody = document.getElementById('git-activity-tbody');
                gitTbody.innerHTML = '';
                if(leaderboardData.top_contributors && leaderboardData.top_contributors.length > 0) {
                    leaderboardData.top_contributors.slice(0,5).forEach((contrib, index) => {
                        gitTbody.innerHTML += `
                            <tr>
                                <td style="width: 30px;"><span class="badge bg-light text-muted">${index + 1}</span></td>
                                <td>
                                    <div class="fw-semibold text-body">${contrib.name}</div>
                                </td>
                                <td class="text-end"><span class="badge bg-primary rounded-pill text-white">${parseInt(contrib.score)} commits</span></td>
                            </tr>
                        `;
                    });
                } else {
                    gitTbody.innerHTML = '<tr><td class="text-center text-muted py-3">No data available</td></tr>';
                }



                // 7. Dynamic AI Insight Generation
                const insights = [
                    `Based on current metrics, ${leaderboardData.top_teams && leaderboardData.top_teams.length > 0 ? leaderboardData.top_teams[0].name : 'Engineering'} is performing exceptionally well. Ensure they have adequate resources.`,
                    `Task completion is at ${completedPct}%. Consider reviewing pending tasks to prevent bottlenecks.`,
                    `Attendance is averaging ${dashboardData.attendance ? dashboardData.attendance.avg_percentage : 0}%. Workforce capacity looks stable.`
                ];
                document.getElementById('ai-insight-text').textContent = insights[Math.floor(Math.random() * insights.length)];

            }).catch(error => {
                console.error("Dashboard Load Error: ", error);
                document.querySelectorAll('.widget-loader').forEach(loader => loader.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-danger"></i>');
            });
    });
</script>
@endpush
