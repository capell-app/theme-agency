<?php

declare(strict_types=1);

namespace Capell\LiveChat\Models;

use Capell\Core\Models\Site;
use Capell\LiveChat\Enums\KnowledgeSourceStatus;
use Capell\LiveChat\Enums\KnowledgeSourceType;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int|null $installation_id
 * @property int|null $source_id
 * @property int|null $site_id
 * @property KnowledgeSourceType $source_type
 * @property string $source_key
 * @property int $chunk_index
 * @property string $title
 * @property string|null $url
 * @property string $content
 * @property string $content_hash
 * @property KnowledgeSourceStatus $status
 * @property CarbonImmutable|null $last_synced_at
 * @property array<string, mixed>|null $metadata
 * @property-read LiveChatInstallation|null $installation
 * @property-read LiveChatKnowledgeSource|null $source
 * @property-read Site|null $site
 */
class LiveChatKnowledgeDocument extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'chunk_index',
        'content',
        'content_hash',
        'installation_id',
        'last_synced_at',
        'metadata',
        'site_id',
        'source_id',
        'source_key',
        'source_type',
        'status',
        'title',
        'url',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'chunk_index' => 0,
        'status' => 'active',
    ];

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-live-chat.tables.knowledge_documents');

        return is_string($tableName) ? $tableName : 'live_chat_knowledge_documents';
    }

    /**
     * @return BelongsTo<LiveChatInstallation, $this>
     */
    public function installation(): BelongsTo
    {
        return $this->belongsTo(LiveChatInstallation::class, 'installation_id');
    }

    /**
     * @return BelongsTo<LiveChatKnowledgeSource, $this>
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(LiveChatKnowledgeSource::class, 'source_id');
    }

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'chunk_index' => 'integer',
            'last_synced_at' => 'immutable_datetime',
            'metadata' => 'encrypted:array',
            'source_type' => KnowledgeSourceType::class,
            'status' => KnowledgeSourceStatus::class,
        ];
    }
}
