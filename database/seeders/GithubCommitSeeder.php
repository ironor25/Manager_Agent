<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\GithubCommit;

class GithubCommitSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::all();
        foreach ($employees as $employee) {
            GithubCommit::factory(rand(5, 12))->create(['employee_id' => $employee->id]);
        }
    }
}
