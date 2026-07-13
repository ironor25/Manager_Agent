@extends('master')

@section('page-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Workload Analysis</h4>
    <div class="d-flex gap-2">
        @include('components.date-filter')
        <button class="btn btn-outline-primary me-2" onclick="refreshData()">
            <i class="fa-solid fa-arrows-rotate me-1"></i> Refresh Data
        </button>
        <button class="btn btn-danger me-2" onclick="exportPageToPDF('.main-content', 'workload-analysis.pdf')">
            <i class="fa-solid fa-file-pdf me-1"></i> Export PDF
        </button>
        <button class="btn btn-primary" onclick="calculateWorkload()">
            <i class="fa-solid fa-calculator me-1"></i> Force Recalculate
        </button>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-4 col-xl-2">
        <div class="card h-100">
            <div class="card-body">
                <p class="text-muted mb-1 small fw-semibold text-uppercase">Overloaded Employees</p>
                <h3 class="mb-0 fw-bold text-danger" id="kpi-overloaded">0</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl-2">
        <div class="card h-100">
            <div class="card-body">
                <p class="text-muted mb-1 small fw-semibold text-uppercase">Underutilized</p>
                <h3 class="mb-0 fw-bold text-info" id="kpi-underutilized">0</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl-2">
        <div class="card h-100">
            <div class="card-body">
                <p class="text-muted mb-1 small fw-semibold text-uppercase">Optimal Capacity</p>
                <h3 class="mb-0 fw-bold text-success" id="kpi-optimal">0</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl-2">
        <div class="card h-100">
            <div class="card-body">
                <p class="text-muted mb-1 small fw-semibold text-uppercase">Avg Org Workload</p>
                <h3 class="mb-0 fw-bold text-primary" id="kpi-avg-workload">0%</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl-2">
        <div class="card h-100">
            <div class="card-body">
                <p class="text-muted mb-1 small fw-semibold text-uppercase">Most Loaded Team</p>
                <h5 class="mb-0 fw-bold text-warning text-truncate" id="kpi-most-loaded-team">-</h5>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl-2">
        <div class="card h-100">
            <div class="card-body">
                <p class="text-muted mb-1 small fw-semibold text-uppercase">Least Loaded Team</p>
                <h5 class="mb-0 fw-bold text-secondary text-truncate" id="kpi-least-loaded-team">-</h5>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 1 -->
<div class="row mb-4">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Workload Distribution</span>
            </div>
            <div class="card-body d-flex justify-content-center align-items-center" style="height: 300px;">
                <canvas id="distributionChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Team Workload Comparison</span>
            </div>
            <div class="card-body" style="height: 300px;">
                <canvas id="teamComparisonChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 2 -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header text-danger">
                <i class="fa-solid fa-fire me-2"></i> Top 10 Most Loaded Employees
            </div>
            <div class="card-body" style="height: 300px;">
                <canvas id="topLoadedChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header text-info">
                <i class="fa-solid fa-snowflake me-2"></i> Top 10 Least Utilized Employees
            </div>
            <div class="card-body" style="height: 300px;">
                <canvas id="leastUtilizedChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 3 -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fa-solid fa-arrow-trend-up me-2"></i> Workload Trends
            </div>
            <div class="card-body" style="height: 300px;">
                <canvas id="trendsChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Team Workload Data Table -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fa-solid fa-users me-2"></i> Team Workload Analysis
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="teamsTable">
                <thead>
                    <tr>
                        <th>Team Name</th>
                        <th>Total Members</th>
                        <th>Avg Workload %</th>
                        <th>Overloaded Count</th>
                        <th>Underutilized Count</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Employee Workload Data Table -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fa-solid fa-user-group me-2"></i> Employee Workload Details
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover w-100" id="employeesTable">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Team</th>
                        <th>Active Tasks</th>
                        <th>Completed</th>
                        <th>Overdue</th>
                        <th>Workload %</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

@endsection

@push('page-script')
<script>
    let charts = {};

    $(document).ready(function() {
        loadKpis();
        loadCharts();
        initDataTables();
    });

    function refreshData() {
        loadKpis();
        loadCharts();
        $('#employeesTable').DataTable().ajax.reload();
        $('#teamsTable').DataTable().ajax.reload();
    }

    function calculateWorkload() {
        if(!confirm('This will force calculate workload for all employees. It may take a few moments. Continue?')) return;
        
        const btn = $('button[onclick="calculateWorkload()"]');
        const originalHtml = btn.html();
        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Calculating...');

        $.ajax({
            url: "{{ route('workload.recalculate') }}",
            method: "POST",
            success: function(response) {
                btn.prop('disabled', false).html(originalHtml);
                alert(response.message || "Workload recalculation completed successfully!");
                refreshData();
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(originalHtml);
                alert("An error occurred during recalculation. Please check the logs.");
            }
        });
    }

    function loadKpis() {
        const qs = getDateFilterQueryString();
        $.get(`{{ route('workload.kpis') }}?${qs}`, function(data) {
            $('#kpi-overloaded').text(data.totalOverloaded);
            $('#kpi-underutilized').text(data.totalUnderutilized);
            $('#kpi-optimal').text(data.totalOptimal);
            $('#kpi-avg-workload').text(data.averageOrgWorkload + '%');
            $('#kpi-most-loaded-team').text(data.mostLoadedTeam);
            $('#kpi-least-loaded-team').text(data.leastLoadedTeam);
        });
    }

    function loadCharts() {
        const qs = getDateFilterQueryString();
        $.get(`{{ route('workload.chart') }}?${qs}`, function(data) {
            renderDistributionChart(data.distribution);
            renderTeamComparisonChart(data.teamComparison);
            renderTopLoadedChart(data.topLoaded);
            renderLeastUtilizedChart(data.leastUtilized);
            renderTrendsChart(data.trends);
        });
    }

    const chartColors = {
        underutilized: '#0dcaf0',
        optimal: '#198754',
        busy: '#fd7e14',
        overloaded: '#dc3545',
        primary: '#0d6efd',
        secondary: '#6c757d'
    };

    function destroyChart(id) {
        if (charts[id]) {
            charts[id].destroy();
        }
    }

    function renderDistributionChart(data) {
        destroyChart('distChart');
        const ctx = document.getElementById('distributionChart').getContext('2d');
        charts['distChart'] = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Underutilized', 'Optimal', 'Busy', 'Overloaded'],
                datasets: [{
                    data: [data.underutilized, data.optimal, data.busy, data.overloaded],
                    backgroundColor: [chartColors.underutilized, chartColors.optimal, chartColors.busy, chartColors.overloaded]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'right' } }
            }
        });
    }

    function renderTeamComparisonChart(data) {
        destroyChart('teamCompChart');
        const labels = data.map(d => d.team);
        const values = data.map(d => d.avg_workload);

        const ctx = document.getElementById('teamComparisonChart').getContext('2d');
        charts['teamCompChart'] = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Avg Workload %',
                    data: values,
                    backgroundColor: chartColors.primary
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    function renderTopLoadedChart(data) {
        destroyChart('topLoaded');
        const labels = data.map(d => d.name);
        const values = data.map(d => d.workload);

        const ctx = document.getElementById('topLoadedChart').getContext('2d');
        charts['topLoaded'] = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Workload %',
                    data: values,
                    backgroundColor: chartColors.overloaded
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                scales: { x: { beginAtZero: true } }
            }
        });
    }

    function renderLeastUtilizedChart(data) {
        destroyChart('leastUtilized');
        const labels = data.map(d => d.name);
        const values = data.map(d => d.workload);

        const ctx = document.getElementById('leastUtilizedChart').getContext('2d');
        charts['leastUtilized'] = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Workload %',
                    data: values,
                    backgroundColor: chartColors.underutilized
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                scales: { x: { beginAtZero: true } }
            }
        });
    }

    function renderTrendsChart(data) {
        destroyChart('trends');
        const labels = data.map(d => d.date);
        const avgValues = data.map(d => d.average_workload);

        const ctx = document.getElementById('trendsChart').getContext('2d');
        charts['trends'] = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Average Workload %',
                        data: avgValues,
                        borderColor: chartColors.primary,
                        tension: 0.3,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    function initDataTables() {
        $('#teamsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('workload.teams.data') }}",
                data: function(d) {
                    return $.extend({}, d, getDateFilterParams());
                }
            },
            columns: [
                { data: 'team_name', name: 'employees.team' },
                { data: 'total_members', name: 'total_members', searchable: false },
                { data: 'average_workload', name: 'average_workload', searchable: false },
                { data: 'overloaded_count', name: 'overloaded_count', searchable: false },
                { data: 'underutilized_count', name: 'underutilized_count', searchable: false }
            ],
            order: [[2, 'desc']]
        });

        $('#employeesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('workload.employees.data') }}",
                data: function(d) {
                    return $.extend({}, d, getDateFilterParams());
                }
            },
            columns: [
                { data: 'employee_name', name: 'employee.name' },
                { data: 'team', name: 'employee.team' },
                { data: 'active_tasks', name: 'active_tasks' },
                { data: 'completed_tasks', name: 'completed_tasks' },
                { data: 'overdue_tasks', name: 'overdue_tasks' },
                { 
                    data: 'workload_percentage', 
                    name: 'workload_percentage',
                    render: function(data) { return parseFloat(data).toFixed(2) + '%'; }
                },
                { 
                    data: 'status', 
                    name: 'status',
                    render: function(data) {
                        let badgeClass = 'bg-secondary';
                        if(data === 'underutilized') badgeClass = 'bg-info text-dark';
                        if(data === 'optimal') badgeClass = 'bg-success';
                        if(data === 'busy') badgeClass = 'bg-warning text-dark';
                        if(data === 'overloaded') badgeClass = 'bg-danger';
                        return `<span class="badge ${badgeClass}">${data.toUpperCase()}</span>`;
                    }
                },
                { data: 'updated_at', name: 'updated_at', searchable: false }
            ],
            order: [[5, 'desc']]
        });
    }
    window.onDateFilterChange = refreshData;
</script>
@include('components.date-filter-js')
@endpush
