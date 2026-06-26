<?php

namespace Database\Factories;

use App\Models\Artifact;
use App\Models\DatasetExport;
use App\Models\ExportItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExportItem>
 */
class ExportItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'export_request_id' => DatasetExport::factory(),
            'artifact_id' => Artifact::factory(),
            'original_filename' => fake()->word().'.csv',
        ];
    }
}
