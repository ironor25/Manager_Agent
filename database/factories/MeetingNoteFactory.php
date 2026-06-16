<?php

namespace Database\Factories;

use App\Models\MeetingNote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MeetingNote>
 */
class MeetingNoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'notes_text' => $this->faker->paragraph(),
            'meeting_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
