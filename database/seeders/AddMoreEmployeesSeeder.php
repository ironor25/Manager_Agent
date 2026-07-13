<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\Employee;
use App\Models\Task;
use App\Models\Attendance;
use App\Models\GithubCommit;
use App\Models\MeetingNote;

class AddMoreEmployeesSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $teams = ['Engineering', 'Marketing', 'Sales', 'HR', 'Support'];

        for ($i = 0; $i < 10; $i++) {
            // Create Employee
            $employee = Employee::create([
                'name' => $faker->name(),
                'email' => $faker->unique()->safeEmail(),
                'team' => $faker->randomElement($teams),
            ]);

            // Add 10 Tasks
            for ($t = 0; $t < 10; $t++) {
                Task::create([
                    'employee_id' => $employee->id,
                    'title' => $faker->sentence(3),
                    'description' => $faker->paragraph(),
                    'status' => $faker->randomElement(['pending', 'in_progress', 'completed']),
                    'priority' => $faker->randomElement(['low', 'normal', 'high']),
                    'deadline_at' => $faker->dateTimeBetween('-1 month', '+1 month'),
                ]);
            }

            // Add 15 days of attendance
            for ($d = 1; $d <= 15; $d++) {
                Attendance::create([
                    'employee_id' => $employee->id,
                    'login_time' => now()->subDays($d)->setTime(rand(8, 10), rand(0, 59)),
                    'logout_time' => now()->subDays($d)->setTime(rand(17, 19), rand(0, 59)),
                    'late_flag' => rand(0, 10) > 8 ? 1 : 0,
                    'leave_flag' => rand(0, 10) > 9 ? 1 : 0,
                ]);
            }

            // Add 5-20 GitHub Commits
            $commitsCount = rand(5, 20);
            for ($c = 0; $c < $commitsCount; $c++) {
                $repo = $faker->word() . '-repo';
                $hash = $faker->sha1();
                GithubCommit::create([
                    'employee_id' => $employee->id,
                    'repo_name' => $repo,
                    'commit_hash' => $hash,
                    'commit_message' => $faker->sentence(),
                    'commit_date' => $faker->dateTimeBetween('-1 month', 'now'),
                    'url' => "https://github.com/company/{$repo}/commit/{$hash}",
                ]);
            }

            // Add 1-4 Meeting Notes
            $meetingsCount = rand(1, 4);
            for ($m = 0; $m < $meetingsCount; $m++) {
                MeetingNote::create([
                    'employee_id' => $employee->id,
                    'meeting_date' => $faker->dateTimeBetween('-1 month', 'now'),
                    'notes_text' => $faker->paragraphs(2, true),
                ]);
            }
        }
    }
}
