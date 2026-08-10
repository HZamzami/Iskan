<?php

namespace App\Models;

use App\Enums\WorkflowStatus;
use App\Models\Concerns\BelongsToDocumentType;
use App\Models\Concerns\HasReferenceNumber;
use App\Models\Concerns\HasWorkflow;
use App\Models\Concerns\LogsArchiveActivity;
use Database\Factories\GeoDocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeoDocument extends Model
{
    use BelongsToDocumentType;

    /** @use HasFactory<GeoDocumentFactory> */
    use HasFactory;

    use HasReferenceNumber;
    use HasWorkflow;
    use LogsArchiveActivity;

    protected $fillable = [
        'reference_number',
        'type',
        'sites',
        'title',
        'drawing_number',
        'document_date',
        'file_path',
        'notes',
        'created_by',
        'workflow_status',
        'assigned_to',
        'assigned_entity_type_id',
        'completed_at',
    ];

    public static function referencePrefix(): string
    {
        return 'خريطة';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sites' => 'array',
            'document_date' => 'date',
            'workflow_status' => WorkflowStatus::class,
            'completed_at' => 'datetime',
        ];
    }

    public static function documentTypeClass(): string
    {
        return GeoDocumentType::class;
    }
}
