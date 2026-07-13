<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class GithubCommitSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $employeeIds = DB::table('employees')->pluck('id')->toArray();

        $repos = [];
        for ($i = 0; $i < 1000; $i++) {
            $repos[] = 'company/' . $faker->unique()->slug;
        }

        $totalCommits = app()->runningUnitTests() ? 30 : 180000;
        $chunkSize = app()->runningUnitTests() ? 10 : 2000;

        echo "Seeding {$totalCommits} Github Commits...\n";

        for ($i = 0; $i < $totalCommits; $i += $chunkSize) {
            $commits = [];
            for ($j = 0; $j < $chunkSize; $j++) {
                $date = $faker->dateTimeBetween('-6 months', 'now');
                $repo = $faker->randomElement($repos);
                $hash = $faker->sha1;
                $commits[] = [
                    'employee_id' => $faker->randomElement($employeeIds),
                    'repo_name' => $repo,
                    'commit_hash' => $hash,
                    'commit_message' => $faker->sentence(6),
                    'commit_date' => $date->format('Y-m-d H:i:s'),
                    'url' => "https://github.com/{$repo}/commit/{$hash}",
                    'created_at' => $date->format('Y-m-d H:i:s'),
                    'updated_at' => $date->format('Y-m-d H:i:s'),
                ];
            }
            DB::table('github_commits')->insert($commits);
        }
    }
}
