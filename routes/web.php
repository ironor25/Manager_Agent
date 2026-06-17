<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PerformanceReportController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TeamDashboardController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\DeveloperToolsController;
use App\Http\Controllers\ApiKeyController;

// Public Routes
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'getDashboardData'])->name('dashboard.data');

    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/data', [EmployeeController::class, 'getEmployees'])->name('employees.data');
    Route::get('/employees/{employee}/details', [EmployeeController::class, 'details'])->name('employees.details');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

    Route::post('/employees/generate-all-reports', [PerformanceReportController::class, 'generateAllReports'])->name('employees.generate_all_reports');
    Route::get('/employee/{id}/report', [PerformanceReportController::class, 'generateReport'])->name('employee.report');   

    Route::get('/teams', [TeamDashboardController::class, 'index'])->name('teams.dashboard');
    Route::get('/teams/chart-data', [TeamDashboardController::class, 'getChartData'])->name('teams.chart');
    Route::get('/teams/leaderboard', [TeamDashboardController::class, 'leaderboard'])->name('teams.leaderboard');
    Route::get('/teams/{team}/report', [TeamDashboardController::class, 'generateReport'])->name('teams.generate_report');
    Route::post('/teams/assign-task', [TeamDashboardController::class, 'assignTask'])->name('teams.assign_task');
    Route::post('/teams', [TeamDashboardController::class, 'storeTeam'])->name('teams.store');
    Route::put('/teams/{team}', [TeamDashboardController::class, 'updateTeam'])->name('teams.update');
    Route::delete('/teams/{team}', [TeamDashboardController::class, 'destroyTeam'])->name('teams.destroy');
    Route::post('/teams/{team}/members', [TeamDashboardController::class, 'addMember'])->name('teams.members.add');
    Route::delete('/teams/{team}/members/{employee}', [TeamDashboardController::class, 'removeMember'])->name('teams.members.remove');
    Route::get('/teams/{team}/details', [TeamDashboardController::class, 'show'])->name('teams.show');

    // Tasks
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    // Commits & Meetings
    Route::get('/commits', [\App\Http\Controllers\GithubCommitController::class, 'index'])->name('commits.index');
    Route::get('/meetings', [\App\Http\Controllers\MeetingNoteController::class, 'index'])->name('meetings.index');

    // Developer Tools
    Route::get('/developer-tools', [DeveloperToolsController::class, 'index'])->name('developer-tools.index');
    Route::post('/developer-tools/api-keys', [ApiKeyController::class, 'store'])->name('api-keys.store');
    Route::delete('/developer-tools/api-keys/{id}', [ApiKeyController::class, 'destroy'])->name('api-keys.destroy');
    Route::post('/developer-tools/api-keys/{id}/toggle', [ApiKeyController::class, 'toggleStatus'])->name('api-keys.toggle');

    // Profile Management
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile.show');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
});