@extends('master')

@push('page-style')
<style>
    .action-btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        background: #f1f5f9;
        color: var(--secondary);
        border: none;
        transition: all 0.2s;
    }
    .action-btn:hover { background: var(--primary); color: white !important; }
    .action-btn:hover i { color: white !important; }
    .action-btn.delete-btn:hover { background: var(--danger); }
</style>
@endpush

@section('page-content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0  fw-bold">Attendance Management</h1>
            <p class="text-muted mb-0">Record and manage daily employee attendance.</p>
        </div>
        <div class="d-flex gap-2">
            @include('components.date-filter')
            <button class="btn btn-primary btn-sm rounded-pill px-3 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fa-solid fa-plus me-1"></i> Add Record
            </button>
            <button class="btn btn-success btn-sm rounded-pill px-3 py-2 shadow-sm" onclick="exportDataTableToCSV('#dataTable', 'attendance-management.csv')">
                <i class="fa-solid fa-file-excel me-1"></i> Export Excel
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive px-4 py-3">
                <table class="table table-hover align-middle mb-0 w-100" id="dataTable">
                    <thead class="bg-light">
                        <tr>
                            <th>Employee</th>
                            <th>Team</th>
                            <th>Date</th>
                            <th>Login</th>
                            <th>Logout</th>
                            <th>Hours</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
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

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Add Attendance Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('attendance.store') }}" method="POST">
                @csrf
                <div class="modal-body row">
                    <div class="col-12 mb-3">
                        <label class="form-label fw-semibold small">Employee</label>
                        <select name="employee_id" class="form-select" required>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label fw-semibold small">Date</label>
                        <input type="date" name="date" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label fw-semibold small">Login Time</label>
                        <input type="time" name="login_time" class="form-control">
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label fw-semibold small">Logout Time</label>
                        <input type="time" name="logout_time" class="form-control">
                    </div>
                    <div class="col-6 mb-3 form-check ps-4">
                        <input class="form-check-input" type="checkbox" name="late_flag" id="late_flag" value="1">
                        <label class="form-check-label fw-semibold small" for="late_flag">Mark as Late</label>
                    </div>
                    <div class="col-6 mb-3 form-check ps-4">
                        <input class="form-check-input" type="checkbox" name="leave_flag" id="leave_flag" value="1">
                        <label class="form-check-label fw-semibold small" for="leave_flag">On Leave</label>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit Attendance Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body row">
                    <div class="col-12 mb-3">
                        <label class="form-label fw-semibold small">Employee</label>
                        <input type="text" id="edit_emp_name" class="form-control" readonly disabled>
                        <input type="hidden" name="employee_id" id="edit_emp">
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label fw-semibold small">Date</label>
                        <input type="date" name="date" id="edit_date" class="form-control" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label fw-semibold small">Login Time</label>
                        <input type="time" name="login_time" id="edit_login" class="form-control">
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label fw-semibold small">Logout Time</label>
                        <input type="time" name="logout_time" id="edit_logout" class="form-control">
                    </div>
                    <div class="col-6 mb-3 form-check ps-4">
                        <input class="form-check-input" type="checkbox" name="late_flag" id="edit_late" value="1">
                        <label class="form-check-label fw-semibold small" for="edit_late">Mark as Late</label>
                    </div>
                    <div class="col-6 mb-3 form-check ps-4">
                        <input class="form-check-input" type="checkbox" name="leave_flag" id="edit_leave" value="1">
                        <label class="form-check-label fw-semibold small" for="edit_leave">On Leave</label>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('page-script')
<script>
window.disableGlobalFilterLoader = true;
$(document).ready(function() {
    $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('attendance.data') }}",
        columns: [
            { data: 'employee_name', name: 'employee_name', render: data => `<strong>${data}</strong>` },
            { data: 'team', name: 'team' },
            { data: 'date_display', name: 'date_display' },
            { data: 'login_time', name: 'login_time', render: data => data ? data.split('T')[1].substring(0,5) : '-' },
            { data: 'logout_time', name: 'logout_time', render: data => data ? data.split('T')[1].substring(0,5) : '-' },
            { data: 'working_hours', name: 'working_hours', searchable: false },
            { data: 'attendance_status', name: 'attendance_status', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false, class: 'text-end' }
        ]
    });

    $('body').on('click', '.edit-btn', function() {
        const record = $(this).data('record');
        const form = document.getElementById('editForm');
        
        form.setAttribute('action', `/admin/attendance/management/${record.id}`);
        document.getElementById('edit_emp_name').value = record.employee_name;
        document.getElementById('edit_emp').value = record.employee_id;
        document.getElementById('edit_date').value = record.date;
        document.getElementById('edit_login').value = record.login_time;
        document.getElementById('edit_logout').value = record.logout_time;
        document.getElementById('edit_late').checked = record.late_flag;
        document.getElementById('edit_leave').checked = record.leave_flag;
        
        new bootstrap.Modal(document.getElementById('editModal')).show();
    });
});
</script>
@endpush