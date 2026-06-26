<?php

namespace Database\Factories;

use App\Models\DatasetExport;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DatasetExport>
 */
class DatasetExportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'requested_by_id' => User::factory(),
            'name' => fake()->words(3, true),
            'status' => 'queued',
            'disk' => null,
            'path' => null,
            'manifest_path' => null,
            'failure_reason' => null,
            'completed_at' => null,
        ];
    }
}
