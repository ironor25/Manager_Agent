@extends('master')

@push('page-style')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<style>
    .rank-badge {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: white;
    }
    .action-btn {
        background: transparent;
        border: none;
        color: var(--text-muted);
        padding: 6px;
        border-radius: 6px;
        transition: all 0.2s;
        cursor: pointer;
    }
    .action-btn:hover {
        background: var(--light);
        color: var(--primary);
    }
    .action-btn.delete-btn:hover {
        color: var(--danger);
    }
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 0.9rem;
        outline: none;
        transition: border-color 0.2s;
    }
    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: var(--primary);
    }
    .dataTables_wrapper .dataTables_length select {
        border: 1px solid var(--border-color);
        border-radius: 6px;
        padding: 4px 24px 4px 8px;
        min-width: 65px;
    }
    .page-item.active .page-link {
        background-color: var(--primary);
        border-color: var(--primary);
    }
    .page-link {
        color: var(--text-muted);
    }
</style>
@endpush

@section('page-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1 ">Team Management</h3>
        <p class="text-muted mb-0">Create, manage, and assign tasks to your teams</p>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 ">Teams Overview</h5>
                <div>
                    <button class="btn btn-outline-primary btn-sm me-2 shadow-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#assignTaskModal">
                        <i class="fa-solid fa-tasks me-1"></i> Assign Task
                    </button>
                    <button class="btn btn-primary btn-sm shadow-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#createTeamModal">
                        <i class="fa-solid fa-plus me-1"></i> Create Team
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive px-4 py-3">
                    <table class="table table-hover align-middle mb-0 w-100" id="teamsTable">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3" style="width: 25%;">Team Name</th>
                                <th class="py-3" style="width: 45%;">Description</th>
                                <th class="py-3" style="width: 15%;">Members</th>
                                <th class="text-end px-4 py-3" style="width: 15%;">Actions</th>
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

<!-- Modals -->
<!-- Create Team Modal -->
<div class="modal fade" id="createTeamModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('teams.store') }}" method="POST" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold "><i class="fa-solid fa-plus-circle text-primary me-2"></i>Create New Team</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-medium">Team Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. Engineering">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Optional team details..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Add Members (Optional)</label>
                    <select name="employee_ids[]" id="create_team_employees" class="form-select employee-search" multiple="multiple" data-placeholder="Search for employees...">
                    </select>
                    <small class="text-muted">Search by employee name.</small>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-3">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary px-4">Create Team</button>
            </div>
        </form>
    </div>
</div>

<!-- Assign Task Modal -->
<div class="modal fade" id="assignTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold "><i class="fa-solid fa-tasks text-primary me-2"></i>Assign Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Nav tabs -->
                <ul class="nav nav-pills mb-4 bg-light p-1 rounded-pill" id="assignTaskTabs" role="tablist">
                    <li class="nav-item w-50" role="presentation">
                        <button class="nav-link active w-100 rounded-pill fw-medium" id="assign-team-tab" data-bs-toggle="pill" data-bs-target="#assign-team" type="button" role="tab" aria-selected="true">
                            Assign to Team (Bulk)
                        </button>
                    </li>
                    <li class="nav-item w-50" role="presentation">
                        <button class="nav-link w-100 rounded-pill fw-medium" id="assign-individual-tab" data-bs-toggle="pill" data-bs-target="#assign-individual" type="button" role="tab" aria-selected="false">
                            Assign to Individual
                        </button>
                    </li>
                </ul>

                <form id="assignTaskForm" action="{{ route('teams.assign_task') }}" method="POST">
                    @csrf
                    <input type="hidden" name="assignment_type" id="assignment_type" value="team">
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Select Team</label>
                            <select name="team_name" id="assign_team_name" class="form-select" required>
                                <option value="">-- Select Team --</option>
                                @foreach($teams as $team)
                                    <option value="{{ $team->name }}">{{ $team->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6" id="individual_employee_wrapper" style="display: none;">
                            <label class="form-label fw-medium">Select Employee</label>
                            <select name="employee_id" id="assign_employee_id" class="form-select">
                                <option value="">-- Select Employee --</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Task Title</label>
                        <input type="text" name="title" class="form-control" required placeholder="e.g. Update UI design">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Task details..."></textarea>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="low">Low</option>
                                <option value="normal" selected>Normal</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Deadline (Optional)</label>
                            <input type="datetime-local" name="deadline_at" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Est. Hours (Optional)</label>
                            <input type="number" step="0.5" min="0" name="estimated_hours" class="form-control" placeholder="e.g. 2.5">
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top text-end">
                        <button type="button" class="btn btn-secondary px-4 me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4" id="assignTaskSubmitBtn">Assign Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Team Modal -->
<div class="modal fade" id="editTeamModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="editTeamForm" method="POST" class="modal-content border-0 shadow">
            @csrf
            @method('PUT')
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold "><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Edit Team</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-medium">Team Name</label>
                    <input type="text" id="edit_team_name" class="form-control" disabled>
                    <small class="text-muted">Team names cannot be changed currently.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Description</label>
                    <textarea name="description" id="edit_team_description" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-3">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary px-4">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Team Modal -->
<div class="modal fade" id="deleteTeamModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-4 border-0 shadow">
            <div class="mb-3">
                <i class="fa-solid fa-triangle-exclamation text-danger" style="font-size: 3rem;"></i>
            </div>
            <h5 class="mb-3 fw-bold">Delete Team</h5>
            <p class="text-muted mb-4" style="font-size: 0.9rem;">Are you sure you want to delete this team? Members will be safely unassigned.</p>
            <div class="d-flex justify-content-center gap-2">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteTeamForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger px-4">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Custom Confirmation Modal -->
<div class="modal fade" id="customConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-4 border-0 shadow" style="border-radius: 16px;">
            <div class="mb-3" id="customConfirmIconContainer">
                <i class="fa-solid fa-circle-question text-warning" style="font-size: 3rem;"></i>
            </div>
            <h5 class="mb-3 fw-bold" id="customConfirmTitle">Confirm Action</h5>
            <p class="text-muted mb-4" id="customConfirmMessage" style="font-size: 0.9rem;">Are you sure you want to proceed?</p>
            <div class="d-flex justify-content-center gap-2">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary px-4" id="customConfirmSubmitBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Yajra DataTable
        var table = $('#teamsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('teams.data') }}",
            columns: [
                { data: 'name', name: 'name', render: function(data, type, row) {
                    return `<div class="fw-bold text-primary text-decoration-underline">${data}</div>`;
                }},
                { data: 'description', name: 'description', render: function(data, type, row) {
                    return data ? `<div class="text-muted small text-truncate" style="max-width: 450px;">${data}</div>` : '<span class="text-muted fst-italic small">No description provided.</span>';
                }},
                { data: 'employees_count', name: 'employees_count', searchable: false, render: function(data, type, row) {
                    return `<span class="badge bg-light text-primary border border-primary-subtle"><i class="fa-solid fa-users me-1"></i> ${data}</span>`;
                }},
                { data: 'action', name: 'action', orderable: false, searchable: false, class: 'text-end px-4' }
            ],
            createdRow: function(row, data, dataIndex) {
                $(row).attr('data-team', data.name).addClass('cursor-pointer');
            },
            language: {
                search: "",
                searchPlaceholder: "Search teams..."
            },
            dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
        });

        // Clickable rows to details page
        $('#teamsTable').on('click', 'tbody tr', function(e) {
            if ($(e.target).closest('button, a, .action-btn, form').length > 0) {
                return;
            }
            const teamName = $(this).attr('data-team');
            if (teamName) {
                window.location.href = `/admin/teams/${encodeURIComponent(teamName)}/details`;
            }
        });

        // Edit team event delegation
        $('body').on('click', '.edit-team-btn', function(e) {
            e.stopPropagation();
            const team = $(this).attr('data-team');
            const desc = $(this).attr('data-desc');
            document.getElementById('edit_team_name').value = team;
            document.getElementById('edit_team_description').value = desc || '';
            document.getElementById('editTeamForm').action = `/admin/teams/${encodeURIComponent(team)}`;
            new bootstrap.Modal(document.getElementById('editTeamModal')).show();
        });

        // Delete team event delegation
        $('body').on('click', '.trigger-team-delete', function(e) {
            e.stopPropagation();
            const team = $(this).attr('data-team');
            document.getElementById('deleteTeamForm').action = `/admin/teams/${encodeURIComponent(team)}`;
            new bootstrap.Modal(document.getElementById('deleteTeamModal')).show();
        });

        // Initialize Select2 with AJAX
        $('.employee-search').select2({
            theme: 'bootstrap-5',
            dropdownParent: function(elem) {
                return elem.closest('.modal') || document.body;
            },
            ajax: {
                url: '/admin/employees/search',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.name + (item.team ? ' (' + item.team + ')' : ' (No Team)'),
                                id: item.id
                            }
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 0
        });

        // Setup custom confirm submit button click handler
        const customConfirmSubmitBtn = document.getElementById('customConfirmSubmitBtn');
        if (customConfirmSubmitBtn) {
            customConfirmSubmitBtn.addEventListener('click', function() {
                if (onCustomConfirmCallback) {
                    onCustomConfirmCallback();
                }
                const modalEl = document.getElementById('customConfirmModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            });
        }
    });

    let onCustomConfirmCallback = null;

    function showCustomConfirm({ title, message, iconClass, btnClass, onConfirm }) {
        document.getElementById('customConfirmTitle').innerText = title;
        document.getElementById('customConfirmMessage').innerText = message;
        
        const iconContainer = document.getElementById('customConfirmIconContainer');
        iconContainer.innerHTML = `<i class="${iconClass || 'fa-solid fa-circle-question text-warning'}" style="font-size: 3rem;"></i>`;
        
        const submitBtn = document.getElementById('customConfirmSubmitBtn');
        submitBtn.className = `btn ${btnClass || 'btn-primary'} px-4`;
        
        onCustomConfirmCallback = onConfirm;
        
        const modalEl = document.getElementById('customConfirmModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    function showToast(message, type = 'success') {
        var container = $('.toast-container');
        if (container.length === 0) {
            container = $('<div class="toast-container position-fixed bottom-0 end-0 p-4" style="z-index: 1070;"></div>');
            $('body').append(container);
        }
        var bgClass = 'bg-success';
        if (type === 'danger') {
            bgClass = 'bg-danger';
        } else if (type === 'warning') {
            bgClass = 'bg-warning text-dark';
        } else if (type === 'info') {
            bgClass = 'bg-info text-dark';
        }
        var toastHtml = `
            <div class="toast align-items-center text-white ${bgClass} border-0 shadow-lg mb-2" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
                <div class="d-flex">
                    <div class="toast-body fw-medium py-3 px-4" style="font-size: 0.95rem;">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        var toastEl = $(toastHtml);
        container.append(toastEl);
        var toast = new bootstrap.Toast(toastEl[0]);
        toast.show();
        
        toastEl.on('hidden.bs.toast', function () {
            toastEl.remove();
        });
    }

    // Assign Task Logic
    const assignTaskTabs = document.getElementById('assignTaskTabs');
    const assignTypeInput = document.getElementById('assignment_type');
    const employeeWrapper = document.getElementById('individual_employee_wrapper');
    const employeeSelect = document.getElementById('assign_employee_id');
    const teamSelect = document.getElementById('assign_team_name');

    if (assignTaskTabs) {
        assignTaskTabs.addEventListener('click', function(e) {
            if (e.target.id === 'assign-individual-tab') {
                assignTypeInput.value = 'individual';
                employeeWrapper.style.display = 'block';
                employeeSelect.required = true;
            } else if (e.target.id === 'assign-team-tab') {
                assignTypeInput.value = 'team';
                employeeWrapper.style.display = 'none';
                employeeSelect.required = false;
            }
        });
    }

    if (teamSelect) {
        teamSelect.addEventListener('change', function() {
            const teamName = this.value;
            employeeSelect.innerHTML = '<option value="">-- Loading... --</option>';
            
            if (teamName) {
                fetch(`/admin/teams/${encodeURIComponent(teamName)}/members/list`)
                    .then(res => res.json())
                    .then(members => {
                        employeeSelect.innerHTML = '<option value="">-- Select Employee --</option>';
                        members.forEach(member => {
                            employeeSelect.innerHTML += `<option value="${member.id}">${member.name}</option>`;
                        });
                    })
                    .catch(err => {
                        console.error("Failed to load members data", err);
                        employeeSelect.innerHTML = '<option value="">-- Error loading members --</option>';
                    });
            } else {
                employeeSelect.innerHTML = '<option value="">-- Select Employee --</option>';
            }
        });
    }

    const assignTaskForm = document.getElementById('assignTaskForm');
    if (assignTaskForm) {
        assignTaskForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const submitBtn = document.getElementById('assignTaskSubmitBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Assigning...';
            submitBtn.disabled = true;

            fetch(this.action, {
                method: 'POST',
                body: new FormData(this),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                
                if (data.success) {
                    showToast(data.message || 'Task assigned successfully!', 'success');
                    const modal = bootstrap.Modal.getInstance(document.getElementById('assignTaskModal'));
                    if (modal) modal.hide();
                    assignTaskForm.reset();
                    employeeSelect.innerHTML = '<option value="">-- Select Employee --</option>';
                } else {
                    showToast(data.message || 'Failed to assign task.', 'danger');
                }
            })
            .catch(err => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                showToast('Network error occurred while assigning task.', 'danger');
                console.error(err);
            });
        });
    }
</script>
@endpush