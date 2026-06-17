<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\MeetingNote;

class MeetingNoteSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::all();
        foreach ($employees as $employee) {
            MeetingNote::factory(rand(2, 5))->create(['employee_id' => $employee->id]);
        }
    }
}
