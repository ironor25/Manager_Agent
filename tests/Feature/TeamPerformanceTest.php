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

        $response = $this->get('/teams/performance');

        $response->assertStatus(200);
        $response->assertViewIs('team-performance');
        $response->assertViewHas('teamReports');
        $response->assertSee('Compare Team Performances');
        $response->assertSee('Team Leaderboard');
    }
}
