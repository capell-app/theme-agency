<?php

declare(strict_types=1);

namespace Capell\DocumentLifecycle\Models;

use Capell\DocumentLifecycle\Database\Factories\DocumentAcceptanceFactory;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Override;

/**
 * Backward-compatible model for the existing legal_acceptances table.
 *
 * @property int $id
 * @property string|null $acceptor_type
 * @property int|null $acceptor_id
 * @property string|null $subject_type
 * @property int|null $subject_id
 * @property string $document_key
 * @property string $document_version
 * @property int|null $document_publication_id
 * @property string|null $document_hash
 * @property string|null $legal_bundle_version
 * @property string|null $legal_bundle_hash
 * @property array<array-key, mixed>|null $legal_document_versions
 * @property CarbonImmutable $accepted_at
 * @property string|null $context
 * @property string|null $ip_hash
 * @property string|null $user_agent_hash
 * @property array<array-key, mixed>|null $metadata
 */
class DocumentAcceptance extends Model
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $table = 'legal_acceptances';

    protected $fillable = [
        'acceptor_type',
        'acceptor_id',
        'subject_type',
        'subject_id',
        'document_key',
        'document_version',
        'document_publication_id',
        'document_hash',
        'legal_bundle_version',
        'legal_bundle_hash',
        'legal_document_versions',
        'accepted_at',
        'context',
        'ip_hash',
        'user_agent_hash',
        'metadata',
    ];

    /**
     * @return MorphTo<Model, $this>
     */
    public function acceptor(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<DocumentPublication, $this>
     */
    public function publication(): BelongsTo
    {
        return $this->belongsTo(DocumentPublication::class, 'document_publication_id');
    }

    /**
     * @return DocumentAcceptanceFactory
     */
    protected static function newFactory(): Factory
    {
        return DocumentAcceptanceFactory::new();
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'accepted_at' => 'immutable_datetime',
            'legal_document_versions' => 'array',
            'metadata' => 'array',
        ];
    }
}
