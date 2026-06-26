<?php

namespace App\Policies;

use App\Models\DatasetExport;
use App\Models\User;

class DatasetExportPolicy
{
    /**
     * Determine whether the user can view the export.
     */
    public function view(User $user, DatasetExport $datasetExport): bool
    {
        return $user->belongsToTeam($datasetExport->project->team);
    }

    /**
     * Determine whether the user can download the export package.
     */
    public function download(User $user, DatasetExport $datasetExport): bool
    {
        return $this->view($user, $datasetExport);
    }
}
