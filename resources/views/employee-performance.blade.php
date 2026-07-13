@extends('master')

@push('page-style')
<!-- Bootstrap CDN as requested -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Font Awesome for premium icons -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --success-gradient: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
        --warning-gradient: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
        --danger-gradient: linear-gradient(135deg, #ff0844 0%, #ffb199 100%);
        --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        --glass-bg: rgba(255, 255, 255, 0.95);
        --glass-border: rgba(255, 255, 255, 0.2);
    }

    body {
        background-color: #f4f7fe;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    /* Premium Card Aesthetics */
    .premium-card {
        background: var(--glass-bg);
        border: 1px solid var(--glass-border);
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        backdrop-filter: blur(10px);
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        overflow: hidden;
    }

    .premium-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    }

    .card-header-custom {
        background: transparent;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        padding: 20px 25px;
        font-weight: 700;
        font-size: 1.1rem;
        color: #2b3674;
    }

    /* Gradients and Text */
    .text-gradient-primary {
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    /* Badges */
    .status-badge {
        padding: 8px 20px;
        border-radius: 30px;
        font-weight: 700;
        font-size: 0.95rem;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .badge-excellent { background: var(--success-gradient); color: #000; }
    .badge-good { background: var(--info-gradient); color: #fff; }
    .badge-needs-improvement { background: var(--danger-gradient); color: #fff; }

    /* Progress Bars */
    .progress-custom {
        height: 12px;
        border-radius: 10px;
        background-color: #e9ecef;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
        overflow: visible;
        margin-top: 10px;
    }
    
    .progress-bar-custom {
        border-radius: 10px;
        position: relative;
        box-shadow: 0 3px 10px rgba(0,0,0,0.2);
    }

    .progress-bar-custom::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.3) 50%, rgba(255,255,255,0) 100%);
        border-radius: 10px;
        animation: shimmer 2.5s infinite;
    }

    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }

    /* Icons */
    .icon-box {
        width: 55px;
        height: 55px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        color: white;
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    }

    /* List Items for Strengths/Weaknesses/Recommendations */
    .list-card {
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 15px;
        font-weight: 600;
        transition: transform 0.2s ease;
    }

    .list-card:last-child {
        margin-bottom: 0;
    }

    .list-card:hover {
        transform: scale(1.02);
    }

    .list-card-green { background: rgba(132, 250, 176, 0.15); border-left: 4px solid #38c172; color: #1f5c35; }
    .list-card-red { background: rgba(255, 8, 68, 0.1); border-left: 4px solid #e3342f; color: #721c24; }
    .list-card-yellow { background: rgba(246, 211, 101, 0.15); border-left: 4px solid #f6993f; color: #856404; }
    .list-card-blue { background: rgba(79, 172, 254, 0.15); border-left: 4px solid #4facfe; color: #0a4b78; } /* Alternative for recommendations */

    /* Leaderboard Score Circle */
    .score-circle {
        width: 160px;
        height: 160px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        background: var(--primary-gradient);
        color: white;
        box-shadow: 0 10px 25px rgba(118, 75, 162, 0.4);
        margin: 0 auto;
        position: relative;
    }
    .score-circle::before {
        content: '';
        position: absolute;
        top: -12px; left: -12px; right: -12px; bottom: -12px;
        border: 2px dashed rgba(118, 75, 162, 0.4);
        border-radius: 50%;
        animation: spin 15s linear infinite;
    }

    @keyframes spin {
        100% { transform: rotate(360deg); }
    }

    .score-value {
        font-size: 3.5rem;
        font-weight: 800;
        line-height: 1;
    }
    .score-label {
        font-size: 0.95rem;
        font-weight: 600;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-top: 5px;
    }

    /* Animations */
    .fade-in-up {
        animation: fadeInUp 0.7s cubic-bezier(0.25, 0.8, 0.25, 1) forwards;
        opacity: 0;
        transform: translateY(30px);
    }

    @keyframes fadeInUp {
        to { opacity: 1; transform: translateY(0); }
    }

    .delay-1 { animation-delay: 0.1s; }
    .delay-2 { animation-delay: 0.2s; }
    .delay-3 { animation-delay: 0.3s; }
</style>
@endpush

@section('page-content')

@php
    // Logic for Status Badge based on Final Leadership Score
    $score = $report['final_leadership_score'] ?? 0;
    
    if($score >= 85) {
        $status = 'Excellent';
        $badgeClass = 'badge-excellent';
        $statusIcon = 'fa-star';
    } elseif($score >= 70) {
        $status = 'Good';
        $badgeClass = 'badge-good';
        $statusIcon = 'fa-thumbs-up';
    } else {
        $status = 'Needs Improvement';
        $badgeClass = 'badge-needs-improvement';
        $statusIcon = 'fa-triangle-exclamation';
    }
@endphp

<div class="container-fluid py-4">
    
    <!-- Top Row: Header & Leaderboard Score -->
    <div class="row g-4 mb-4 align-items-stretch">
        <!-- Header Card -->
        <div class="col-lg-8 fade-in-up">
            <div class="premium-card h-100 p-4 p-md-5 d-flex flex-column justify-content-center position-relative overflow-hidden">
                <div class="position-absolute top-0 end-0 opacity-10" style="font-size: 18rem; transform: translate(15%, -25%); color: #667eea;">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
                <div class="z-1">
                    <h6 class=" text-uppercase fw-bold mb-2 tracking-wide"><i class="fa-solid fa-id-badge me-2"></i>Employee Performance Overview</h6>
                    <h1 class="display-4 fw-bolder mb-4 text-gradient-primary">{{ $report['employee_name'] }}</h1>
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <span class="status-badge {{ $badgeClass }}">
                            <i class="fa-solid {{ $statusIcon }}"></i> {{ $status }}
                        </span>
                        <span class="text-muted fw-semibold px-3 py-2 rounded-pill bg-light border">
                            <i class="fa-solid fa-chart-line text-primary me-2"></i> Leadership Assessment Complete
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Leaderboard Score Card -->
        <div class="col-lg-4 fade-in-up delay-1">
            <div class="premium-card h-100 p-4 p-md-5 text-center d-flex flex-column justify-content-center">
                <h5 class="fw-bold  mb-4 text-uppercase tracking-wide">Final Leadership Score</h5>
                <div class="score-circle mx-auto">
                    <div class="score-value">{{ $report['final_leadership_score'] }}</div>
                    <div class="score-label">/ 100</div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Cards Row -->
    <div class="row g-4 mb-4">
        <!-- Task Completion -->
        <div class="col-sm-6 col-xl-3 fade-in-up delay-2">
            <div class="premium-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class=" fw-bold mb-1">Task Completion Rate</h6>
                        <h3 class="fw-bold mb-0 ">{{ $report['task_completion_rate'] }}%</h3>
                    </div>
                    <div class="icon-box" style="background: var(--info-gradient);">
                        <i class="fa-solid fa-list-check"></i>
                    </div>
                </div>
                <div class="progress progress-custom">
                    <div class="progress-bar progress-bar-custom" style="width: {{ $report['task_completion_rate'] }}%; background: var(--info-gradient);" role="progressbar" aria-valuenow="{{ $report['task_completion_rate'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>

        <!-- On-Time Delivery -->
        <div class="col-sm-6 col-xl-3 fade-in-up delay-2">
            <div class="premium-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class=" fw-bold mb-1">On-Time Delivery Rate</h6>
                        <h3 class="fw-bold mb-0 ">{{ $report['on_time_completion_rate'] }}%</h3>
                    </div>
                    <div class="icon-box" style="background: var(--warning-gradient);">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                </div>
                <div class="progress progress-custom">
                    <div class="progress-bar progress-bar-custom" style="width: {{ $report['on_time_completion_rate'] }}%; background: var(--warning-gradient);" role="progressbar" aria-valuenow="{{ $report['on_time_completion_rate'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>

        <!-- Attendance -->
        <div class="col-sm-6 col-xl-3 fade-in-up delay-2">
            <div class="premium-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class=" fw-bold mb-1">Attendance Score</h6>
                        <h3 class="fw-bold mb-0 ">{{ $report['attendance_score'] }}%</h3>
                    </div>
                    <div class="icon-box" style="background: var(--success-gradient);">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                </div>
                <div class="progress progress-custom">
                    <div class="progress-bar progress-bar-custom" style="width: {{ $report['attendance_score'] }}%; background: var(--success-gradient);" role="progressbar" aria-valuenow="{{ $report['attendance_score'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>

        <!-- Git Contribution -->
        <div class="col-sm-6 col-xl-3 fade-in-up delay-2">
            <div class="premium-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class=" fw-bold mb-1">Git Contribution</h6>
                        <h3 class="fw-bold mb-0 ">{{ $report['git_contribution_score'] }}%</h3>
                    </div>
                    <div class="icon-box" style="background: linear-gradient(135deg, #434343 0%, #000000 100%);">
                        <i class="fa-brands fa-git-alt"></i>
                    </div>
                </div>
                <div class="progress progress-custom">
                    <div class="progress-bar progress-bar-custom" style="width: {{ $report['git_contribution_score'] }}%; background: linear-gradient(135deg, #434343 0%, #000000 100%);" role="progressbar" aria-valuenow="{{ $report['git_contribution_score'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Summary Section -->
    <div class="row mb-4 fade-in-up delay-3">
        <div class="col-12">
            <div class="premium-card border-0" style="border-left: 5px solid #667eea;">
                <div class="card-header-custom d-flex align-items-center gap-2">
                    <i class="fa-solid fa-wand-magic-sparkles text-primary"></i> AI Performance Summary
                </div>
                <div class="card-body p-4">
                    <p class="mb-0 fs-5 text-body fw-medium" style="line-height: 1.7;">
                        {{ $report['summary'] }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Strengths, Weaknesses, Recommendations -->
    <div class="row g-4 fade-in-up delay-3">
        
        <!-- Strengths -->
        <div class="col-lg-4 col-md-6">
            <div class="premium-card h-100">
                <div class="card-header-custom border-bottom-0 pb-0">
                    <i class="fa-solid fa-arrow-trend-up text-success me-2"></i> Strengths
                </div>
                <div class="card-body p-4">
                    @foreach($report['strengths'] as $strength)
                        <div class="list-card list-card-green shadow-sm">
                            <i class="fa-solid fa-circle-check fs-5"></i>
                            <span>{{ $strength }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Weaknesses -->
        <div class="col-lg-4 col-md-6">
            <div class="premium-card h-100">
                <div class="card-header-custom border-bottom-0 pb-0">
                    <i class="fa-solid fa-arrow-trend-down text-danger me-2"></i> Areas for Improvement
                </div>
                <div class="card-body p-4">
                    @foreach($report['weaknesses'] as $weakness)
                        <div class="list-card list-card-red shadow-sm">
                            <i class="fa-solid fa-circle-xmark fs-5"></i>
                            <span>{{ $weakness }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Recommendations -->
        <div class="col-lg-4 col-md-12">
            <div class="premium-card h-100">
                <div class="card-header-custom border-bottom-0 pb-0">
                    <i class="fa-regular fa-lightbulb text-warning me-2"></i> Recommendations
                </div>
                <div class="card-body p-4">
                    @foreach($report['recommendations'] as $recommendation)
                        <div class="list-card list-card-blue shadow-sm">
                            <i class="fa-solid fa-bullseye fs-5"></i>
                            <span>{{ $recommendation }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

</div>

@endsection
