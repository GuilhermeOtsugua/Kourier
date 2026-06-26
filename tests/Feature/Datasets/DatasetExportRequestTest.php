<?php

use App\Jobs\BuildDatasetExport;
use App\Models\Artifact;
use App\Models\DatasetExport;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

it('lets an authorized project member request an export for selected artifacts', function () {
    Queue::fake();

    $user = User::factory()->create();
    $team = $user->currentTeam;
    $project = Project::factory()
        ->for($team)
        ->for($user, 'creator')
        ->create();
    $artifacts = Artifact::factory()
        ->count(2)
        ->for($project)
        ->for($user, 'uploader')
        ->create();

    $response = $this
        ->actingAs($user)
        ->post(route('exports.store', [$team, $project]), [
            'name' => 'Approved training package',
            'artifact_ids' => $artifacts->pluck('id')->all(),
        ]);

    $export = DatasetExport::query()->first();

    $response->assertRedirect(route('projects.show', [$team, $project]));

    expect($export)
        ->not->toBeNull()
        ->project_id->toBe($project->id)
        ->requested_by_id->toBe($user->id)
        ->name->toBe('Approved training package')
        ->status->toBe('queued');

    expect($export->items()->pluck('artifact_id')->all())
        ->toEqualCanonicalizing($artifacts->pluck('id')->all());

    Queue::assertPushed(BuildDatasetExport::class);
});
