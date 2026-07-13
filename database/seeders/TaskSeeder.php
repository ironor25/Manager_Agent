<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $employeeIds = DB::table('employees')->pluck('id')->toArray();
        $projectIds = DB::table('projects')->pluck('id')->toArray();

        $totalTasks = app()->runningUnitTests() ? 30 : 180000;
        $chunkSize = app()->runningUnitTests() ? 10 : 2000;

        echo "Seeding {$totalTasks} Tasks...\n";

        for ($i = 0; $i < $totalTasks; $i += $chunkSize) {
            $tasks = [];
            for ($j = 0; $j < $chunkSize; $j++) {
                $status = $faker->randomElement(['pending', 'in_progress', 'completed', 'late_completed']);
                $createdAt = $faker->dateTimeBetween('-1 year', 'now');
                
                $completedAt = null;
                $delayHours = null;
                
                if (in_array($status, ['completed', 'late_completed'])) {
                    $completedAt = $faker->dateTimeBetween($createdAt, 'now');
                    if ($status === 'late_completed') {
                        $delayHours = $faker->randomFloat(1, 1, 48);
                    }
                }

                $tasks[] = [
                    'employee_id' => $faker->randomElement($employeeIds),
                    'title' => $faker->sentence(4),
                    'description' => $faker->paragraph(1),
                    'status' => $status,
                    'priority' => $faker->randomElement(['low', 'normal', 'high']),
                    'deadline_at' => $faker->dateTimeBetween($createdAt, '+1 month')->format('Y-m-d H:i:s'),
                    'completed_at' => $completedAt ? $completedAt->format('Y-m-d H:i:s') : null,
                    'estimated_hours' => $faker->randomFloat(1, 1, 40),
                    'actual_hours' => $completedAt ? $faker->randomFloat(1, 1, 50) : null,
                    'delay_hours' => $delayHours,
                    'project_id' => !empty($projectIds) ? $faker->randomElement($projectIds) : null,
                    'created_at' => $createdAt->format('Y-m-d H:i:s'),
                    'updated_at' => $createdAt->format('Y-m-d H:i:s'),
                ];
            }
            DB::table('tasks')->insert($tasks);
        }
    }
}
