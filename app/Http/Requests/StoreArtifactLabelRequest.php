<?php

namespace App\Http\Requests;

use App\Models\Artifact;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreArtifactLabelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $team = $this->route('current_team');
        $project = $this->route('project');
        $artifact = $this->route('artifact');

        return $team instanceof Team
            && $project instanceof Project
            && $artifact instanceof Artifact
            && $project->team_id === $team->id
            && $artifact->project_id === $project->id
            && $this->user()?->can('labelArtifacts', $project);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:80'],
            'value' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'review_status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
        ];
    }
}
