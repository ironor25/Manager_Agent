<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        // Creates 5 employees in the Engineering Team (which is the default in the factory)
        Employee::factory(5)->create();
    }
}
