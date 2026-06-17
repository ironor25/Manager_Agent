<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Attendance;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::all();
        foreach ($employees as $employee) {
            Attendance::factory(15)->create(['employee_id' => $employee->id]);
        }
    }
}
