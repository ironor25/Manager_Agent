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
    .action-btn:hover {
        background: var(--primary);
        color: white !important;
    }
    .action-btn:hover i {
        color: white !important;
    }
    .action-btn.delete-btn:hover {
        background: var(--danger);
        color: white !important;
    }
    .action-btn.delete-btn:hover i {
        color: white !important;
    }
</style>
@endpush

@section('page-content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0  fw-bold">Project Management</h1>
            <p class="text-muted mb-0">Create, edit, and manage projects.</p>
        </div>
        <div>
            <button class="btn btn-primary btn-sm rounded-pill px-3 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#createProjectModal">
                <i class="fa-solid fa-plus me-1"></i> Create Project
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <h6 class="fw-bold mb-2"><i class="fa-solid fa-triangle-exclamation me-2"></i> Error:</h6>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive px-4 py-3">
                        <table class="table table-hover align-middle mb-0 w-100" id="projectsTable">
                            <thead class="bg-light">
                                <tr>
                                    <th>Project Name</th>
                                    <th>Status</th>
                                    <th>Team</th>
                                    <th>Manager</th>
                                    <th>Tasks Count</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Loaded dynamically by Yajra DataTables -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Project Modal -->
<div class="modal fade" id="createProjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Create New Project</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('projects.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label fw-semibold small">Project Name</label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label fw-semibold small">Status</label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="active">Active</option>
                                <option value="completed">Completed</option>
                                <option value="on_hold">On Hold</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="category" class="form-label fw-semibold small">Category</label>
                            <input type="text" name="category" id="category" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="team_name" class="form-label fw-semibold small">Team</label>
                            <select name="team_name" id="team_name" class="form-select">
                                <option value="">No Team</option>
                                @foreach($teams as $team)
                                    <option value="{{ $team->name }}">{{ $team->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="manager_id" class="form-label fw-semibold small">Manager</label>
                            <select name="manager_id" id="manager_id" class="form-select">
                                <option value="">No Manager</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="budget_hours" class="form-label fw-semibold small">Budget Hours</label>
                            <input type="number" step="0.1" name="budget_hours" id="budget_hours" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label fw-semibold small">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label fw-semibold small">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control">
                        </div>
                        <div class="col-12 mb-3">
                            <label for="description" class="form-label fw-semibold small">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="repo_name" class="form-label fw-semibold small">Repository Name</label>
                            <input type="text" name="repo_name" id="repo_name" class="form-control" placeholder="e.g. ironor25/Manager_Agent">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="repo_url" class="form-label fw-semibold small">Repository URL</label>
                            <input type="url" name="repo_url" id="repo_url" class="form-control" placeholder="e.g. https://github.com/...">
                        </div>
                        <div class="col-12 mb-3">
                            <label for="requirements" class="form-label fw-semibold small">Requirements</label>
                            <textarea name="requirements" id="requirements" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Project</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Project Modal -->
<div class="modal fade" id="editProjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit Project</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editProjectForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_name" class="form-label fw-semibold small">Project Name</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_status" class="form-label fw-semibold small">Status</label>
                            <select name="status" id="edit_status" class="form-select" required>
                                <option value="active">Active</option>
                                <option value="completed">Completed</option>
                                <option value="on_hold">On Hold</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_category" class="form-label fw-semibold small">Category</label>
                            <input type="text" name="category" id="edit_category" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_team_name" class="form-label fw-semibold small">Team</label>
                            <select name="team_name" id="edit_team_name" class="form-select">
                                <option value="">No Team</option>
                                @foreach($teams as $team)
                                    <option value="{{ $team->name }}">{{ $team->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_manager_id" class="form-label fw-semibold small">Manager</label>
                            <select name="manager_id" id="edit_manager_id" class="form-select">
                                <option value="">No Manager</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_budget_hours" class="form-label fw-semibold small">Budget Hours</label>
                            <input type="number" step="0.1" name="budget_hours" id="edit_budget_hours" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_start_date" class="form-label fw-semibold small">Start Date</label>
                            <input type="date" name="start_date" id="edit_start_date" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_end_date" class="form-label fw-semibold small">End Date</label>
                            <input type="date" name="end_date" id="edit_end_date" class="form-control">
                        </div>
                        <div class="col-12 mb-3">
                            <label for="edit_description" class="form-label fw-semibold small">Description</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_repo_name" class="form-label fw-semibold small">Repository Name</label>
                            <input type="text" name="repo_name" id="edit_repo_name" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_repo_url" class="form-label fw-semibold small">Repository URL</label>
                            <input type="url" name="repo_url" id="edit_repo_url" class="form-control">
                        </div>
                        <div class="col-12 mb-3">
                            <label for="edit_requirements" class="form-label fw-semibold small">Requirements</label>
                            <textarea name="requirements" id="edit_requirements" class="form-control" rows="3"></textarea>
                        </div>
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

<!-- Delete Confirm Modal -->
<div class="modal fade" id="deleteProjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-4">
            <div class="text-danger mb-3">
                <i class="fa-solid fa-triangle-exclamation fs-1"></i>
            </div>
            <h4 class="fw-bold ">Delete Project?</h4>
            <p class="text-muted small">Are you sure you want to delete this project? This will remove all member associations. This action cannot be undone.</p>
            <form id="deleteProjectForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="d-flex justify-content-center gap-3 mt-4">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger px-4">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var table = $('#projectsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('projects.data') }}",
            columns: [
                { data: 'name', name: 'name', render: function(data, type, row) {
                    return `<div class="fw-bold text-primary text-decoration-underline cursor-pointer" onclick="window.location.href='/admin/projects/${row.id}/members'">${data}</div>`;
                }},
                { data: 'status_badge', name: 'status', orderable: false, searchable: false },
                { data: 'team_name_display', name: 'team_name' },
                { data: 'manager_name', name: 'manager_id', orderable: false, searchable: false },
                { data: 'tasks_count', name: 'tasks_count', searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false, class: 'text-end' }
            ]
        });

        $('body').on('click', '.edit-project-btn', function(e) {
            e.stopPropagation();
            const project = $(this).data('project');
            const form = document.getElementById('editProjectForm');
            
            form.setAttribute('action', `/admin/projects/management/${project.id}`);
            document.getElementById('edit_name').value = project.name;
            document.getElementById('edit_status').value = project.status;
            document.getElementById('edit_category').value = project.category || '';
            document.getElementById('edit_team_name').value = project.team_name || '';
            document.getElementById('edit_manager_id').value = project.manager_id || '';
            document.getElementById('edit_budget_hours').value = project.budget_hours || '';
            if(project.start_date) document.getElementById('edit_start_date').value = project.start_date.split('T')[0];
            if(project.end_date) document.getElementById('edit_end_date').value = project.end_date.split('T')[0];
            document.getElementById('edit_description').value = project.description || '';
            document.getElementById('edit_repo_name').value = project.repo_name || '';
            document.getElementById('edit_repo_url').value = project.repo_url || '';
            document.getElementById('edit_requirements').value = project.requirements || '';
            
            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('editProjectModal'));
            modal.show();
        });

        $('body').on('click', '.delete-project-btn', function(e) {
            e.stopPropagation();
            const actionUrl = $(this).data('action');
            const form = document.getElementById('deleteProjectForm');
            
            form.setAttribute('action', actionUrl);
            
            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteProjectModal'));
            modal.show();
        });
    });
</script>
@endpush