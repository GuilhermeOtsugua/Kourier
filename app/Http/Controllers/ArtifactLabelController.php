<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreArtifactLabelRequest;
use App\Models\Artifact;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;

class ArtifactLabelController extends Controller
{
    /**
     * Store a label and review status for an artifact.
     */
    public function store(StoreArtifactLabelRequest $request, Team $current_team, Project $project, Artifact $artifact): RedirectResponse
    {
        abort_unless($project->team_id === $current_team->id && $artifact->project_id === $project->id, 404);

        $validated = $request->validated();

        $artifact->labels()->create([
            'created_by_id' => $request->user()->id,
            'key' => $validated['key'],
            'value' => $validated['value'],
            'notes' => $validated['notes'] ?? null,
        ]);

        $artifact->update([
            'review_status' => $validated['review_status'],
            'reviewed_at' => $validated['review_status'] === 'pending' ? null : now(),
        ]);

        return redirect()->route('projects.show', [$current_team, $project]);
    }
}
