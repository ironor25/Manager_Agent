@extends('master')

@push('page-style')
<style>
    .action-btn { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; background: #f1f5f9; color: var(--secondary); border: none; transition: all 0.2s; }
    .action-btn:hover { background: var(--primary); color: white !important; }
    .action-btn:hover i { color: white !important; }
</style>
@endpush

@section('page-content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0  fw-bold">Access Control</h1>
            <p class="text-muted mb-0">Manage user roles and link accounts to employee profiles.</p>
        </div>
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
                <table class="table table-hover align-middle mb-0 w-100" id="usersTable">
                    <thead class="bg-light">
                        <tr>
                            <th>User Name</th>
                            <th>Email</th>
                            <th>System Role</th>
                            <th>Linked Employee Profile</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit Role Modal -->
<div class="modal fade" id="editRoleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit User Access</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editRoleForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body row">
                    <div class="col-12 mb-3">
                        <label class="form-label fw-semibold small">User Name</label>
                        <input type="text" id="edit_name" class="form-control" disabled>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label fw-semibold small">System Role</label>
                        <select name="role" id="edit_role" class="form-select" required>
                            <option value="admin">Admin</option>
                            <option value="employee">Employee</option>
                        </select>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label fw-semibold small">Linked Employee Profile</label>
                        <select name="employee_id" id="edit_employee_id" class="form-select">
                            <option value="">-- No Linked Profile --</option>
                            @php
                                $employees = \App\Models\Employee::orderBy('name')->get();
                            @endphp
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->email }})</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Linking a profile allows the user to see their personal performance data.</small>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('page-script')
<script>
$(document).ready(function() {
    $('#usersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('access-control.data') }}",
        columns: [
            { data: 'name', name: 'name', render: data => `<strong>${data}</strong>` },
            { data: 'email', name: 'email' },
            { data: 'role_badge', name: 'role' },
            { data: 'linked_employee', name: 'linked_employee', searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false, class: 'text-end' }
        ]
    });

    $('body').on('click', '.edit-role-btn', function() {
        const user = $(this).data('user');
        const form = document.getElementById('editRoleForm');
        
        form.setAttribute('action', `/access-control/${user.id}`);
        document.getElementById('edit_name').value = user.name;
        document.getElementById('edit_role').value = user.role;
        document.getElementById('edit_employee_id').value = user.employee_id || '';
        
        new bootstrap.Modal(document.getElementById('editRoleModal')).show();
    });
});
</script>
@endpush