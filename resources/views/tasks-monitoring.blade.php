@extends('master')

@push('page-style')
<style>
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }
    
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
        <h3 class="fw-bold mb-1 ">Task Monitoring</h3>
        <p class="text-muted mb-0">Real-time operational visibility and bottleneck tracking</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary d-flex align-items-center gap-2" onclick="refreshMonitoring()">
            <i class="fa-solid fa-arrows-rotate"></i> Refresh Analytics
        </button>
        <button class="btn btn-danger d-flex align-items-center gap-2" onclick="exportPageToPDF('.main-content', 'tasks-monitoring.pdf')">
            <i class="fa-solid fa-file-pdf"></i> Export PDF
        </button>
    </div>
</div>

<!-- KPIs -->
<div class="row g-4 mb-4">
    <!-- Active Tasks -->
    <div class="col-md-6 col-lg-4 col-xl-2.4 col-xxl-2">
        <div class="card kpi-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="widget-loader kpi-loader"><div class="spinner-border spinner-border-sm text-primary"></div></div>
                <p class="text-muted mb-2 small fw-semibold text-uppercase">Active Tasks</p>
                <h3 class="mb-0 fw-bold " id="kpi-active">-</h3>
            </div>
        </div>
    </div>
    <!-- Due Today -->
    <div class="col-md-6 col-lg-4 col-xl-2.4 col-xxl-2">
        <div class="card kpi-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="widget-loader kpi-loader"><div class="spinner-border spinner-border-sm text-primary"></div></div>
                <p class="text-muted mb-2 small fw-semibold text-uppercase">Due Today</p>
                <h3 class="mb-0 fw-bold " id="kpi-due-today">-</h3>
            </div>
        </div>
    </div>
    <!-- Overdue Tasks -->
    <div class="col-md-6 col-lg-4 col-xl-2.4 col-xxl-2">
        <div class="card kpi-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="widget-loader kpi-loader"><div class="spinner-border spinner-border-sm text-primary"></div></div>
                <p class="text-muted mb-2 small fw-semibold text-uppercase">Overdue Tasks</p>
                <h3 class="mb-0 fw-bold " id="kpi-overdue">-</h3>
            </div>
        </div>
    </div>
    <!-- High Priority -->
    <div class="col-md-6 col-lg-4 col-xl-2.4 col-xxl-2">
        <div class="card kpi-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="widget-loader kpi-loader"><div class="spinner-border spinner-border-sm text-primary"></div></div>
                <p class="text-muted mb-2 small fw-semibold text-uppercase">High Priority</p>
                <h3 class="mb-0 fw-bold " id="kpi-high-priority">-</h3>
            </div>
        </div>
    </div>
    <!-- Blocked Tasks -->
    <div class="col-md-6 col-lg-4 col-xl-2.4 col-xxl-2">
        <div class="card kpi-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="widget-loader kpi-loader"><div class="spinner-border spinner-border-sm text-primary"></div></div>
                <p class="text-muted mb-2 small fw-semibold text-uppercase">Blocked Tasks</p>
                <h3 class="mb-0 fw-bold " id="kpi-blocked">-</h3>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="row g-4 mb-4">
    <!-- Tasks by Status -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-header border-0 bg-transparent pb-0">
                <h6 class="fw-bold mb-0">Tasks by Status</h6>
            </div>
            <div class="card-body">
                <div class="widget-loader chart-loader"><div class="spinner-border text-primary"></div></div>
                <div class="chart-container">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <!-- Tasks by Priority -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-header border-0 bg-transparent pb-0">
                <h6 class="fw-bold mb-0">Tasks by Priority</h6>
            </div>
            <div class="card-body">
                <div class="widget-loader chart-loader"><div class="spinner-border text-primary"></div></div>
                <div class="chart-container">
                    <canvas id="priorityChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <!-- Due Date Distribution -->
    <div class="col-md-12 col-lg-4">
        <div class="card h-100">
            <div class="card-header border-0 bg-transparent pb-0">
                <h6 class="fw-bold mb-0">Due Date Distribution (Next 7 Days)</h6>
            </div>
            <div class="card-body">
                <div class="widget-loader chart-loader"><div class="spinner-border text-primary"></div></div>
                <div class="chart-container">
                    <canvas id="dueDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Overdue Trend -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header border-0 bg-transparent pb-0">
                <h6 class="fw-bold mb-0">Overdue Task Trend (30 Days)</h6>
            </div>
            <div class="card-body">
                <div class="widget-loader chart-loader"><div class="spinner-border text-primary"></div></div>
                <div class="chart-container">
                    <canvas id="overdueTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <!-- Team Task Distribution -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header border-0 bg-transparent pb-0">
                <h6 class="fw-bold mb-0">Team Task Distribution</h6>
            </div>
            <div class="card-body">
                <div class="widget-loader chart-loader"><div class="spinner-border text-primary"></div></div>
                <div class="chart-container">
                    <canvas id="teamDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- 
