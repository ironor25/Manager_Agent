@extends('master')

@push('page-style')
<style>
    .rank-badge {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: white;
    }
    .rank-1 { background: linear-gradient(135deg, #fbbf24, #d97706); box-shadow: 0 4px 10px rgba(217, 119, 6, 0.3); }
    .rank-2 { background: linear-gradient(135deg, #94a3b8, #64748b); box-shadow: 0 4px 10px rgba(100, 116, 139, 0.3); }
    .rank-3 { background: linear-gradient(135deg, #d97706, #92400e); box-shadow: 0 4px 10px rgba(146, 64, 14, 0.3); }
    .rank-other { background: #f1f5f9; color: #64748b; font-weight: 600; }
    
    .list-group-item {
        border-left: none;
        border-right: none;
        padding: 1rem;
        transition: background-color 0.2s;
    }
    .list-group-item:first-child { border-top: none; }
    .list-group-item:last-child { border-bottom: none; }
    .list-group-item:hover { background-color: #f8fafc; }
</style>
@endpush

@section('page-content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0  fw-bold">Organization Leaderboard</h1>
            <p class="text-muted mb-0">High-level rankings across the entire organization.</p>
        </div>
    </div>

    <div class="row" id="leaderboardCards">
        <!-- Top Employees -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-bottom py-3 d-flex align-items-center">
                    <i class="fa-solid fa-trophy text-warning me-2 fs-5"></i>
                    <h6 class="mb-0 fw-bold">Top Employees</h6>
                </div>
                <ul class="list-group list-group-flush" id="topEmployeesList">
                    <li class="list-group-item text-center py-4 text-muted"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</li>
                </ul>
            </div>
        </div>

        <!-- Top Teams -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-bottom py-3 d-flex align-items-center">
                    <i class="fa-solid fa-users-rays text-primary me-2 fs-5"></i>
                    <h6 class="mb-0 fw-bold">Top Teams</h6>
                </div>
                <ul class="list-group list-group-flush" id="topTeamsList">
                    <li class="list-group-item text-center py-4 text-muted"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</li>
                </ul>
            </div>
        </div>

        <!-- Top Contributors -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-bottom py-3 d-flex align-items-center">
                    <i class="fa-brands fa-gitlab text-warning me-2 fs-5" style="color: #fc6d26 !important;"></i>
                    <h6 class="mb-0 fw-bold">Top Code Contributors</h6>
                </div>
                <ul class="list-group list-group-flush" id="topContributorsList">
                    <li class="list-group-item text-center py-4 text-muted"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-script')
<script>
$(document).ready(function() {
    function getRankBadge(index) {
        const rank = index + 1;
        if (rank === 1) return '<div class="rank-badge rank-1">1</div>';
        if (rank === 2) return '<div class="rank-badge rank-2">2</div>';
        if (rank === 3) return '<div class="rank-badge rank-3">3</div>';
        return `<div class="rank-badge rank-other">${rank}</div>`;
    }

    function loadOrganizationData() {
        $.get("{{ route('leaderboard.organization.data') }}", function(data) {
            
            // Render Top Employees
            let empHtml = '';
            if(data.top_employees.length > 0) {
                data.top_employees.forEach((emp, i) => {
                    empHtml += `
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                ${getRankBadge(i)}
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark">${emp.name}</h6>
                                    <small class="text-muted">${emp.team || 'No Team'}</small>
                                </div>
                            </div>
                            <span class="badge bg-primary rounded-pill fs-6">${parseFloat(emp.score).toFixed(1)}</span>
                        </li>
                    `;
                });
            } else {
                empHtml = '<li class="list-group-item text-center py-4 text-muted">No data available</li>';
            }
            $('#topEmployeesList').html(empHtml);

            // Render Top Teams
            let teamHtml = '';
            if(data.top_teams.length > 0) {
                data.top_teams.forEach((team, i) => {
                    teamHtml += `
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                ${getRankBadge(i)}
                                <h6 class="mb-0 fw-bold text-dark">${team.name}</h6>
                            </div>
                            <span class="badge bg-primary rounded-pill fs-6">${parseFloat(team.score).toFixed(1)}</span>
                        </li>
                    `;
                });
            } else {
                teamHtml = '<li class="list-group-item text-center py-4 text-muted">No data available</li>';
            }
            $('#topTeamsList').html(teamHtml);

            // Render Top Contributors
            let contHtml = '';
            if(data.top_contributors.length > 0) {
                data.top_contributors.forEach((emp, i) => {
                    contHtml += `
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                ${getRankBadge(i)}
                                <h6 class="mb-0 fw-bold text-dark">${emp.name}</h6>
                            </div>
                            <span class="badge bg-dark rounded-pill fs-6" style="background-color: #fc6d26 !important;"><i class="fa-brands fa-gitlab me-1"></i> ${parseFloat(emp.score).toFixed(0)}</span>
                        </li>
                    `;
                });
            } else {
                contHtml = '<li class="list-group-item text-center py-4 text-muted">No data available</li>';
            }
            $('#topContributorsList').html(contHtml);
        });
    }

    loadOrganizationData();
});
</script>
@endpush