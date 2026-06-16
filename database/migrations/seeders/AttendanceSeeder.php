<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Attendance Seeder (15 days sample)

        for ($i = 1; $i <= 15; $i++) {

            DB::table('attendances')->insert([

                [
                    'employee_id' => 1,
                    'login_time' => now()->subDays($i)->setTime(9, 5),
                    'logout_time' => now()->subDays($i)->setTime(18, 0),
                    'late_flag' => 0,
                    'leave_flag' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                [
                    'employee_id' => 2,
                    'login_time' => now()->subDays($i)->setTime(9, 40),
                    'logout_time' => now()->subDays($i)->setTime(18, 10),
                    'late_flag' => 1,
                    'leave_flag' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                [
                    'employee_id' => 3,
                    'login_time' => now()->subDays($i)->setTime(10, 15),
                    'logout_time' => now()->subDays($i)->setTime(17, 30),
                    'late_flag' => 1,
                    'leave_flag' => ($i % 5 == 0) ? 1 : 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

            ]);
}
    }
}
