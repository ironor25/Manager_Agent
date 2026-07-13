@extends('master')

@push('page-style')
<style>
    .profile-header {
        background: linear-gradient(135deg, var(--primary) 0%, #3b82f6 100%);
        color: white;
        border-radius: 12px;
        padding: 30px;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }
    .profile-avatar {
        width: 100px;
        height: 100px;
        background: rgba(255,255,255,0.2);
        color: white;
        font-size: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        border: 4px solid rgba(255,255,255,0.5);
        overflow: hidden;
    }
    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .kpi-box {
        background: #f8fafc;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        border: 1px solid #e2e8f0;
    }
</style>
@endpush

@section('page-content')
<div class="container-fluid">
    <div class="mb-4 d-flex align-items-center gap-3">
        <a href="{{ route('employees.index') }}" class="btn btn-light border shadow-sm">
            <i class="fa-solid fa-arrow-left"></i> Back
        </a>
        <h1 class="h3 mb-0  fw-bold">Employee Profile</h1>
    </div>

    <div class="profile-header shadow-sm">
        <div class="d-flex align-items-center gap-4">
            <div class="profile-avatar">
                @if($employee->photo)
                    <img src="{{ asset('storage/' . $employee->photo) }}" alt="{{ $employee->name }}">
                @else
                    {{ substr($employee->name, 0, 1) }}
                @endif
            </div>
            <div>
                <h2 class="fw-bold mb-1">{{ $employee->name }}</h2>
                <p class="mb-2 opacity-75 fs-5"><i class="fa-solid fa-users me-2"></i>{{ $employee->team ?? 'No Team Assigned' }}</p>
                <div class="d-flex gap-3 text-sm">
                    <span><i class="fa-solid fa-envelope me-1"></i> {{ $employee->email }}</span>
                    <span><i class="fa-solid fa-calendar me-1"></i> Joined: {{ $employee->created_at ? $employee->created_at->format('M d, Y') : 'Unknown' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Task Metrics -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-list-check me-2"></i>Task Productivity</h6>
                </div>
                <div class="card-body">
                    <h2 class="fw-bold">{{ $taskMetrics->productivity_score ?? 0 }}<small class=" fs-6">/100</small></h2>
                    <p class="text-muted small">Score Category: <span class="badge bg-info">{{ $taskMetrics->rating_category ?? 'N/A' }}</span></p>
                    <hr>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span>Completed Tasks</span>
                        <span class="fw-bold">{{ $taskMetrics->completed_tasks ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span>Overdue Tasks</span>
                        <span class="fw-bold text-danger">{{ $taskMetrics->overdue_tasks ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span>Completion Rate</span>
                        <span class="fw-bold">{{ $taskMetrics->completion_rate ?? 0 }}%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Metrics -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-success"><i class="fa-solid fa-clock me-2"></i>Attendance & Punctuality</h6>
                </div>
                <div class="card-body">
                    <h2 class="fw-bold">{{ $attendanceMetrics->attendance_score ?? 0 }}<small class=" fs-6">/100</small></h2>
                    <p class="text-muted small">Attendance %: <span class="fw-bold text-success">{{ $attendanceMetrics->attendance_percentage ?? 0 }}%</span></p>
                    <hr>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span>Present Days</span>
                        <span class="fw-bold">{{ $attendanceMetrics->present_days ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span>Late Arrivals</span>
                        <span class="fw-bold text-warning">{{ $attendanceMetrics->late_days ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span>Absent / Leave</span>
                        <span class="fw-bold text-danger">{{ $attendanceMetrics->absent_days ?? 0 }} / {{ $attendanceMetrics->leave_days ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Workload Metrics -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-warning"><i class="fa-solid fa-weight-hanging me-2"></i>Current Workload</h6>
                </div>
                <div class="card-body">
                    <h2 class="fw-bold">{{ $workloadMetrics->workload_percentage ?? 0 }}<small class=" fs-6">%</small></h2>
                    <p class="text-muted small">Status: <span class="badge bg-warning text-dark text-uppercase">{{ $workloadMetrics->status ?? 'Unknown' }}</span></p>
                    <hr>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span>Active Tasks</span>
                        <span class="fw-bold">{{ $workloadMetrics->active_tasks ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span>Assigned Projects</span>
                        <span class="fw-bold">{{ $employee->projects->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- GitLab Metrics -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-bottom py-3">
                    <h6 class="fw-bold mb-0" style="color: #fc6d26;"><i class="fa-brands fa-gitlab me-2"></i>GitLab Activity</h6>
                </div>
                <div class="card-body text-center d-flex flex-column justify-content-center">
                    <h1 class="display-4 fw-bold text-dark mb-0">{{ $githubCommits }}</h1>
                    <p class="text-muted mb-0">Total Lifetime Commits</p>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Performance & Leadership Report Section -->
    <div class="row g-4 mt-2 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-robot me-2"></i>AI Performance & Leadership Report</h5>
                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3" id="btn-regenerate-report" style="{{ $existingReport ? '' : 'display: none;' }}" onclick="generateReport()">
                        <i class="fa-solid fa-arrows-rotate me-1"></i> Regenerate AI Report
                    </button>
                </div>
                <div class="card-body" id="report-card-body">
                    
                    <!-- Loading State (hidden initially) -->
                    <div class="text-center py-5" id="report-loading" style="display: none;">
                        <div class="spinner-border text-primary mb-3" role="status"></div>
                        <h6 class=" fw-medium" id="report-loading-text">Generating AI Insights... This may take up to 20 seconds.</h6>
                    </div>

                    @if($existingReport)
                        <!-- Render Stored Report -->
                        <div id="report-container">
                            <div class="row g-4">
                                <!-- Left Panel: Score and Strengths -->
                                <div class="col-lg-5">
                                    <div class="p-4 bg-light rounded-3 text-center border mb-3">
                                        <h6 class="text-uppercase  fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 1px;">Leadership Score</h6>
                                        <div class="d-flex align-items-center justify-content-center gap-3">
                                            <h1 class="fw-bolder  mb-0" id="report-score" style="font-size: 3rem;">{{ $existingReport->leadership_score }}</h1>
                                            @php
                                                $score = $existingReport->leadership_score;
                                                if ($score >= 85) {
                                                    $badgeClass = 'bg-success text-white';
                                                    $badgeText = 'Excellent';
                                                } elseif ($score >= 70) {
                                                    $badgeClass = 'bg-primary text-white';
                                                    $badgeText = 'Good';
                                                } else {
                                                    $badgeClass = 'bg-warning text-dark';
                                                    $badgeText = 'Needs Improvement';
                                                }
                                            @endphp
                                            <span class="badge {{ $badgeClass }}" id="report-status" style="font-size: 0.9rem; padding: 8px 16px;">{{ $badgeText }}</span>
                                        </div>
                                    </div>
                                    <div class="p-3 border rounded-3 bg-white">
                                        <h6 class="fw-bold text-success mb-3"><i class="fa-solid fa-circle-check me-2"></i>Key Strengths</h6>
                                        <div id="report-strengths" class="d-flex flex-column gap-2 text-muted" style="font-size: 0.9rem;">
                                            @if(!empty($existingReport->strengths))
                                                @foreach($existingReport->strengths as $strength)
                                                    <div class="d-flex gap-2 mb-1">
                                                        <i class="fa-solid fa-check text-success mt-1"></i>
                                                        <span>{{ $strength }}</span>
                                                    </div>
                                                @endforeach
                                            @else
                                                <span class="text-muted fst-italic">None recorded</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Right Panel: Summary, Weaknesses, Recommendations -->
                                <div class="col-lg-7">
                                    <div class="mb-4">
                                        <h6 class="fw-bold text-primary mb-2"><i class="fa-solid fa-file-lines me-2"></i>AI Executive Summary</h6>
                                        <p id="report-summary" class="text-muted lh-lg mb-0" style="font-size: 0.95rem; text-align: justify;">{{ $existingReport->summary }}</p>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="p-3 border rounded-3 bg-white h-100">
                                                <h6 class="fw-bold text-danger mb-3"><i class="fa-solid fa-circle-xmark me-2"></i>Areas for Improvement</h6>
                                                <div id="report-weaknesses" class="d-flex flex-column gap-2 text-muted" style="font-size: 0.9rem;">
                                                    @if(!empty($existingReport->weaknesses))
                                                        @foreach($existingReport->weaknesses as $weakness)
                                                            <div class="d-flex gap-2 mb-1">
                                                                <i class="fa-solid fa-xmark text-danger mt-1"></i>
                                                                <span>{{ $weakness }}</span>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted fst-italic">None recorded</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="p-3 border rounded-3 bg-white h-100">
                                                <h6 class="fw-bold text-warning mb-3"><i class="fa-regular fa-lightbulb me-2"></i>Recommendations</h6>
                                                <div id="report-recommendations" class="d-flex flex-column gap-2 text-muted" style="font-size: 0.9rem;">
                                                    @if(!empty($existingReport->recommendations))
                                                        @foreach($existingReport->recommendations as $rec)
                                                            <div class="d-flex gap-2 mb-1">
                                                                <i class="fa-solid fa-angle-right text-warning mt-1"></i>
                                                                <span>{{ $rec }}</span>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted fst-italic">None recorded</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- No Stored Report Action Banner -->
                        <div class="text-center py-5" id="report-empty">
                            <i class="fa-solid fa-file-contract text-muted fa-3x mb-3 opacity-50"></i>
                            <h5 class="fw-bold ">No AI Performance Report Generated Yet</h5>
                            <p class="text-muted mx-auto mb-4" style="max-width: 500px;">Generate a comprehensive assessment including leadership capability scoring, strengths, key weaknesses, and customized growth recommendations.</p>
                            <button class="btn btn-primary px-4 py-2 rounded-pill shadow-sm" onclick="generateReport()">
                                <i class="fa-solid fa-wand-magic-sparkles me-2"></i> Generate AI Report
                            </button>
                        </div>
                        
                        <!-- Placeholder Container for Dynamic Rendering -->
                        <div id="report-container" style="display: none;">
                            <div class="row g-4">
                                <div class="col-lg-5">
                                    <div class="p-4 bg-light rounded-3 text-center border mb-3">
                                        <h6 class="text-uppercase  fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 1px;">Leadership Score</h6>
                                        <div class="d-flex align-items-center justify-content-center gap-3">
                                            <h1 class="fw-bolder  mb-0" id="report-score" style="font-size: 3rem;">0</h1>
                                            <span class="badge" id="report-status" style="font-size: 0.9rem; padding: 8px 16px;">Pending</span>
                                        </div>
                                    </div>
                                    <div class="p-3 border rounded-3 bg-white">
                                        <h6 class="fw-bold text-success mb-3"><i class="fa-solid fa-circle-check me-2"></i>Key Strengths</h6>
                                        <div id="report-strengths" class="d-flex flex-column gap-2 text-muted" style="font-size: 0.9rem;"></div>
                                    </div>
                                </div>
                                <div class="col-lg-7">
                                    <div class="mb-4">
                                        <h6 class="fw-bold text-primary mb-2"><i class="fa-solid fa-file-lines me-2"></i>AI Executive Summary</h6>
                                        <p id="report-summary" class="text-muted lh-lg mb-0" style="font-size: 0.95rem; text-align: justify;"></p>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="p-3 border rounded-3 bg-white h-100">
                                                <h6 class="fw-bold text-danger mb-3"><i class="fa-solid fa-circle-xmark me-2"></i>Areas for Improvement</h6>
                                                <div id="report-weaknesses" class="d-flex flex-column gap-2 text-muted" style="font-size: 0.9rem;"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="p-3 border rounded-3 bg-white h-100">
                                                <h6 class="fw-bold text-warning mb-3"><i class="fa-regular fa-lightbulb me-2"></i>Recommendations</h6>
                                                <div id="report-recommendations" class="d-flex flex-column gap-2 text-muted" style="font-size: 0.9rem;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-script')
<script>
function generateReport() {
    var employeeId = "{{ $employee->id }}";
    var url = "/admin/employee/" + employeeId + "/report";
    
    $('#report-empty').hide();
    $('#report-container').hide();
    $('#report-loading').show();
    $('#btn-regenerate-report').hide();

    $.ajax({
        url: url,
        type: 'GET',
        success: function(response) {
            $('#report-loading').hide();
            
            // Populate fields
            $('#report-summary').text(response.summary || 'No summary available.');
            
            let score = response.leadership_score || 0;
            $('#report-score').text(score);
            
            let statusBadge = $('#report-status');
            if (score >= 85) {
                statusBadge.text('Excellent').removeClass('bg-warning bg-danger text-body').addClass('bg-success text-white');
            } else if (score >= 70) {
                statusBadge.text('Good').removeClass('bg-warning bg-danger text-body').addClass('bg-primary text-white');
            } else {
                statusBadge.text('Needs Improvement').removeClass('bg-success bg-primary text-white').addClass('bg-warning text-dark');
            }

            const formatList = (arr, iconClass) => arr && arr.length ? arr.map(item => `<div class="d-flex gap-2 mb-1"><i class="${iconClass} mt-1"></i><span>${item}</span></div>`).join('') : '<span class="text-muted fst-italic">None recorded</span>';

            $('#report-strengths').html(formatList(response.strengths, 'fa-solid fa-check text-success'));
            $('#report-weaknesses').html(formatList(response.weaknesses, 'fa-solid fa-xmark text-danger'));
            $('#report-recommendations').html(formatList(response.recommendations, 'fa-solid fa-angle-right text-warning'));

            $('#btn-regenerate-report').show();
            $('#report-container').fadeIn();
        },
        error: function() {
            $('#report-loading').hide();
            $('#report-empty').show();
            alert('Failed to generate AI performance report. Please check system logs.');
        }
    });
}
</script>
@endpush
