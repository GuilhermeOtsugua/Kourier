<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    /**
     * Display a listing of dataset projects for the current team.
     */
    public function index(Team $current_team): View
    {
        $projects = Project::query()
            ->whereBelongsTo($current_team)
            ->latest()
            ->get();

        return view('projects.index', [
            'team' => $current_team,
            'projects' => $projects,
        ]);
    }

    /**
     * Show the form for creating a new dataset project.
     */
    public function create(Team $current_team): View
    {
        return view('projects.create', ['team' => $current_team]);
    }

    /**
     * Store a newly created dataset project.
     */
    public function store(StoreProjectRequest $request, Team $current_team): RedirectResponse
    {
        $project = $current_team->projects()->create([
            ...$request->validated(),
            'created_by_id' => $request->user()->id,
        ]);

        return redirect()->route('projects.show', [$current_team, $project]);
    }

    /**
     * Display the specified dataset project.
     */
    public function show(Team $current_team, Project $project): View
    {
        abort_unless($project->team_id === $current_team->id, 404);

        Gate::authorize('view', $project);

        return view('projects.show', [
            'team' => $current_team,
            'project' => $project->load(['artifacts' => fn ($query) => $query->with('labels')->latest()]),
        ]);
    }
}
