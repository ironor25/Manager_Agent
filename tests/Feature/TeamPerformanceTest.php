<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TeamPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_performance_page_returns_successful_response(): void
    {
        $this->seed();

        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/teams');

        $response->assertStatus(200);
        $response->assertViewIs('team-dashboard');
        $response->assertSee('Team Performance');
        $response->assertSee('Team Leaderboard');
    }
}
