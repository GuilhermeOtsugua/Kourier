<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreArtifactRequest;
use App\Jobs\ProcessArtifactPreview;
use App\Models\AuditEvent;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class ArtifactController extends Controller
{
    /**
     * Store a newly uploaded artifact for a project.
     */
    public function store(StoreArtifactRequest $request, Team $current_team, Project $project): RedirectResponse
    {
        abort_unless($project->team_id === $current_team->id, 404);

        $file = $request->file('artifact');
        $disk = config('kourier.storage.disk');
        $directory = trim(config('kourier.storage.artifact_path'), '/').'/'.$project->id;
        $filename = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs($directory, $filename, $disk);

        $artifact = $project->artifacts()->create([
            'uploaded_by_id' => $request->user()->id,
            'disk' => $disk,
            'path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'checksum' => hash_file('sha256', $file->getRealPath()),
            'processing_status' => 'queued',
        ]);

        AuditEvent::recordForRequest($request, 'artifact.uploaded', $project, $artifact, [
            'filename' => $artifact->original_filename,
            'size_bytes' => $artifact->size_bytes,
        ]);

        ProcessArtifactPreview::dispatch($artifact);

        return redirect()->route('projects.show', [$current_team, $project]);
    }
}
