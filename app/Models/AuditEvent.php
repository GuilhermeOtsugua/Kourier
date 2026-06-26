<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $user_id
 * @property int|null $team_id
 * @property int|null $project_id
 * @property string|null $auditable_type
 * @property int|null $auditable_id
 * @property string $event
 * @property array<string, mixed>|null $metadata
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property Carbon|null $created_at
 * @property-read Model|null $auditable
 * @property-read Project|null $project
 * @property-read Team|null $team
 * @property-read User|null $user
 */
#[Fillable([
    'user_id',
    'team_id',
    'project_id',
    'auditable_type',
    'auditable_id',
    'event',
    'metadata',
    'ip_address',
    'user_agent',
])]
class AuditEvent extends Model
{
    public const UPDATED_AT = null;

    /**
     * Record an audit event for the current request.
     *
     * @param  array<string, mixed>  $metadata
     */
    public static function recordForRequest(
        Request $request,
        string $event,
        ?Project $project = null,
        ?Model $auditable = null,
        array $metadata = [],
    ): self {
        $team = $request->route('current_team');

        return self::query()->create([
            'user_id' => $request->user()?->id,
            'team_id' => $team instanceof Team ? $team->id : $project?->team_id,
            'project_id' => $project?->id,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'event' => $event,
            'metadata' => $metadata === [] ? null : $metadata,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    /**
     * Get the user associated with the audit event.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the team associated with the audit event.
     *
     * @return BelongsTo<Team, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the project associated with the audit event.
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the audited model.
     *
     * @return MorphTo<Model, $this>
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }
}
