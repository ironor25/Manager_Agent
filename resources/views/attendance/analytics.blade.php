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
            <h1 class="h3 mb-0  fw-bold">Attendance Analytics</h1>
            <p class="text-muted mb-0">Overview of company-wide attendance and trends.</p>
        </div>
        <div class="d-flex gap-2">
            @include('components.date-filter')
            <button class="btn btn-outline-primary btn-sm" onclick="fetchData()">
                <i class="fa-solid fa-arrows-rotate me-1"></i> Refresh
            </button>
        </div>
    </div>

    <!-- KPIs -->
    <div class="row mb-4">
        <div class="col-md-2 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <h6 class=" small fw-bold text-uppercase mb-2">Present Today</h6>
                    <h3 class="fw-bold  mb-0" id="kpi-present">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <h6 class=" small fw-bold text-uppercase mb-2">Absent Today</h6>
                    <h3 class="fw-bold  mb-0" id="kpi-absent">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <h6 class=" small fw-bold text-uppercase mb-2">Late Today</h6>
                    <h3 class="fw-bold  mb-0" id="kpi-late">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <h6 class=" small fw-bold text-uppercase mb-2">On Leave</h6>
                    <h3 class="fw-bold  mb-0" id="kpi-leave">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <h6 class=" small fw-bold text-uppercase mb-2">Avg Attendance</h6>
                    <h3 class="fw-bold  mb-0" id="kpi-avg-pct">0%</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <h6 class=" small fw-bold text-uppercase mb-2">Avg Score</h6>
                    <h3 class="fw-bold  mb-0" id="kpi-avg-score">0</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-0 pt-4 pb-0">
                    <h6 class="fw-bold mb-0">Daily Attendance Trend (Last 14 Days)</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-0 pt-4 pb-0">
                    <h6 class="fw-bold mb-0">Attendance Performance Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="distChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-script')
<script>
    let charts = {};

    function initChart(id, type, data, options) {
        const ctx = document.getElementById(id);
        if (charts[id]) charts[id].destroy();
        charts[id] = new Chart(ctx, { type, data, options });
    }

    function fetchData() {
        const qs = getDateFilterQueryString();
        fetch(`{{ route('attendance.analytics.data') }}?${qs}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('kpi-present').innerText = data.kpis.present;
                document.getElementById('kpi-absent').innerText = data.kpis.absent;
                document.getElementById('kpi-late').innerText = data.kpis.late;
                document.getElementById('kpi-leave').innerText = data.kpis.leave;
                document.getElementById('kpi-avg-pct').innerText = data.kpis.avg_percentage + '%';
                document.getElementById('kpi-avg-score').innerText = data.kpis.avg_score;

                initChart('trendChart', 'line', {
                    labels: data.charts.trend.labels,
                    datasets: [
                        { label: 'Present', data: data.charts.trend.present, borderColor: '#10b981', tension: 0.4 },
                        { label: 'Absent', data: data.charts.trend.absent, borderColor: '#ef4444', tension: 0.4 }
                    ]
                }, { maintainAspectRatio: false });

                const catLabels = Object.keys(data.charts.categories);
                const catData = Object.values(data.charts.categories);
                const colors = catLabels.map(l => {
                    if (l === 'Excellent') return '#10b981';
                    if (l === 'Good') return '#3b82f6';
                    if (l === 'Average') return '#f59e0b';
                    return '#ef4444';
                });

                initChart('distChart', 'pie', {
                    labels: catLabels,
                    datasets: [{ data: catData, backgroundColor: colors }]
                }, { maintainAspectRatio: false });
            });
    }

    window.onDateFilterChange = fetchData;
    $(document).ready(function() { fetchData(); });
</script>
@include('components.date-filter-js')
@endpush