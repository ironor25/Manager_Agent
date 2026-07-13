@extends('master')

@push('page-style')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .project-header-card {
        background: linear-gradient(135deg, rgba(79, 70, 229, 0.1) 0%, rgba(6, 182, 212, 0.05) 100%);
        border: 1px solid rgba(79, 70, 229, 0.2);
        border-radius: 16px;
    }
</style>
@endpush

@section('page-content')
@php
    $teams = $employees->pluck('team')->unique()->filter()->values();
@endphp
<div class="container-fluid">
    <div class="mb-4">
        <a href="{{ route('projects.index') }}" class="text-decoration-none text-muted mb-3 d-inline-block">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Projects Overview
        </a>
        
        <div class="project-header-card p-4 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1 text-primary"><i class="fa-solid fa-diagram-project me-2"></i>{{ $project->name }} Project</h2>
                <p class="text-muted mb-0" style="max-width: 600px;">{{ $project->description ?? 'No description provided.' }}</p>
            </div>
            <div>
                <button class="btn btn-primary px-4 py-2 shadow-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                    <i class="fa-solid fa-user-plus me-2"></i> Add Member
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header border-bottom p-0">
            <ul class="nav nav-tabs nav-justified border-0" id="projectTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-semibold py-3 border-0 border-bottom border-primary border-3" id="nav-team-tab" data-bs-toggle="tab" data-bs-target="#nav-team" type="button" role="tab" aria-controls="nav-team" aria-selected="true">
                        <i class="fa-solid fa-users me-2"></i>Team (<span id="totalMembersCount">0</span>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-semibold py-3 border-0 text-muted" id="nav-tasks-tab" data-bs-toggle="tab" data-bs-target="#nav-tasks" type="button" role="tab" aria-controls="nav-tasks" aria-selected="false">
                        <i class="fa-solid fa-list-check me-2"></i>Tasks
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-semibold py-3 border-0 text-muted" id="nav-commits-tab" data-bs-toggle="tab" data-bs-target="#nav-commits" type="button" role="tab" aria-controls="nav-commits" aria-selected="false">
                        <i class="fa-solid fa-code-commit me-2"></i>Commits
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-semibold py-3 border-0 text-muted" id="nav-data-tab" data-bs-toggle="tab" data-bs-target="#nav-data" type="button" role="tab" aria-controls="nav-data" aria-selected="false">
                        <i class="fa-solid fa-database me-2"></i>Data / Metadata
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-0">
            <div class="tab-content" id="nav-tabContent">
                <!-- Team Tab -->
                <div class="tab-pane fade show active" id="nav-team" role="tabpanel" aria-labelledby="nav-team-tab" tabindex="0">
                    <div class="table-responsive px-4 py-3">
                        <table class="table table-hover align-middle mb-0 w-100" id="membersTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="px-4 py-3">Employee Name</th>
                                    <th class="py-3">Email</th>
                                    <th class="py-3">Team</th>
                                    <th class="text-end px-4 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Loaded dynamically by Yajra DataTables -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tasks Tab -->
                <div class="tab-pane fade" id="nav-tasks" role="tabpanel" aria-labelledby="nav-tasks-tab" tabindex="0">
                    <div class="d-flex justify-content-end px-4 pt-3 pb-0">
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                            <i class="fa-solid fa-plus me-1"></i> Add Task
                        </button>
                    </div>
                    <div class="table-responsive px-4 py-3">
                        <table class="table table-hover align-middle mb-0 w-100" id="tasksTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="px-4 py-3">Task Title</th>
                                    <th class="py-3">Assignee</th>
                                    <th class="py-3">Priority</th>
                                    <th class="py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Loaded dynamically by Yajra DataTables -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Commits Tab -->
                <div class="tab-pane fade" id="nav-commits" role="tabpanel" aria-labelledby="nav-commits-tab" tabindex="0">
                    <div class="table-responsive px-4 py-3">
                        <table class="table table-hover align-middle mb-0 w-100" id="commitsTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="px-4 py-3">Commit Message</th>
                                    <th class="py-3">Employee</th>
                                    <th class="py-3">Source</th>
                                    <th class="py-3">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Loaded dynamically by Yajra DataTables -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Data Tab -->
                <div class="tab-pane fade" id="nav-data" role="tabpanel" aria-labelledby="nav-data-tab" tabindex="0">
                    <div class="p-4">
                        <!-- Edit Mode Form (Initially Hidden) -->
                        <form id="metadataForm" style="display: none;">
                            @csrf
                            <input type="hidden" name="_method" value="PUT">
                            <!-- Existing fields that the update controller method expects -->
                            <input type="hidden" name="name" value="{{ $project->name }}">
                            <input type="hidden" name="status" value="{{ $project->status }}">
                            
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-muted text-uppercase">Repository Name</label>
                                    <input type="text" name="repo_name" class="form-control shadow-sm" value="{{ $project->repo_name }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-muted text-uppercase">Repository URL</label>
                                    <input type="url" name="repo_url" class="form-control shadow-sm" value="{{ $project->repo_url }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold small text-muted text-uppercase">Requirements / Specifications</label>
                                    <textarea name="requirements" class="form-control shadow-sm" rows="6">{{ $project->requirements }}</textarea>
                                </div>
                                <div class="col-12 text-end">
                                    <button type="button" class="btn btn-light me-2 border" id="cancelMetadataEdit">Cancel</button>
                                    <button type="submit" class="btn btn-primary px-4" id="saveMetadataBtn">Save Changes</button>
                                </div>
                            </div>
                        </form>

                        <!-- Read Only View (Initially Shown) -->
                        <div id="metadataReadView">
                            <div class="d-flex justify-content-end mb-3">
                                <button class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm" id="editMetadataBtn">
                                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit Metadata
                                </button>
                            </div>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <h6 class=" fw-bold text-uppercase mb-2" style="font-size: 0.8rem;">Repository Name</h6>
                                    <p class="fw-semibold text-body fs-5" id="read_repo_name">{{ $project->repo_name ?: 'Not set' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class=" fw-bold text-uppercase mb-2" style="font-size: 0.8rem;">Repository URL</h6>
                                    <p id="read_repo_url_wrapper">
                                        @if($project->repo_url)
                                            <a href="{{ $project->repo_url }}" target="_blank" class="fw-semibold text-primary text-decoration-none fs-5"><i class="fa-solid fa-link me-2"></i>{{ $project->repo_url }}</a>
                                        @else
                                            <span class="fw-semibold text-body fs-5">Not set</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="col-12">
                                    <h6 class=" fw-bold text-uppercase mb-2" style="font-size: 0.8rem;">Requirements / Specifications</h6>
                                    <div class="bg-light p-4 rounded-3 border">
                                        @if($project->requirements)
                                            <p class="mb-0 text-body" id="read_requirements" style="white-space: pre-wrap;">{{ $project->requirements }}</p>
                                        @else
                                            <p class="text-muted fst-italic mb-0" id="read_requirements">No requirements defined yet.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Member Modal -->
<div class="modal fade" id="addMemberModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Add Members to Project</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pb-0">
                <!-- Nav Tabs for selection modes -->
                <ul class="nav nav-pills nav-fill mb-3" id="addMemberTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active small py-2 fw-semibold" id="by-team-tab" data-bs-toggle="tab" data-bs-target="#by-team" type="button" role="tab" aria-controls="by-team" aria-selected="true">
                            <i class="fa-solid fa-users me-1"></i> Select by Team
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link small py-2 fw-semibold" id="by-name-tab" data-bs-toggle="tab" data-bs-target="#by-name" type="button" role="tab" aria-controls="by-name" aria-selected="false">
                            <i class="fa-solid fa-magnifying-glass me-1"></i> Search by Name
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content pt-2" id="addMemberTabsContent">
                    <!-- By Team Tab -->
                    <div class="tab-pane fade show active mb-3" id="by-team" role="tabpanel" aria-labelledby="by-team-tab">
                        <form id="addMultipleMembersForm">
                            <div class="mb-3">
                                <label class="form-label text-muted fw-semibold small">Choose Teams</label>
                                <select id="team_select" class="form-select" multiple="multiple" style="width: 100%;">
                                    @foreach($teams as $team)
                                        <option value="{{ $team }}">{{ $team }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div id="team_employees_wrapper" class="mb-3" style="display: none;">
                                <div class="mb-2">
                                    <input type="text" id="member_search_input" class="form-control form-control-sm shadow-sm" placeholder="Type to search members in selected teams...">
                                </div>
                                <label class="form-label text-muted fw-semibold small d-flex justify-content-between">
                                    <span>Select Employees</span>
                                    <div>
                                        <span class="text-primary cursor-pointer me-2" style="font-size: 0.8rem; text-decoration: underline;" onclick="selectAllTeamEmployees(true)">Select All</span>
                                        <span class="text-secondary cursor-pointer" style="font-size: 0.8rem; text-decoration: underline;" onclick="selectAllTeamEmployees(false)">Deselect All</span>
                                    </div>
                                </label>
                                <div id="team_employees_container" class="border rounded p-3 bg-light" style="max-height: 180px; overflow-y: auto;">
                                    <!-- Dynamic Checkboxes loaded via JS -->
                                </div>
                            </div>
                            <div class="modal-footer border-0 px-0 pb-0">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" id="addMultipleMembersSubmit" class="btn btn-primary" disabled>Add Selected Members</button>
                            </div>
                        </form>
                    </div>

                    <!-- By Name Tab -->
                    <div class="tab-pane fade mb-3" id="by-name" role="tabpanel" aria-labelledby="by-name-tab">
                        <form action="{{ route('projects.members.add', $project->id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label text-muted fw-semibold small">Search Employee</label>
                                <select name="employee_id" id="employee_search_select" class="form-select employee-search" style="width: 100%;">
                                    <option value="">Search by employee name...</option>
                                </select>
                                <div class="form-text">Type a name to search existing employees.</div>
                            </div>
                            <div class="modal-footer border-0 px-0 pb-0">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Add to Project</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <h5 class="modal-title fw-bold ">
                    <i class="fa-solid fa-plus-circle text-primary me-2"></i> Assign New Task
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('tasks.store') }}" method="POST">
                @csrf
                <input type="hidden" name="project_id" value="{{ $project->id }}">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-body">Task Title</label>
                        <input type="text" name="title" class="form-control" required placeholder="Enter task title">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-body">Description / Metadata / Link</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Provide details, links, or instructions..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-body">Assign To</label>
                        <select name="employee_id" class="form-select employee-search-task" required data-placeholder="Search for employee...">
                            <option value=""></option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-body">Depends On (Dependency Task)</label>
                        <select name="depends_on_task_id" class="form-select task-search" data-placeholder="Search for a task it depends on (Optional)...">
                            <option value=""></option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold text-body">Deadline</label>
                            <input type="date" name="deadline_at" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold text-body">Priority</label>
                            <select name="priority" class="form-select" required>
                                <option value="low">Low</option>
                                <option value="normal" selected>Normal</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold text-body">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="pending" selected>Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="blocked">Blocked</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3 mt-2">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Assign Task</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('page-script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Yajra DataTable
        var table = $('#membersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('projects.members.data', $project->id) }}",
            columns: [
                { data: 'name', name: 'name', class: 'px-4 fw-semibold' },
                { data: 'email', name: 'email' },
                { data: 'team', name: 'team', render: function(data, type, row) {
                    return data ? `<span class="badge bg-light text-primary border border-primary-subtle">${data}</span>` : '<span class="text-muted fst-italic small">No Team</span>';
                }},
                { data: 'action', name: 'action', orderable: false, searchable: false, class: 'text-end px-4' }
            ],
            drawCallback: function(settings) {
                if(settings.json) {
                    $('#totalMembersCount').text(settings.json.recordsTotal);
                }
            }
        });

        // Initialize Select2 with AJAX for employee search
        $('.employee-search').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#addMemberModal'),
            ajax: {
                url: '/admin/employees/search',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term // search term
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

        // Initialize Select2 for employee search in Add Task Modal
        $('.employee-search-task').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#addTaskModal'),
            ajax: {
                url: '/admin/employees/search',
                dataType: 'json',
                delay: 250,
                data: function (params) { return { q: params.term }; },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return { text: item.name + (item.team ? ' (' + item.team + ')' : ' (No Team)'), id: item.id }
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 0
        });

        // Initialize Select2 for task dependency search in Add Task Modal
        $('.task-search').select2({
            theme: 'bootstrap-5',
            allowClear: true,
            dropdownParent: $('#addTaskModal'),
            ajax: {
                url: '{{ route('tasks.search') }}',
                dataType: 'json',
                delay: 250,
                data: function (params) { return { q: params.term }; },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return { text: item.title, id: item.id }
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 0
        });
        // Tab changing visual styles
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            $('button[data-bs-toggle="tab"]').removeClass('border-bottom border-primary border-3 text-body').addClass('text-muted');
            $(e.target).addClass('border-bottom border-primary border-3 text-body').removeClass('text-muted');
        });

        // Initialize Tasks DataTable
        var tasksTable = $('#tasksTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('projects.tasks.data', $project->id) }}",
            columns: [
                { data: 'title', name: 'title', class: 'px-4 fw-semibold text-body' },
                { data: 'employee_name', name: 'employee.name' },
                { data: 'priority', name: 'priority', render: function(data) {
                    var str = data.charAt(0).toUpperCase() + data.slice(1);
                    return '<span class="badge bg-light text-dark border">' + str + '</span>';
                }},
                { data: 'status_badge', name: 'status', orderable: false, searchable: false }
            ]
        });

        // Initialize Commits DataTable
        var commitsTable = $('#commitsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('projects.commits.data', $project->id) }}",
            columns: [
                { data: 'commit_message', name: 'commit_message', class: 'px-4 fw-medium text-body' },
                { data: 'employee_name', name: 'employee_name', orderable: false },
                { data: 'source_badge', name: 'source', orderable: false, searchable: false },
                { data: 'commit_date_formatted', name: 'commit_date', orderable: false }
            ],
            order: [[3, 'desc']]
        });

        // Team Select & Checkbox Logic for adding members
        const allEmployees = @json($employees);
        let checkedEmployeeIds = new Set();

        // Initialize Select2 for multi-team select
        $('#team_select').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#addMemberModal'),
            placeholder: 'Select Teams...'
        });

        $('#team_select').on('change', function() {
            renderEmployeeList();
        });

        $('#member_search_input').on('input', function() {
            renderEmployeeList();
        });

        function renderEmployeeList() {
            const selectedTeams = $('#team_select').val() || [];
            const searchQuery = $('#member_search_input').val().toLowerCase().trim();
            const container = $('#team_employees_container');
            const wrapper = $('#team_employees_wrapper');
            const submitBtn = $('#addMultipleMembersSubmit');

            container.empty();
            if (selectedTeams.length === 0) {
                wrapper.hide();
                submitBtn.prop('disabled', checkedEmployeeIds.size === 0);
                return;
            }

            let filtered = allEmployees.filter(emp => selectedTeams.includes(emp.team));

            if (searchQuery) {
                filtered = filtered.filter(emp => 
                    emp.name.toLowerCase().includes(searchQuery) || 
                    (emp.email && emp.email.toLowerCase().includes(searchQuery))
                );
            }

            if (filtered.length === 0) {
                container.html('<p class="text-muted small mb-0 text-center py-2">No matching employees found.</p>');
                wrapper.show();
                return;
            }

            filtered.forEach(emp => {
                const isChecked = checkedEmployeeIds.has(emp.id.toString()) || checkedEmployeeIds.has(emp.id);
                container.append(`
                    <div class="form-check mb-2">
                        <input class="form-check-input employee-checkbox" type="checkbox" value="${emp.id}" id="emp_${emp.id}" ${isChecked ? 'checked' : ''}>
                        <label class="form-check-label small" for="emp_${emp.id}">
                            <strong>${emp.name}</strong> (${emp.team || 'No Team'}) - ${emp.email}
                        </label>
                    </div>
                `);
            });

            wrapper.show();
        }

        $(document).on('change', '.employee-checkbox', function() {
            const id = $(this).val();
            if ($(this).is(':checked')) {
                checkedEmployeeIds.add(id);
            } else {
                checkedEmployeeIds.delete(id);
            }
            updateSubmitButtonState();
        });

        function updateSubmitButtonState() {
            const checkedCount = checkedEmployeeIds.size;
            $('#addMultipleMembersSubmit').prop('disabled', checkedCount === 0);
        }

        window.selectAllTeamEmployees = function(val) {
            $('#team_employees_container input.employee-checkbox').each(function() {
                $(this).prop('checked', val);
                const id = $(this).val();
                if (val) {
                    checkedEmployeeIds.add(id);
                } else {
                    checkedEmployeeIds.delete(id);
                }
            });
            updateSubmitButtonState();
        };

        $('#addMultipleMembersForm').on('submit', function(e) {
            e.preventDefault();
            const submitBtn = $('#addMultipleMembersSubmit');
            const selectedIds = Array.from(checkedEmployeeIds);

            if (selectedIds.length === 0) return;

            submitBtn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Adding...');

            const promises = selectedIds.map(id => {
                return $.ajax({
                    url: "{{ route('projects.members.add', $project->id) }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        employee_id: id
                    }
                });
            });

            Promise.all(promises).then(() => {
                window.location.reload();
            }).catch((err) => {
                console.error(err);
                alert("Failed to add some members. Refreshing page...");
                window.location.reload();
            });
        });

        // Inline Metadata Editing Logic
        $('#editMetadataBtn').on('click', function() {
            $('#metadataReadView').hide();
            $('#metadataForm').fadeIn(200);
        });

        $('#cancelMetadataEdit').on('click', function() {
            $('#metadataForm').hide();
            $('#metadataReadView').fadeIn(200);
        });

        $('#metadataForm').on('submit', function(e) {
            e.preventDefault();
            const saveBtn = $('#saveMetadataBtn');
            saveBtn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Saving...');

            $.ajax({
                url: "{{ route('projects.update', $project->id) }}",
                type: "POST",
                data: $(this).serialize(),
                success: function() {
                    window.location.reload();
                },
                error: function(xhr) {
                    alert("Failed to save metadata changes. Please check fields and try again.");
                    saveBtn.prop('disabled', false).text('Save Changes');
                }
            });
        });
    });
</script>
@endpush