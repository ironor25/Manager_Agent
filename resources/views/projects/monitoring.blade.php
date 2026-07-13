@extends('master')

@push('page-style')
<style>
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('page-content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0  fw-bold">Project Monitoring</h1>
            <p class="text-muted mb-0">Track project health, progress, and status.</p>
        </div>
        <div class="d-flex gap-2">
            @include('components.date-filter')
            <button class="btn btn-outline-primary btn-sm" onclick="fetchData()">
                <i class="fa-solid fa-arrows-rotate me-1"></i> Refresh
            </button>
            <button class="btn btn-danger btn-sm" onclick="exportPageToPDF('.container-fluid', 'project-monitoring.pdf')">
                <i class="fa-solid fa-file-pdf me-1"></i> Export PDF
            </button>
            <button class="btn btn-success btn-sm" onclick="exportDataTableToCSV('#monitoringTable', 'project-monitoring.csv')">
                <i class="fa-solid fa-file-excel me-1"></i> Export Excel
            </button>
        </div>
    </div>

    <!-- KPIs -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class=" mb-2 small fw-bold text-uppercase">Active Projects</h6>
                    <h3 class="mb-0 fw-bold " id="kpi-active">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class=" mb-2 small fw-bold text-uppercase">Avg Completion</h6>
                    <h3 class="mb-0 fw-bold " id="kpi-completion">0%</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class=" mb-2 small fw-bold text-uppercase">At Risk</h6>
                    <h3 class="mb-0 fw-bold " id="kpi-risk">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class=" mb-2 small fw-bold text-uppercase">Critical</h6>
                    <h3 class="mb-0 fw-bold " id="kpi-critical">0</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-0 pt-4 pb-0">
                    <h6 class="fw-bold mb-0">Projects by Status</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-0 pt-4 pb-0">
                    <h6 class="fw-bold mb-0">Projects by Category</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-0 pt-4 pb-0">
                    <h6 class="fw-bold mb-0">Project Health Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="healthChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header border-bottom py-3">
            <h5 class="fw-bold mb-0 ">Active Projects Tracking</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive px-4 py-3">
                <table class="table table-hover align-middle mb-0 w-100" id="monitoringTable">
                    <thead class="bg-light">
                        <tr>
                            <th>Project</th>
                            <th>Manager</th>
                            <th>Progress</th>
                            <th>Health Score</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-script')
<script>
    let charts = {};

    function initChart(id, type, labels, data, colors) {
        const ctx = document.getElementById(id);
        if (charts[id]) charts[id].destroy();
        
        charts[id] = new Chart(ctx, {
            type: type,
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
                    borderWidth: 0,
                    borderRadius: type === 'bar' ? 4 : 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: type === 'doughnut' ? 'bottom' : 'none',
                        labels: { padding: 20, usePointStyle: true }
                    }
                },
                scales: type === 'bar' ? {
                    y: { beginAtZero: true, grid: { borderDash: [2, 4] } },
                    x: { grid: { display: false } }
                } : undefined
            }
        });
    }

    function fetchData() {
        const qs = getDateFilterQueryString();
        fetch(`{{ route('projects.monitoring.data') }}?${qs}`)
            .then(res => res.json())
            .then(data => {
                // KPIs
                document.getElementById('kpi-active').innerText = data.kpis.active_projects;
                document.getElementById('kpi-completion').innerText = data.kpis.avg_completion + '%';
                document.getElementById('kpi-risk').innerText = data.kpis.at_risk;
                document.getElementById('kpi-critical').innerText = data.kpis.critical;

                // Status Chart
                const sLabels = Object.keys(data.charts.status);
                const sData = Object.values(data.charts.status);
                initChart('statusChart', 'doughnut', sLabels, sData, ['#3b82f6', '#10b981', '#f59e0b', '#6b7280']);

                // Category Chart
                const cLabels = Object.keys(data.charts.categories);
                const cData = Object.values(data.charts.categories);
                const cColors = cLabels.map((_, i) => `hsl(${i * 360 / cLabels.length}, 70%, 60%)`);
                initChart('categoryChart', 'bar', cLabels, cData, cColors);

                // Health Chart
                const hLabels = Object.keys(data.charts.health);
                const hData = Object.values(data.charts.health);
                const hColors = hLabels.map(l => l === 'On Track' ? '#10b981' : (l === 'At Risk' ? '#f59e0b' : '#ef4444'));
                initChart('healthChart', 'pie', hLabels, hData, hColors);
            });
            
        if ($.fn.DataTable.isDataTable('#monitoringTable')) {
            $('#monitoringTable').DataTable().ajax.reload();
        }
    }

    $(document).ready(function() {
        fetchData();

        $('#monitoringTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('projects.monitoring.table') }}",
                data: function (d) {
                    return $.extend({}, d, getDateFilterParams());
                }
            },
            columns: [
                { data: 'project_name', name: 'project_name' },
                { data: 'manager', name: 'manager' },
                { data: 'progress_bar', name: 'completion_percentage' },
                { data: 'health_score', name: 'health_score', render: function(data) { return `<strong>${data}</strong>`; } },
                { data: 'health_badge', name: 'health_status' }
            ],
            order: [[3, 'asc']] // Order by lowest health score first
        });
    });
    window.onDateFilterChange = fetchData;
</script>
@include('components.date-filter-js')
@endpush