<?php

namespace App\Jobs;

use App\Models\DatasetExport;
use App\Models\ExportItem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;
use ZipArchive;

class BuildDatasetExport implements ShouldQueue
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
    public function __construct(public DatasetExport $export)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $export = $this->export->fresh(['items.artifact']);

        if (! $export) {
            return;
        }

        $diskName = config('kourier.storage.disk');
        $directory = trim(config('kourier.storage.export_path'), '/').'/'.$export->id;
        $zipPath = $directory.'/dataset-export-'.$export->id.'.zip';
        $manifestPath = $directory.'/manifest.csv';

        $export->update([
            'status' => 'processing',
            'disk' => $diskName,
            'path' => null,
            'manifest_path' => null,
            'failure_reason' => null,
        ]);

        $manifest = $this->buildManifest($export);
        $tempZip = tempnam(sys_get_temp_dir(), 'kourier-export-');

        if ($tempZip === false) {
            throw new RuntimeException('Unable to allocate a temporary export file.');
        }

        $zip = new ZipArchive;

        if ($zip->open($tempZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Unable to create dataset export archive.');
        }

        $zip->addFromString('manifest.csv', $manifest);

        $export->items->each(function (ExportItem $item) use ($zip): void {
            $artifact = $item->artifact;
            $sourceDisk = Storage::disk($artifact->disk);

            if (! $sourceDisk->exists($artifact->path)) {
                throw new RuntimeException('Export artifact file is missing: '.$artifact->original_filename);
            }

            $zip->addFromString(
                'artifacts/'.$artifact->id.'-'.$this->archiveSafeFilename($artifact->original_filename),
                $sourceDisk->get($artifact->path),
            );
        });

        $zip->close();

        $targetDisk = Storage::disk($diskName);
        $targetDisk->put($manifestPath, $manifest);
        $targetDisk->put($zipPath, file_get_contents($tempZip));

        @unlink($tempZip);

        $export->update([
            'status' => 'completed',
            'path' => $zipPath,
            'manifest_path' => $manifestPath,
            'completed_at' => now(),
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        $this->export->update([
            'status' => 'failed',
            'failure_reason' => $exception?->getMessage(),
        ]);
    }

    /**
     * Build the CSV manifest for the export.
     */
    private function buildManifest(DatasetExport $export): string
    {
        $handle = fopen('php://temp', 'r+');

        if ($handle === false) {
            throw new RuntimeException('Unable to allocate manifest memory.');
        }

        fputcsv($handle, [
            'artifact_id',
            'original_filename',
            'mime_type',
            'size_bytes',
            'checksum',
            'review_status',
            'archive_path',
        ]);

        $export->items->each(function (ExportItem $item) use ($handle): void {
            $artifact = $item->artifact;

            fputcsv($handle, [
                $artifact->id,
                $artifact->original_filename,
                $artifact->mime_type,
                $artifact->size_bytes,
                $artifact->checksum,
                $artifact->review_status,
                'artifacts/'.$artifact->id.'-'.$this->archiveSafeFilename($artifact->original_filename),
            ]);
        });

        rewind($handle);
        $manifest = stream_get_contents($handle);
        fclose($handle);

        return $manifest === false ? '' : $manifest;
    }

    /**
     * Remove path separators from filenames before writing to the archive.
     */
    private function archiveSafeFilename(string $filename): string
    {
        return basename(str_replace('\\', '/', $filename));
    }
}
