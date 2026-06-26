<?php

namespace App\Http\Requests;

use App\Models\Project;
use App\Models\Team;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDatasetExportRequest extends FormRequest
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
            && $this->user()?->can('export', $project);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $project = $this->route('project');

        return [
            'name' => ['required', 'string', 'max:255'],
            'artifact_ids' => ['required', 'array', 'min:1'],
            'artifact_ids.*' => [
                'integer',
                Rule::exists('artifacts', 'id')->where('project_id', $project?->id),
            ],
        ];
    }
}
