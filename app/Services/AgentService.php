<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AgentService
{

    public static function fixedPrompt(array $analytics): string
    {
        $meetingNotes = implode("\n        - ", $analytics['meeting_notes']);
        $recentCommits = implode("\n        - ", $analytics['recent_commits'] ?? []);

        return "
    You are an employee performance analytics AI.

        Analyze these metrics.

        Employee: {$analytics['employee_name']}

        Metrics:
        - Task Completion Rate: {$analytics['task_completion_rate']}%
        - On Time Delivery Rate: {$analytics['on_time_completion_rate']}%
        - Attendance Score: {$analytics['attendance_score']}
        - Github Commits: {$analytics['git_commit_count']}
        - Github Contribution Score: {$analytics['git_contribution_score']}
        - Final Leadership Score: {$analytics['final_leadership_score']}

        Meeting Notes:
        - {$meetingNotes}

        Recent Commits (Sample):
        - {$recentCommits}

        Based on both the quantitative metrics, qualitative meeting notes, and the commit messages, return ONLY valid JSON:

        {
          \"summary\": \"\",
          \"strengths\": [],
          \"weaknesses\": [],
          \"recommendations\": [],
          \"leadership_score\": 0
        }

        ";
    }
    
   public static function generateSummary(string $prompt): string
    {
        $response = Http::timeout(120)->post(
            'http://localhost:11434/api/generate',
            [
                'model' => env('OLLAMA_MODEL', 'gemma3'),
                'prompt' => $prompt,
                'stream' => false
            ]
        );

        return $response->json()['response'] ?? '';
    }

    public static function chat(array $messages, array $tools = [], ?string $format = null)
    {
        $payload = [
            'model' => env('OLLAMA_CHAT_MODEL', 'gemma3:latest'),
            'messages' => $messages,
            'stream' => false,
        ];

        if (!empty($tools)) {
            $payload['tools'] = $tools;
        }

        if ($format) {
            $payload['format'] = $format;
        }

        $response = Http::timeout(120)->post(
            'http://localhost:11434/api/chat',
            $payload
        );

        return $response->json();
    }
}