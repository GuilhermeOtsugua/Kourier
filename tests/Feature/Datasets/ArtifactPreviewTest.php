<?php

use App\Jobs\ProcessArtifactPreview;
use App\Models\Artifact;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('processes lightweight preview metadata for an uploaded artifact', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $project = Project::factory()
        ->for($user->currentTeam)
        ->for($user, 'creator')
        ->create();

    Storage::disk('local')->put('datasets/artifacts/sample.csv', "timestamp,value\n1,42\n");

    $artifact = Artifact::factory()
        ->for($project)
        ->for($user, 'uploader')
        ->create([
            'disk' => 'local',
            'path' => 'datasets/artifacts/sample.csv',
            'original_filename' => 'sample.csv',
            'mime_type' => 'text/csv',
            'size_bytes' => 21,
            'processing_status' => 'queued',
            'preview_metadata' => null,
        ]);

    (new ProcessArtifactPreview($artifact))->handle();

    $artifact->refresh();

    expect($artifact->processing_status)->toBe('ready')
        ->and($artifact->preview_metadata)->toMatchArray([
            'filename' => 'sample.csv',
            'mime_type' => 'text/csv',
            'size_bytes' => 21,
            'line_count' => 2,
        ]);
});
