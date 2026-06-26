<?php

use App\Jobs\BuildDatasetExport;
use App\Jobs\ProcessArtifactPreview;
use App\Models\Artifact;
use App\Models\AuditEvent;
use App\Models\DatasetExport;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('records audit events for dataset artifact and export activity', function () {
    Storage::fake('local');
    Queue::fake([ProcessArtifactPreview::class, BuildDatasetExport::class]);

    $user = User::factory()->create();
    $team = $user->currentTeam;
    $project = Project::factory()
        ->for($team)
        ->for($user, 'creator')
        ->create();

    $this
        ->actingAs($user)
        ->post(route('artifacts.store', [$team, $project]), [
            'artifact' => UploadedFile::fake()->createWithContent('sample.csv', "timestamp,value\n1,42\n"),
        ]);

    $artifact = Artifact::query()->firstOrFail();

    $artifactDownload = $this
        ->actingAs($user)
        ->get(route('artifacts.download', [$team, $project, $artifact]))
        ->headers->get('Location');

    $this->actingAs($user)->get($artifactDownload);

    $this
        ->actingAs($user)
        ->post(route('artifact-labels.store', [$team, $project, $artifact]), [
            'key' => 'discipline',
            'value' => 'dressage',
            'review_status' => 'approved',
        ]);

    $this
        ->actingAs($user)
        ->post(route('exports.store', [$team, $project]), [
            'name' => 'Approved package',
            'artifact_ids' => [$artifact->id],
        ]);

    $export = DatasetExport::query()->firstOrFail();
    Storage::disk('local')->put('datasets/exports/'.$export->id.'/export.zip', 'zip-bytes');
    $export->update([
        'status' => 'completed',
        'disk' => 'local',
        'path' => 'datasets/exports/'.$export->id.'/export.zip',
    ]);

    $exportDownload = $this
        ->actingAs($user)
        ->get(route('exports.download', [$team, $project, $export]))
        ->headers->get('Location');

    $this->actingAs($user)->get($exportDownload);

    expect(AuditEvent::query()->pluck('event')->all())
        ->toContain('artifact.uploaded')
        ->toContain('artifact.downloaded')
        ->toContain('artifact.labeled')
        ->toContain('export.requested')
        ->toContain('export.downloaded');
});
