<?php

namespace App\Models;

use Database\Factories\ArtifactLabelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $artifact_id
 * @property int $created_by_id
 * @property string $key
 * @property string $value
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Artifact $artifact
 * @property-read User $creator
 */
#[Fillable(['created_by_id', 'key', 'value', 'notes'])]
class ArtifactLabel extends Model
{
    /** @use HasFactory<ArtifactLabelFactory> */
    use HasFactory;

    /**
     * Get the artifact this label describes.
     *
     * @return BelongsTo<Artifact, $this>
     */
    public function artifact(): BelongsTo
    {
        return $this->belongsTo(Artifact::class);
    }

    /**
     * Get the user that created the label.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}
