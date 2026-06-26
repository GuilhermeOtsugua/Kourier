<?php

namespace App\Models;

use Database\Factories\ExportItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $export_request_id
 * @property int $artifact_id
 * @property string $original_filename
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Artifact $artifact
 * @property-read DatasetExport $export
 */
#[Fillable(['artifact_id', 'original_filename'])]
class ExportItem extends Model
{
    /** @use HasFactory<ExportItemFactory> */
    use HasFactory;

    /**
     * Get the export request this item belongs to.
     *
     * @return BelongsTo<DatasetExport, $this>
     */
    public function export(): BelongsTo
    {
        return $this->belongsTo(DatasetExport::class, 'export_request_id');
    }

    /**
     * Get the artifact selected for export.
     *
     * @return BelongsTo<Artifact, $this>
     */
    public function artifact(): BelongsTo
    {
        return $this->belongsTo(Artifact::class);
    }
}
