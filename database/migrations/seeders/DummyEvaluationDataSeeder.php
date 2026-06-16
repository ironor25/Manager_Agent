<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DummyEvaluationDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = \App\Models\Employee::all();

        foreach ($employees as $employee) {
            // Give them between 5 and 50 commits
            \App\Models\GithubCommit::factory()
                ->count(rand(5, 50))
                ->create(['employee_id' => $employee->id]);

            // Give them between 1 and 4 meeting notes
            \App\Models\MeetingNote::factory()
                ->count(rand(1, 4))
                ->create(['employee_id' => $employee->id]);
        }
    }
}
