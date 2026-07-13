<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PerformanceReportController;
use App\Http\Controllers\AiAgentController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TeamDashboardController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\DeveloperToolsController;
use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\AttendanceController;

// Public Routes
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware('auth')->group(function () {

    // Profile Settings (shared by both roles)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // -- Employee Protected Routes --
    Route::prefix('employee')->middleware('role:employee')->group(function () {
        Route::get('/dashboard', [EmployeeController::class, 'employeeDashboard'])->name('employee.dashboard');
        Route::get('/tasks', [EmployeeController::class, 'employeeTasks'])->name('employee.tasks');
        Route::get('/tasks/data', [EmployeeController::class, 'employeeTasksData'])->name('employee.tasks.data');
        Route::get('/leaderboard', [EmployeeController::class, 'employeeLeaderboard'])->name('employee.leaderboard');
        Route::get('/leaderboard/data', [EmployeeController::class, 'employeeLeaderboardData'])->name('employee.leaderboard.data');
        Route::get('/commits', [EmployeeController::class, 'employeeCommits'])->name('employee.commits');
        Route::get('/commits/data', [EmployeeController::class, 'employeeCommitsData'])->name('employee.commits.data');
        Route::get('/meetings', [EmployeeController::class, 'employeeMeetings'])->name('employee.meetings');
        Route::get('/meetings/data', [EmployeeController::class, 'employeeMeetingsData'])->name('employee.meetings.data');
        Route::get('/attendance', [EmployeeController::class, 'employeeAttendance'])->name('employee.attendance');
        Route::get('/attendance/data', [EmployeeController::class, 'employeeAttendanceData'])->name('employee.attendance.data');

        Route::match(['get', 'post'], '/tasks/{task}/start', [EmployeeController::class, 'startTaskTimer'])->name('employee.tasks.start');
        Route::match(['get', 'post'], '/tasks/{task}/pause', [EmployeeController::class, 'pauseTaskTimer'])->name('employee.tasks.pause');
        Route::match(['get', 'post'], '/tasks/{task}/stop', [EmployeeController::class, 'stopTaskTimer'])->name('employee.tasks.stop');

        // AI Employee Agent
        Route::prefix('ai-agent')->group(function () {
            Route::get('/', [AiAgentController::class, 'index'])->name('employee.ai-agent.index');
            Route::get('/sessions', [AiAgentController::class, 'getSessions'])->name('employee.ai-agent.sessions');
            Route::get('/sessions/{id}', [AiAgentController::class, 'loadSession'])->name('employee.ai-agent.session.load');
            Route::post('/message', [AiAgentController::class, 'sendMessage'])->name('employee.ai-agent.message');
            Route::delete('/sessions/{id}', [AiAgentController::class, 'destroySession'])->name('employee.ai-agent.session.destroy');
        });
    });

    // -- Admin Protected Routes --
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/dashboard/data', [DashboardController::class, 'getDashboardData'])->name('dashboard.data');

        // -- Employee Management Module --
        Route::prefix('employees')->group(function () {
            Route::get('/', function() { return redirect()->route('employees.index'); });
            Route::get('/search', [EmployeeController::class, 'search'])->name('employees.search');
            Route::get('/management', [EmployeeController::class, 'index'])->name('employees.index');
            Route::get('/management/data', [EmployeeController::class, 'getEmployees'])->name('employees.data');
            Route::get('/management/{employee}', [EmployeeController::class, 'profile'])->name('employees.profile');
            Route::post('/management', [EmployeeController::class, 'store'])->name('employees.store');
            Route::put('/management/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
            Route::delete('/management/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
            
            // Analytics
            Route::get('/analytics', [\App\Http\Controllers\EmployeeAnalyticsController::class, 'index'])->name('employees.analytics');
            Route::get('/analytics/data', [\App\Http\Controllers\EmployeeAnalyticsController::class, 'getAnalyticsData'])->name('employees.analytics.data');
        });

        // -- Access Control --
        Route::prefix('access-control')->middleware('role:admin')->group(function () {
            Route::get('/', [\App\Http\Controllers\AccessControlController::class, 'index'])->name('access-control.index');
            Route::get('/data', [\App\Http\Controllers\AccessControlController::class, 'getUsers'])->name('access-control.data');
            Route::put('/{user}', [\App\Http\Controllers\AccessControlController::class, 'updateRole'])->name('access-control.update');
        });

        Route::post('/employees/generate-all-reports', [PerformanceReportController::class, 'generateAllReports'])->name('employees.generate_all_reports');
        Route::get('/employee/{id}/report', [PerformanceReportController::class, 'generateReport'])->name('employee.report');   

        Route::get('/teams', [TeamDashboardController::class, 'index'])->name('teams.dashboard');
        Route::get('/teams-management', [TeamDashboardController::class, 'management'])->name('teams.management');
        Route::get('/teams-management/data', [TeamDashboardController::class, 'getTeamsData'])->name('teams.data');
        Route::get('/teams/kpis', [TeamDashboardController::class, 'getKpis'])->name('teams.kpis');
        Route::get('/teams/chart-data', [TeamDashboardController::class, 'getChartData'])->name('teams.chart');
        Route::get('/teams/leaderboard', [TeamDashboardController::class, 'leaderboard'])->name('teams.leaderboard');
        Route::get('/teams/{team}/report', [TeamDashboardController::class, 'generateReport'])->name('teams.generate_report');
        Route::post('/teams/assign-task', [TeamDashboardController::class, 'assignTask'])->name('teams.assign_task');
        Route::post('/teams', [TeamDashboardController::class, 'storeTeam'])->name('teams.store');
        Route::put('/teams/{team}', [TeamDashboardController::class, 'updateTeam'])->name('teams.update');
        Route::delete('/teams/{team}', [TeamDashboardController::class, 'destroyTeam'])->name('teams.destroy');
        Route::post('/teams/{team}/members', [TeamDashboardController::class, 'addMember'])->name('teams.members.add');
        Route::delete('/teams/{team}/members/{employee}', [TeamDashboardController::class, 'removeMember'])->name('teams.members.remove');
        Route::get('/teams/{team}/members/list', [TeamDashboardController::class, 'getMembersList'])->name('teams.members.list');
        Route::get('/teams/{team}/members/data', [TeamDashboardController::class, 'getTeamMembersData'])->name('teams.members.data');
        Route::get('/teams/{team}/details', [TeamDashboardController::class, 'show'])->name('teams.show');

        // Tasks
        Route::get('/tasks', function() { return redirect()->route('tasks.index'); });
        Route::get('/tasks/search', [TaskController::class, 'search'])->name('tasks.search');
        Route::get('/tasks/management', [TaskController::class, 'index'])->name('tasks.index');
        Route::get('/tasks/data', [TaskController::class, 'getTasks'])->name('tasks.data');
        Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
        Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
        Route::get('/tasks/monitoring', [TaskController::class, 'monitoring'])->name('tasks.monitoring');
        Route::get('/tasks/monitoring/data', [TaskController::class, 'getMonitoringData'])->name('tasks.monitoring.data');
        Route::get('/tasks/monitoring/table/{type}', [TaskController::class, 'getMonitoringTable'])->name('tasks.monitoring.table');
        Route::get('/tasks/metrics', [TaskController::class, 'metrics'])->name('tasks.metrics');
        Route::get('/tasks/metrics/data', [TaskController::class, 'getMetricsData'])->name('tasks.metrics.data');

        // Commits & Meetings
        Route::get('/commits', [\App\Http\Controllers\GithubCommitController::class, 'index'])->name('commits.index');
        Route::get('/commits/{id}/diff', [\App\Http\Controllers\GithubCommitController::class, 'diff'])->name('commits.diff');
        Route::get('/meetings', [\App\Http\Controllers\MeetingNoteController::class, 'index'])->name('meetings.index');

        // Developer Tools
        Route::get('/developer-tools', [DeveloperToolsController::class, 'index'])->name('developer-tools.index');
        Route::post('/developer-tools/api-keys', [ApiKeyController::class, 'store'])->name('api-keys.store');
        Route::delete('/developer-tools/api-keys/{id}', [ApiKeyController::class, 'destroy'])->name('api-keys.destroy');
        Route::post('/developer-tools/api-keys/{id}/toggle', [ApiKeyController::class, 'toggleStatus'])->name('api-keys.toggle');



        // Projects Management
        Route::get('/projects', function() { return redirect()->route('projects.index'); });
        Route::get('/projects/management', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('/projects/management/data', [ProjectController::class, 'getProjects'])->name('projects.data');
        Route::post('/projects/management', [ProjectController::class, 'store'])->name('projects.store');
        Route::put('/projects/management/{project}', [ProjectController::class, 'update'])->name('projects.update');
        Route::delete('/projects/management/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
        
        // Project Members & Tabs
        Route::get('/projects/{project}/members', [ProjectController::class, 'show'])->name('projects.show');
        Route::get('/projects/{project}/members/data', [ProjectController::class, 'getMembersData'])->name('projects.members.data');
        Route::get('/projects/{project}/tasks/data', [ProjectController::class, 'getTasksData'])->name('projects.tasks.data');
        Route::get('/projects/{project}/commits/data', [ProjectController::class, 'getCommitsData'])->name('projects.commits.data');
        Route::post('/projects/{project}/members', [ProjectController::class, 'addMember'])->name('projects.members.add');
        Route::delete('/projects/{project}/members/{employee}', [ProjectController::class, 'removeMember'])->name('projects.members.remove');

        // Project Monitoring
        Route::get('/projects/monitoring', [ProjectController::class, 'monitoring'])->name('projects.monitoring');
        Route::get('/projects/monitoring/data', [ProjectController::class, 'getMonitoringData'])->name('projects.monitoring.data');
        Route::get('/projects/monitoring/table', [ProjectController::class, 'getMonitoringTable'])->name('projects.monitoring.table');

        // Project Reports
        Route::get('/projects/reports', [ProjectController::class, 'reports'])->name('projects.reports');
        Route::get('/projects/reports/data', [ProjectController::class, 'getReportsData'])->name('projects.reports.data');

        // Workload Analysis
        Route::get('/workload', [\App\Http\Controllers\WorkloadController::class, 'index'])->name('workload.index');
        Route::get('/workload/kpis', [\App\Http\Controllers\WorkloadController::class, 'getKpis'])->name('workload.kpis');
        Route::get('/workload/chart-data', [\App\Http\Controllers\WorkloadController::class, 'getChartData'])->name('workload.chart');
        Route::get('/workload/employees-data', [\App\Http\Controllers\WorkloadController::class, 'getEmployeesData'])->name('workload.employees.data');
        Route::get('/workload/teams-data', [\App\Http\Controllers\WorkloadController::class, 'getTeamWorkloadData'])->name('workload.teams.data');
        Route::post('/workload/recalculate', [\App\Http\Controllers\WorkloadController::class, 'recalculate'])->name('workload.recalculate');

        // Attendance Module
        Route::get('/attendance', function() { return redirect()->route('attendance.index'); });
        Route::get('/attendance/management', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('/attendance/management/data', [AttendanceController::class, 'getAttendances'])->name('attendance.data');
        Route::post('/attendance/management', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::put('/attendance/management/{attendance}', [AttendanceController::class, 'update'])->name('attendance.update');
        Route::delete('/attendance/management/{attendance}', [AttendanceController::class, 'destroy'])->name('attendance.destroy');

        Route::get('/attendance/analytics', [AttendanceController::class, 'analytics'])->name('attendance.analytics');
        Route::get('/attendance/analytics/data', [AttendanceController::class, 'getAnalyticsData'])->name('attendance.analytics.data');

        Route::get('/attendance/reports', [AttendanceController::class, 'reports'])->name('attendance.reports');
        Route::get('/attendance/reports/data', [AttendanceController::class, 'getReportsData'])->name('attendance.reports.data');

        // Leaderboard Module
        Route::prefix('leaderboard')->group(function () {
            Route::get('/', function() { return redirect()->route('leaderboard.organization'); });
            
            Route::get('/individual', [\App\Http\Controllers\LeaderboardController::class, 'individual'])->name('leaderboard.individual');
            Route::get('/individual/data', [\App\Http\Controllers\LeaderboardController::class, 'getIndividualData'])->name('leaderboard.individual.data');
            Route::get('/individual/metrics/{employee}', [\App\Http\Controllers\LeaderboardController::class, 'getEmployeeMetrics'])->name('leaderboard.individual.metrics');
            
            Route::get('/team', [\App\Http\Controllers\LeaderboardController::class, 'team'])->name('leaderboard.team');
            Route::get('/team/data', [\App\Http\Controllers\LeaderboardController::class, 'getTeamData'])->name('leaderboard.team.data');
            Route::get('/team/{team_name}', [\App\Http\Controllers\LeaderboardController::class, 'teamDetails'])->name('leaderboard.team.details');
            
            Route::get('/organization', [\App\Http\Controllers\LeaderboardController::class, 'organization'])->name('leaderboard.organization');
            Route::get('/organization/data', [\App\Http\Controllers\LeaderboardController::class, 'getOrganizationData'])->name('leaderboard.organization.data');
        });

        // AI Manager Agent
        Route::prefix('ai-agent')->group(function () {
            Route::get('/', [AiAgentController::class, 'index'])->name('ai-agent.index');
            Route::get('/sessions', [AiAgentController::class, 'getSessions'])->name('ai-agent.sessions');
            Route::get('/sessions/{id}', [AiAgentController::class, 'loadSession'])->name('ai-agent.session.load');
            Route::post('/message', [AiAgentController::class, 'sendMessage'])->name('ai-agent.message');
            Route::delete('/sessions/{id}', [AiAgentController::class, 'destroySession'])->name('ai-agent.session.destroy');
        });
    });
});