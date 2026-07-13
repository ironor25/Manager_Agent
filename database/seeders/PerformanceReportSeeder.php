<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class PerformanceReportSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $employeeIds = DB::table('employees')->pluck('id')->toArray();

        $totalReports = count($employeeIds); // 1 report per employee
        $chunkSize = 1000;

        echo "Seeding {$totalReports} Performance Reports...\n";

        for ($i = 0; $i < $totalReports; $i += $chunkSize) {
            $reports = [];
            $chunkIds = array_slice($employeeIds, $i, $chunkSize);
            
            foreach ($chunkIds as $id) {
                $reports[] = [
                    'employee_id' => $id,
                    'summary' => $faker->paragraph(2),
                    'leadership_score' => $faker->randomFloat(2, 40, 100),
                    'strengths' => json_encode([$faker->word, $faker->word, $faker->word]),
                    'weaknesses' => json_encode([$faker->word, $faker->word]),
                    'recommendations' => json_encode([$faker->sentence, $faker->sentence]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('performance_reports')->insert($reports);
        }
    }
}
