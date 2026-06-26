<?php

namespace Database\Factories;

use App\Models\Artifact;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Artifact>
 */
class ArtifactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $filename = fake()->word().'.csv';

        return [
            'project_id' => Project::factory(),
            'uploaded_by_id' => User::factory(),
            'disk' => 'local',
            'path' => 'datasets/artifacts/'.Str::uuid().'.csv',
            'original_filename' => $filename,
            'mime_type' => 'text/plain',
            'size_bytes' => fake()->numberBetween(100, 10000),
            'checksum' => null,
            'processing_status' => 'queued',
            'preview_metadata' => null,
        ];
    }
}
