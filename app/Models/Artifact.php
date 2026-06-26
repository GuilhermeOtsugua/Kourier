<?php

namespace App\Models;

use Database\Factories\ArtifactFactory;
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
 * @property int $uploaded_by_id
 * @property string $disk
 * @property string $path
 * @property string $original_filename
 * @property string|null $mime_type
 * @property int $size_bytes
 * @property string|null $checksum
 * @property string $processing_status
 * @property string $review_status
 * @property Carbon|null $reviewed_at
 * @property array<string, mixed>|null $preview_metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, ArtifactLabel> $labels
 * @property-read Project $project
 * @property-read User $uploader
 */
#[Fillable([
    'uploaded_by_id',
    'disk',
    'path',
    'original_filename',
    'mime_type',
    'size_bytes',
    'checksum',
    'processing_status',
    'review_status',
    'reviewed_at',
    'preview_metadata',
])]
class Artifact extends Model
{
    /** @use HasFactory<ArtifactFactory> */
    use HasFactory;

    /**
     * Get the project that owns the artifact.
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user that uploaded the artifact.
     *
     * @return BelongsTo<User, $this>
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }

    /**
     * Get labels attached to the artifact.
     *
     * @return HasMany<ArtifactLabel, $this>
     */
    public function labels(): HasMany
    {
        return $this->hasMany(ArtifactLabel::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'preview_metadata' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }
}
