@extends('master')

@push('page-style')
<style>
    .leaderboard-header {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        border-radius: 12px;
        padding: 30px;
        margin-bottom: 24px;
        box-shadow: 0 4px 15px rgba(217, 119, 6, 0.15);
    }
    .rank-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.9rem;
    }
    .tr-highlighted {
        background-color: rgba(var(--primary-rgb), 0.05) !important;
        border-left: 4px solid var(--primary);
        font-weight: 600;
    }
    .badge-score {
        font-size: 0.95rem;
        padding: 6px 12px;
        border-radius: 20px;
    }
</style>
@endpush

@section('page-content')
<div class="container-fluid">
    @if(!$employee)
        <div class="alert alert-warning border-0 shadow-sm p-4 mb-4" role="alert">
            <h5 class="alert-heading fw-bold mb-1">No Profile Linked</h5>
            <p class="mb-0 text-muted">Your account is not linked to any employee profile.</p>
        </div>
    @else
        <!-- Leaderboard Welcome Banner -->
        <div class="leaderboard-header shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <i class="fa-solid fa-medal fa-3x"></i>
                <div>
                    <h3 class="fw-bold mb-1">Team Leaderboard: {{ $employee->team ?? 'Unassigned' }}</h3>
                    <p class="mb-0 opacity-90 fs-6">Find where you stand compared to your teammates based on performance metrics.</p>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle w-100 mb-0" id="employee-leaderboard-table">
                        <thead class="table-light">
                            <tr>
                                <th width="80">Rank</th>
                                <th>Employee Name</th>
                                <th>Designation</th>
                                <th>Task Completion</th>
                                <th>Task Quality</th>
                                <th>Attendance</th>
                                <th>Git Commit Score</th>
                                <th>Overall Score</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('page-script')
<script>
$(document).ready(function() {
    $('#employee-leaderboard-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('employee.leaderboard.data') }}",
        createdRow: function(row, data, dataIndex) {
            if (data.is_self) {
                $(row).addClass('tr-highlighted');
            }
        },
        columns: [
            { 
                data: null, 
                orderable: false, 
                searchable: false, 
                render: function (data, type, row, meta) {
                    const rank = meta.row + meta.settings._iDisplayStart + 1;
                    let circleBg = '#cbd5e1';
                    let circleColor = '#1e293b';
                    if (rank == 1) { circleBg = '#f59e0b'; circleColor = '#ffffff'; }
                    else if (rank == 2) { circleBg = '#94a3b8'; circleColor = '#ffffff'; }
                    else if (rank == 3) { circleBg = '#b45309'; circleColor = '#ffffff'; }
                    return `<div class="rank-circle" style="background-color: ${circleBg}; color: ${circleColor};">${rank}</div>`;
                }
            },
            { 
                data: 'name', 
                name: 'name', 
                render: function(data, type, row) {
                    let label = data;
                    if (row.is_self) {
                        label += ' <span class="badge bg-primary ms-2">You</span>';
                    }
                    return `<div class="fw-bold">${label}</div>`;
                }
            },
            { data: 'designation', name: 'designation', defaultContent: '<span class="text-muted">N/A</span>' },
            { data: 'task_completion_score', name: 'task_completion_score', searchable: false },
            { data: 'task_quality_score', name: 'task_quality_score', searchable: false },
            { data: 'attendance_score', name: 'attendance_score', searchable: false },
            { data: 'gitlab_score', name: 'gitlab_score', searchable: false },
            { 
                data: 'overall_score', 
                name: 'overall_score', 
                searchable: false,
                render: function(data) {
                    return `<span class="badge bg-primary badge-score">${data}</span>`;
                }
            }
        ]
    });
});
</script>
@endpush
