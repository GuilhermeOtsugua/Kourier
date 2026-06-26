<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDatasetExportRequest;
use App\Models\Artifact;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class DatasetExportController extends Controller
{
    /**
     * Store a dataset export request for selected artifacts.
     */
    public function store(StoreDatasetExportRequest $request, Team $current_team, Project $project): RedirectResponse
    {
        abort_unless($project->team_id === $current_team->id, 404);

        $validated = $request->validated();
        $artifactIds = collect($validated['artifact_ids'])->unique()->values();

        DB::transaction(function () use ($project, $request, $validated, $artifactIds): void {
            $export = $project->exports()->create([
                'requested_by_id' => $request->user()->id,
                'name' => $validated['name'],
                'status' => 'queued',
            ]);

            Artifact::query()
                ->whereBelongsTo($project)
                ->whereIn('id', $artifactIds)
                ->orderBy('id')
                ->get()
                ->each(fn (Artifact $artifact) => $export->items()->create([
                    'artifact_id' => $artifact->id,
                    'original_filename' => $artifact->original_filename,
                ]));
        });

        return redirect()->route('projects.show', [$current_team, $project]);
    }
}
