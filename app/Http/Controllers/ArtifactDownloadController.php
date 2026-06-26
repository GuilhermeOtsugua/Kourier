<?php

namespace App\Http\Controllers;

use App\Models\Artifact;
use App\Models\AuditEvent;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ArtifactDownloadController extends Controller
{
    /**
     * Redirect an authorized user to a short-lived signed download route.
     */
    public function redirect(Team $current_team, Project $project, Artifact $artifact): RedirectResponse
    {
        abort_unless($project->team_id === $current_team->id && $artifact->project_id === $project->id, 404);

        Gate::authorize('download', $artifact);

        return redirect()->to(URL::temporarySignedRoute(
            'artifacts.download.signed',
            now()->addMinutes(10),
            [$current_team, $artifact],
        ));
    }

    /**
     * Download an artifact through a signed, authenticated route.
     */
    public function download(Request $request, Team $current_team, Artifact $artifact): StreamedResponse
    {
        abort_unless($artifact->project->team_id === $current_team->id, 404);

        Gate::authorize('download', $artifact);

        abort_unless(Storage::disk($artifact->disk)->exists($artifact->path), 404);

        AuditEvent::recordForRequest($request, 'artifact.downloaded', $artifact->project, $artifact, [
            'filename' => $artifact->original_filename,
        ]);

        return Storage::disk($artifact->disk)->download($artifact->path, $artifact->original_filename);
    }
}
