<?php

namespace Database\Factories;

use App\Models\Artifact;
use App\Models\ArtifactLabel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArtifactLabel>
 */
class ArtifactLabelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'artifact_id' => Artifact::factory(),
            'created_by_id' => User::factory(),
            'key' => fake()->word(),
            'value' => fake()->word(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
