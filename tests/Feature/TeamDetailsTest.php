<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Team;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TeamDetailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_details_page_loads_successfully()
    {
        $this->seed();

        $team = Team::first();
        $this->assertNotNull($team);

        // We need to login as a user
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->get("/admin/teams/{$team->name}/details");

        $response->assertStatus(200);
        $response->assertViewIs('team-details');
        $response->assertViewHas('team');
        $response->assertViewHas('totalMembersCount');
    }

    public function test_team_members_data_endpoint_returns_datatable_json()
    {
        $this->seed();

        $team = Team::first();
        $this->assertNotNull($team);

        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson("/admin/teams/{$team->name}/members/data");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data',
        ]);
    }
}