<!-- Tabbed Monitoring Tables -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 pb-0 pt-3">
        <ul class="nav nav-tabs card-header-tabs" id="monitoringTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-semibold text-danger" id="overdue-tab" data-bs-toggle="tab" data-bs-target="#overdue" type="button" role="tab" aria-controls="overdue" aria-selected="true">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> Overdue Tasks
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold text-warning" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming" type="button" role="tab" aria-controls="upcoming" aria-selected="false">
                    <i class="fa-solid fa-hourglass-half me-1"></i> Upcoming Deadlines
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold text-info" id="high-priority-tab" data-bs-toggle="tab" data-bs-target="#high-priority" type="button" role="tab" aria-controls="high-priority" aria-selected="false">
                    <i class="fa-solid fa-circle-up me-1"></i> High Priority
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold text-dark" id="blocked-tab" data-bs-toggle="tab" data-bs-target="#blocked" type="button" role="tab" aria-controls="blocked" aria-selected="false">
                    <i class="fa-solid fa-ban me-1"></i> Blocked Tasks
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body position-relative">
        <div class="tab-content" id="monitoringTabsContent">
            <!-- Overdue -->
            <div class="tab-pane fade show active" id="overdue" role="tabpanel" aria-labelledby="overdue-tab">
                <div class="table-responsive">
                    <table class="table table-hover align-middle w-100" id="overdue-table">
                        <thead>
                            <tr>
                                <th>Task Title</th>
                                <th>Assigned To</th>
                                <th>Team</th>
                                <th>Priority</th>
                                <th>Due Date</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <!-- Upcoming -->
            <div class="tab-pane fade" id="upcoming" role="tabpanel" aria-labelledby="upcoming-tab">
                <div class="table-responsive">
                    <table class="table table-hover align-middle w-100" id="upcoming-table">
                        <thead>
                            <tr>
                                <th>Task Title</th>
                                <th>Assigned To</th>
                                <th>Team</th>
                                <th>Priority</th>
                                <th>Due Date</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <!-- High Priority -->
            <div class="tab-pane fade" id="high-priority" role="tabpanel" aria-labelledby="high-priority-tab">
                <div class="table-responsive">
                    <table class="table table-hover align-middle w-100" id="high-priority-table">
                        <thead>
                            <tr>
                                <th>Task Title</th>
                                <th>Assigned To</th>
                                <th>Team</th>
                                <th>Status</th>
                                <th>Due Date</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <!-- Blocked -->
            <div class="tab-pane fade" id="blocked" role="tabpanel" aria-labelledby="blocked-tab">
                <div class="table-responsive">
                    <table class="table table-hover align-middle w-100" id="blocked-table">
                        <thead>
                            <tr>
                                <th>Task Title</th>
                                <th>Assigned To</th>
                                <th>Team</th>
                                <th>Priority</th>
                                <th>Due Date</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div> --}}
@endsection

