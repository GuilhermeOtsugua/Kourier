<?php

namespace App\Policies;

use App\Models\Artifact;
use App\Models\User;

class ArtifactPolicy
{
    /**
     * Determine whether the user can view the artifact.
     */
    public function view(User $user, Artifact $artifact): bool
    {
        return $user->belongsToTeam($artifact->project->team);
    }

    /**
     * Determine whether the user can download the artifact.
     */
    public function download(User $user, Artifact $artifact): bool
    {
        return $this->view($user, $artifact);
    }
}
