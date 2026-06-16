@extends('master')

@push('page-style')
<style>
    .task-card {
        background: var(--surface);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        transition: all 0.3s ease;
    }
    .status-badge {
        padding: 6px 12px;
        border-radius: 50px;
        font-weight: 500;
        font-size: 0.75rem;
    }
    .status-pending { background: #fef3c7; color: #d97706; }
    .status-in_progress { background: #e0e7ff; color: #4338ca; }
    .status-completed { background: #dcfce7; color: #15803d; }
    
    .priority-badge {
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    .priority-low { background: #f1f5f9; color: #64748b; }
    .priority-normal { background: #e0f2fe; color: #0284c7; }
    .priority-high { background: #fee2e2; color: #b91c1c; }
    
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
        color: white;
    }
    .action-btn.delete-btn:hover {
        background: var(--danger);
        color: white;
    }
</style>
@endpush

@section('page-content')


<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1 text-body">Tasks Management</h3>
        <p class="text-muted mb-0">Assign and track employee tasks</p>
    </div>
    <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addTaskModal">
        <i class="fa-solid fa-plus"></i> Add New Task
    </button>
</div>

<div class="card border-0">
    <div class="card-body p-0">
        <div class="table-responsive px-4 py-3">
            <table class="table table-hover align-middle w-100" id="tasks-table">
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Assigned To</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Deadline</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                    <tr class="task-row" data-task="{{ json_encode($task) }}" style="cursor: pointer;">
                        <td>
                            <div class="fw-bold text-body">{{ $task->title }}</div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar bg-light text-primary fw-bold" style="width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">
                                    {{ substr($task->employee->name ?? '?', 0, 1) }}
                                </div>
                                <span class="fw-medium text-body">{{ $task->employee->name ?? 'Unknown' }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge status-{{ $task->status }}">
                                {{ str_replace('_', ' ', ucfirst($task->status)) }}
                            </span>
                        </td>
                        <td>
                            <span class="priority-badge priority-{{ $task->priority }}">
                                {{ ucfirst($task->priority) }}
                            </span>
                        </td>
                        <td>
                            @if($task->deadline_at)
                                <span class="text-muted"><i class="fa-regular fa-calendar me-1"></i> {{ $task->deadline_at->format('M d, Y') }}</span>
                            @else
                                <span class="text-muted fst-italic">No deadline</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <button class="action-btn edit-task-btn" data-task="{{ json_encode($task) }}" data-bs-toggle="modal" data-bs-target="#editTaskModal" title="Edit Task">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button type="button" class="action-btn delete-btn trigger-delete" data-action="{{ route('tasks.destroy', $task->id) }}" title="Delete Task">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <div class="fs-1 mb-3"><i class="fa-solid fa-clipboard-list text-light"></i></div>
                            <h6>No tasks assigned yet.</h6>
                            <button class="btn btn-outline-primary mt-2" data-bs-toggle="modal" data-bs-target="#addTaskModal">Create your first task</button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- View Task Modal -->
<div class="modal fade" id="viewTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <h5 class="modal-title fw-bold text-body">
                    <i class="fa-solid fa-list-check text-primary me-2"></i> Task Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <h6 class="text-muted mb-1" style="font-size: 0.8rem; text-transform: uppercase;">Task Title</h6>
                <h4 id="view_task_title" class="fw-bold text-body mb-4"></h4>
                
                <h6 class="text-muted mb-2" style="font-size: 0.8rem; text-transform: uppercase;">Description</h6>
                <div class="p-3 bg-light rounded-3 mb-4">
                    <p id="view_task_description" class="text-muted mb-0" style="white-space: pre-wrap; font-size: 0.95rem;"></p>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-3 p-3 border rounded-3 h-100">
                            <div class="avatar bg-light text-primary fw-bold" style="width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1" style="font-size: 0.8rem; text-transform: uppercase;">Assigned To</h6>
                                <div id="view_task_employee" class="fw-bold text-body mb-0"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-3 p-3 border rounded-3 h-100">
                            <div class="avatar bg-light text-primary fw-bold" style="width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-regular fa-calendar"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1" style="font-size: 0.8rem; text-transform: uppercase;">Deadline</h6>
                                <div id="view_task_deadline" class="fw-bold text-body mb-0"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-3 p-3 border rounded-3 h-100">
                            <div class="avatar bg-light text-primary fw-bold" style="width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-flag"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1" style="font-size: 0.8rem; text-transform: uppercase;">Priority</h6>
                                <div id="view_task_priority" class="fw-bold text-body mb-0"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-3 p-3 border rounded-3 h-100">
                            <div class="avatar bg-light text-primary fw-bold" style="width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-bars-progress"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1" style="font-size: 0.8rem; text-transform: uppercase;">Status</h6>
                                <div id="view_task_status" class="fw-bold text-body mb-0"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-3">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <h5 class="modal-title fw-bold text-body">
                    <i class="fa-solid fa-plus-circle text-primary me-2"></i> Assign New Task
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('tasks.store') }}" method="POST">
                @csrf
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
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select Employee</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->team }})</option>
                            @endforeach
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

<!-- Edit Task Modal -->
<div class="modal fade" id="editTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <h5 class="modal-title fw-bold text-body">
                    <i class="fa-solid fa-pen-to-square text-primary me-2"></i> Edit Task
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editTaskForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-body">Task Title</label>
                        <input type="text" name="title" id="edit_title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-body">Description</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-body">Assign To</label>
                        <select name="employee_id" id="edit_employee_id" class="form-select" required>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->team }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold text-body">Deadline</label>
                            <input type="date" name="deadline_at" id="edit_deadline_at" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold text-body">Priority</label>
                            <select name="priority" id="edit_priority" class="form-select" required>
                                <option value="low">Low</option>
                                <option value="normal">Normal</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold text-body">Status</label>
                            <select name="status" id="edit_status" class="form-select" required>
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3 mt-2">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Update Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Task Modal -->
<div class="modal fade" id="deleteTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-4 border-0">
            <div class="mb-3">
                <i class="fa-solid fa-triangle-exclamation text-danger" style="font-size: 3rem;"></i>
            </div>
            <h5 class="mb-3 fw-bold">Confirm Deletion</h5>
            <p class="text-muted mb-4" style="font-size: 0.9rem;">Are you sure you want to delete this task? This action cannot be undone.</p>
            <div class="d-flex justify-content-center gap-2">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteTaskForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger px-4">Delete Task</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-script')
<script>
    $(document).ready(function() {
        $('#tasks-table').DataTable({
            language: {
                search: "",
                searchPlaceholder: "Search tasks..."
            },
            dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
        });

        $('.edit-task-btn').on('click', function() {
            var task = $(this).data('task');
            
            $('#editTaskForm').attr('action', '/tasks/' + task.id);
            $('#edit_title').val(task.title);
            $('#edit_description').val(task.description);
            $('#edit_employee_id').val(task.employee_id);
            $('#edit_priority').val(task.priority);
            $('#edit_status').val(task.status);
            
            if(task.deadline_at) {
                // Task deadline is sent back as an ISO string or similar from Laravel date casting
                var date = new Date(task.deadline_at);
                var formattedDate = date.toISOString().split('T')[0];
                $('#edit_deadline_at').val(formattedDate);
            } else {
                $('#edit_deadline_at').val('');
            }
        });

        $('.trigger-delete').on('click', function() {
            var actionUrl = $(this).data('action');
            $('#deleteTaskForm').attr('action', actionUrl);
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteTaskModal'));
            deleteModal.show();
        });

        $('#tasks-table tbody').on('click', '.task-row', function(e) {
            // Prevent opening modal if clicking on buttons
            if ($(e.target).closest('button').length > 0) {
                return;
            }
            
            var task = $(this).data('task');
            $('#view_task_title').text(task.title);
            $('#view_task_description').text(task.description || 'No description provided.');
            $('#view_task_employee').text(task.employee ? task.employee.name : 'Unassigned');
            
            var statusStr = task.status.replace('_', ' ');
            statusStr = statusStr.charAt(0).toUpperCase() + statusStr.slice(1);
            $('#view_task_status').html('<span class="status-badge status-' + task.status + '">' + statusStr + '</span>');
            
            var priorityStr = task.priority.charAt(0).toUpperCase() + task.priority.slice(1);
            $('#view_task_priority').html('<span class="priority-badge priority-' + task.priority + '">' + priorityStr + '</span>');
            
            if (task.deadline_at) {
                var date = new Date(task.deadline_at);
                $('#view_task_deadline').text(date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' }));
            } else {
                $('#view_task_deadline').text('No deadline');
            }

            var viewModal = new bootstrap.Modal(document.getElementById('viewTaskModal'));
            viewModal.show();
        });
    });
</script>
@endpush

