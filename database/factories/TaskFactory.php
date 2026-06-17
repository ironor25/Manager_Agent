<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(['pending', 'in_progress', 'completed']);
        $assignedAt = fake()->dateTimeBetween('-1 month', 'now');
        
        $startedAt = null;
        $completedAt = null;
        if ($status !== 'pending') {
            $startedAt = (clone $assignedAt)->modify('+' . rand(1, 48) . ' hours');
        }
        if ($status === 'completed') {
            $completedAt = (clone $startedAt)->modify('+' . rand(2, 40) . ' hours');
        }

        $estimatedHours = fake()->randomFloat(2, 2, 40);
        $actualHours = $completedAt ? fake()->randomFloat(2, 2, 50) : null;
        $delayHours = ($actualHours && $actualHours > $estimatedHours) ? ($actualHours - $estimatedHours) : null;

        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'status' => $status,
            'assigned_at' => $assignedAt,
            'started_at' => $startedAt,
            'deadline_at' => (clone $assignedAt)->modify('+' . rand(2, 14) . ' days'),
            'completed_at' => $completedAt,
            'estimated_hours' => $estimatedHours,
            'actual_hours' => $actualHours,
            'delay_hours' => $delayHours,
            'priority' => fake()->randomElement(['low', 'normal', 'high', 'urgent']),
        ];
    }
}
