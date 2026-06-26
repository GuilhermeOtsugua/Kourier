<?php

use App\Jobs\BuildDatasetExport;
use App\Models\Artifact;
use App\Models\DatasetExport;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('builds a zip package and manifest for a dataset export', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $project = Project::factory()
        ->for($user->currentTeam)
        ->for($user, 'creator')
        ->create();

    Storage::disk('local')->put('datasets/artifacts/one.csv', "timestamp,value\n1,42\n");
    Storage::disk('local')->put('datasets/artifacts/two.csv', "timestamp,value\n2,84\n");

    $firstArtifact = Artifact::factory()
        ->for($project)
        ->for($user, 'uploader')
        ->create([
            'disk' => 'local',
            'path' => 'datasets/artifacts/one.csv',
            'original_filename' => 'one.csv',
            'mime_type' => 'text/csv',
            'size_bytes' => 21,
            'review_status' => 'approved',
        ]);
    $secondArtifact = Artifact::factory()
        ->for($project)
        ->for($user, 'uploader')
        ->create([
            'disk' => 'local',
            'path' => 'datasets/artifacts/two.csv',
            'original_filename' => 'two.csv',
            'mime_type' => 'text/csv',
            'size_bytes' => 21,
            'review_status' => 'approved',
        ]);

    $export = DatasetExport::factory()
        ->for($project)
        ->for($user, 'requester')
        ->create(['status' => 'queued']);

    $export->items()->create([
        'artifact_id' => $firstArtifact->id,
        'original_filename' => $firstArtifact->original_filename,
    ]);
    $export->items()->create([
        'artifact_id' => $secondArtifact->id,
        'original_filename' => $secondArtifact->original_filename,
    ]);

    (new BuildDatasetExport($export))->handle();

    $export->refresh();

    expect($export->status)->toBe('completed')
        ->and($export->disk)->toBe('local')
        ->and($export->path)->not->toBeNull()
        ->and($export->manifest_path)->not->toBeNull();

    Storage::disk('local')->assertExists($export->path);
    Storage::disk('local')->assertExists($export->manifest_path);

    $manifest = Storage::disk('local')->get($export->manifest_path);

    expect($manifest)
        ->toContain('artifact_id,original_filename')
        ->toContain('one.csv')
        ->toContain('two.csv');

    $zip = new ZipArchive();
    $zip->open(Storage::disk('local')->path($export->path));

    expect($zip->locateName('manifest.csv'))->toBeInt()
        ->and($zip->locateName('artifacts/'.$firstArtifact->id.'-one.csv'))->toBeInt()
        ->and($zip->locateName('artifacts/'.$secondArtifact->id.'-two.csv'))->toBeInt();

    $zip->close();
});
