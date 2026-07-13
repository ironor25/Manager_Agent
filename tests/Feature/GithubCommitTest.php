<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\GithubCommit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

class GithubCommitTest extends TestCase
{
    use RefreshDatabase;

    public function test_diff_requires_authentication()
    {
        $this->seed();
        $commit = GithubCommit::first();
        $commit->update([
            'project_id' => '407',
            'commit_hash' => '29b7042b',
        ]);

        $response = $this->getJson("/admin/commits/{$commit->id}/diff");
        $response->assertStatus(401);
    }

    public function test_diff_returns_bad_request_when_missing_metadata()
    {
        $this->seed();
        $user = User::factory()->create();
        $commit = GithubCommit::first();
        $commit->update([
            'project_id' => null,
            'commit_hash' => null,
        ]);

        $response = $this->actingAs($user)->getJson("/admin/commits/{$commit->id}/diff");
        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'Missing project ID or commit hash to fetch diff.'
        ]);
    }

    public function test_diff_successfully_fetches_and_returns_diff_from_gitlab()
    {
        $this->seed();
        $user = User::factory()->create();
        $commit = GithubCommit::first();
        $commit->update([
            'project_id' => '407',
            'commit_hash' => '29b7042b',
        ]);

        $mockResponse = [
            [
                'diff' => '@@ -9,7 +9,7 @@ app.get("/health",...',
                'new_path' => 'server.js',
                'old_path' => 'server.js',
                'a_mode' => '100644',
                'b_mode' => '100644',
                'new_file' => false,
                'renamed_file' => false,
                'deleted_file' => false,
                'generated_file' => null
            ]
        ];

        Http::fake([
            '*/api/v4/projects/407/repository/commits/29b7042b/diff' => Http::response($mockResponse, 200)
        ]);

        $response = $this->actingAs($user)->getJson("/admin/commits/{$commit->id}/diff");
        
        $response->assertStatus(200);
        $response->assertJson($mockResponse);
    }

    public function test_diff_handles_gitlab_api_errors()
    {
        $this->seed();
        $user = User::factory()->create();
        $commit = GithubCommit::first();
        $commit->update([
            'project_id' => '407',
            'commit_hash' => 'invalid_hash',
        ]);

        Http::fake([
            '*/api/v4/projects/407/repository/commits/invalid_hash/diff' => Http::response('Not Found', 404)
        ]);

        $response = $this->actingAs($user)->getJson("/admin/commits/{$commit->id}/diff");
        
        $response->assertStatus(404);
        $response->assertJson([
            'error' => 'Failed to fetch diff from GitLab API. Status: 404'
        ]);
    }
}
