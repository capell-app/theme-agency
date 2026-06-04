<?php

declare(strict_types=1);

namespace Capell\DocumentLifecycle\Models;

use Capell\DocumentLifecycle\Database\Factories\DocumentPublicationFactory;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Override;

/**
 * @property int $id
 * @property int $document_id
 * @property int|null $published_revision_id
 * @property string $version_label
 * @property string $content_hash
 * @property string|null $published_actor_type
 * @property int|null $published_actor_id
 * @property CarbonImmutable $published_at
 * @property array<array-key, mixed>|null $metadata
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Document $document
 */
class DocumentPublication extends Model
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $table = 'document_lifecycle_publications';

    protected $fillable = [
        'document_id',
        'published_revision_id',
        'version_label',
        'content_hash',
        'published_actor_type',
        'published_actor_id',
        'published_at',
        'metadata',
    ];

    /**
     * @return BelongsTo<Document, $this>
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function publishedActor(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'published_actor_type', 'published_actor_id');
    }

    /**
     * @return DocumentPublicationFactory
     */
    protected static function newFactory(): Factory
    {
        return DocumentPublicationFactory::new();
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'published_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }
}