@push('page-script')
<script>
    let activeCharts = {};
    let tableConfigs = {
        '#overdue-tab': { id: '#overdue-table', url: "{{ route('tasks.monitoring.table', 'overdue') }}", columns: [
            { data: 'title', name: 'tasks.title' },
            { data: 'employee_name', name: 'users.name' },
            { data: 'team', name: 'users.team' },
            { data: 'priority', name: 'tasks.priority', render: p => `<span class="priority-badge priority-${p}">${p.toUpperCase()}</span>` },
            { data: 'formatted_deadline', name: 'tasks.deadline_at', render: d => `<span class="text-danger fw-semibold">${d}</span>` }
        ]},
        '#upcoming-tab': { id: '#upcoming-table', url: "{{ route('tasks.monitoring.table', 'upcoming') }}", columns: [
            { data: 'title', name: 'tasks.title' },
            { data: 'employee_name', name: 'users.name' },
            { data: 'team', name: 'users.team' },
            { data: 'priority', name: 'tasks.priority', render: p => `<span class="priority-badge priority-${p}">${p.toUpperCase()}</span>` },
            { data: 'formatted_deadline', name: 'tasks.deadline_at', render: d => `<span class="text-warning fw-semibold">${d}</span>` }
        ]},
        '#high-priority-tab': { id: '#high-priority-table', url: "{{ route('tasks.monitoring.table', 'high-priority') }}", columns: [
            { data: 'title', name: 'tasks.title' },
            { data: 'employee_name', name: 'users.name' },
            { data: 'team', name: 'users.team' },
            { data: 'status', name: 'tasks.status', render: s => `<span class="status-badge status-${s}">${s.replace('_', ' ').toUpperCase()}</span>` },
            { data: 'formatted_deadline', name: 'tasks.deadline_at' }
        ]},
        '#blocked-tab': { id: '#blocked-table', url: "{{ route('tasks.monitoring.table', 'blocked') }}", columns: [
            { data: 'title', name: 'tasks.title' },
            { data: 'employee_name', name: 'users.name' },
            { data: 'team', name: 'users.team' },
            { data: 'priority', name: 'tasks.priority', render: p => `<span class="priority-badge priority-${p}">${p.toUpperCase()}</span>` },
            { data: 'formatted_deadline', name: 'tasks.deadline_at' }
        ]}
    };

    $(document).ready(function() {
        // Show loaders
        $('.widget-loader').show();
        fetchMonitoringData();
        initMonitoringTables();
    });

    function refreshMonitoring() {
        $('.widget-loader').show();
        fetchMonitoringData();
        
        // Only reload tables that have actually been initialized
        Object.values(tableConfigs).forEach(conf => {
            if ($.fn.dataTable.isDataTable(conf.id)) {
                $(conf.id).DataTable().ajax.reload();
            }
        });
    }

    function destroyChart(name) {
        if (activeCharts[name]) {
            activeCharts[name].destroy();
        }
    }

    function fetchMonitoringData() {
        $.get(`{{ route('tasks.monitoring.data') }}`, function(data) {
            
            // Hide KPI loaders and update
            $('.kpi-loader').fadeOut(200);
            $('#kpi-active').text(data.kpis.totalActive);
            $('#kpi-due-today').text(data.kpis.dueToday);
            $('#kpi-overdue').text(data.kpis.overdue);
            $('#kpi-high-priority').text(data.kpis.highPriority);
            $('#kpi-blocked').text(data.kpis.blocked);

            // Hide Chart loaders
            $('.chart-loader').fadeOut(200);

            // Chart 1: Tasks by Status
            destroyChart('status');
            activeCharts['status'] = new Chart(document.getElementById('statusChart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Pending', 'In Progress', 'Completed', 'Blocked'],
                    datasets: [{
                        data: [
                            data.charts.status.pending,
                            data.charts.status.in_progress,
                            data.charts.status.completed,
                            data.charts.status.blocked
                        ],
                        backgroundColor: ['#fd7e14', '#0d6efd', '#198754', '#dc3545'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'right' } }
                }
            });

            // Chart 2: Tasks by Priority
            destroyChart('priority');
            activeCharts['priority'] = new Chart(document.getElementById('priorityChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: ['Low', 'Normal', 'High'],
                    datasets: [{
                        label: 'Tasks',
                        data: [
                            data.charts.priority.low,
                            data.charts.priority.normal,
                            data.charts.priority.high
                        ],
                        backgroundColor: ['#6c757d', '#0ea5e9', '#dc3545'],
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true } }
                }
            });

            // Chart 3: Overdue Trend
            destroyChart('overdueTrend');
            activeCharts['overdueTrend'] = new Chart(document.getElementById('overdueTrendChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: data.charts.trends.map(t => t.date),
                    datasets: [{
                        label: 'Overdue Tasks',
                        data: data.charts.trends.map(t => t.tasks_delayed),
                        borderColor: '#dc3545',
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

            // Chart 4: Due Date Distribution
            destroyChart('dueDistribution');
            activeCharts['dueDistribution'] = new Chart(document.getElementById('dueDistributionChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: data.charts.dueDates.map(d => d.date),
                    datasets: [{
                        label: 'Tasks Due',
                        data: data.charts.dueDates.map(d => d.count),
                        backgroundColor: '#f59e0b',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true } }
                }
            });

            // Chart 5: Team Task Distribution
            destroyChart('teamDistribution');
            activeCharts['teamDistribution'] = new Chart(document.getElementById('teamDistributionChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: data.charts.teamDistribution.map(t => t.team),
                    datasets: [{
                        label: 'Active Tasks',
                        data: data.charts.teamDistribution.map(t => t.count),
                        backgroundColor: '#8b5cf6',
                        borderRadius: 4
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { x: { beginAtZero: true } }
                }
            });
        }).fail(function() {
            $('.widget-loader').html('<i class="fa-solid fa-triangle-exclamation text-danger fs-4"></i>');
        });
    }

    function initTable(tabId) {
        const conf = tableConfigs[tabId];
        if (!conf) return;
        if ($.fn.dataTable.isDataTable(conf.id)) return; // already initialized

        $(conf.id).DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: conf.url,
                data: function(d) {
                    return $.extend({}, d, {});
                }
            },
            columns: conf.columns,
            pageLength: 5,
            lengthMenu: [5, 10, 25],
            dom: 'rtip'
        });
    }

    function initMonitoringTables() {
        // Initialize the active tab's table first
        const activeTabButton = $('#monitoringTabs button.active');
        if (activeTabButton.length) {
            initTable('#' + activeTabButton.attr('id'));
        }

        // Listen for tab shown event to lazy load
        $('#monitoringTabs button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            const targetTabId = '#' + $(e.target).attr('id');
            initTable(targetTabId);
            // Re-align headers when switching tabs
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        });
    }
</script>
@endpush