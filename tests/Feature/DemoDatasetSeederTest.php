<?php

use App\Models\Artifact;
use App\Models\ArtifactLabel;
use App\Models\AuditEvent;
use App\Models\DatasetExport;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\DemoDatasetSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('seeds a verified demo workspace with downloadable dataset files', function () {
    Storage::fake('local');

    $this->seed(DemoDatasetSeeder::class);

    $user = User::query()->where('email', 'otsugua@example.com')->first();
    $project = Project::query()->where('name', 'Equine sensor demo')->first();
    $export = DatasetExport::query()->where('name', 'Approved training package')->first();

    expect($user)
        ->not->toBeNull()
        ->email_verified_at->not->toBeNull()
        ->and(Hash::check('pass', $user->password))->toBeTrue()
        ->and($user->currentTeam)->not->toBeNull()
        ->and($project)->not->toBeNull()
        ->and($project->team_id)->toBe($user->current_team_id)
        ->and(Artifact::query()->whereBelongsTo($project)->count())->toBe(3)
        ->and(ArtifactLabel::query()->count())->toBeGreaterThanOrEqual(3)
        ->and($export)->not->toBeNull()
        ->and($export->status)->toBe('completed')
        ->and($export->items()->count())->toBe(3)
        ->and(AuditEvent::query()->count())->toBeGreaterThanOrEqual(5);

    Artifact::query()->whereBelongsTo($project)->each(
        fn (Artifact $artifact) => Storage::disk('local')->assertExists($artifact->path),
    );

    Storage::disk('local')->assertExists($export->path);
    Storage::disk('local')->assertExists($export->manifest_path);
});
