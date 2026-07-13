<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\GithubCommit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GitlabWebhookService
{
    public function processPushEvent(array $payload)
    {
        $commits = $payload['commits'] ?? [];
        $repoName = $payload['project']['name'] ?? 'unknown_repo';

        Log::info("GitLab Webhook: processing push event with " . count($commits) . " commits.");

        foreach ($commits as $commitData) {
            $authorEmail = $commitData['author']['email'] ?? null;
            Log::info("GitLab Webhook: Processing commit {$commitData['id']} by email: {$authorEmail}");

            if (!$authorEmail) {
                Log::warning("GitLab Webhook: Commit {$commitData['id']} has no author email.");
                continue;
            }

            $employee = Employee::where('email', $authorEmail)->first();

            if ($employee) {
                Log::info("GitLab Webhook: Found employee ID {$employee->id} for email {$authorEmail}");
                
                $projectId = $payload['project']['id'] ?? null;
                
                $commit = GithubCommit::firstOrCreate(
                    ['commit_hash' => $commitData['id']],
                    [
                        'employee_id' => $employee->id,
                        'repo_name' => $repoName,
                        'project_id' => $projectId,
                        'commit_message' => $commitData['message'] ?? '',
                        'commit_date' => Carbon::parse($commitData['timestamp'] ?? now()),
                        'source' => 'gitlab',
                        'event_type' => 'push',
                        'url' => $commitData['url'] ?? null,
                    ]
                );

                Log::info("GitLab Webhook: Saved/Retrieved commit ID: " . $commit->id);
            } else {
                Log::info("GitLab Webhook: No employee found in database for email {$authorEmail}");
            }
        }
    }

    public function processMergeRequestEvent(array $payload)
    {
        // For now, we are just noting merge request events. If we want to store them, 
        // we can store them in github_commits with event_type = 'merge_request' 
        // but the current Analytics mainly look at commits.
        $action = $payload['object_attributes']['action'] ?? '';
        $authorEmail = $payload['user']['email'] ?? null;
        
        if ($authorEmail && in_array($action, ['open', 'merge', 'close'])) {
            $employee = Employee::where('email', $authorEmail)->first();
            if ($employee) {
                // If the user later requires us to record MRs in the same table, we can do:
                // GithubCommit::create([
                //     'employee_id' => $employee->id,
                //     'repo_name' => $payload['project']['name'] ?? 'unknown',
                //     'commit_hash' => 'mr_' . $payload['object_attributes']['id'] . '_' . $action,
                //     'commit_message' => "MR $action: " . ($payload['object_attributes']['title'] ?? ''),
                //     'commit_date' => now(),
                //     'source' => 'gitlab',
                //     'event_type' => 'merge_request',
                // ]);
                Log::info("GitLab Merge Request {$action} by {$authorEmail} processed.");
            }
        }
    }
}
