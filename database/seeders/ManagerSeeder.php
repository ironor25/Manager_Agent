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
use App\Models\Performance_report;
use App\Models\EmployeePerformanceMetric;
use App\Services\PerformanceAnalyticsService;
use Carbon\Carbon;

class ManagerSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $teams = DB::table('teams')->pluck('name')->toArray();
        if (empty($teams)) {
            $teams = ['Engineering', 'Marketing', 'Product', 'Sales'];
        }

        $managerNames = [
            'Manager Alice',
            'Manager Bob',
            'Manager Charlie',
            'Manager David',
            'Manager Emma',
            'Manager Frank',
            'Manager Grace',
            'Manager Henry',
            'Manager Ivy',
            'Manager Jack'
        ];

        echo "Seeding 10 Managers and their associated metrics...\n";

        foreach ($managerNames as $name) {
            // 1. Create Employee
            $email = strtolower(str_replace(' ', '.', $name)) . '@company.com';
            
            // Check if employee already exists
            $existing = Employee::where('email', $email)->first();
            if ($existing) {
                continue;
            }

            $employee = Employee::create([
                'name' => $name,
                'email' => $email,
                'team' => $faker->randomElement($teams),
                'designation' => 'Manager',
                'status' => 'active',
                'join_date' => Carbon::now()->subMonths(12)->toDateString(),
            ]);

            $empId = $employee->id;

            // 2. Seed Attendances (last 30 days)
            $attendances = [];
            for ($d = 29; $d >= 0; $d--) {
                $date = Carbon::now()->subDays($d);
                if ($date->isWeekend()) {
                    continue; // Skip weekends for cleaner attendance
                }

                $loginTime = $date->copy()->setTime(9, rand(0, 45));
                $logoutTime = $loginTime->copy()->addHours(8)->addMinutes(rand(0, 60));
                
                $late = $loginTime->hour == 9 && $loginTime->minute > 15;
                $leave = $faker->boolean(5); // 5% chance of leave

                $attendances[] = [
                    'employee_id' => $empId,
                    'date' => $date->toDateString(),
                    'login_time' => $leave ? null : $loginTime->toDateTimeString(),
                    'logout_time' => $leave ? null : $logoutTime->toDateTimeString(),
                    'leave_flag' => $leave,
                    'late_flag' => $leave ? false : $late,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('attendances')->insert($attendances);

            // 3. Seed Tasks
            $tasks = [];
            for ($t = 0; $t < 15; $t++) {
                $status = $faker->randomElement(['completed', 'completed', 'completed', 'late_completed', 'in_progress', 'pending']);
                $createdAt = Carbon::now()->subDays(rand(5, 30));
                $deadline = $createdAt->copy()->addDays(rand(1, 10));
                
                $completedAt = null;
                $delayHours = null;
                if (in_array($status, ['completed', 'late_completed'])) {
                    if ($status === 'late_completed') {
                        $completedAt = $deadline->copy()->addHours(rand(1, 48));
                        $delayHours = $completedAt->diffInHours($deadline);
                    } else {
                        $completedAt = $faker->dateTimeBetween($createdAt, $deadline);
                    }
                }

                $tasks[] = [
                    'employee_id' => $empId,
                    'title' => $faker->sentence(4),
                    'description' => $faker->paragraph(1),
                    'status' => $status,
                    'priority' => $faker->randomElement(['low', 'normal', 'high']),
                    'deadline_at' => $deadline->toDateTimeString(),
                    'completed_at' => $completedAt ? Carbon::parse($completedAt)->toDateTimeString() : null,
                    'estimated_hours' => $faker->randomFloat(1, 2, 20),
                    'actual_hours' => $completedAt ? $faker->randomFloat(1, 2, 25) : null,
                    'delay_hours' => $delayHours,
                    'created_at' => $createdAt->toDateTimeString(),
                    'updated_at' => now(),
                ];
            }
            DB::table('tasks')->insert($tasks);

            // 4. Seed Github Commits (last 30 days)
            $commits = [];
            for ($c = 0; $c < 20; $c++) {
                $date = Carbon::now()->subDays(rand(1, 30))->setTime(rand(9, 18), rand(0, 59));
                $repo = 'company/management-dashboard';
                $hash = $faker->sha1;
                $commits[] = [
                    'employee_id' => $empId,
                    'repo_name' => $repo,
                    'commit_hash' => $hash,
                    'commit_message' => $faker->randomElement([
                        'Refactored user dashboard components',
                        'Updated styling tokens and color schemes',
                        'Added unit tests for team performance analytics',
                        'Optimized query execution for leaderboard tables',
                        'Fixed sorting bugs in Datatables handlers',
                        'Aligned overall scores calculation parameters'
                    ]) . ' (Commit #' . ($c + 1) . ')',
                    'commit_date' => $date->toDateTimeString(),
                    'url' => "https://github.com/{$repo}/commit/{$hash}",
                    'created_at' => $date->toDateTimeString(),
                    'updated_at' => $date->toDateTimeString(),
                ];
            }
            DB::table('github_commits')->insert($commits);

            // 5. Seed Meeting Notes
            $meetingNotes = [];
            for ($m = 0; $m < 3; $m++) {
                $date = Carbon::now()->subDays(rand(1, 30));
                $meetingNotes[] = [
                    'employee_id' => $empId,
                    'meeting_date' => $date->toDateString(),
                    'notes_text' => $faker->randomElement([
                        'Discussed timeline and budget allocations for upcoming project iterations.',
                        'Reviewed team metrics and resolved blockers related to backend dependencies.',
                        'Aligned sprint priorities and assigned main tasks to team engineers.',
                        'Conducted weekly sync on performance indicators and git commit progression.'
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('meeting_notes')->insert($meetingNotes);

            // 6. Calculate & Store Employee Performance Metric (all-time)
            try {
                $score = PerformanceAnalyticsService::calculateEmployeeScore($employee);

                EmployeePerformanceMetric::updateOrCreate(
                    ['employee_id' => $empId],
                    [
                        'task_completion_rate' => $score['task_completion_rate'],
                        'on_time_completion_rate' => $score['on_time_completion_rate'],
                        'attendance_score' => $score['attendance_score'],
                        'git_contribution_score' => $score['git_contribution_score'],
                        'final_score' => $score['final_leadership_score'],
                        'total_tasks' => $score['total_tasks'],
                        'completed_tasks' => $score['completed_tasks'],
                        'late_tasks' => $score['late_tasks'],
                        'total_attendance_records' => $score['total_attendance_records'],
                        'git_commit_count' => $score['git_commit_count'],
                        'recent_commits' => $score['recent_commits'],
                        'commit_chart_data' => $score['commit_chart_data'],
                    ]
                );

                // 7. Seed Performance Report with their computed leadership score
                Performance_report::create([
                    'employee_id' => $empId,
                    'summary' => "Manager evaluation summary for {$name}. Demonstrated outstanding leadership and organization skills.",
                    'leadership_score' => $score['final_leadership_score'],
                    'strengths' => ['Leadership', 'Communication', 'Strategic planning'],
                    'weaknesses' => ['Time management'],
                    'recommendations' => ['Continue to refine sprint planning', 'Promote agile values in team meeting notes'],
                ]);
            } catch (\Exception $e) {
                echo "Error calculating metrics for {$name}: " . $e->getMessage() . "\n";
            }
        }
    }
}
