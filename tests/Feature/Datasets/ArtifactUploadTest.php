<?php

use App\Jobs\ProcessArtifactPreview;
use App\Models\Artifact;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('stores uploaded artifacts privately for an authorized project member', function () {
    Storage::fake('local');
    Queue::fake();

    $user = User::factory()->create();
    $team = $user->currentTeam;
    $project = Project::factory()
        ->for($team)
        ->for($user, 'creator')
        ->create();

    $file = UploadedFile::fake()->createWithContent('sample.csv', "timestamp,value\n1,42\n");

    $response = $this
        ->actingAs($user)
        ->post(route('artifacts.store', [$team, $project]), [
            'artifact' => $file,
        ]);

    $artifact = Artifact::query()->first();

    $response->assertRedirect(route('projects.show', [$team, $project]));

    expect($artifact)
        ->not->toBeNull()
        ->project_id->toBe($project->id)
        ->uploaded_by_id->toBe($user->id)
        ->disk->toBe('local')
        ->original_filename->toBe('sample.csv')
        ->mime_type->toBe('text/csv')
        ->processing_status->toBe('queued');

    Storage::disk('local')->assertExists($artifact->path);
    Queue::assertPushed(ProcessArtifactPreview::class);
});
