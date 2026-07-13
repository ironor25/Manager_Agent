@extends('master')

@push('page-style')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
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
</style>
@endpush

@section('page-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1 ">Team Performance</h3>
        <p class="text-muted mb-0">Overview of team performance, metrics, and insights</p>
    </div>
</div>

<!-- Top KPI Section -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted mb-2 fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Total Teams</p>
                <h3 class="fw-bold mb-0 " id="kpi-total-teams"><div class="spinner-border spinner-border-sm text-primary" role="status"></div></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted mb-2 fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Best Performing Team</p>
                <h3 class="fw-bold mb-0 " id="kpi-best-team"><div class="spinner-border spinner-border-sm text-success" role="status"></div></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted mb-2 fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Average Score</p>
                <h3 class="fw-bold mb-0 " id="kpi-average-score"><div class="spinner-border spinner-border-sm text-warning" role="status"></div></h3>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0">
            <div class="card-header border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 ">Metrics Comparison</h5>
                <select id="chartPeriodSelect" class="form-select form-select-sm w-auto">
                    <option value="7" selected>Last 7 Days</option>
                    <option value="30">Last 30 Days</option>
                    <option value="all">All Time</option>
                </select>
            </div>
            <div class="card-body">
                <div style="height: 350px; position: relative;">
                    <div id="chartLoader" class="position-absolute top-50 start-50 translate-middle text-center" style="z-index: 10;">
                        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Loading chart...</span>
                        </div>
                        <p class="text-muted mt-2 small fw-semibold">Loading chart data...</p>
                    </div>
                    <canvas id="teamChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0">
            <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 ">Team Leaderboard</h5>
                <select id="leaderboardPeriodSelect" class="form-select form-select-sm w-auto">
                    <option value="7" selected>Last 7 Days</option>
                    <option value="30">Last 30 Days</option>
                    <option value="all">All Time</option>
                </select>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="leaderboardTable">
                        <thead>
                            <tr>
                                <th width="100" class="text-center">Rank</th>
                                <th>Team Name</th>
                                <th>Efficiency Score</th>
                                <th>Top 3 Performers</th>
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

<!-- AI Report Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <h5 class="modal-title fw-bold ">
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
@endsection

@push('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Chart Configuration defaults for sleek look
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = '#64748b';
        
        let teamChartInstance = null;

        function loadDashboardData(period = '7') {
            // 0. Load KPIs
            fetch('/admin/teams/kpis?period=' + period)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('kpi-total-teams').innerText = data.totalTeams;
                    document.getElementById('kpi-best-team').innerText = data.bestTeamName;
                    document.getElementById('kpi-average-score').innerText = data.averageTeamScore;
                })
                .catch(err => console.error("Failed to load KPIs", err));

            // 1. Load Chart Data
            const loader = document.getElementById('chartLoader');
            if (loader) loader.style.display = 'block';

            fetch('/admin/teams/chart-data?period=' + period)
                .then(res => res.json())
                .then(data => {
                    if (loader) loader.style.display = 'none';

                    const labels = data.map(item => item.team);
                    const attendanceData = data.map(item => item.attendance_score);
                    const taskCompletionData = data.map(item => item.task_completion);
                    const leadershipData = data.map(item => item.leadership_score);
                    const gitContributionData = data.map(item => item.git_contribution);

                    const ctx = document.getElementById('teamChart').getContext('2d');
                    
                    if (teamChartInstance) {
                        teamChartInstance.destroy();
                    }

                    teamChartInstance = new Chart(ctx, {
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
            })
            .catch(err => {
                console.error("Failed to load Chart Data", err);
                const loader = document.getElementById('chartLoader');
                if (loader) {
                    loader.innerHTML = '<div class="text-danger"><i class="fa-solid fa-triangle-exclamation fs-3"></i><p class="mt-2 small fw-semibold">Failed to load chart data</p></div>';
                }
            });
        }

        // 2. Load Leaderboard Table
        function loadLeaderboard(period = '7') {
            const tbody = document.querySelector('#leaderboardTable tbody');
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></td></tr>';

            fetch('/admin/teams/leaderboard?period=' + period)
                .then(res => res.json())
                .then(data => {
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

                    let performersHtml = '';
                    if (team.top_performers && team.top_performers.length > 0) {
                        performersHtml = '<div class="d-flex flex-column gap-2 py-1">';
                        team.top_performers.forEach(p => {
                            performersHtml += `
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar bg-light text-primary fw-bold" style="width: 24px; height: 24px; font-size: 0.65rem; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                                        ${p.name.charAt(0)}
                                    </div>
                                    <span class="small text-body fw-medium">${p.name}</span>
                                    <span class="badge bg-light text-muted border" style="font-size: 0.65rem; padding: 2px 4px;">${p.score}%</span>
                                </div>
                            `;
                        });
                        performersHtml += '</div>';
                    } else {
                        performersHtml = '<span class="text-muted small">No performance records</span>';
                    }

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
                            ${performersHtml}
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
        }

        // Initial Data Load
        loadDashboardData('7');
        loadLeaderboard('7');

        // Event Listeners for Filters
        const chartSelect = document.getElementById('chartPeriodSelect');
        if (chartSelect) {
            chartSelect.addEventListener('change', (e) => loadDashboardData(e.target.value));
        }

        const leaderboardSelect = document.getElementById('leaderboardPeriodSelect');
        if (leaderboardSelect) {
            leaderboardSelect.addEventListener('change', (e) => loadLeaderboard(e.target.value));
        }

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
                        <h6 class=" fw-medium">Analyzing data and generating report...</h6>
                    </div>
                `;
                modal.show();

                fetch(`/admin/teams/${teamName}/report`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.report) {
                            const r = data.report;
                            
                            const formatList = (arr, iconClass) => arr && arr.length ? arr.map(item => `<div class="d-flex gap-2 mb-2"><i class="${iconClass} mt-1"></i><span>${item}</span></div>`).join('') : '<span class="text-muted">None recorded</span>';
                            
                            document.getElementById('modalBodyContent').innerHTML = `
                                <div class="row g-3">
                                    <!-- Left Column: Summary & Recommendations -->
                                    <div class="col-md-5 d-flex flex-column gap-3">
                                        <div class="p-3 border rounded-3 bg-white h-100">
                                            <h6 class="fw-bold text-primary mb-2"><i class="fa-solid fa-file-lines me-2"></i>Summary</h6>
                                            <p class="text-muted lh-lg mb-0" style="font-size: 0.875rem;">${r.summary}</p>
                                        </div>
                                        <div class="p-3 border rounded-3 bg-light">
                                            <h6 class="fw-bold text-primary mb-2"><i class="fa-solid fa-lightbulb me-2"></i>Recommendations</h6>
                                            <div class="text-muted" style="font-size: 0.875rem;">${formatList(r.recommendations, 'fa-solid fa-angle-right text-primary')}</div>
                                        </div>
                                    </div>
                                    <!-- Middle Column: Strengths & Weaknesses -->
                                    <div class="col-md-4 d-flex flex-column gap-3">
                                        <div class="p-3 border rounded-3 bg-white h-100">
                                            <h6 class="fw-bold text-success mb-2"><i class="fa-solid fa-circle-check me-2"></i>Key Strengths</h6>
                                            <div class="text-muted" style="font-size: 0.875rem;">${formatList(r.strengths, 'fa-solid fa-check text-success')}</div>
                                        </div>
                                        <div class="p-3 border rounded-3 bg-white h-100">
                                            <h6 class="fw-bold text-danger mb-2"><i class="fa-solid fa-circle-xmark me-2"></i>Areas for Improvement</h6>
                                            <div class="text-muted" style="font-size: 0.875rem;">${formatList(r.weaknesses, 'fa-solid fa-xmark text-danger')}</div>
                                        </div>
                                    </div>
                                    <!-- Right Column: Team Members -->
                                    <div class="col-md-3 d-flex flex-column gap-3">
                                        <div class="p-3 border rounded-3 bg-white h-100 d-flex flex-column">
                                            <h6 class="fw-bold text-info mb-2"><i class="fa-solid fa-users me-2"></i>Team Members</h6>
                                            <div class="overflow-auto pe-2 flex-grow-1" style="max-height: calc(100vh - 260px); min-height: 350px;">
                                                ${data.members && data.members.length ? data.members.map(m => `
                                                    <div class="d-flex align-items-center gap-2 mb-2 p-2 border rounded-2 bg-light">
                                                        <div class="avatar bg-primary text-white fw-bold d-flex align-items-center justify-content-center rounded-circle" style="width: 28px; height: 28px; font-size: 0.75rem;">
                                                            ${m.name.charAt(0)}
                                                        </div>
                                                        <span class="small fw-medium text-body">${m.name}</span>
                                                    </div>
                                                `).join('') : '<span class="text-muted small">No members</span>'}
                                            </div>
                                        </div>
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
    });
</script>
@endpush