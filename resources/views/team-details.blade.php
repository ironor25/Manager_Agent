@extends('master')

@push('page-style')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
<style>
    .report-btn {
        background-color: transparent;
        color: var(--primary);
        border: 1px solid var(--primary);
        border-radius: 50px;
        padding: 4px 12px;
        font-weight: 500;
        font-size: 0.75rem;
        transition: all 0.2s;
    }
    .report-btn:hover {
        background-color: var(--primary);
        color: white;
    }
    .remove-member-form button {
        padding: 4px 12px !important;
        font-size: 0.75rem !important;
        border-radius: 50px !important;
    }
    .team-header-card {
        background: linear-gradient(135deg, rgba(79, 70, 229, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%);
        border: 1px solid rgba(79, 70, 229, 0.2);
        border-radius: 16px;
    }
    /* DataTables styles */
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
        padding: 4px 8px;
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
<div class="mb-4">
    <a href="{{ route('teams.management') }}" class="text-decoration-none text-muted mb-3 d-inline-block">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Team Management
    </a>
    
    <div class="team-header-card p-4 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1 text-primary">{{ $team->name }} Team</h2>
            <p class="text-muted mb-0" style="max-width: 600px;">{{ $team->description ?? 'No description provided.' }}</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary px-4 py-2 shadow-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#addTeamMemberModal">
                <i class="fa-solid fa-user-plus me-2"></i> Add Member
            </button>
            <button class="btn btn-primary px-4 py-2 shadow-sm rounded-pill" id="generateTeamReportBtn" data-team="{{ $team->name }}">
                <i class="fa-solid fa-wand-magic-sparkles me-2"></i> Whole Team Performance
            </button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 ">Team Members (<span id="totalMembersCount">{{ $totalMembersCount }}</span>)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive px-4 py-3">
                    <table class="table table-hover align-middle mb-0 w-100" id="membersTable">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3">Employee</th>
                                <th class="py-3">Email</th>
                                <th class="text-end px-4 py-3" style="width: 320px;">Actions</th>
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

<!-- Team AI Report Modal -->
<div class="modal fade" id="teamReportModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <h5 class="modal-title fw-bold ">
            <i class="fa-solid fa-wand-magic-sparkles text-primary me-2"></i> Team Report: <span id="modalTeamName" class="text-primary"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" id="teamModalBodyContent">
      </div>
    </div>
  </div>
</div>

<!-- Individual AI Report Modal -->
<div class="modal fade" id="individualReportModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <h5 class="modal-title fw-bold ">
            <i class="fa-solid fa-wand-magic-sparkles text-primary me-2"></i> Individual Report: <span id="modalIndName" class="text-primary"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" id="indModalBodyContent">
      </div>
    </div>
  </div>
</div>

<!-- Add Team Member Modal -->
<div class="modal fade" id="addTeamMemberModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Add Member to Team</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('teams.members.add', $team->name) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-muted fw-semibold small">Search Employee</label>
                        <select name="employee_id" id="team_employee_search_select" class="form-select employee-search" style="width: 100%;" required>
                            <option value="">Search by employee name...</option>
                        </select>
                        <div class="form-text">Type a name to search existing employees.</div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add to Team</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('page-script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2 with AJAX for employee search
        $('.employee-search').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#addTeamMemberModal'),
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
        
        // Helper to format lists
        const formatList = (arr, iconClass) => arr && arr.length ? arr.map(item => `<div class="d-flex gap-2 mb-2"><i class="${iconClass} mt-1"></i><span>${item}</span></div>`).join('') : '<span class="text-muted">None recorded</span>';

        // Initialize DataTable
        var table = $('#membersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('teams.members.data', $team->name) }}",
            columns: [
                { data: 'name', name: 'name', render: function(data, type, row) {
                    return `
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar bg-light text-primary fw-bold" style="width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem;">
                                ${data.charAt(0)}
                            </div>
                            <div class="fw-bold text-body">${data}</div>
                        </div>
                    `;
                }},
                { data: 'email', name: 'email', render: function(data) { return `<span class="text-muted">${data}</span>`; } },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end px-4' }
            ],
            language: {
                search: "",
                searchPlaceholder: "Search members..."
            },
            dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
        });

        // Sync total count dynamically on table draw
        table.on('draw', function () {
            var info = table.page.info();
            $('#totalMembersCount').text(info.recordsTotal);
        });

        // 1. Whole Team Performance Report
        const teamReportBtn = document.getElementById('generateTeamReportBtn');
        if (teamReportBtn) {
            teamReportBtn.addEventListener('click', function() {
                const teamName = this.dataset.team;
                const modal = new bootstrap.Modal(document.getElementById('teamReportModal'));
                document.getElementById('modalTeamName').innerText = teamName;
                document.getElementById('teamModalBodyContent').innerHTML = `
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary mb-3" role="status"></div>
                        <h6 class=" fw-medium">Analyzing data and generating team report...</h6>
                    </div>
                `;
                modal.show();

                fetch(`/admin/teams/${teamName}/report`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.report) {
                            const r = data.report;
                            document.getElementById('teamModalBodyContent').innerHTML = `
                                <div class="row g-3">
                                    <!-- Left Column: Summary & Recommendations -->
                                    <div class="col-md-6 d-flex flex-column gap-3">
                                        <div class="p-3 border rounded-3 bg-white h-100">
                                            <h6 class="fw-bold text-primary mb-2"><i class="fa-solid fa-file-lines me-2"></i>Summary</h6>
                                            <p class="text-muted lh-lg mb-0" style="font-size: 0.875rem;">${r.summary}</p>
                                        </div>
                                        <div class="p-3 border rounded-3 bg-light">
                                            <h6 class="fw-bold text-primary mb-2"><i class="fa-solid fa-lightbulb me-2"></i>Recommendations</h6>
                                            <div class="text-muted" style="font-size: 0.875rem;">${formatList(r.recommendations, 'fa-solid fa-angle-right text-primary')}</div>
                                        </div>
                                    </div>
                                    <!-- Right Column: Strengths & Weaknesses -->
                                    <div class="col-md-6 d-flex flex-column gap-3">
                                        <div class="p-3 border rounded-3 bg-white h-100">
                                            <h6 class="fw-bold text-success mb-2"><i class="fa-solid fa-circle-check me-2"></i>Key Strengths</h6>
                                            <div class="text-muted" style="font-size: 0.875rem;">${formatList(r.strengths, 'fa-solid fa-check text-success')}</div>
                                        </div>
                                        <div class="p-3 border rounded-3 bg-white h-100">
                                            <h6 class="fw-bold text-danger mb-2"><i class="fa-solid fa-circle-xmark me-2"></i>Areas for Improvement</h6>
                                            <div class="text-muted" style="font-size: 0.875rem;">${formatList(r.weaknesses, 'fa-solid fa-xmark text-danger')}</div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        } else {
                            document.getElementById('teamModalBodyContent').innerHTML = `<div class="alert alert-danger">Failed to generate report. ${data.error || ''}</div>`;
                        }
                    }).catch(err => {
                        document.getElementById('teamModalBodyContent').innerHTML = `<div class="alert alert-danger">Network error.</div>`;
                    });
            });
        }

        // 2. Individual Performance Report (via dynamic DataTable event delegation)
        $('#membersTable').on('click', '.generate-individual-report-btn', function() {
            const btn = $(this);
            const empId = btn.data('id');
            const empName = btn.data('name');
            
            const modal = new bootstrap.Modal(document.getElementById('individualReportModal'));
            document.getElementById('modalIndName').innerText = empName;
            document.getElementById('indModalBodyContent').innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <h6 class=" fw-medium">Analyzing data and generating individual report...</h6>
                </div>
            `;
            modal.show();

            fetch(`/admin/employee/${empId}/report`)
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Server error');
                    }
                    return res.json();
                })
                .then(data => {
                    if (data && !data.error) {
                        const r = data;
                        document.getElementById('indModalBodyContent').innerHTML = `
                            <div class="row g-3">
                                <!-- Left Column: Summary & Recommendations -->
                                <div class="col-md-6 d-flex flex-column gap-3">
                                    <div class="p-3 border rounded-3 bg-white h-100">
                                        <h6 class="fw-bold text-primary mb-2"><i class="fa-solid fa-file-lines me-2"></i>Summary</h6>
                                        <p class="text-muted lh-lg mb-0" style="font-size: 0.875rem;">${r.summary}</p>
                                    </div>
                                    <div class="p-3 border rounded-3 bg-light">
                                        <h6 class="fw-bold text-primary mb-2"><i class="fa-solid fa-lightbulb me-2"></i>Recommendations</h6>
                                        <div class="text-muted" style="font-size: 0.875rem;">${formatList(r.recommendations, 'fa-solid fa-angle-right text-primary')}</div>
                                    </div>
                                </div>
                                <!-- Right Column: Strengths & Weaknesses -->
                                <div class="col-md-6 d-flex flex-column gap-3">
                                    <div class="p-3 border rounded-3 bg-white h-100">
                                        <h6 class="fw-bold text-success mb-2"><i class="fa-solid fa-circle-check me-2"></i>Key Strengths</h6>
                                        <div class="text-muted" style="font-size: 0.875rem;">${formatList(r.strengths, 'fa-solid fa-check text-success')}</div>
                                    </div>
                                    <div class="p-3 border rounded-3 bg-white h-100">
                                        <h6 class="fw-bold text-danger mb-2"><i class="fa-solid fa-circle-xmark me-2"></i>Areas for Improvement</h6>
                                        <div class="text-muted" style="font-size: 0.875rem;">${formatList(r.weaknesses, 'fa-solid fa-xmark text-danger')}</div>
                                    </div>
                                </div>
                            </div>
                        `;
                    } else {
                        document.getElementById('indModalBodyContent').innerHTML = `<div class="alert alert-danger">Failed to generate report. ${data.error || ''}</div>`;
                    }
                }).catch(err => {
                    document.getElementById('indModalBodyContent').innerHTML = `<div class="alert alert-danger">Network error.</div>`;
                });
        });
    });
</script>
@endpush