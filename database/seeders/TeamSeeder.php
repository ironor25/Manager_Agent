<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $teams = [
            'Engineering', 'Marketing', 'Sales', 'HR', 'Support', 
            'Product', 'Design', 'Finance', 'Operations', 'Legal'
        ];

        $teamData = [];
        foreach ($teams as $team) {
            $teamData[] = [
                'name' => $team,
                'description' => "This is the {$team} team.",
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('teams')->insert($teamData);
    }
}
