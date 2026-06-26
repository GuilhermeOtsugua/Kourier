<?php

namespace App\Http\Requests;

use App\Models\Project;
use App\Models\Team;
use Illuminate\Foundation\Http\FormRequest;

class StoreArtifactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $team = $this->route('current_team');
        $project = $this->route('project');

        return $team instanceof Team
            && $project instanceof Project
            && $project->team_id === $team->id
            && $this->user()?->can('uploadArtifact', $project);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'artifact' => ['required', 'file', 'max:51200'],
        ];
    }
}
