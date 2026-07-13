@extends('master')

@section('page-content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0  fw-bold">Team Leaderboard</h1>
            <p class="text-muted mb-0">Rankings of teams based on aggregate metrics. Click a team to view detailed member rankings.</p>
        </div>
        <div class="d-flex gap-2">
            <select id="dateFilter" class="form-select form-select-sm shadow-sm" style="width: 150px;">
                <option value="all_time">All Time</option>
                <option value="monthly">Last 30 Days</option>
            </select>
            <button class="btn btn-success btn-sm shadow-sm" onclick="exportDataTableToCSV('#teamTable', 'team-leaderboard.csv')">
                <i class="fa-solid fa-file-excel me-1"></i> Export Excel
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-0">
            <div class="table-responsive px-4 py-3">
                <table class="table table-hover align-middle mb-0 w-100" id="teamTable" style="cursor: pointer;">
                    <thead class="bg-light">
                        <tr>
                            <th>Rank</th>
                            <th>Team Name</th>
                            <th>Productivity Score</th>
                            <th>Delivery Score</th>
                            <th>Attendance Score</th>
                            <th>Overall Score</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-script')
<script>
$(document).ready(function() {
    const table = $('#teamTable').DataTable({
        processing: true,
        serverSide: true,
        order: [[5, 'desc']], // Order by overall score
        ajax: {
            url: "{{ route('leaderboard.team.data') }}",
            data: function(d) {
                d.date_filter = $('#dateFilter').val();
            }
        },
        columns: [
            { data: 'rank', render: function (data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; }, searchable: false, orderable: false },
            { data: 'team_name', name: 'team_name', render: data => `<span class="fw-bold text-primary">${data}</span>` },
            { data: 'productivity', name: 'productivity', searchable: false, render: data => parseFloat(data).toFixed(1) },
            { data: 'delivery', name: 'delivery', searchable: false, render: data => parseFloat(data).toFixed(1) },
            { data: 'attendance_score', name: 'attendance_score', searchable: false, render: data => parseFloat(data).toFixed(1) },
            { data: 'overall_score', name: 'overall_score', searchable: false, render: data => `<span class="badge bg-primary fs-6">${data}</span>` }
        ],
        createdRow: function(row, data, dataIndex) {
            $(row).attr('data-team', data.team_name);
        }
    });

    $('#dateFilter').on('change', function() {
        table.draw();
    });

    $('#teamTable tbody').on('click', 'tr', function() {
        const teamName = $(this).data('team');
        if(teamName) {
            window.location.href = `/admin/leaderboard/team/${teamName}`;
        }
    });
});
</script>
@endpush