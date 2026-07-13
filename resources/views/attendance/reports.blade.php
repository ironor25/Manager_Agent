@extends('master')

@section('page-content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0  fw-bold">Attendance Reports</h1>
            <p class="text-muted mb-0">Detailed breakdown of employee attendance metrics.</p>
        </div>
        <div class="d-flex gap-2">
            @include('components.date-filter')
            <button class="btn btn-outline-secondary btn-sm" onclick="$('#reportsTable').DataTable().ajax.reload()">
                <i class="fa-solid fa-arrows-rotate me-1"></i> Refresh Data
            </button>
            <button class="btn btn-success btn-sm" onclick="exportDataTableToCSV('#reportsTable', 'attendance-reports.csv')">
                <i class="fa-solid fa-file-excel me-1"></i> Export Excel
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive px-4 py-3">
                <table class="table table-hover align-middle mb-0 w-100" id="reportsTable">
                    <thead class="bg-light">
                        <tr>
                            <th>Employee</th>
                            <th>Team</th>
                            <th>Present</th>
                            <th>Absent</th>
                            <th>Late</th>
                            <th>Leave</th>
                            <th>Attendance %</th>
                            <th>Score</th>
                            <th>Category</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Loaded dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-script')
<script>
    window.disableGlobalFilterLoader = true;
    $(document).ready(function() {
        $('#reportsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('attendance.reports.data') }}",
                data: function(d) {
                    return $.extend({}, d, getDateFilterParams());
                }
            },
            columns: [
                { data: 'employee_name', name: 'employee_name', render: data => `<strong>${data}</strong>` },
                { data: 'team', name: 'team' },
                { data: 'present_days', name: 'present_days' },
                { data: 'absent_days', name: 'absent_days', class: 'text-danger' },
                { data: 'late_days', name: 'late_days', class: 'text-warning' },
                { data: 'leave_days', name: 'leave_days', class: 'text-secondary' },
                { data: 'attendance_percentage', name: 'attendance_percentage' },
                { data: 'attendance_score', name: 'attendance_score' },
                { data: 'category_badge', name: 'attendance_category', orderable: false, searchable: false }
            ]
        });
    });
    window.onDateFilterChange = function() {
        $('#reportsTable').DataTable().ajax.reload();
    };
</script>
@include('components.date-filter-js')
@endpush