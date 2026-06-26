<?php

use App\Models\Artifact;
use App\Models\ArtifactLabel;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lets an authorized project member label and review an artifact', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;
    $project = Project::factory()
        ->for($team)
        ->for($user, 'creator')
        ->create();
    $artifact = Artifact::factory()
        ->for($project)
        ->for($user, 'uploader')
        ->create();

    $response = $this
        ->actingAs($user)
        ->post(route('artifact-labels.store', [$team, $project, $artifact]), [
            'key' => 'discipline',
            'value' => 'dressage',
            'notes' => 'Reviewed for training export.',
            'review_status' => 'approved',
        ]);

    $label = ArtifactLabel::query()->first();

    $response->assertRedirect(route('projects.show', [$team, $project]));

    expect($label)
        ->not->toBeNull()
        ->artifact_id->toBe($artifact->id)
        ->created_by_id->toBe($user->id)
        ->key->toBe('discipline')
        ->value->toBe('dressage')
        ->notes->toBe('Reviewed for training export.');

    expect($artifact->fresh()->review_status)->toBe('approved');
});
