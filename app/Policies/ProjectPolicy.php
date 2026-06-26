<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        return $user->belongsToTeam($project->team);
    }

    /**
     * Determine whether the user can upload artifacts to the project.
     */
    public function uploadArtifact(User $user, Project $project): bool
    {
        return $user->belongsToTeam($project->team);
    }

    /**
     * Determine whether the user can label artifacts in the project.
     */
    public function labelArtifacts(User $user, Project $project): bool
    {
        return $user->belongsToTeam($project->team);
    }

    /**
     * Determine whether the user can request exports for the project.
     */
    public function export(User $user, Project $project): bool
    {
        return $user->belongsToTeam($project->team);
    }
}
