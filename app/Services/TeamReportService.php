<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\TeamReport;
use App\Services\AgentService;

class TeamReportService
{
    protected TeamAnalyticsService $analyticsService;

    public function __construct(TeamAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Generate Team Report using AI
     */
    public function generateTeamReport(string $team)
    {
        // Check if report exists and is less than a week old
        $existingReport = TeamReport::where('team_name', $team)
            ->where('updated_at', '>=', now()->subDays(7))
            ->first();

        if ($existingReport) {
            return $existingReport;
        }

        // 1. Get all employees in team
        $employees = Employee::where('team', $team)->get();
        $employeeCount = $employees->count();

        // 3. Aggregate team metrics (calculate)
        $efficiencyScore = $this->analyticsService->calculateTeamEfficiency($team);
        $attendanceScore = $this->analyticsService->calculateAverageAttendance($team);
        $taskCompletionScore = $this->analyticsService->calculateAverageTaskCompletion($team);
        
        $topPerformer = $this->analyticsService->getTopPerformer($team);
        $topPerformerName = $topPerformer ? $topPerformer->name : 'N/A';
        $topPerformerId = $topPerformer ? $topPerformer->id : null;

        // 5. Send aggregated data to AI
        $inputData = [
            'team_name' => $team,
            'team_efficiency' => $efficiencyScore,
            'attendance_score' => $attendanceScore,
            'task_completion_score' => $taskCompletionScore,
            'git_contribution_score' => $this->analyticsService->calculateAverageGitContribution($team),
            'employee_count' => $employeeCount,
            'top_performer' => $topPerformerName,
            'recent_commits_sample' => $this->analyticsService->getTeamRecentCommits($team),
            'recent_meeting_notes_sample' => $this->analyticsService->getTeamMeetingNotes($team)
        ];

        // Format prompt for AgentService
        $prompt = "You are an expert HR Manager and Team Analyst. Analyze the following team performance metrics (including recent qualitative meeting notes and recent quantitative git commit messages) and provide a comprehensive team report.\n\n";
        $prompt .= "Team Metrics:\n";
        $prompt .= json_encode($inputData, JSON_PRETTY_PRINT) . "\n\n";
        $prompt .= "Provide the output in ONLY valid JSON format without any markdown wrappers or text outside the JSON.\n";
        $prompt .= "Format exactly like this:\n";
        $prompt .= "{\n  \"summary\": \"Overall summary of team performance\",\n  \"strengths\": [\"Strength 1\", \"Strength 2\"],\n  \"weaknesses\": [\"Area to improve 1\", \"Area to improve 2\"],\n  \"recommendations\": [\"Recommendation 1\", \"Recommendation 2\"]\n}";

        $aiResponseJson = AgentService::generateSummary($prompt);
        
        // Clean markdown backticks if LLM added them
        $cleanJson = preg_replace('/```json\s*(.*?)\s*```/s', '$1', $aiResponseJson);
        $cleanJson = preg_replace('/```\s*(.*?)\s*```/s', '$1', $cleanJson);
        
        $aiResponse = json_decode(trim($cleanJson), true);
        
        if (json_last_error() !== JSON_ERROR_NONE || !isset($aiResponse['summary'])) {
            // Fallback if AI fails completely
            $aiResponse = [
                'summary' => 'Team performance data has been recorded successfully.',
                'strengths' => ['Strong metrics reported'],
                'weaknesses' => ['Need more time for comprehensive analysis'],
                'recommendations' => ['Continue monitoring KPI trends'],
            ];
        }

        // Save to DB
        $report = TeamReport::updateOrCreate(
            ['team_name' => $team],
            [
                'efficiency_score' => $efficiencyScore,
                'top_performer_id' => $topPerformerId,
                'top_performer_name' => $topPerformerName,
                'summary' => $aiResponse['summary'] ?? '',
                'strengths' => $aiResponse['strengths'] ?? [],
                'weaknesses' => $aiResponse['weaknesses'] ?? [],
                'recommendations' => $aiResponse['recommendations'] ?? [],
            ]
        );

        return $report;
    }
}
