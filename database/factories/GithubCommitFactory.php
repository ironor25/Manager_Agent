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
        $repo = $this->faker->word() . '-repo';
        $hash = $this->faker->sha1();
        return [
            'repo_name' => $repo,
            'commit_hash' => $hash,
            'commit_message' => $this->faker->sentence(),
            'commit_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'url' => "https://github.com/company/{$repo}/commit/{$hash}",
        ];
    }
}
