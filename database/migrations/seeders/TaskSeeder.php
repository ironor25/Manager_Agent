<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tasks Seeder

DB::table('tasks')->insert([

    [
        'employee_id' => 1,
        'title' => 'Build Login API',
        'description' => 'JWT login system',
        'status' => 'completed',
        'assigned_at' => '2026-06-01 09:00:00',
        'started_at' => '2026-06-01 09:30:00',
        'deadline_at' => '2026-06-02 18:00:00',
        'completed_at' => '2026-06-02 16:00:00',
        'estimated_hours' => 8,
        'actual_hours' => 7,
        'delay_hours' => 0,
        'priority' => 'high',
    ],

    [
        'employee_id' => 1,
        'title' => 'Fix Auth Bug',
        'description' => 'Resolve token issue',
        'status' => 'late_completed',
        'assigned_at' => '2026-06-03 09:00:00',
        'started_at' => '2026-06-03 10:00:00',
        'deadline_at' => '2026-06-04 18:00:00',
        'completed_at' => '2026-06-05 12:00:00',
        'estimated_hours' => 6,
        'actual_hours' => 10,
        'delay_hours' => 18,
        'priority' => 'medium',
    ],

    [
        'employee_id' => 2,
        'title' => 'Create Dashboard UI',
        'description' => 'Analytics dashboard',
        'status' => 'completed',
        'assigned_at' => '2026-06-01 09:00:00',
        'started_at' => '2026-06-01 09:15:00',
        'deadline_at' => '2026-06-03 18:00:00',
        'completed_at' => '2026-06-03 17:00:00',
        'estimated_hours' => 12,
        'actual_hours' => 11,
        'delay_hours' => 0,
        'priority' => 'high',
    ],

    [
        'employee_id' => 2,
        'title' => 'Responsive Fixes',
        'description' => 'Mobile responsiveness',
        'status' => 'completed',
        'assigned_at' => '2026-06-04 09:00:00',
        'started_at' => '2026-06-04 09:30:00',
        'deadline_at' => '2026-06-05 18:00:00',
        'completed_at' => '2026-06-05 15:00:00',
        'estimated_hours' => 5,
        'actual_hours' => 4,
        'delay_hours' => 0,
        'priority' => 'medium',
    ],

    [
        'employee_id' => 3,
        'title' => 'Test Payment Module',
        'description' => 'QA testing',
        'status' => 'late_completed',
        'assigned_at' => '2026-06-02 09:00:00',
        'started_at' => '2026-06-02 10:00:00',
        'deadline_at' => '2026-06-03 18:00:00',
        'completed_at' => '2026-06-04 20:00:00',
        'estimated_hours' => 7,
        'actual_hours' => 11,
        'delay_hours' => 26,
        'priority' => 'high',
    ],

    [
        'employee_id' => 3,
        'title' => 'Regression Testing',
        'description' => 'Full app testing',
        'status' => 'completed',
        'assigned_at' => '2026-06-05 09:00:00',
        'started_at' => '2026-06-05 09:20:00',
        'deadline_at' => '2026-06-06 18:00:00',
        'completed_at' => '2026-06-06 17:00:00',
        'estimated_hours' => 8,
        'actual_hours' => 7,
        'delay_hours' => 0,
        'priority' => 'medium',
    ],

]);
    }
}
