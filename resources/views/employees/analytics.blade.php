@extends('master')

@push('page-style')
<style>
    .kpi-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
    .chart-container { position: relative; height: 300px; width: 100%; }
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('page-content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0  fw-bold">Employee Analytics & Skill Matrix</h1>
            <p class="text-muted mb-0">High-level insights into employee distribution, skills, and overall performance.</p>
        </div>
        <div class="d-flex gap-2">
            @include('components.date-filter')
            <button class="btn btn-outline-primary btn-sm" onclick="fetchData()">
                <i class="fa-solid fa-arrows-rotate me-1"></i> Refresh
            </button>
            <button class="btn btn-danger btn-sm" onclick="exportPageToPDF('.container-fluid', 'employee-analytics.pdf')">
                <i class="fa-solid fa-file-pdf me-1"></i> Export PDF
            </button>
        </div>
    </div>

    <!-- KPIs -->
    <div class="row mb-4">
        <div class="col-md-2 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <h6 class=" small fw-bold text-uppercase mb-2">Total Employees</h6>
                    <h3 class="fw-bold  mb-0" id="kpi-total">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <h6 class=" small fw-bold text-uppercase mb-2">Active Staff</h6>
                    <h3 class="fw-bold  mb-0" id="kpi-active">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <h6 class=" small fw-bold text-uppercase mb-2">Avg Productivity</h6>
                    <h3 class="fw-bold  mb-0" id="kpi-task">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <h6 class=" small fw-bold text-uppercase mb-2">Avg Attendance</h6>
                    <h3 class="fw-bold  mb-0" id="kpi-att">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <h6 class=" small fw-bold text-uppercase mb-2">Avg Workload</h6>
                    <h3 class="fw-bold  mb-0" id="kpi-workload">0%</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-0 pt-4 pb-0">
                    <h6 class="fw-bold mb-0">Team Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="teamChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-0 pt-4 pb-0">
                    <h6 class="fw-bold mb-0">Company Workload Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="workloadChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="fa-solid fa-trophy text-warning me-2"></i>Top 5 Performers</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush" id="top-performers-list">
                        <!-- Loaded dynamically -->
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Trend -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom py-3">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-chart-line text-primary me-2"></i>Performance Trend</h5>
                    <p class="text-muted small mb-0">Company average overall score over time.</p>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 350px;">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Skill Matrix -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header border-bottom py-3">
            <h5 class="fw-bold mb-0 ">Company Skill Coverage</h5>
            <p class="text-muted small mb-0">Number of employees possessing specific skills.</p>
        </div>
        <div class="card-body">
            <div class="chart-container" style="height: 400px;">
                <canvas id="skillChart"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-script')
<script>
    let charts = {};

    const loader = document.getElementById('filter-loader');
    const showLoader = () => { if (loader) loader.classList.remove('d-none'); };
    const hideLoader = () => { if (loader) loader.classList.add('d-none'); };

    function initChart(id, type, data, options) {
        const ctx = document.getElementById(id);
        if (charts[id]) {
            charts[id].destroy();
        }
        charts[id] = new Chart(ctx, { type, data, options });
    }

    function fetchData() {
        showLoader();
        const qs = getDateFilterQueryString();
        fetch(`{{ route('employees.analytics.data') }}?${qs}`)
            .then(res => {
                if (!res.ok) throw new Error('Server error ' + res.status);
                return res.json();
            })
            .then(data => {
                hideLoader();

                document.getElementById('kpi-total').innerText = data.kpis.total;
                document.getElementById('kpi-active').innerText = data.kpis.active;
                document.getElementById('kpi-task').innerText = data.kpis.avg_task_score;
                document.getElementById('kpi-att').innerText = data.kpis.avg_attendance_score;
                document.getElementById('kpi-workload').innerText = data.kpis.avg_workload;

                const tLabels = Object.keys(data.charts.teams);
                const tData = Object.values(data.charts.teams);
                const colors = tLabels.map((_, i) => `hsl(${i * 360 / tLabels.length}, 70%, 60%)`);

                initChart('teamChart', 'doughnut', {
                    labels: tLabels,
                    datasets: [{ data: tData, backgroundColor: colors }]
                }, { 
                    maintainAspectRatio: false,
                    animation: {
                        animateRotate: true,
                        animateScale: true,
                        duration: 1000
                    }
                });

                const wLabels = Object.keys(data.charts.workload);
                const wData = Object.values(data.charts.workload);
                const wColors = wLabels.map(l => {
                    if (l === 'optimal') return '#10b981';
                    if (l === 'busy') return '#3b82f6';
                    if (l === 'underutilized') return '#94a3b8';
                    return '#ef4444';
                });

                initChart('workloadChart', 'pie', {
                    labels: wLabels,
                    datasets: [{ data: wData, backgroundColor: wColors }]
                }, { 
                    maintainAspectRatio: false,
                    animation: {
                        animateRotate: true,
                        animateScale: true,
                        duration: 1000
                    }
                });

                initChart('trendChart', 'line', {
                    labels: data.charts.trends.labels,
                    datasets: [{
                        label: 'Avg Overall Score',
                        data: data.charts.trends.data,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.3
                    }]
                }, {
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true, max: 100 } },
                    plugins: { legend: { display: false } }
                });

                initChart('skillChart', 'bar', {
                    labels: data.charts.skills.labels,
                    datasets: [{
                        label: 'Employee Count',
                        data: data.charts.skills.data,
                        backgroundColor: '#3b82f6',
                        borderRadius: 4
                    }]
                }, {
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    scales: { x: { beginAtZero: true } }
                });

                const perfList = document.getElementById('top-performers-list');
                perfList.innerHTML = '';
                if (!data.top_performers || data.top_performers.length === 0) {
                    perfList.innerHTML = '<li class="list-group-item text-muted text-center py-4">No data for selected period</li>';
                } else {
                    data.top_performers.forEach((p, i) => {
                        perfList.innerHTML += `
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div class="d-flex align-items-center">
                                    <span class="fw-bold text-muted me-3">#${i+1}</span>
                                    <div>
                                        <h6 class="mb-0 fw-bold">${p.name}</h6>
                                        <small class="text-muted">${p.team || 'No Team'}</small>
                                    </div>
                                </div>
                                <span class="badge bg-success rounded-pill p-2">${parseFloat(p.overall_score).toFixed(1)}</span>
                            </li>
                        `;
                    });
                }
            })
            .catch(err => {
                hideLoader();
                console.error('Analytics fetch error:', err);
            });
    }

    window.onDateFilterChange = fetchData;
    $(document).ready(function() { fetchData(); });
</script>
@include('components.date-filter-js')
@endpush