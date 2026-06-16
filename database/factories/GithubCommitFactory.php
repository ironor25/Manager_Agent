<?php

namespace Database\Factories;

use App\Models\GithubCommit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GithubCommit>
 */
class GithubCommitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'repo_name' => $this->faker->word() . '-repo',
            'commit_hash' => $this->faker->sha1(),
            'commit_message' => $this->faker->sentence(),
            'commit_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
