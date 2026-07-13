@extends('master')

@push('page-style')
<style>
    .chart-container {
        height: 260px;
        position: relative;
    }
    
    .card-body {
        position: relative;
    }

    .kpi-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .bg-light-primary {
        background-color: rgba(var(--primary-rgb), 0.1);
        color: var(--primary);
    }
</style>
@endpush

@section('page-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1 ">Task Metrics</h3>
        <p class="text-muted mb-0">Precomputed performance analytics and productivity leaderboards</p>
    </div>
    <div class="d-flex gap-2">
        @include('components.date-filter')
        <button class="btn btn-outline-primary d-flex align-items-center gap-2" onclick="refreshMetrics()">
            <i class="fa-solid fa-arrows-rotate"></i> Refresh Metrics
        </button>
        <button class="btn btn-danger d-flex align-items-center gap-2" onclick="exportPageToPDF('.main-content', 'task-metrics.pdf')">
            <i class="fa-solid fa-file-pdf"></i> Export PDF
        </button>
    </div>
</div>

<!-- KPIs -->
<div class="row g-4 mb-4">
    <!-- Tasks Assigned -->
    <div class="col-md-4 col-xl-2">
        <div class="card kpi-card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted mb-2 small fw-semibold text-uppercase">Tasks Assigned</p>
                <h3 class="mb-0 fw-bold " id="kpi-assigned">0</h3>
            </div>
        </div>
    </div>
    <!-- Tasks Completed -->
    <div class="col-md-4 col-xl-2">
        <div class="card kpi-card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted mb-2 small fw-semibold text-uppercase">Tasks Completed</p>
                <h3 class="mb-0 fw-bold " id="kpi-completed">0</h3>
            </div>
        </div>
    </div>
    <!-- Tasks Delayed -->
    <div class="col-md-4 col-xl-2">
        <div class="card kpi-card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted mb-2 small fw-semibold text-uppercase">Tasks Delayed</p>
                <h3 class="mb-0 fw-bold " id="kpi-delayed">0</h3>
            </div>
        </div>
    </div>
    <!-- Completion Rate -->
    <div class="col-md-4 col-xl-2">
        <div class="card kpi-card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted mb-2 small fw-semibold text-uppercase">Completion Rate</p>
                <h3 class="mb-0 fw-bold " id="kpi-completion-rate">0%</h3>
            </div>
        </div>
    </div>
    <!-- Avg Time (Hrs) -->
    <div class="col-md-4 col-xl-2">
        <div class="card kpi-card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted mb-2 small fw-semibold text-uppercase">Avg Time (Hrs)</p>
                <h3 class="mb-0 fw-bold " id="kpi-avg-time">0h</h3>
            </div>
        </div>
    </div>
    <!-- Productivity Score -->
    <div class="col-md-4 col-xl-2">
        <div class="card kpi-card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted mb-2 small fw-semibold text-uppercase">Productivity Score</p>
                <h3 class="mb-0 fw-bold " id="kpi-prod-score">0</h3>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="row g-4 mb-4">
    <!-- Task Completion Trend -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-header border-0 bg-transparent pb-0">
                <h6 class="fw-bold mb-0">Task Completion Trend</h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="completionTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <!-- Delayed Tasks Trend -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-header border-0 bg-transparent pb-0">
                <h6 class="fw-bold mb-0">Delayed Tasks Trend</h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="delayedTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <!-- Team Productivity Comparison -->
    <div class="col-md-12 col-lg-4">
        <div class="card h-100">
            <div class="card-header border-0 bg-transparent pb-0">
                <h6 class="fw-bold mb-0">Team Productivity Comparison</h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="teamProductivityChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Employee Productivity Comparison -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header border-0 bg-transparent pb-0">
                <h6 class="fw-bold mb-0">Top/Bottom Employee Productivity</h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="employeeProductivityChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <!-- Monthly Task Performance Trend -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header border-0 bg-transparent pb-0">
                <h6 class="fw-bold mb-0">Monthly Task Performance Trend</h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="monthlyTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leaderboards -->
<div class="row g-4 mb-4">
    <!-- Top Performers -->
    <div class="col-lg-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-success"><i class="fa-solid fa-trophy me-2"></i> Top Task Performers</h5>
                <button class="btn btn-outline-success btn-sm" onclick="exportDOMTableToCSV('#top-performers-table', 'top-task-performers.csv')">
                    <i class="fa-solid fa-file-excel me-1"></i> Export Excel
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="top-performers-table">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Name</th>
                                <th>Team</th>
                                <th>Score</th>
                                <th>Rating</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- Lowest Task Completion Rates -->
    <div class="col-lg-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-danger"><i class="fa-solid fa-arrow-down-long me-2"></i> Lowest Task Completion Rates</h5>
                <button class="btn btn-outline-success btn-sm" onclick="exportDOMTableToCSV('#lowest-completion-table', 'lowest-task-completion.csv')">
                    <i class="fa-solid fa-file-excel me-1"></i> Export Excel
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="lowest-completion-table">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Name</th>
                                <th>Team</th>
                                <th>Completion Rate</th>
                                <th>Assigned Tasks</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-script')
<script>
    let activeCharts = {};
    const metricsLoader = document.getElementById('filter-loader');

    $(document).ready(function() {
        metricsLoader.classList.remove('d-none');
        fetchMetricsData();
    });

    function refreshMetrics() {
        metricsLoader.classList.remove('d-none');
        fetchMetricsData();
    }

    function destroyChart(name) {
        if (activeCharts[name]) {
            activeCharts[name].destroy();
        }
    }

    function getRatingBadge(category) {
        let badgeClass = 'badge-improvement';
        if (category === 'Excellent') badgeClass = 'badge-excellent';
        else if (category === 'Good') badgeClass = 'badge-good';
        else if (category === 'Average') badgeClass = 'badge-average';
        return `<span class="badge ${badgeClass}">${category}</span>`;
    }

    function fetchMetricsData() {
        const qs = getDateFilterQueryString();
        $.get(`{{ route('tasks.metrics.data') }}?${qs}`, function(data) {
            // Hide loaders
            metricsLoader.classList.add('d-none');

            // Update KPIs
            $('#kpi-assigned').text(data.kpis.assigned.toLocaleString());
            $('#kpi-completed').text(data.kpis.completed.toLocaleString());
            $('#kpi-delayed').text(data.kpis.delayed.toLocaleString());
            $('#kpi-completion-rate').text(data.kpis.completionRate + '%');
            $('#kpi-avg-time').text(data.kpis.avgTime + ' hrs');
            $('#kpi-prod-score').text(data.kpis.prodScore);

            // Chart 1: Task Completion Trend
            destroyChart('completionTrend');
            activeCharts['completionTrend'] = new Chart(document.getElementById('completionTrendChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: data.charts.trends.map(t => t.date),
                    datasets: [{
                        label: 'Tasks Completed',
                        data: data.charts.trends.map(t => t.tasks_completed),
                        borderColor: '#10b981',
                        tension: 0.3,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true } }
                }
            });

            // Chart 2: Delayed Tasks Trend
            destroyChart('delayedTrend');
            activeCharts['delayedTrend'] = new Chart(document.getElementById('delayedTrendChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: data.charts.trends.map(t => t.date),
                    datasets: [{
                        label: 'Tasks Delayed',
                        data: data.charts.trends.map(t => t.tasks_delayed),
                        borderColor: '#ef4444',
                        tension: 0.3,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true } }
                }
            });

            // Chart 3: Team Productivity Comparison
            destroyChart('teamProductivity');
            activeCharts['teamProductivity'] = new Chart(document.getElementById('teamProductivityChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: data.charts.teamProductivity.map(t => t.team),
                    datasets: [{
                        label: 'Avg Productivity Score',
                        data: data.charts.teamProductivity.map(t => t.avg_score),
                        backgroundColor: '#8b5cf6'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true, max: 100 } }
                }
            });

            // Chart 4: Top/Bottom Employee Productivity
            destroyChart('employeeProductivity');
            let empLabels = [];
            let empData = [];
            let empColors = [];
            
            data.charts.topEmployees.forEach(e => {
                empLabels.push(e.employee ? e.employee.name : 'Unknown');
                empData.push(e.productivity_score);
                empColors.push('#10b981');
            });
            data.charts.bottomEmployees.forEach(e => {
                empLabels.push(e.employee ? e.employee.name : 'Unknown');
                empData.push(e.productivity_score);
                empColors.push('#ef4444');
            });

            activeCharts['employeeProductivity'] = new Chart(document.getElementById('employeeProductivityChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: empLabels,
                    datasets: [{
                        label: 'Productivity Score',
                        data: empData,
                        backgroundColor: empColors
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { x: { beginAtZero: true, max: 100 } }
                }
            });

            // Chart 5: Monthly Performance Trend
            destroyChart('monthlyTrend');
            activeCharts['monthlyTrend'] = new Chart(document.getElementById('monthlyTrendChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: data.charts.monthlyTrend.map(m => m.month),
                    datasets: [
                        {
                            label: 'Assigned',
                            data: data.charts.monthlyTrend.map(m => m.assigned),
                            backgroundColor: '#0ea5e9'
                        },
                        {
                            label: 'Completed',
                            data: data.charts.monthlyTrend.map(m => m.completed),
                            backgroundColor: '#10b981'
                        },
                        {
                            label: 'Delayed',
                            data: data.charts.monthlyTrend.map(m => m.delayed),
                            backgroundColor: '#ef4444'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true } }
                }
            });

            // Leaderboards: Top Performers Table
            let topHtml = '';
            data.leaderboards.topPerformers.forEach((p, idx) => {
                topHtml += `
                    <tr>
                        <td class="fw-bold">${idx + 1}</td>
                        <td class="fw-medium">${p.employee ? p.employee.name : 'Unknown'}</td>
                        <td>${p.employee ? p.employee.team : 'N/A'}</td>
                        <td class="fw-bold text-success">${p.productivity_score}</td>
                        <td>${getRatingBadge(p.productivity_category)}</td>
                    </tr>
                `;
            });
            $('#top-performers-table tbody').html(topHtml);

            // Leaderboards: Lowest Task Completion Rates Table
            let lowestHtml = '';
            data.leaderboards.lowestCompletion.forEach((p, idx) => {
                lowestHtml += `
                    <tr>
                        <td class="fw-bold">${idx + 1}</td>
                        <td class="fw-medium">${p.employee ? p.employee.name : 'Unknown'}</td>
                        <td>${p.employee ? p.employee.team : 'N/A'}</td>
                        <td class="fw-bold text-danger">${parseFloat(p.completion_rate).toFixed(2)}%</td>
                        <td>${p.tasks_assigned}</td>
                    </tr>
                `;
            });
            $('#lowest-completion-table tbody').html(lowestHtml);
        });
    }
    window.onDateFilterChange = refreshMetrics;
</script>
@include('components.date-filter-js')
@endpush