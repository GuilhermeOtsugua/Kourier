<?php

namespace App\Http\Controllers;

use App\Models\AuditEvent;
use App\Models\DatasetExport;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatasetExportDownloadController extends Controller
{
    /**
     * Redirect an authorized user to a short-lived signed export download route.
     */
    public function redirect(Team $current_team, Project $project, DatasetExport $export): RedirectResponse
    {
        abort_unless($project->team_id === $current_team->id && $export->project_id === $project->id, 404);

        Gate::authorize('download', $export);
        abort_unless($export->status === 'completed', 404);

        return redirect()->to(URL::temporarySignedRoute(
            'exports.download.signed',
            now()->addMinutes(10),
            [$current_team, $export],
        ));
    }

    /**
     * Download a completed dataset export through a signed, authenticated route.
     */
    public function download(Request $request, Team $current_team, DatasetExport $export): StreamedResponse
    {
        abort_unless($export->project->team_id === $current_team->id, 404);

        Gate::authorize('download', $export);
        abort_unless($export->status === 'completed' && $export->disk && $export->path, 404);
        abort_unless(Storage::disk($export->disk)->exists($export->path), 404);

        AuditEvent::recordForRequest($request, 'export.downloaded', $export->project, $export, [
            'name' => $export->name,
        ]);

        return Storage::disk($export->disk)->download($export->path, $export->name.'.zip');
    }
}
