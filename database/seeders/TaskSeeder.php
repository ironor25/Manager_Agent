<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Task;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::all();
        foreach ($employees as $employee) {
            Task::factory(rand(5, 8))->create(['employee_id' => $employee->id]);
        }
    }
}
