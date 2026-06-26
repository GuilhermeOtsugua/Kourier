<?php

namespace Database\Seeders;

use App\Enums\TeamRole;
use App\Jobs\BuildDatasetExport;
use App\Models\Artifact;
use App\Models\AuditEvent;
use App\Models\DatasetExport;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @phpstan-type DemoArtifactLabel array{key: string, value: string, notes: string|null}
 * @phpstan-type DemoArtifactFixture array{filename: string, mime_type: string, contents: string, labels: list<DemoArtifactLabel>}
 */
class DemoDatasetSeeder extends Seeder
{
    /**
     * Seed a local demo workspace for the public Koúrier landing page.
     */
    public function run(): void
    {
        config(['kourier.storage.disk' => 'local']);

        $user = User::query()->updateOrCreate(
            ['email' => 'otsugua@example.com'],
            [
                'name' => 'Guilherme Otsugua',
                'password' => Hash::make('pass'),
                'email_verified_at' => now(),
            ],
        );

        $team = Team::query()->updateOrCreate(
            ['slug' => 'otsugua-demo-lab'],
            [
                'name' => 'Otsugua Demo Lab',
                'is_personal' => false,
            ],
        );

        $team->members()->syncWithoutDetaching([
            $user->id => ['role' => TeamRole::Owner->value],
        ]);

        $user->switchTeam($team);

        $project = $team->projects()->updateOrCreate(
            ['name' => 'Equine sensor demo'],
            [
                'created_by_id' => $user->id,
                'description' => 'Demo workspace for secure sensor artifacts, labels, and export packaging.',
            ],
        );

        $artifacts = collect($this->artifactFixtures())
            ->map(fn (array $fixture): Artifact => $this->seedArtifact($project, $user, $fixture));

        $export = $project->exports()->updateOrCreate(
            ['name' => 'Approved training package'],
            [
                'requested_by_id' => $user->id,
                'status' => 'queued',
                'disk' => null,
                'path' => null,
                'manifest_path' => null,
                'failure_reason' => null,
                'completed_at' => null,
            ],
        );

        $export->items()->whereNotIn('artifact_id', $artifacts->pluck('id'))->delete();

        $artifacts->each(fn (Artifact $artifact) => $export->items()->updateOrCreate(
            ['artifact_id' => $artifact->id],
            ['original_filename' => $artifact->original_filename],
        ));

        (new BuildDatasetExport($export))->handle();

        $this->seedAuditEvents($project, $user, $artifacts, $export->fresh());
    }

    /**
     * @return list<DemoArtifactFixture>
     */
    private function artifactFixtures(): array
    {
        return [
            [
                'filename' => 'sensor-session-001.csv',
                'mime_type' => 'text/csv',
                'contents' => "timestamp,stride_rate,heart_rate\n2026-06-26T10:00:00Z,92,118\n2026-06-26T10:00:05Z,95,121\n",
                'labels' => [
                    ['key' => 'discipline', 'value' => 'dressage', 'notes' => 'Approved for training export.'],
                    ['key' => 'sensor', 'value' => 'stride-band', 'notes' => null],
                ],
            ],
            [
                'filename' => 'stride-audio-note.txt',
                'mime_type' => 'text/plain',
                'contents' => "Handler note: clean stride cadence after warmup. Background barn noise present but acceptable.\n",
                'labels' => [
                    ['key' => 'modality', 'value' => 'audio-note', 'notes' => 'Usable as paired context.'],
                ],
            ],
            [
                'filename' => 'barn-camera-frame.json',
                'mime_type' => 'application/json',
                'contents' => '{"frame":"barn-camera-0421","horse":"demo-mare","review":"approved"}',
                'labels' => [
                    ['key' => 'modality', 'value' => 'image-metadata', 'notes' => 'Metadata-only demo artifact.'],
                ],
            ],
        ];
    }

    /**
     * @param  DemoArtifactFixture  $fixture
     */
    private function seedArtifact(Project $project, User $user, array $fixture): Artifact
    {
        $path = 'datasets/artifacts/demo/'.$fixture['filename'];
        $contents = (string) $fixture['contents'];

        Storage::disk('local')->put($path, $contents);

        $artifact = $project->artifacts()->updateOrCreate(
            ['path' => $path],
            [
                'uploaded_by_id' => $user->id,
                'disk' => 'local',
                'original_filename' => (string) $fixture['filename'],
                'mime_type' => (string) $fixture['mime_type'],
                'size_bytes' => strlen($contents),
                'checksum' => hash('sha256', $contents),
                'processing_status' => 'ready',
                'review_status' => 'approved',
                'reviewed_at' => now(),
                'preview_metadata' => [
                    'filename' => (string) $fixture['filename'],
                    'mime_type' => (string) $fixture['mime_type'],
                    'size_bytes' => strlen($contents),
                    'checksum' => hash('sha256', $contents),
                    'line_count' => Str::contains($contents, "\n") ? substr_count(rtrim($contents, "\r\n"), "\n") + 1 : 1,
                    'processed_at' => now()->toISOString(),
                ],
            ],
        );

        foreach ($fixture['labels'] as $label) {
            $artifact->labels()->updateOrCreate(
                ['key' => $label['key']],
                [
                    'created_by_id' => $user->id,
                    'value' => $label['value'],
                    'notes' => $label['notes'],
                ],
            );
        }

        return $artifact;
    }

    /**
     * @param  Collection<int, Artifact>  $artifacts
     */
    private function seedAuditEvents(Project $project, User $user, Collection $artifacts, ?DatasetExport $export): void
    {
        AuditEvent::query()->where('project_id', $project->id)->delete();

        $team = $project->team;

        $artifacts->each(function (Artifact $artifact) use ($project, $team, $user): void {
            AuditEvent::query()->create([
                'user_id' => $user->id,
                'team_id' => $team->id,
                'project_id' => $project->id,
                'auditable_type' => $artifact->getMorphClass(),
                'auditable_id' => $artifact->id,
                'event' => 'artifact.uploaded',
                'metadata' => ['filename' => $artifact->original_filename],
            ]);
        });

        if (! $export) {
            return;
        }

        foreach (['artifact.labeled', 'export.requested', 'export.downloaded'] as $event) {
            AuditEvent::query()->create([
                'user_id' => $user->id,
                'team_id' => $team->id,
                'project_id' => $project->id,
                'auditable_type' => $export->getMorphClass(),
                'auditable_id' => $export->id,
                'event' => $event,
                'metadata' => ['demo' => true],
            ]);
        }
    }
}
