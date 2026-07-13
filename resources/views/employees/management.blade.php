@extends('master')

@push('page-style')
<style>
    .action-btn { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; background: #f1f5f9; color: var(--secondary); border: none; transition: all 0.2s; }
    .action-btn:hover { background: var(--primary); color: white !important; }
    .action-btn.delete-btn:hover { background: var(--danger); color: white !important; }
    #employeesTable tbody tr { cursor: pointer; }
</style>
@endpush

@section('page-content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0  fw-bold">Employee Management</h1>
            <p class="text-muted mb-0">Manage all employee records and profiles.</p>
        </div>
        <button class="btn btn-primary btn-sm rounded-pill px-3 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="fa-solid fa-plus me-1"></i> Add Employee
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive px-4 py-3">
                <table class="table table-hover align-middle mb-0 w-100" id="employeesTable">
                    <thead class="bg-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Team</th>
                            <th>Join Date</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Add Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold small">Full Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold small">Email Address</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold small">Team</label>
                        <select name="team" class="form-select">
                            <option value="">No Team</option>
                            @foreach($teams as $team)
                                <option value="{{ $team->name }}">{{ $team->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold small">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="on_leave">On Leave</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold small">Employee Photo</label>
                        <input type="file" name="photo" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold small">Full Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold small">Email Address</label>
                        <input type="email" name="email" id="edit_email" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold small">Team</label>
                        <select name="team" id="edit_team" class="form-select">
                            <option value="">No Team</option>
                            @foreach($teams as $team)
                                <option value="{{ $team->name }}">{{ $team->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold small">Status</label>
                        <select name="status" id="edit_status" class="form-select" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="on_leave">On Leave</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold small">Password</label>
                        <input type="text" name="password" id="edit_password" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold small">Employee Photo</label>
                        <div class="d-flex align-items-center gap-3">
                            <div id="edit_photo_container">
                                <img id="edit_photo_preview" src="" alt="Employee Photo" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover; display: none;">
                                <div id="edit_photo_placeholder" class="bg-light border rounded d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                    <i class="fa-solid fa-user text-muted fa-2x"></i>
                                </div>
                            </div>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteEmpModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center pb-4">
                <div class="mb-3">
                    <i class="fa-solid fa-triangle-exclamation text-danger" style="font-size: 3rem;"></i>
                </div>
                <h4 class="fw-bold mb-2">Delete Employee?</h4>
                <p class="text-muted mb-4">Are you sure you want to delete this employee? This action cannot be undone.</p>
                
                <form id="deleteEmpForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger px-4">Yes, Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('page-script')
<script>
$(document).ready(function() {
    $('#employeesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('employees.data') }}",
        columns: [
            { data: 'name', name: 'name', render: function(data, type, row) {
                return `<a href="/admin/employees/management/${row.id}" class="text-decoration-none fw-bold">${data}</a>`;
            }},
            { data: 'email', name: 'email' },
            { data: 'team', name: 'team' },
            { data: 'joining_date', name: 'joining_date' },
            { data: 'status_badge', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false, class: 'text-end' }
        ]
    });

    // Make rows clickable to redirect to profile page
    $('#employeesTable tbody').on('click', 'tr', function(e) {
        if ($(e.target).closest('a, button, form, input, select').length) {
            return;
        }
        const table = $('#employeesTable').DataTable();
        const rowData = table.row(this).data();
        if (rowData && rowData.id) {
            window.location.href = `/admin/employees/management/${rowData.id}`;
        }
    });

    $('body').on('click', '.edit-emp-btn', function() {
        const emp = $(this).data('emp');
        const form = document.getElementById('editForm');
        
        form.setAttribute('action', `/admin/employees/management/${emp.id}`);
        document.getElementById('edit_name').value = emp.name;
        document.getElementById('edit_email').value = emp.email;
        document.getElementById('edit_team').value = emp.team || '';
        document.getElementById('edit_status').value = emp.status || 'active';
        document.getElementById('edit_password').value = emp.password || '';
        
        if (emp.photo) {
            document.getElementById('edit_photo_preview').src = `/storage/${emp.photo}`;
            document.getElementById('edit_photo_preview').style.display = 'block';
            document.getElementById('edit_photo_placeholder').style.display = 'none';
        } else {
            document.getElementById('edit_photo_preview').style.display = 'none';
            document.getElementById('edit_photo_placeholder').style.display = 'flex';
        }
        
        new bootstrap.Modal(document.getElementById('editModal')).show();
    });

    $('body').on('click', '.trigger-delete-emp', function() {
        const actionUrl = $(this).data('action');
        $('#deleteEmpForm').attr('action', actionUrl);
        new bootstrap.Modal(document.getElementById('deleteEmpModal')).show();
    });
});
</script>
@endpush