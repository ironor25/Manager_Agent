<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DeepakYadavSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create or get Employee
        $employeeId = DB::table('employees')->insertGetId([
            'name' => 'Deepak Yadav',
            'email' => 'rock.on0103@gmail.com',
            'team' => 'Engineering',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        echo "Created employee Deepak Yadav with ID: {$employeeId}\n";

        // 2. Create Attendance records (last 10 days, excluding weekends)
        $attendances = [];
        $date = Carbon::now()->subDays(12);

        for ($i = 0; $i < 10; $i++) {
            while ($date->isWeekend()) {
                $date->addDay();
            }

            $loginTime = $date->copy()->hour(9)->minute(rand(0, 15))->second(rand(0, 59));
            $logoutTime = $loginTime->copy()->addHours(8)->addMinutes(rand(0, 30));

            $attendances[] = [
                'employee_id' => $employeeId,
                'login_time' => $loginTime->format('Y-m-d H:i:s'),
                'logout_time' => $logoutTime->format('Y-m-d H:i:s'),
                'leave_flag' => false,
                'late_flag' => $loginTime->minute > 10,
                'created_at' => $loginTime->format('Y-m-d H:i:s'),
                'updated_at' => $loginTime->format('Y-m-d H:i:s'),
            ];

            $date->addDay();
        }

        DB::table('attendances')->insert($attendances);
        echo "Seeded 10 attendance records.\n";

        // 3. Create Tasks
        $tasks = [
            [
                'employee_id' => $employeeId,
                'title' => 'Setup Laravel Webhook endpoint for GitLab',
                'description' => 'Implement GitLab webhook controller and service to parse incoming git commits.',
                'status' => 'completed',
                'priority' => 'high',
                'deadline_at' => Carbon::now()->subDays(2)->format('Y-m-d H:i:s'),
                'completed_at' => Carbon::now()->subDays(3)->format('Y-m-d H:i:s'),
                'estimated_hours' => 8.0,
                'actual_hours' => 7.5,
                'delay_hours' => null,
                'created_at' => Carbon::now()->subDays(5)->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->subDays(3)->format('Y-m-d H:i:s'),
            ],
            [
                'employee_id' => $employeeId,
                'title' => 'Secure webhook with GitLab Token',
                'description' => 'Write middleware to validate X-Gitlab-Token from incoming webhook headers.',
                'status' => 'completed',
                'priority' => 'normal',
                'deadline_at' => Carbon::now()->subDay()->format('Y-m-d H:i:s'),
                'completed_at' => Carbon::now()->subDays(1)->format('Y-m-d H:i:s'),
                'estimated_hours' => 4.0,
                'actual_hours' => 3.5,
                'delay_hours' => null,
                'created_at' => Carbon::now()->subDays(4)->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->subDays(1)->format('Y-m-d H:i:s'),
            ],
            [
                'employee_id' => $employeeId,
                'title' => 'Integrate webhook with dashboard and reports',
                'description' => 'Link GitLab commits with database employee matching and update reports logic.',
                'status' => 'in_progress',
                'priority' => 'high',
                'deadline_at' => Carbon::now()->addDays(3)->format('Y-m-d H:i:s'),
                'completed_at' => null,
                'estimated_hours' => 12.0,
                'actual_hours' => null,
                'delay_hours' => null,
                'created_at' => Carbon::now()->subDays(2)->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                'employee_id' => $employeeId,
                'title' => 'Document GitLab webhook integration',
                'description' => 'Create a guide for setting up webhook credentials and URLs in GitLab.',
                'status' => 'pending',
                'priority' => 'low',
                'deadline_at' => Carbon::now()->addDays(5)->format('Y-m-d H:i:s'),
                'completed_at' => null,
                'estimated_hours' => 3.0,
                'actual_hours' => null,
                'delay_hours' => null,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ],
        ];

        DB::table('tasks')->insert($tasks);
        echo "Seeded 4 tasks.\n";

        // 4. Create Meeting Notes
        $meetings = [
            [
                'employee_id' => $employeeId,
                'notes_text' => 'Deepak discussed progress on GitLab webhook integration. The base setup and database schema updates are completed. Envisioned a quick test using ngrok.',
                'meeting_date' => Carbon::now()->subDays(4)->hour(11)->minute(00)->format('Y-m-d H:i:s'),
                'created_at' => Carbon::now()->subDays(4)->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->subDays(4)->format('Y-m-d H:i:s'),
            ],
            [
                'employee_id' => $employeeId,
                'notes_text' => 'Deepak Yadav reported that ngrok request testing is in progress, and the security middleware validation is working as expected.',
                'meeting_date' => Carbon::now()->subDays(1)->hour(10)->minute(30)->format('Y-m-d H:i:s'),
                'created_at' => Carbon::now()->subDays(1)->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->subDays(1)->format('Y-m-d H:i:s'),
            ]
        ];

        DB::table('meeting_notes')->insert($meetings);
        echo "Seeded 2 meeting notes.\n";

        // 5. Create a Performance Report
        DB::table('performance_reports')->insert([
            'employee_id' => $employeeId,
            'leadership_score' => 85,
            'summary' => 'Deepak Yadav is demonstrating strong ownership of backend integrations. His technical skills in setting up webhooks and middleware integrations are excellent.',
            'strengths' => json_encode(['Backend Architecture', 'Security Middleware', 'Fast Deliveries']),
            'weaknesses' => json_encode(['Needs to document architectural patterns more']),
            'recommendations' => json_encode(['Mentorship of junior developers on security practices', 'Write technical integration docs']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "Seeded initial performance report.\n";
    }
}
