<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $teams = DB::table('teams')->pluck('name')->toArray();

        $totalEmployees = app()->runningUnitTests() ? 10 : 10000;
        $chunkSize = app()->runningUnitTests() ? 10 : 1000;

        echo "Seeding {$totalEmployees} Employees...\n";

        for ($i = 0; $i < $totalEmployees; $i += $chunkSize) {
            $employees = [];
            for ($j = 0; $j < $chunkSize; $j++) {
                $employees[] = [
                    'name' => $faker->name,
                    'email' => $faker->unique()->safeEmail,
                    'team' => $faker->randomElement($teams),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('employees')->insert($employees);
        }
    }
}
