<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SkillsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $skills = [
            ['name' => 'PHP', 'category' => 'Backend'],
            ['name' => 'Laravel', 'category' => 'Backend'],
            ['name' => 'MySQL', 'category' => 'Database'],
            ['name' => 'PostgreSQL', 'category' => 'Database'],
            ['name' => 'Docker', 'category' => 'DevOps'],
            ['name' => 'AWS', 'category' => 'DevOps'],
            ['name' => 'CI/CD', 'category' => 'DevOps'],
            ['name' => 'JavaScript', 'category' => 'Frontend'],
            ['name' => 'React', 'category' => 'Frontend'],
            ['name' => 'Vue.js', 'category' => 'Frontend'],
            ['name' => 'Tailwind CSS', 'category' => 'Frontend'],
            ['name' => 'Python', 'category' => 'Backend'],
            ['name' => 'Git', 'category' => 'Tools'],
            ['name' => 'Redis', 'category' => 'Database'],
            ['name' => 'Project Management', 'category' => 'Soft Skills'],
            ['name' => 'Agile/Scrum', 'category' => 'Soft Skills'],
            ['name' => 'Figma', 'category' => 'Design'],
            ['name' => 'TypeScript', 'category' => 'Frontend'],
            ['name' => 'Node.js', 'category' => 'Backend'],
            ['name' => 'UI/UX Design', 'category' => 'Design'],
        ];

        $now = now()->toDateTimeString();
        foreach ($skills as &$skill) {
            $skill['created_at'] = $now;
            $skill['updated_at'] = $now;
        }

        $this->command->info('Seeding skills into database...');
        DB::table('skills')->insertOrIgnore($skills);

        $skillIds = DB::table('skills')->pluck('id')->toArray();
        $employeeIds = DB::table('employees')->pluck('id')->toArray();

        $this->command->info('Generating employee-skill mappings...');
        $pivotData = [];

        foreach ($employeeIds as $employeeId) {
            // Assign 2 to 5 random skills to each employee
            $numSkills = rand(2, 5);
            $randomSkillIds = array_rand(array_flip($skillIds), $numSkills);
            if (!is_array($randomSkillIds)) {
                $randomSkillIds = [$randomSkillIds];
            }

            foreach ($randomSkillIds as $skillId) {
                $pivotData[] = [
                    'employee_id' => $employeeId,
                    'skill_id' => $skillId,
                    'proficiency_level' => rand(1, 5),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $this->command->info('Writing employee-skill mappings to database...');
        
        // Clean out existing mappings to avoid duplicates
        DB::table('employee_skill')->truncate();

        $chunks = array_chunk($pivotData, 2000);
        foreach ($chunks as $chunk) {
            DB::table('employee_skill')->insert($chunk);
        }

        $this->command->info('Seeding employee-skill relationships completed successfully.');
    }
}
