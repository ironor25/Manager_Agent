@extends('master')

@push('page-style')
<style>
    .fixed-card-container {
        position: sticky;
        top: 20px;
        height: calc(100vh - 40px);
        overflow-y: auto;
    }
    .metric-ring {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: #f8fafc;
        border: 8px solid var(--primary);
        margin: 0 auto 20px auto;
    }
    .metric-ring .score { font-size: 2rem; font-weight: 800; line-height: 1; color: var(--primary); }
    .metric-ring .label { font-size: 0.75rem; color: #64748b; text-transform: uppercase; letter-spacing: 1px; margin-top: 5px; }
    
    .mini-metric { display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; background: #f8fafc; border-radius: 8px; margin-bottom: 10px; }
    .mini-metric .title { font-size: 0.85rem; font-weight: 600; color: #475569; }
    .mini-metric .value { font-size: 1.1rem; font-weight: 700; color: #0f172a; }
    
    table.dataTable tbody tr.selected { background-color: rgba(59, 130, 246, 0.05) !important; }
    table.dataTable tbody tr { cursor: pointer; transition: background-color 0.2s; }
    table.dataTable tbody tr:hover { background-color: rgba(59, 130, 246, 0.02); }
</style>
@endpush

@section('page-content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0  fw-bold">Individual Leaderboard</h1>
            <p class="text-muted mb-0">Rankings based on overall task, attendance, and contribution metrics.</p>
        </div>
        <div class="d-flex gap-2">
            <select id="teamFilter" class="form-select form-select-sm shadow-sm" style="width: 200px;">
                <option value="all">All Teams (Overall)</option>
                @foreach($teams as $team)
                    <option value="{{ $team->name }}">{{ $team->name }}</option>
                @endforeach
            </select>
            <select id="dateFilter" class="form-select form-select-sm shadow-sm" style="width: 150px;">
                <option value="all_time">All Time</option>
                <option value="daily">Today</option>
                <option value="weekly">Last 7 Days</option>
                <option value="monthly">Last 30 Days</option>
                <option value="quarterly">Quarterly</option>
                <option value="yearly">Yearly</option>
                <option value="custom">Custom Range</option>
            </select>
            <div id="customDateContainer" class="d-none d-flex gap-2">
                <input type="date" id="startDate" class="form-control form-control-sm">
                <input type="date" id="endDate" class="form-control form-control-sm">
                <button class="btn btn-sm btn-primary" id="applyCustomDate">Apply</button>
            </div>
            <button class="btn btn-success btn-sm shadow-sm" onclick="exportDataTableToCSV('#leaderboardTable', 'individual-leaderboard.csv')">
                <i class="fa-solid fa-file-excel me-1"></i> Export Excel
            </button>
        </div>
    </div>

    <div class="row">
        <!-- Scrollable Table Pane -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-0">
                    <div class="table-responsive px-4 py-3">
                        <table class="table align-middle mb-0 w-100" id="leaderboardTable">
                            <thead class="bg-light">
                                <tr>
                                    <th>Rank</th>
                                    <th>Employee</th>
                                    <th>Team</th>
                                    <th>Task Completion</th>
                                    <th>Task Quality</th>
                                    <th>Attendance</th>
                                    <th>Overall Score</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sticky Employee Card Pane -->
        <div class="col-md-4">
            <div class="fixed-card-container">
                <div class="card border-0 shadow-sm" id="employeeDetailsCard" style="display: none;">
                    <div class="card-header border-bottom py-3">
                        <h6 class="fw-bold mb-0 text-center text-primary" id="cardEmpName">Employee Name</h6>
                        <p class="text-muted small text-center mb-0" id="cardEmpTeam">Team Name</p>
                    </div>
                    <div class="card-body pt-4">
                        <div class="metric-ring">
                            <span class="score" id="cardOverall">0.0</span>
                            <span class="label">Overall</span>
                        </div>
                        
                        <div class="mini-metric border-start border-4 border-primary">
                            <span class="title">Task Completion Score</span>
                            <span class="value" id="cardTaskCompletion">0.0</span>
                        </div>
                        <div class="mini-metric border-start border-4 border-success">
                            <span class="title">Task Quality Score</span>
                            <span class="value" id="cardTaskQuality">0.0</span>
                        </div>
                        <div class="mini-metric border-start border-4 border-info">
                            <span class="title">Attendance Score</span>
                            <span class="value" id="cardAttendance">0.0</span>
                        </div>
                        <div class="mini-metric border-start border-4 border-warning">
                            <span class="title" style="color: #ea580c;">GitLab Contribution</span>
                            <span class="value" id="cardGitlab">0</span>
                        </div>
                        <div class="mini-metric border-start border-4 border-secondary">
                            <span class="title">Project Contribution</span>
                            <span class="value" id="cardProject">0</span>
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm text-center py-5" id="employeeDetailsPlaceholder">
                    <div class="card-body">
                        <i class="fa-solid fa-hand-pointer fs-1 text-muted opacity-25 mb-3"></i>
                        <h6 class="">Select an employee from the table to view their detailed metrics</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-script')
<script>
$(document).ready(function() {
    let selectedEmployeeId = null;

    const table = $('#leaderboardTable').DataTable({
        processing: true,
        serverSide: true,
        order: [[6, 'desc']], // Order by overall score
        ajax: {
            url: "{{ route('leaderboard.individual.data') }}",
            data: function(d) {
                d.date_filter = $('#dateFilter').val();
                d.team = $('#teamFilter').val();
                if(d.date_filter === 'custom') {
                    d.start_date = $('#startDate').val();
                    d.end_date = $('#endDate').val();
                }
            }
        },
        columns: [
            { data: 'rank', render: function (data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; }, searchable: false, orderable: false },
            { data: 'name', name: 'name', render: data => `<span class="fw-bold">${data}</span>` },
            { data: 'team', name: 'team' },
            { data: 'task_completion_score', name: 'task_completion_score', searchable: false, render: data => parseFloat(data).toFixed(1) },
            { data: 'task_quality_score', name: 'task_quality_score', searchable: false, render: data => parseFloat(data).toFixed(1) },
            { data: 'attendance_score', name: 'attendance_score', searchable: false, render: data => parseFloat(data).toFixed(1) },
            { data: 'overall_score', name: 'overall_score', searchable: false, render: data => `<span class="badge bg-primary fs-6">${data}</span>` }
        ],
        createdRow: function(row, data, dataIndex) {
            $(row).attr('data-id', data.id);
            if (data.id == selectedEmployeeId) {
                $(row).addClass('selected');
            }
        }
    });

    $('#dateFilter').on('change', function() {
        if ($(this).val() === 'custom') {
            $('#customDateContainer').removeClass('d-none');
        } else {
            $('#customDateContainer').addClass('d-none');
            table.draw();
            refreshCard();
        }
    });

    $('#teamFilter').on('change', function() {
        table.draw();
    });

    $('#applyCustomDate').on('click', function() {
        table.draw();
        refreshCard();
    });

    $('#leaderboardTable tbody').on('click', 'tr', function() {
        if($(this).find('.dataTables_empty').length) return;
        
        $('#leaderboardTable tbody tr').removeClass('selected');
        $(this).addClass('selected');
        selectedEmployeeId = $(this).data('id');
        refreshCard();
    });

    function refreshCard() {
        if(!selectedEmployeeId) return;
        
        $('#employeeDetailsPlaceholder').hide();
        $('#employeeDetailsCard').show();
        
        // Show loading state
        $('#cardOverall').text('...');
        
        const filter = $('#dateFilter').val();
        let url = `/admin/leaderboard/individual/metrics/${selectedEmployeeId}?date_filter=${filter}`;
        if(filter === 'custom') {
            url += `&start_date=${$('#startDate').val()}&end_date=${$('#endDate').val()}`;
        }

        $.get(url, function(data) {
            $('#cardEmpName').text(data.name);
            $('#cardEmpTeam').text(data.team || 'No Team');
            $('#cardOverall').text(data.overall);
            $('#cardTaskCompletion').text(data.task_completion);
            $('#cardTaskQuality').text(data.task_quality);
            $('#cardAttendance').text(data.attendance);
            $('#cardGitlab').text(data.gitlab);
            $('#cardProject').text(data.project);
        });
    }
});
</script>
@endpush