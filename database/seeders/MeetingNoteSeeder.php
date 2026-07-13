<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class MeetingNoteSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $employeeIds = DB::table('employees')->pluck('id')->toArray();

        if (empty($employeeIds)) {
            echo "No employees found. Please run EmployeeSeeder first.\n";
            return;
        }

        $totalNotes = app()->runningUnitTests() ? 30 : 150000;
        $chunkSize = app()->runningUnitTests() ? 10 : 2000;

        echo "Seeding {$totalNotes} Meeting Notes...\n";

        for ($i = 0; $i < $totalNotes; $i += $chunkSize) {
            $notes = [];
            for ($j = 0; $j < $chunkSize; $j++) {
                $createdAt = $faker->dateTimeBetween('-1 year', 'now');
                
                $notes[] = [
                    'employee_id' => $faker->randomElement($employeeIds),
                    'notes_text' => $faker->paragraph(2),
                    'meeting_date' => $faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s'),
                    'created_at' => $createdAt->format('Y-m-d H:i:s'),
                    'updated_at' => $createdAt->format('Y-m-d H:i:s'),
                ];
            }
            DB::table('meeting_notes')->insert($notes);
        }
    }
}
