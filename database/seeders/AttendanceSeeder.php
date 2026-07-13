<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $employeeIds = DB::table('employees')->pluck('id')->toArray();

        $totalAttendances = app()->runningUnitTests() ? 30 : 100000;
        $chunkSize = app()->runningUnitTests() ? 10 : 2000;

        echo "Seeding {$totalAttendances} Attendances...\n";

        for ($i = 0; $i < $totalAttendances; $i += $chunkSize) {
            $attendances = [];
            for ($j = 0; $j < $chunkSize; $j++) {
                $loginTime = $faker->dateTimeBetween('-6 months', 'now');
                $logoutTime = (clone $loginTime)->modify('+8 hours');
                
                $attendances[] = [
                    'employee_id' => $faker->randomElement($employeeIds),
                    'login_time' => $loginTime->format('Y-m-d H:i:s'),
                    'logout_time' => $logoutTime->format('Y-m-d H:i:s'),
                    'leave_flag' => $faker->boolean(10), // 10% chance of leave
                    'late_flag' => $faker->boolean(15),  // 15% chance of being late
                    'created_at' => $loginTime->format('Y-m-d H:i:s'),
                    'updated_at' => $loginTime->format('Y-m-d H:i:s'),
                ];
            }
            DB::table('attendances')->insert($attendances);
        }
    }
}
