<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GitlabWebhookController extends Controller
{
    protected $webhookService;

    public function __construct(\App\Services\GitlabWebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    public function handle(Request $request)
    {
        $eventType = $request->header('X-Gitlab-Event');
        $payload = $request->all();

        \Illuminate\Support\Facades\Log::info("GitLab Webhook received event: {$eventType}", [
            'headers' => $request->headers->all(),
            'payload' => $payload
        ]);

        if ($eventType === 'Push Hook') {
            $this->webhookService->processPushEvent($payload);
        } elseif ($eventType === 'Merge Request Hook') {
            $this->webhookService->processMergeRequestEvent($payload);
        }

        return response()->json(['status' => 'success', 'message' => 'Webhook processed.']);
    }
}
