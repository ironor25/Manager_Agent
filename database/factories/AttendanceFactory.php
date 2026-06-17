<?php

namespace Database\Factories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $loginTime = $this->faker->dateTimeBetween('-1 month', 'now');
        $logoutTime = (clone $loginTime)->modify('+' . rand(7, 9) . ' hours');
        $isLeave = $this->faker->boolean(10); // 10% chance of leave

        return [
            'login_time' => $isLeave ? null : $loginTime,
            'logout_time' => $isLeave ? null : $logoutTime,
            'late_flag' => $isLeave ? false : $this->faker->boolean(20), // 20% chance of being late
            'leave_flag' => $isLeave,
        ];
    }
}
