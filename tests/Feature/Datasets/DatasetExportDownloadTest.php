<?php

use App\Models\DatasetExport;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('issues a temporary signed download for completed dataset exports', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $team = $user->currentTeam;
    $project = Project::factory()
        ->for($team)
        ->for($user, 'creator')
        ->create();

    Storage::disk('local')->put('datasets/exports/1/export.zip', 'zip-bytes');

    $export = DatasetExport::factory()
        ->for($project)
        ->for($user, 'requester')
        ->create([
            'name' => 'Approved package',
            'status' => 'completed',
            'disk' => 'local',
            'path' => 'datasets/exports/1/export.zip',
        ]);

    $response = $this
        ->actingAs($user)
        ->get(route('exports.download', [$team, $project, $export]));

    $response->assertRedirect();

    $signedUrl = $response->headers->get('Location');

    expect($signedUrl)
        ->toContain('signature=')
        ->toContain('expires=');

    $this
        ->actingAs($user)
        ->get($signedUrl)
        ->assertSuccessful()
        ->assertDownload('Approved package.zip');
});

it('blocks users outside the project team from dataset export downloads', function () {
    Storage::fake('local');

    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $team = $owner->currentTeam;
    $project = Project::factory()
        ->for($team)
        ->for($owner, 'creator')
        ->create();

    Storage::disk('local')->put('datasets/exports/1/export.zip', 'zip-bytes');

    $export = DatasetExport::factory()
        ->for($project)
        ->for($owner, 'requester')
        ->create([
            'status' => 'completed',
            'disk' => 'local',
            'path' => 'datasets/exports/1/export.zip',
        ]);

    $this
        ->actingAs($intruder)
        ->get(route('exports.download', [$team, $project, $export]))
        ->assertForbidden();
});
