<?php

declare(strict_types=1);

namespace Capell\LiveChat\Models;

use Capell\Core\Models\Site;
use Capell\LiveChat\Enums\KnowledgeSourceStatus;
use Capell\LiveChat\Enums\KnowledgeSourceType;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property int $id
 * @property int|null $site_id
 * @property KnowledgeSourceType $type
 * @property KnowledgeSourceStatus $status
 * @property string $source_key
 * @property string $title
 * @property string|null $url
 * @property string|null $content
 * @property string|null $content_hash
 * @property CarbonImmutable|null $last_synced_at
 * @property array<string, mixed>|null $metadata
 */
class LiveChatKnowledgeSource extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'content_hash',
        'content',
        'last_synced_at',
        'metadata',
        'site_id',
        'source_key',
        'status',
        'title',
        'type',
        'url',
    ];

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-live-chat.tables.knowledge_sources');

        return is_string($tableName) ? $tableName : 'live_chat_knowledge_sources';
    }

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * @return HasMany<LiveChatKnowledgeDocument, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(LiveChatKnowledgeDocument::class, 'source_id');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'last_synced_at' => 'immutable_datetime',
            'content' => 'encrypted',
            'metadata' => 'encrypted:array',
            'status' => KnowledgeSourceStatus::class,
            'type' => KnowledgeSourceType::class,
        ];
    }
}
