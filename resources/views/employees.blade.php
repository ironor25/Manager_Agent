@extends('master')

@push('page-style')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    /* Table specific premium styles */
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
    .action-btn.delete-emp-btn:hover {
        background: var(--danger);
        color: white;
    }
    .badge-role {
        padding: 6px 12px;
        border-radius: 50px;
        font-weight: 500;
        font-size: 0.75rem;
        background: #e0e7ff;
        color: #4338ca;
    }
</style>
@endpush

@section('page-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1 ">Employees List</h3>
        <p class="text-muted mb-0">Manage and track your team members</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-secondary d-flex align-items-center gap-2" id="generateAllReportsBtn">
            <i class="fa-solid fa-file-contract"></i> Generate All Reports
        </button>
        <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addEmpModal">
            <i class="fa-solid fa-plus"></i> Add Employee
        </button>
    </div>
</div>

<div class="card border-0">
    <div class="card-body p-0">
        <div class="table-responsive px-4 py-3">
            <table class="table table-hover align-middle w-100" id="employees-table">
                <thead>
                    <tr>
                        <th width="80">ID</th>
                        <th>Employee Details</th>
                        <th>Email</th>
                        <th>Team</th>
                        <th>Designation</th>
                        <th width="150" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- DataTable content -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Employee Modal -->
<div class="modal fade" id="addEmpModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header border-0 pb-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <h5 class="modal-title fw-bold ">
                    <i class="fa-solid fa-user-plus text-primary me-2"></i> Add Employee
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('employees.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-body">Full Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. John Doe">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-body">Email Address</label>
                        <input type="email" name="email" class="form-control" required placeholder="e.g. john@example.com">
                    </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-body">Team</label>
                                <select name="team" class="form-select" required>
                                    <option value="" disabled selected>Select a Team</option>
                                    @foreach($teams as $team)
                                        <option value="{{ $team->name }}">{{ $team->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3 mt-2">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Employee Modal -->
<div class="modal fade" id="editEmpModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header border-0 pb-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <h5 class="modal-title fw-bold ">
                    <i class="fa-solid fa-user-pen text-primary me-2"></i> Edit Employee
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editEmpForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-body">Full Name</label>
                        <input type="text" name="name" id="edit_emp_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-body">Email Address</label>
                        <input type="email" name="email" id="edit_emp_email" class="form-control" required>
                    </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-body">Team</label>
                                <select name="team" id="edit_emp_team" class="form-select" required>
                                    <option value="" disabled>Select a Team</option>
                                    @foreach($teams as $team)
                                        <option value="{{ $team->name }}">{{ $team->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3 mt-2">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Update Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Employee Modal -->
<div class="modal fade" id="deleteEmpModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-4 border-0">
            <div class="mb-3">
                <i class="fa-solid fa-triangle-exclamation text-danger" style="font-size: 3rem;"></i>
            </div>
            <h5 class="mb-3 fw-bold">Confirm Deletion</h5>
            <p class="text-muted mb-4" style="font-size: 0.9rem;">Are you sure you want to delete this employee? All their tasks and data will be removed.</p>
            <div class="d-flex justify-content-center gap-2">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteEmpForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger px-4">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Performance Modal -->
<div class="modal fade" id="performanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header border-0 pb-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <h5 class="modal-title fw-bold">
                    <i class="fa-solid fa-chart-line text-primary me-2"></i> Performance Report
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="performance-content">
                <!-- Loader -->
                <div class="text-center py-5" id="report-loader">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <h6 class=" fw-medium">Generating AI Insights...</h6>
                </div>
                
                <!-- Report Container -->
                <div id="report-container" style="display: none;">
                    <div class="row g-3">
                        <!-- Left Column: Leadership Score, Summary, Git Commit Chart -->
                        <div class="col-md-5 d-flex flex-column gap-3">
                            <!-- Score and Status -->
                            <div class="p-3 bg-light rounded-3 text-center border">
                                <h6 class="text-uppercase  fw-bold mb-1" style="font-size: 0.75rem; letter-spacing: 1px;">Leadership Score</h6>
                                <div class="d-flex align-items-center justify-content-center gap-3">
                                    <h2 class="fw-bolder  mb-0" id="report-score" style="font-size: 2.2rem; line-height: 1;">0</h2>
                                    <span class="badge" id="report-status" style="font-size: 0.85rem; padding: 6px 12px;">Status</span>
                                </div>
                            </div>
                            
                            <!-- AI Summary -->
                            <div class="p-3 border rounded-3 bg-white flex-grow-1">
                                <h6 class="fw-bold text-primary mb-2" style="font-size: 0.95rem;"><i class="fa-solid fa-file-lines me-2"></i>AI Summary</h6>
                                <p id="report-summary" class="text-muted lh-lg mb-0" style="font-size: 0.875rem;"></p>
                            </div>

                            <!-- Git Commit Chart -->
                            <div class="p-3 border rounded-3 bg-white">
                                <h6 class="fw-bold  mb-2" style="font-size: 0.95rem;"><i class="fa-brands fa-git-alt me-2 text-dark"></i>Git Commits (Last 30 Days)</h6>
                                <div style="height: 180px; position: relative;">
                                    <canvas id="employeeCommitChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Strengths, Weaknesses, Recommendations -->
                        <div class="col-md-7 d-flex flex-column gap-3">
                            <!-- Strengths -->
                            <div class="p-3 border rounded-3 bg-white flex-grow-1">
                                <h6 class="fw-bold text-success mb-2" style="font-size: 0.95rem;"><i class="fa-solid fa-circle-check me-2"></i>Key Strengths</h6>
                                <div id="report-strengths" class="d-flex flex-column gap-2 text-muted" style="font-size: 0.875rem;"></div>
                            </div>

                            <!-- Weaknesses -->
                            <div class="p-3 border rounded-3 bg-white flex-grow-1">
                                <h6 class="fw-bold text-danger mb-2" style="font-size: 0.95rem;"><i class="fa-solid fa-circle-xmark me-2"></i>Areas for Improvement</h6>
                                <div id="report-weaknesses" class="d-flex flex-column gap-2 text-muted" style="font-size: 0.875rem;"></div>
                            </div>

                            <!-- Recommendations -->
                            <div class="p-3 border rounded-3 bg-light">
                                <h6 class="fw-bold text-warning mb-2" style="font-size: 0.95rem;"><i class="fa-regular fa-lightbulb me-2"></i>Recommendations</h6>
                                <div id="report-recommendations" class="d-flex flex-column gap-2 text-muted" style="font-size: 0.875rem;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-3 mt-3">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- Employee Details Modal -->
<div class="modal fade" id="employeeDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 h-100">
            <div class="modal-header border-bottom py-3 px-4 bg-light">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar bg-primary text-white fw-bold" id="detail-emp-avatar" style="width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold  mb-0" id="detail-emp-name"></h5>
                        <p class="text-muted mb-0 small" id="detail-emp-email-team"></p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="p-4" style="overflow-y: auto;">
                    <h6 class="fw-bold mb-3"><i class="fa-solid fa-list-check me-2 text-primary"></i>Assigned Tasks</h6>
                    <div id="detail-tasks-container">
                        <!-- Loader -->
                        <div class="text-center py-5" id="detail-tasks-loader">
                            <div class="spinner-border text-primary" role="status"></div>
                        </div>
                        <div id="detail-tasks-list" class="d-flex flex-column gap-2" style="display: none;"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-3">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-script')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        var table = $('#employees-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('employees.data') }}",
            columns: [
                { data: 'id', name: 'id', render: function(data) { return `<span class="fw-medium text-muted">#${data}</span>`; }},
                { data: 'name', name: 'name', render: function(data, type, row) {
                    return `
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar bg-light text-primary fw-bold" style="width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                ${data.charAt(0)}
                            </div>
                            <div>
                                <div class="fw-bold text-body">${data}</div>
                            </div>
                        </div>
                    `;
                }},
                { data: 'email', name: 'email', render: function(data) { return `<span class="text-muted small">${data}</span>`; } },
                { data: 'team', name: 'team', render: function(data) { return `<span class="fw-medium">${data}</span>`; } },
                { data: 'designation', name: 'designation', render: function(data) { return data ? `<span class="fw-medium text-muted">${data}</span>` : `<span class="text-muted fst-italic">None</span>`; } },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end' }
            ],
            language: {
                search: "",
                searchPlaceholder: "Search employees..."
            },
            dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
        });

        // Make rows clickable for details
        $('#employees-table tbody').on('click', 'tr', function (e) {
            if ($(e.target).closest('button, a, .action-btn').length > 0) {
                return;
            }
            var data = table.row(this).data();
            if(!data) return;

            var employeeId = data.id;
            
            $('#detail-emp-avatar').text(data.name.charAt(0));
            $('#detail-emp-name').text(data.name);
            $('#detail-emp-email-team').text(data.email + ' • ' + data.team);
            
            $('#detail-tasks-list').hide();
            $('#detail-tasks-loader').show();
            
            var modal = new bootstrap.Modal(document.getElementById('employeeDetailsModal'));
            modal.show();

            $.ajax({
                url: '/admin/employees/' + employeeId + '/details',
                type: 'GET',
                success: function(response) {
                    var tasks = response.tasks;
                    var html = '';
                    if(!tasks || tasks.length === 0) {
                        html = '<div class="alert alert-light text-center text-muted border border-light">No tasks assigned to this employee.</div>';
                    } else {
                        tasks.forEach(function(task) {
                            var statusBadge = '';
                            if(task.status === 'completed' || task.status === 'late_completed') statusBadge = '<span class="badge bg-success">Completed</span>';
                            else if(task.status === 'in_progress') statusBadge = '<span class="badge bg-primary">In Progress</span>';
                            else statusBadge = '<span class="badge bg-secondary">Pending</span>';

                            var priorityBadge = '';
                            if(task.priority === 'high') priorityBadge = '<span class="badge bg-danger">High</span>';
                            else if(task.priority === 'low') priorityBadge = '<span class="badge bg-info">Low</span>';
                            else priorityBadge = '<span class="badge bg-secondary">Normal</span>';

                            var deadline = task.deadline_at ? new Date(task.deadline_at).toLocaleDateString() : 'No Deadline';

                            html += `
                                <div class="card border border-light bg-white shadow-sm mb-2 rounded-3">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="fw-bold mb-0 ">${task.title}</h6>
                                            <div class="d-flex gap-1">${priorityBadge} ${statusBadge}</div>
                                        </div>
                                        <p class="text-muted small mb-2">${task.description || 'No description provided.'}</p>
                                        <div class="text-muted small fw-medium"><i class="fa-regular fa-calendar me-1"></i> Deadline: ${deadline}</div>
                                    </div>
                                </div>
                            `;
                        });
                    }
                    $('#detail-tasks-list').html(html).show();
                    $('#detail-tasks-loader').hide();
                },
                error: function() {
                    $('#detail-tasks-list').html('<div class="alert alert-danger">Failed to load tasks.</div>').show();
                    $('#detail-tasks-loader').hide();
                }
            });
        });

        table.on('draw', function () {
            $('#employees-table tbody tr').css('cursor', 'pointer');
            $('#employees-table tbody tr').hover(function() {
                $(this).addClass('bg-light');
            }, function() {
                $(this).removeClass('bg-light');
            });
        });

        // Edit Employee Modal
        $('body').on('click', '.edit-emp-btn', function() {
            var emp = $(this).data('emp');
            $('#editEmpForm').attr('action', '/admin/employees/management/' + emp.id);
            $('#edit_emp_name').val(emp.name);
            $('#edit_emp_email').val(emp.email);
            $('#edit_emp_team').val(emp.team);
            var editModal = new bootstrap.Modal(document.getElementById('editEmpModal'));
            editModal.show();
        });

        // Delete Employee Modal
        $('body').on('click', '.trigger-delete-emp', function() {
            var actionUrl = $(this).data('action');
            $('#deleteEmpForm').attr('action', actionUrl);
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteEmpModal'));
            deleteModal.show();
        });

        // Handle Performance Button Click
        $('body').on('click', '.view-performance-btn', function() {
            var employeeId = $(this).data('id');
            var url = "/admin/employee/" + employeeId + "/report";
            
            $('#performanceModal').modal('show');
            $('#report-loader').show();
            $('#report-container').hide();
            
            $('#report-summary').text('');
            $('#report-score').text('0');
            $('#report-strengths, #report-weaknesses, #report-recommendations').html('');

            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    $('#report-summary').text(response.summary || 'No summary available.');
                    
                    let score = response.leadership_score || 0;
                    $('#report-score').text(score);
                    
                    let statusBadge = $('#report-status');
                    if (score >= 85) {
                        statusBadge.text('Excellent').removeClass('bg-warning bg-danger text-body').addClass('bg-success text-white');
                    } else if (score >= 70) {
                        statusBadge.text('Good').removeClass('bg-warning bg-danger text-body').addClass('bg-primary text-white');
                    } else {
                        statusBadge.text('Needs Improvement').removeClass('bg-success bg-primary text-white').addClass('bg-warning text-body');
                    }

                    const formatList = (arr, iconClass) => arr && arr.length ? arr.map(item => `<div class="d-flex gap-2 mb-2"><i class="${iconClass} mt-1"></i><span>${item}</span></div>`).join('') : '<span class="text-muted fst-italic">None recorded</span>';

                    $('#report-strengths').html(formatList(response.strengths, 'fa-solid fa-check text-success'));
                    $('#report-weaknesses').html(formatList(response.weaknesses, 'fa-solid fa-xmark text-danger'));
                    $('#report-recommendations').html(formatList(response.recommendations, 'fa-solid fa-angle-right text-warning'));

                    // Render Chart
                    if (window.employeeCommitChartInstance) {
                        window.employeeCommitChartInstance.destroy();
                    }
                    if (response.chart_data && response.chart_data.labels) {
                        const ctx = document.getElementById('employeeCommitChart').getContext('2d');
                        window.employeeCommitChartInstance = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: response.chart_data.labels,
                                datasets: [{
                                    label: 'Commits',
                                    data: response.chart_data.values,
                                    borderColor: '#10b981',
                                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                    fill: true,
                                    tension: 0.4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false }
                                },
                                scales: {
                                    y: { beginAtZero: true, ticks: { stepSize: 1 } },
                                    x: { grid: { display: false } }
                                }
                            }
                        });
                    }

                    $('#report-loader').hide();
                    $('#report-container').fadeIn();
                },
                error: function() {
                    $('#report-loader').hide();
                    $('#performance-content').append('<div class="alert alert-danger" id="error-msg">Failed to load performance report.</div>');
                    setTimeout(() => $('#error-msg').fadeOut().remove(), 3000);
                }
            });
        });

        function showToast(message, type = 'success') {
            var container = $('.toast-container');
            if (container.length === 0) {
                container = $('<div class="toast-container position-fixed bottom-0 end-0 p-4" style="z-index: 1055;"></div>');
                $('body').append(container);
            }
            
            var bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
            var icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            
            var toastHtml = `
                <div class="toast align-items-center text-white ${bgClass} border-0 shadow-lg mb-2" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
                    <div class="d-flex">
                        <div class="toast-body fw-medium py-3 px-4" style="font-size: 0.95rem;">
                            <i class="fa-solid ${icon} me-2"></i> ${message}
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

        // Generate All Reports Button
        $('#generateAllReportsBtn').on('click', function() {
            var btn = $(this);
            var originalText = btn.html();
            btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generating...');
            btn.prop('disabled', true);

            $.ajax({
                url: "{{ route('employees.generate_all_reports') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    btn.html(originalText);
                    btn.prop('disabled', false);
                    if(response.success) {
                        showToast(response.message, 'success');
                    } else {
                        showToast('Something went wrong.', 'danger');
                    }
                },
                error: function(xhr) {
                    btn.html(originalText);
                    btn.prop('disabled', false);
                    showToast('Failed to generate reports.', 'danger');
                }
            });
        });
    });
</script>
@endpush
