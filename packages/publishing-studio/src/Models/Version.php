<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;
use Override;

/**
 * An immutable snapshot of the manifest of record IDs that comprise the
 * published state of the site at a point in time. Exactly one Version at a
 * time has `is_live = true`.
 *
 * @property int $id
 * @property string $uuid
 * @property int $number
 * @property string|null $name
 * @property string|null $notes
 * @property bool $is_live
 * @property array<string, array<int, int>> $manifest
 * @property int|null $source_workspace_id
 * @property int|null $rollback_of_version_id
 * @property string|null $published_by_type
 * @property int|null $published_by_id
 * @property CarbonImmutable|null $published_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Workspace|null $sourceWorkspace
 * @property-read Collection<int, PublishingRevision> $publishingRevisions
 */
class Version extends Model
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'number',
        'name',
        'notes',
        'is_live',
        'manifest',
        'source_workspace_id',
        'rollback_of_version_id',
        'published_by_type',
        'published_by_id',
        'published_at',
    ];

    public static function liveId(): ?int
    {
        return self::query()->withoutGlobalScopes()->where('is_live', true)->value('id');
    }

    public static function currentLive(): ?self
    {
        return self::query()->withoutGlobalScopes()->where('is_live', true)->first();
    }

    /**
     * @return BelongsTo<Workspace, $this>
     */
    public function sourceWorkspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class, 'source_workspace_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function publishedBy(): MorphTo
    {
        return $this->morphTo('published_by');
    }

    /** @return HasMany<PublishingRevision, $this> */
    public function publishingRevisions(): HasMany
    {
        return $this->hasMany(PublishingRevision::class);
    }

    /**
     * Return the list of IDs of a given model class included in this version's
     * manifest. Empty array if the class wasn't part of the manifest.
     *
     * @return array<int, int>
     */
    public function manifestIdsFor(string $modelClass): array
    {
        return $this->manifest[$modelClass] ?? [];
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'is_live' => 'boolean',
            'manifest' => 'array',
            'published_at' => 'immutable_datetime',
        ];
    }
}
