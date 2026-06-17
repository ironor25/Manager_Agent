<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Performance_report;
use Illuminate\Support\Collection;

class TeamAnalyticsService
{
    /**
     * Get all distinct teams (departments).
     */
    public function getAllTeams(): array
    {
        return Employee::whereNotNull('team')
            ->distinct()
            ->pluck('team')
            ->toArray();
    }

    /**
     * Calculate Team Efficiency: Average leadership score of all employees in that team.
     */
    public function calculateTeamEfficiency(string $team): int
    {
        $employeeIds = Employee::where('team', $team)->pluck('id');
        
        if ($employeeIds->isEmpty()) {
            return 0;
        }

        $avgScore = Performance_report::whereIn('employee_id', $employeeIds)->avg('leadership_score');
        return (int) round($avgScore ?? 0);
    }

    /**
     * Calculate Average Attendance Score for the team.
     */
    public function calculateAverageAttendance(string $team): int
    {
        $employees = Employee::where('team', $team)->get();
        
        if ($employees->isEmpty()) {
            return 0;
        }

        $totalAttendanceScore = 0;
        foreach ($employees as $employee) {
            $scoreData = PerformanceAnalyticsService::calculateEmployeeScore($employee->id);
            $totalAttendanceScore += $scoreData['attendance_score'] ?? 0;
        }

        return (int) round($totalAttendanceScore / $employees->count());
    }

    /**
     * Calculate Average Task Completion Score for the team.
     */
    public function calculateAverageTaskCompletion(string $team): int
    {
        $employees = Employee::where('team', $team)->get();
        
        if ($employees->isEmpty()) {
            return 0;
        }

        $totalCompletionScore = 0;
        foreach ($employees as $employee) {
            $scoreData = PerformanceAnalyticsService::calculateEmployeeScore($employee->id);
            $totalCompletionScore += $scoreData['task_completion_rate'] ?? 0;
        }

        return (int) round($totalCompletionScore / $employees->count());
    }

    /**
     * Get the top performer in the team (employee with highest leadership_score).
     */
    public function getTopPerformer(string $team): ?Employee
    {
        $employeeIds = Employee::where('team', $team)->pluck('id');
        
        if ($employeeIds->isEmpty()) {
            return null;
        }

        $topReport = Performance_report::whereIn('employee_id', $employeeIds)
            ->orderByDesc('leadership_score')
            ->first();

        if ($topReport) {
            return Employee::find($topReport->employee_id);
        }

        return null;
    }

    /**
     * Get the Team Leaderboard sorted by efficiency score.
     */
    public function getLeaderboard(): array
    {
        $teams = $this->getAllTeams();
        $leaderboard = [];

        foreach ($teams as $team) {
            $topPerformer = $this->getTopPerformer($team);
            $leaderboard[] = [
                'team_name' => $team,
                'efficiency_score' => $this->calculateTeamEfficiency($team),
                'top_performer_name' => $topPerformer ? $topPerformer->name : 'N/A',
                'top_performer_id' => $topPerformer ? $topPerformer->id : null,
            ];
        }

        // Sort descending by efficiency score
        usort($leaderboard, function ($a, $b) {
            return $b['efficiency_score'] <=> $a['efficiency_score'];
        });

        return $leaderboard;
    }

    public function calculateAverageGitContribution(string $team): int
    {
        $employees = Employee::where('team', $team)->get();
        if ($employees->isEmpty()) return 0;

        $totalGitScore = 0;
        foreach ($employees as $employee) {
            $scoreData = PerformanceAnalyticsService::calculateEmployeeScore($employee->id);
            $totalGitScore += $scoreData['git_contribution_score'] ?? 0;
        }

        return (int) round($totalGitScore / $employees->count());
    }

    public function getTeamMeetingNotes(string $team): array
    {
        $employeeIds = Employee::where('team', $team)->pluck('id');
        return \App\Models\MeetingNote::whereIn('employee_id', $employeeIds)
            ->latest('meeting_date')
            ->take(15)
            ->pluck('notes_text')
            ->toArray();
    }

    public function getTeamRecentCommits(string $team): array
    {
        $employeeIds = Employee::where('team', $team)->pluck('id');
        return \App\Models\GithubCommit::whereIn('employee_id', $employeeIds)
            ->latest('commit_date')
            ->take(15)
            ->pluck('commit_message')
            ->toArray();
    }
}
