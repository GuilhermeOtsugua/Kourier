<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lets an authenticated team member create a dataset project', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    $response = $this
        ->actingAs($user)
        ->post(route('projects.store', $team), [
            'name' => 'Racehorse sensor intake',
            'description' => 'Secure intake for sample motion/audio artifacts.',
        ]);

    $project = Project::query()->first();

    $response->assertRedirect(route('projects.show', [$team, $project]));

    expect($project)
        ->not->toBeNull()
        ->name->toBe('Racehorse sensor intake')
        ->description->toBe('Secure intake for sample motion/audio artifacts.')
        ->team_id->toBe($team->id)
        ->created_by_id->toBe($user->id);
});
