<?php

declare(strict_types=1);

namespace Capell\DocumentLifecycle\Models;

use Capell\DocumentLifecycle\Database\Factories\DocumentFactory;
use Capell\DocumentLifecycle\Enums\DocumentStatusEnum;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Override;

/**
 * @property int $id
 * @property string $key
 * @property string $title
 * @property DocumentStatusEnum $status
 * @property string|null $documentable_type
 * @property int|null $documentable_id
 * @property array<array-key, mixed>|null $metadata
 * @property CarbonImmutable|null $review_due_at
 * @property CarbonImmutable|null $expires_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read int|null $publications_count
 */
class Document extends Model
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $table = 'document_lifecycle_documents';

    protected $fillable = [
        'key',
        'title',
        'status',
        'documentable_type',
        'documentable_id',
        'metadata',
        'review_due_at',
        'expires_at',
    ];

    /**
     * @return MorphTo<Model, $this>
     */
    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return HasMany<DocumentPublication, $this>
     */
    public function publications(): HasMany
    {
        return $this->hasMany(DocumentPublication::class);
    }

    /**
     * @return HasMany<DocumentAcceptance, $this>
     */
    public function acceptances(): HasMany
    {
        return $this->hasMany(DocumentAcceptance::class, 'document_key', 'key');
    }

    public function latestPublication(): ?DocumentPublication
    {
        return $this->publications()
            ->latest('published_at')
            ->latest('id')
            ->first();
    }

    /**
     * @return DocumentFactory
     */
    protected static function newFactory(): Factory
    {
        return DocumentFactory::new();
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'status' => DocumentStatusEnum::class,
            'metadata' => 'array',
            'review_due_at' => 'immutable_datetime',
            'expires_at' => 'immutable_datetime',
        ];
    }
}
