@extends('master')

@push('page-style')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
<style>
    .report-btn {
        background-color: transparent;
        color: var(--primary);
        border: 1px solid var(--primary);
        border-radius: 50px;
        padding: 6px 16px;
        font-weight: 500;
        font-size: 0.85rem;
        transition: all 0.2s;
    }
    .report-btn:hover {
        background-color: var(--primary);
        color: white;
    }
    .team-header-card {
        background: linear-gradient(135deg, rgba(79, 70, 229, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%);
        border: 1px solid rgba(79, 70, 229, 0.2);
        border-radius: 16px;
    }
</style>
@endpush

@section('page-content')
<div class="mb-4">
    <a href="{{ route('teams.dashboard') }}#management" class="text-decoration-none text-muted mb-3 d-inline-block">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Teams Dashboard
    </a>
    
    <div class="team-header-card p-4 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1 text-primary">{{ $team->name }} Team</h2>
            <p class="text-muted mb-0" style="max-width: 600px;">{{ $team->description ?? 'No description provided.' }}</p>
        </div>
        <div>
            <button class="btn btn-primary px-4 py-2 shadow-sm rounded-pill" id="generateTeamReportBtn" data-team="{{ $team->name }}">
                <i class="fa-solid fa-wand-magic-sparkles me-2"></i> Whole Team Performance
            </button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-body">Team Members ({{ $team->employees->count() }})</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="membersTable">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3">Employee</th>
                                <th class="py-3">Email</th>
                                <th class="text-end px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($team->employees as $employee)
                            <tr>
                                <td class="px-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar bg-light text-primary" style="width: 40px; height: 40px; font-size: 1rem; font-weight: bold; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                            {{ substr($employee->name, 0, 1) }}
                                        </div>
                                        <div class="fw-bold text-body">{{ $employee->name }}</div>
                                    </div>
                                </td>
                                <td><span class="text-muted">{{ $employee->email }}</span></td>
                                <td class="text-end px-4">
                                    <button class="report-btn generate-individual-report-btn" data-id="{{ $employee->id }}" data-name="{{ $employee->name }}">
                                        <i class="fa-solid fa-chart-simple me-1"></i> Individual Performance
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-5 text-muted">No members assigned to this team yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Team AI Report Modal -->
<div class="modal fade" id="teamReportModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <h5 class="modal-title fw-bold text-body">
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
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <h5 class="modal-title fw-bold text-body">
            <i class="fa-solid fa-wand-magic-sparkles text-primary me-2"></i> Individual Report: <span id="modalIndName" class="text-primary"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" id="indModalBodyContent">
      </div>
    </div>
  </div>
</div>
@endsection

@push('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // Helper to format lists
        const formatList = (arr, iconClass) => arr && arr.length ? arr.map(item => `<div class="d-flex gap-2 mb-2"><i class="${iconClass} mt-1"></i><span>${item}</span></div>`).join('') : '<span class="text-muted">None recorded</span>';

        // 1. Whole Team Performance
        const teamReportBtn = document.getElementById('generateTeamReportBtn');
        if (teamReportBtn) {
            teamReportBtn.addEventListener('click', function() {
                const teamName = this.dataset.team;
                const modal = new bootstrap.Modal(document.getElementById('teamReportModal'));
                document.getElementById('modalTeamName').innerText = teamName;
                document.getElementById('teamModalBodyContent').innerHTML = `
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary mb-3" role="status"></div>
                        <h6 class="text-muted fw-medium">Analyzing data and generating team report...</h6>
                    </div>
                `;
                modal.show();

                fetch(`/teams/${teamName}/report`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.report) {
                            const r = data.report;
                            document.getElementById('teamModalBodyContent').innerHTML = `
                                <div class="mb-4"><p class="text-muted lh-lg mb-0">${r.summary}</p></div>
                                <div class="row g-4 mb-4">
                                    <div class="col-md-6"><div class="p-3 border rounded-3 h-100 bg-white"><h6 class="fw-bold text-success mb-3"><i class="fa-solid fa-arrow-trend-up me-2"></i>Strengths</h6><div class="text-muted" style="font-size: 0.9rem;">${formatList(r.strengths, 'fa-solid fa-check text-success')}</div></div></div>
                                    <div class="col-md-6"><div class="p-3 border rounded-3 h-100 bg-white"><h6 class="fw-bold text-danger mb-3"><i class="fa-solid fa-arrow-trend-down me-2"></i>Weaknesses</h6><div class="text-muted" style="font-size: 0.9rem;">${formatList(r.weaknesses, 'fa-solid fa-xmark text-danger')}</div></div></div>
                                </div>
                                <div><div class="p-3 border rounded-3 bg-light"><h6 class="fw-bold text-primary mb-3"><i class="fa-solid fa-lightbulb me-2"></i>Recommendations</h6><div class="text-muted" style="font-size: 0.9rem;">${formatList(r.recommendations, 'fa-solid fa-angle-right text-primary')}</div></div></div>
                            `;
                        } else {
                            document.getElementById('teamModalBodyContent').innerHTML = `<div class="alert alert-danger">Failed to generate report. ${data.error || ''}</div>`;
                        }
                    }).catch(err => {
                        document.getElementById('teamModalBodyContent').innerHTML = `<div class="alert alert-danger">Network error.</div>`;
                    });
            });
        }

        // 2. Individual Performance
        document.querySelector('#membersTable').addEventListener('click', function(e) {
            const btn = e.target.closest('.generate-individual-report-btn');
            if (btn) {
                const empId = btn.dataset.id;
                const empName = btn.dataset.name;
                
                const modal = new bootstrap.Modal(document.getElementById('individualReportModal'));
                document.getElementById('modalIndName').innerText = empName;
                document.getElementById('indModalBodyContent').innerHTML = `
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary mb-3" role="status"></div>
                        <h6 class="text-muted fw-medium">Analyzing data and generating individual report...</h6>
                    </div>
                `;
                modal.show();

                fetch(`/employee/${empId}/report`)
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
                                <div class="mb-4"><p class="text-muted lh-lg mb-0">${r.summary}</p></div>
                                <div class="row g-4 mb-4">
                                    <div class="col-md-6"><div class="p-3 border rounded-3 h-100 bg-white"><h6 class="fw-bold text-success mb-3"><i class="fa-solid fa-arrow-trend-up me-2"></i>Strengths</h6><div class="text-muted" style="font-size: 0.9rem;">${formatList(r.strengths, 'fa-solid fa-check text-success')}</div></div></div>
                                    <div class="col-md-6"><div class="p-3 border rounded-3 h-100 bg-white"><h6 class="fw-bold text-danger mb-3"><i class="fa-solid fa-arrow-trend-down me-2"></i>Weaknesses</h6><div class="text-muted" style="font-size: 0.9rem;">${formatList(r.weaknesses, 'fa-solid fa-xmark text-danger')}</div></div></div>
                                </div>
                                <div><div class="p-3 border rounded-3 bg-light"><h6 class="fw-bold text-primary mb-3"><i class="fa-solid fa-lightbulb me-2"></i>Recommendations</h6><div class="text-muted" style="font-size: 0.9rem;">${formatList(r.recommendations, 'fa-solid fa-angle-right text-primary')}</div></div></div>
                            `;
                        } else {
                            document.getElementById('indModalBodyContent').innerHTML = `<div class="alert alert-danger">Failed to generate report. ${data.error || ''}</div>`;
                        }
                    }).catch(err => {
                        document.getElementById('indModalBodyContent').innerHTML = `<div class="alert alert-danger">Network error.</div>`;
                    });
            }
        });
    });
</script>
@endpush
