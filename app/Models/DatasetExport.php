<?php

namespace App\Models;

use Database\Factories\DatasetExportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $project_id
 * @property int $requested_by_id
 * @property string $name
 * @property string $status
 * @property string|null $disk
 * @property string|null $path
 * @property string|null $manifest_path
 * @property string|null $failure_reason
 * @property Carbon|null $completed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, ExportItem> $items
 * @property-read Project $project
 * @property-read User $requester
 */
#[Fillable([
    'requested_by_id',
    'name',
    'status',
    'disk',
    'path',
    'manifest_path',
    'failure_reason',
    'completed_at',
])]
class DatasetExport extends Model
{
    /** @use HasFactory<DatasetExportFactory> */
    use HasFactory;

    protected $table = 'export_requests';

    /**
     * Get the project this export belongs to.
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user that requested the export.
     *
     * @return BelongsTo<User, $this>
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_id');
    }

    /**
     * Get the artifacts selected for the export.
     *
     * @return HasMany<ExportItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(ExportItem::class, 'export_request_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }
}
