@extends('master'
)

@section('page-content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0  fw-bold">Project Reports</h1>
            <p class="text-muted mb-0">Detailed analytical breakdown of all project metrics.</p>
        </div>
        <div class="d-flex gap-2">
            @include('components.date-filter')
            <button class="btn btn-outline-secondary btn-sm" onclick="$('#reportsTable').DataTable().ajax.reload()">
                <i class="fa-solid fa-arrows-rotate me-1"></i> Refresh Data
            </button>
            <button class="btn btn-success btn-sm" onclick="exportDataTableToCSV('#reportsTable', 'project-reports.csv')">
                <i class="fa-solid fa-file-excel me-1"></i> Export Excel
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header border-bottom py-3">
            <h5 class="fw-bold mb-0 ">Metrics Data Report</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive px-4 py-3">
                <table class="table table-hover align-middle mb-0 w-100" id="reportsTable">
                    <thead class="bg-light">
                        <tr>
                            <th>Project</th>
                            <th>Team</th>
                            <th>Total Tasks</th>
                            <th>Completed Tasks</th>
                            <th>Overdue Tasks</th>
                            <th>Budget vs Actual</th>
                            <th>Health Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Loaded dynamically via Yajra DataTables -->
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
                url: "{{ route('projects.reports.data') }}",
                data: function(d) {
                    return $.extend({}, d, getDateFilterParams());
                }
            },
            columns: [
                { data: 'project_name', name: 'project_name', render: function(data) { return `<strong>${data}</strong>`; } },
                { data: 'team', name: 'team' },
                { data: 'total_tasks', name: 'total_tasks' },
                { data: 'completed_tasks', name: 'completed_tasks', class: 'text-success' },
                { data: 'overdue_tasks', name: 'overdue_tasks', render: function(data) {
                    return data > 0 ? `<span class="text-danger fw-bold">${data}</span>` : `<span class="text-muted">${data}</span>`;
                }},
                { data: 'budget_utilization', name: 'budget_utilization', orderable: false, searchable: false },
                { data: 'health_score', name: 'health_score' }
            ],
            dom: 'Bfrtip',
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
        });
    });
    window.onDateFilterChange = function() {
        $('#reportsTable').DataTable().ajax.reload();
    };
</script>
@include('components.date-filter-js')
@endpush