<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $projects = [
            [
                'name' => 'Project Alpha',
                'description' => 'The core system overhaul and codebase modularization initiative.',
            ],
            [
                'name' => 'Project Phoenix',
                'description' => 'Rebuilding the legacy customer onboarding pipelines and user experience.',
            ],
            [
                'name' => 'Project Apollo',
                'description' => 'Cloud migration, optimization, and zero-downtime scaling efforts.',
            ],
            [
                'name' => 'Project Titan',
                'description' => 'Enterprise security audits, compliance reporting, and API key restrictions.',
            ],
            [
                'name' => 'Project Genesis',
                'description' => 'AI-assisted automation modules and advanced natural language processing tools.',
            ]
        ];

        // Retrieve a subset of employee IDs to assign
        $employeeIds = DB::table('employees')->limit(100)->pluck('id')->toArray();

        if (empty($employeeIds)) {
            echo "No employees found. Please seed employees first to associate project members.\n";
            return;
        }

        echo "Seeding " . count($projects) . " Projects...\n";

        foreach ($projects as $proj) {
            // Check if project already exists
            $existing = DB::table('projects')->where('name', $proj['name'])->first();
            if ($existing) {
                continue;
            }

            // Get some unique repo names from Github commits
            $repoNames = DB::table('github_commits')->select('repo_name')->distinct()->limit(10)->pluck('repo_name')->toArray();
            
            $projectId = DB::table('projects')->insertGetId([
                'name' => $proj['name'],
                'description' => $proj['description'],
                'repo_name' => !empty($repoNames) ? $faker->randomElement($repoNames) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Assign 5 to 15 random employees to this project
            $numMembers = rand(5, 15);
            $chosenIds = $faker->randomElements($employeeIds, min(count($employeeIds), $numMembers));

            $associations = [];
            foreach ($chosenIds as $empId) {
                $associations[] = [
                    'project_id' => $projectId,
                    'employee_id' => $empId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('project_employee')->insert($associations);
        }
    }
}
