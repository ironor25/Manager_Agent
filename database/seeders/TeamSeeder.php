<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('teams')->insert([
            ['name' => 'Engineering Team', 'description' => 'Core software development team', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Product Team', 'description' => 'Product management and design', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
