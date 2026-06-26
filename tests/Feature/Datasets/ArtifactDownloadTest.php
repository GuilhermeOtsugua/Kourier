<?php

use App\Models\Artifact;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('issues a temporary signed download for authorized artifacts', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $team = $user->currentTeam;
    $project = Project::factory()
        ->for($team)
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
        ]);

    $response = $this
        ->actingAs($user)
        ->get(route('artifacts.download', [$team, $project, $artifact]));

    $response->assertRedirect();

    $signedUrl = $response->headers->get('Location');

    expect($signedUrl)
        ->toContain('signature=')
        ->toContain('expires=');

    $this
        ->actingAs($user)
        ->get($signedUrl)
        ->assertSuccessful()
        ->assertDownload('sample.csv');
});

it('blocks users outside the project team from artifact downloads', function () {
    Storage::fake('local');

    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $team = $owner->currentTeam;
    $project = Project::factory()
        ->for($team)
        ->for($owner, 'creator')
        ->create();

    Storage::disk('local')->put('datasets/artifacts/private.csv', 'secret');

    $artifact = Artifact::factory()
        ->for($project)
        ->for($owner, 'uploader')
        ->create([
            'disk' => 'local',
            'path' => 'datasets/artifacts/private.csv',
            'original_filename' => 'private.csv',
        ]);

    $this
        ->actingAs($intruder)
        ->get(route('artifacts.download', [$team, $project, $artifact]))
        ->assertForbidden();
});
