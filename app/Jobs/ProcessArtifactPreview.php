<?php

namespace App\Jobs;

use App\Models\Artifact;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ProcessArtifactPreview implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Calculate retry delays in seconds.
     *
     * @return list<int>
     */
    public function backoff(): array
    {
        return [1, 5, 10];
    }

    /**
     * Create a new job instance.
     */
    public function __construct(public Artifact $artifact)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $artifact = $this->artifact->fresh();

        if (! $artifact) {
            return;
        }

        $disk = Storage::disk($artifact->disk);

        if (! $disk->exists($artifact->path)) {
            $artifact->update([
                'processing_status' => 'failed',
                'preview_metadata' => ['error' => 'Artifact file is missing.'],
            ]);

            return;
        }

        $artifact->update([
            'processing_status' => 'processing',
        ]);

        $metadata = [
            'filename' => $artifact->original_filename,
            'mime_type' => $artifact->mime_type,
            'size_bytes' => $artifact->size_bytes,
            'checksum' => $artifact->checksum,
            'processed_at' => now()->toISOString(),
        ];

        if ($this->canCountLines($artifact)) {
            $contents = $disk->get($artifact->path);
            $metadata['line_count'] = $contents === '' ? 0 : substr_count(rtrim($contents, "\r\n"), "\n") + 1;
        }

        $artifact->update([
            'processing_status' => 'ready',
            'preview_metadata' => $metadata,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        $this->artifact->update([
            'processing_status' => 'failed',
            'preview_metadata' => ['error' => $exception?->getMessage()],
        ]);
    }

    /**
     * Determine whether a lightweight line-count preview is safe.
     */
    private function canCountLines(Artifact $artifact): bool
    {
        $filename = Str::lower($artifact->original_filename);

        return $artifact->size_bytes <= 1_048_576
            && (
                Str::startsWith((string) $artifact->mime_type, 'text/')
                || Str::endsWith($filename, ['.csv', '.txt', '.json', '.jsonl'])
            );
    }
}
